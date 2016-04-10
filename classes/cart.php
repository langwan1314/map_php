<?php
/**
 * @copyright (c) 2015 crycoder.com
 * @file cart.php
 * @brief 购物车类库
 * @author windy
 * @date 2015-04-12
 * @version 0.6
 */

/**
 * @class Cart
 * @brief 购物车类库
 */
class Cart extends IInterceptorBase
{
	static $CART_GOODS_UNSELECTED = 0;
	static $CART_GOODS_SELECTED = 1;

	/*购物车简单cookie存储结构
	* array [goods]=>array(商品主键=>数量) , [product]=>array( 货品主键=>数量 )
	*/
	private $cartStruct = array( 'goods' => array() , 'product' => array() );

	/*购物车复杂存储结构
	* [id]   :array  商品id值;
	* [count]:int    商品数量;
	* [info] :array  商品信息 [goods]=>array( ['id']=>商品ID , ['data'] => array( [商品ID]=>array ( [sell_price]价格, [count]购物车中此商品的数量 ,[type]类型goods,product ,[goods_id]商品ID值 ) ) ) , [product]=>array( 同上 ) , [count]购物车商品和货品数量 , [sum]商品和货品总额 ;
	* [sum]  :int    商品总价格;
	*/
	private $cartExeStruct = array('goods' => array('id' => array(), 'data' => array() ),'product' => array( 'id' => array() , 'data' => array()),'count' => 0,'sum' => 0);

	//购物车名字前缀
	private $cartName    = 'cartkey';

	//购物车中最多容纳的数量
	private $maxCount    = 100;

	//错误信息
	private $error       = '';

	//购物车的存储方式
	private $saveType    = 'cookie';

	/**
	 * 为未登陆用户生成一个临时的cartkey
	 * @param $clientip 客户端ip地址
     * @return cartkey
	 */
    public static function generateCartKey($clientip=null)
    {
        if (!$clientip)
        {
            if (getenv('HTTP_CLIENT_IP'))
            {
                $clientip = getenv('HTTP_CLIENT_IP');
            }
            else if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $clientip = getenv('HTTP_X_FORWARDED_FOR');
            }
            else 
            {
                $clientip = getenv('REMOTE_ADDR');   
            }
        }

        $hashids = new Hashids\Hashids(WM::$app->config['encryptKey'], 32);
        return $hashids->encode(time() + abs(ip2long($clientip)));
    }

	/**
	 * 获取新加入购物车的数据
	 * @param $cartInfo cartStruct
	 * @param $gid 商品或者货品ID
	 * @param $num 数量
	 * @param $type goods 或者 product
	 * @param int $select 是否选中该商品
	 * @return bool|cartStruct
	 */
	public function getUpdateCartData($cartInfo, $gid, $num, $type, $select  = 1)
	{
		$gid = intval($gid);
		$num = intval($num);
		if($type != 'goods')
		{
			$type = 'product';
		}

		//获取基本的商品数据
		$goodsRow = $this->getGoodInfo($gid,$type);
		if($goodsRow)
		{
			//购物车中已经存在此类商品
			if(isset($cartInfo[$type][$gid]))
			{
                if ($cartInfo[$type][$gid]['num'] + $num < 0)
                {
                    $this->error = '购物车中该商品数量不足';
                    return false;
                }

				if($goodsRow['store_nums'] < $cartInfo[$type][$gid]['num'] + $num)
				{
					$this->error = '该商品库存不足';
					return false;
				}
				$cartInfo[$type][$gid]['num'] += $num;
				$cartInfo[$type][$gid]['select'] = $select;
			}

			//购物车中不存在此类商品
			else
			{
                if ($num <= 0)
                {
                    $this->error = '购物车中无此商品';
                    return false;
                }

				if($goodsRow['store_nums'] < $num)
				{
					$this->error = '该商品库存不足';
					return false;
				}

				$cartInfo[$type][$gid] = array('num'=>$num, 'select'=>$select);
			}

			return $cartInfo;
		}
		else
		{
			$this->error = '该商品库存不足';
			return false;
		}
	}

	/**
	 * @brief 将商品或者货品加入购物车
	 * @param $gid  商品或者货品ID值
	 * @param int|购买数量 $num 购买数量
	 * @param string|加入类型 $type 加入类型 goods商品; product:货品;
	 * @param int $select
	 * @return bool
	 */
	public function add($gid, $num = 1 ,$type = 'goods', $select = 1)
	{
		//购物车中已经存在此商品
		$cartInfo = $this->getMyCartStruct();

		if($this->getCartSort($cartInfo) >= $this->maxCount)
		{
			$this->error = '加入购物车失败,购物车中最多只能容纳'.$this->maxCount.'种商品';
			return false;
		}
		else
		{
			$cartInfo = $this->getUpdateCartData($cartInfo, $gid, $num, $type, $select);
			if($cartInfo === false)
			{
				return false;
			}
			else
			{
				return $this->setMyCart($cartInfo);
			}
		}
	}

	public function setCartGoodsUnselected($gid, $type)
	{
		$cartInfo = $this->getMyCartStruct();
		if (isset($cartInfo[$type][$gid])) {
			$cartInfo[$type][$gid]['select'] = self::$CART_GOODS_UNSELECTED;
		}

		//dump($cartInfo,1);
		$this->setMyCart($cartInfo);
	}

	/**
	 * @brief 将商品或者货品设置为选中态/非选中态
	 * @param $gid    商品或者货品ID值
	 * @param $select 是否选中 1:选中; 0:非选中;
	 * @param $type   类型 goods商品; product:货品;
	 * @return bool
	 */
	public function toggle($gid, $select, $type = 'goods')
	{
		//购物车中已经存在此商品
		$cartInfo = $this->getMyCartStruct();
		if($type != 'goods')
		{
			$type = 'product';
		}

        $cart_data = $cartInfo[$type];
        if (!isset($cartInfo[$type][$gid]))
        {
			$this->error = '购物车中没有此商品';
            return false;
        }

        $cartInfo[$type][$gid]['select'] = $select ? self::$CART_GOODS_SELECTED : self::$CART_GOODS_UNSELECTED;
        return $this->setMyCart($cartInfo);
	}

	public function toggle_all($select)
	{
        if ($select != self::$CART_GOODS_SELECTED && $select != self::$CART_GOODS_UNSELECTED)
        {
            return;
        }

		//购物车中已经存在此商品
		$cart_struct = $this->getMyCartStruct();

        foreach ($cart_struct as $type=>&$cart_data)
        {
            foreach ($cart_data as $gid=>&$cart_info)
            {
                $cart_info['select'] = $select;
            }
        }

        return $this->setMyCart($cart_struct);
	}

	//计算商品的种类
	private function getCartSort($mycart)
	{
		$sumSort   = 0;
		$sortArray = array('goods','product');

		foreach($sortArray as $sort)
		{
			$sumSort += count($mycart[$sort]);
		}
		return $sumSort;
	}

	//删除商品
	public function del($gid , $type = 'goods')
	{
		$cartInfo = $this->getMyCartStruct();
		if($type != 'goods')
		{
			$type = 'product';
		}

		//删除商品数据
		if(isset($cartInfo[$type][$gid]))
		{
			unset($cartInfo[$type][$gid]);
			$this->setMyCart($cartInfo);
		}
		else
		{
			$this->error = '购物车中没有此商品';
			return false;
		}
	}

	//根据 $gid 获取商品信息
	private function getGoodInfo($gid, $type = 'goods')
	{
		$dataArray = array();

		//商品方式
		if($type == 'goods')
		{
			$goodsObj  = new IModel('goods');
			$dataArray = $goodsObj->getObj('id = '.$gid.' and is_del = 0','id as goods_id,sell_price,store_nums');
			$dataArray['id'] = $dataArray['goods_id'];
		}

		//货品方式
		else
		{
			$productObj = new IQuery('products as pro , goods as go');
			$productObj->fields = ' go.id as goods_id , pro.sell_price , pro.store_nums ,pro.id ';
			$productObj->where  = ' pro.id = '.$gid.' and go.is_del = 0 and pro.goods_id = go.id';
			$productRow = $productObj->find();
			if($productRow)
			{
				$dataArray = $productRow[0];
			}
		}

		return $dataArray;
	}

	/**
	 * 登录后更新购物车至数据库
	 * @param $userId
	 * @return bool
	 */
	public function updateCartWhenUserLogin($userId)
	{
        $cookieCartInfo = $this->getCartByCookieOrSession();

		$cartInfo = $this->getCartByUserId($userId);

		//update goods in cart
		if (isset($cookieCartInfo['goods'])) {
			foreach	($cookieCartInfo['goods'] ?:array() as $goods_id => $goods_info) {
				$this->add($goods_id, $goods_info['num'], 'goods');
				$cartInfo = $this->getUpdateCartData($cartInfo, $goods_id, $goods_info['num'], 'goods');
			}
		}


		//update products in cart
		if (isset($cookieCartInfo['product'])) {
			foreach ($cookieCartInfo['product'] ?:array() as $product_id => $product_info) {
				$cartInfo = $this->getUpdateCartData($cartInfo, $goods_id, $goods_info['num'], 'product');
			}
		}

		$this->setCartForUser($cartInfo, $userId);
		return true;
	}

	/**
	 * 根据cookie | session 获取购物车信息
	 * @return max|mixed
	 */
	private function getCartByCookieOrSession()
	{
		$cartName = $this->getCartName();
		if($this->saveType == 'session')
		{
			$cartValue = ISession::get($cartName);
		}
		else
		{
			$cartValue = ICookie::get($cartName);
		}

		if ($cartValue)
		{
			$cartValue = JSON::decode(str_replace(array('&','$'),array('"',','),$cartValue));
		}

		return $cartValue;
	}

	/**
	 * 根据用户id获取用户购物车信息
	 * @param $userId
	 * @return mixed|null
	 */
	private function getCartByUserId($userId)
	{
		$goodsCatObj = new IModel('goods_car');
		$goodsCarRow = $goodsCatObj->getObj('user_id = '.$userId);

		if(!isset($goodsCarRow['content']))
		{
			$cartValue = null;
		}
		else
		{
			$cartValue = unserialize($goodsCarRow['content']);
		}

		return $cartValue;
	}

	/**
	 * 获取当前购物车信息
	 * @param bool $isIgnoreUnselectedGoods
	 * @return 获取cartStruct数据结构 是否需要忽略未选择的商品
	 * @internal param bool $isForceNotLoginCart 是否强制获取cookie｜session中的购物车信息（等同于未登录）
	 */
	public function getMyCartStruct($isIgnoreUnselectedGoods = false)
	{
        $user = WM::$app->getController()->user;

		$cartValue = null;
        if (!$user)
        {
			$cartValue = $this->getCartByCookieOrSession();
        }
        else
        {
           $cartValue = $this->getCartByUserId($user['user_id']);
        }

		if($cartValue == null)
		{
			return $this->cartStruct;
		}
		else
		{
			$cartValue = $isIgnoreUnselectedGoods ? $this->filterCartUnSelectedGoods($cartValue) : $cartValue;
			return $cartValue;
		}
	}

	/**
	 * 过滤掉构物车结构体中没有选择的商品
	 * @param $cartStruct
	 * @return array
	 */
	public function filterCartUnSelectedGoods($cartStruct)
	{
		//dump($cartStruct,1);
		$goods = $cartStruct['goods'] ?: array();
		$product = $cartStruct['product'] ?: array();

		$goods = array_filter($goods, function($item) use ($goods){
			return $item['select'] == self::$CART_GOODS_SELECTED ? true : false;
		});

		$product = array_filter($product,  function($item) use ($product) {
			return $item['select'] == self::$CART_GOODS_SELECTED ? true : false;
		});
		$cartStruct = array(
			'goods' => $goods ?: array(),
			'product' => $product ?: array()
		);
		return $cartStruct;
	}

	/**
	 * 获取当前购物车信息
	 * @param $isIgnoreUnselectedGoods
	 * @return 获取cartExeStruct数据结构
	 */
	public function getMyCart($isIgnoreUnselectedGoods=false)
	{
        $cartStruct = $this->getMyCartStruct($isIgnoreUnselectedGoods);
        return $this->cartFormat($cartStruct);
	}

	//清空购物车
	public function clear()
	{
        $user = WM::$app->getController()->user;
        if (!$user)
        {
            $cartName = $this->getCartName();
            if($this->saveType == 'session')
            {
                ISession::clear($cartName);
            }
            else
            {
                ICookie::clear($cartName);
            }
        }
        else
        {
	    	$goodsCarObj = new IModel('goods_car');
            $goodsCarObj->del('user_id = '.$user['user_id']);
        }
	}

	//订单生成后, 清空购物车中选中的商品
	public function clearSelected()
	{
        $user = WM::$app->getController()->user;
        if (!$user)
        {
            // 未登录用户暂时不能购买, 也不用清空了
            return;
        }

		$cartStruct = $this->getMyCartStruct();
        foreach ($cartStruct as $goods_type => &$cart_goods)
        {
            foreach ($cart_goods as $gid=>$goods)
            {
                if ($goods['select'] == self::$CART_GOODS_SELECTED)
                {
                    unset($cart_goods[$gid]);
                }
            }
        }
        
        $this->setMyCart($cartStruct);
	}

	//清空购物车拦截器 解决cookie header头延迟发送问题
	public static function onFinishAction()
	{
		$cartObj = new Cart();
		//$cartObj->clear();
		$cartObj->clearSelected();
	}

	/**
	 * 未登录用户设置购物车
	 * @param $goodsInfo
	 * @return bool
	 */
	private function setCartForSession($goodsInfo)
	{
		$goodsInfo = str_replace(array('"',','),array('&','$'),JSON::encode($goodsInfo));
		$cartName = $this->getCartName();
		if($this->saveType == 'session')
		{
			ISession::set($cartName,$goodsInfo);
		}
		else
		{
			ICookie::set($cartName,$goodsInfo,'7200');
		}

		return true;
	}

	/**
	 * 登录用户设置购物车
	 * @param $goodsInfo
	 * @param $userId
	 * @return bool
	 */
	private function setCartForUser($goodsInfo, $userId)
	{
		$dataArray = array(
			'user_id'     => $userId,
			'content'     => serialize($goodsInfo),
			'create_time' => ITime::getDateTime(),
		);

		$goodsCarObj = new IModel('goods_car');
		$goodsCarRow = $goodsCarObj->getObj('user_id = '.$userId);
		$goodsCarObj->setData($dataArray);

		if(empty($goodsCarRow))
		{
			$goodsCarObj->add();
		}
		else
		{
			$goodsCarObj->update('user_id = '.$userId);
		}

		return true;
	}

	/**
	 * 写入购物车
	 * @param $goodsInfo
	 * @return bool
	 */
	public function setMyCart ($goodsInfo)
	{
        $user = WM::$app->getController()->user;

        if (!$user)
        {
            $this->setCartForSession($goodsInfo);
        }
        else
        {
	    	$this->setCartForUser($goodsInfo, $user['user_id']);
        }
		return true;
	}

    public function cartUnFormat($cartFormat)
    {
        $cartStruct = $this->cartStruct;

        if (isset($cartFormat['goods']['data']))
        {
            foreach ($cartFormat['goods']['data'] as $gid=>$goods_data)
            {
                $cartStruct['goods'][$gid] = array('num'=>$goods_data['count'], 'select'=>$goods_data['select']);
            }
        }
        if (isset($cartFormat['product']['data']))
        {
            foreach ($cartFormat['product']['data'] as $pid=>$product_data)
            {
                $cartStruct['product'][$pid] = array('num'=>$product_data['count'], 'select'=>$product_data['select']);
            }
        }

        return $cartStruct;
    }

	/**
	 * @brief  转化成为程序所用的数据结构
	 * @param  $cartValue 购物车存储结构
	 * @return array : [goods]=>array( ['id']=>商品ID , ['data'] => array( [商品ID]=>array ([name]商品名称 , [img]图片地址 , [sell_price]价格, [count]购物车中此商品的数量 ,[type]类型goods,product , [goods_id]商品ID值 ) ) ) , [product]=>array( 同上 ) , [count]购物车商品和货品数量 , [sum]商品和货品总额 ;
	 */
	public function cartFormat($cartValue)
	{
		//初始化结果
		$result = $this->cartExeStruct;

		$goodsIdArray = array();

		if(isset($cartValue['goods']) && $cartValue['goods'])
		{
			$goodsIdArray = array_keys($cartValue['goods']);
			$result['goods']['id'] = $goodsIdArray;
			foreach($goodsIdArray as $gid)
			{
				$result['goods']['data'][$gid] = array(
					'id'       => $gid,
					'type'     => 'goods',
					'goods_id' => $gid,
					'count'    => $cartValue['goods'][$gid]['num'],
					'select'   => $cartValue['goods'][$gid]['select'],
				);

				//购物车中的种类数量累加
				$result['count'] += $cartValue['goods'][$gid]['num'];
			}
		}

		if(isset($cartValue['product']) && $cartValue['product'])
		{
			$productIdArray          = array_keys($cartValue['product']);
			$result['product']['id'] = $productIdArray;

			$productObj     = new IModel('products');
			$productData    = $productObj->query('id in ('.join(",",$productIdArray).')','id,goods_id,sell_price');
			foreach($productData as $proVal)
			{
				$result['product']['data'][$proVal['id']] = array(
					'id'         => $proVal['id'],
					'type'       => 'product',
					'goods_id'   => $proVal['goods_id'],
					'count'      => $cartValue['product'][$proVal['id']]['num'],
					'select'     => $cartValue['product'][$proVal['id']]['select'],
					'sell_price' => $proVal['sell_price'],
				);

				if(!in_array($proVal['goods_id'],$goodsIdArray))
				{
					$goodsIdArray[] = $proVal['goods_id'];
				}

				//购物车中的种类数量累加
				$result['count'] += $cartValue['product'][$proVal['id']]['num'];
			}
		}

		if($goodsIdArray)
		{
			$goodsArray = array();

			$goodsObj   = new IModel('goods');
			$goodsData  = $goodsObj->query('id in ('.join(",",$goodsIdArray).')','id,name,img,sell_price');
			foreach($goodsData as $goodsVal)
			{
				$goodsArray[$goodsVal['id']] = $goodsVal;
			}

			foreach($result['goods']['data'] as $key => $val)
			{
				if(isset($goodsArray[$val['goods_id']]))
				{
					$result['goods']['data'][$key]['img']        = Thumb::get($goodsArray[$val['goods_id']]['img'],120,120);
					$result['goods']['data'][$key]['name']       = $goodsArray[$val['goods_id']]['name'];
					$result['goods']['data'][$key]['sell_price'] = $goodsArray[$val['goods_id']]['sell_price'];

					//购物车中的金额累加
					$result['sum'] += $goodsArray[$val['goods_id']]['sell_price'] * $val['count'];
				}
			}

			foreach($result['product']['data'] as $key => $val)
			{
				if(isset($goodsArray[$val['goods_id']]))
				{
					$result['product']['data'][$key]['img']  = Thumb::get($goodsArray[$val['goods_id']]['img'],120,120);
					$result['product']['data'][$key]['name'] = $goodsArray[$val['goods_id']]['name'];

					//购物车中的金额累加
					$result['sum'] += $result['product']['data'][$key]['sell_price'] * $val['count'];
				}
			}
		}
		return $result;
	}

	//[私有]获取购物车名字
	private function getCartName()
	{
		return $this->cartName;
	}

	//获取错误信息
	public function getError()
	{
		return $this->error;
	}
}
