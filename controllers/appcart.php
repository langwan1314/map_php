<?php
/**
 * @file appcart.php
 * @brief
 * @author misty
 * @date 2015-10-11 22:33
 * @version 
 * @note
 */
/**
 * @brief app专用, 购物车
 * @class APPCart
 * @note
 */
class APPCart extends IAPPController
{
    public static $order_goods_thumb_width = 168;
    public static $order_goods_thumb_height = 168;

	function init()
	{
		CheckRights::checkAppUserRights();
	}
    function test()
    {
        echo YunUpload::YUN_UPLOAD_HOST;
    }

    //计算促销规则
    function _calc_promotion($cartStruct = null)
    {
		$cartObj = new Cart();

        if (!$cartStruct)
        {
            $cartStruct = $cartObj->getMyCartStruct();
        }

		$goodsArray  = array();
		$productArray= array();
    	foreach($cartStruct['goods'] as $goods_id => $goods_data)
    	{
            if ($goods_data['select'])
            {
                $goodsArray[$goods_id] = array(
                    'select' => $goods_data['select'],
                    'num' => $goods_data['num'],
                );
            }
    	}
    	foreach($cartStruct['product'] as $product_id => $product_data)
    	{
            if ($product_data['select'])
            {
                $productArray[$product_id] = array(
                    'select' => $product_data['select'],
                    'num' => $product_data['num'],
                );
            }
    	}

		$countSumObj = new CountSum();
        $buyInfo = $cartObj->cartFormat(array("goods" => $goodsArray,"product" => $productArray));

        $promptMessage = '获取成功';
		$calc = $countSumObj->goodsCount($buyInfo, '', '', $promptMessage);
        if ($calc)
        {
            foreach ($calc['goodsList'] as &$goods)
            {
                $goods['img'] = GoodsImage::get_order_goods_url($goods['img']);
            }

            return $calc;
        }

        return $promptMessage;
    }

    //商品加入购物车
    function join_cart()
    {
    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$goods_num = IReq::get('goods_num') === null ? 1 : intval(IReq::get('goods_num'));
		$type      = IFilter::act(IReq::get('type'));

        if (!$this->user)
        {
            return $this->output->set_result("NEED_LOGIN");
        }

        if ($goods_id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("商品id无效");
            return;
        }

		//加入购物车
    	$cartObj   = new Cart();
    	$addResult = $cartObj->add($goods_id,$goods_num,$type);

        if($addResult === false)
        {
            $this->output->set_result("ADD_GOODS_CART");
            $this->output->set_prompt($cartObj->getError());
            return;
        }

		$need_calc = IFilter::act(IReq::get('need_calc'), 'int');
        if ($need_calc)
        {
            $calc = $this->_calc_promotion();
            if (is_string($calc))
            {
                $this->output->set_result("CART_CALC_PROMOTION");
                $this->output->set_prompt($calc);
                return;
            }
            $this->output->set_data($calc);
        }

        $this->output->set_result("SUCCESS");
        return;
    }

    //从购物车中删除商品
    function remove_cart()
    {
        if (!$this->user)
        {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$type      = IFilter::act(IReq::get('type'));

    	$cartObj   = new Cart();
    	$cartInfo  = $cartObj->getMyCart();
    	$delResult = $cartObj->del($goods_id,$type);

        if($delResult === false)
        {
            $this->output->set_result("DEL_GOODS_CART");
            $this->output->set_prompt($cartObj->getError());
            return;
        }

        $goodsRow = $cartInfo[$type]['data'][$goods_id];
        $cartInfo['sum']   -= $goodsRow['sell_price'] * $goodsRow['count'];
        $cartInfo['count'] -= $goodsRow['count'];

        unset($cartInfo[$type]['data'][$goods_id]);

        $cart_array = array_merge($cartInfo['goods']['data'],$cartInfo['product']['data']);
        $this->fill_cart_spec($cart_array);
    	$data['data'] = $cart_array;
        $data['count']= $cartInfo['count'];
        $data['sum']  = $cartInfo['sum'];

        foreach ($data['data'] as &$cart_data)
        {
            $cart_data['img'] = GoodsImage::$site_url_host . '/' . $cart_data['img'];
        }

		$need_calc = IFilter::act(IReq::get('need_calc'), 'int');
        if ($need_calc)
        {
            $calc = $this->_calc_promotion();
            if (is_string($calc))
            {
                $this->output->set_result("CART_CALC_PROMOTION");
                $this->output->set_prompt($calc);
                return;
            }
            $data['calc'] = $calc;
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($data);
        return;
    }

    //清空购物车
    function clear_cart()
    {
        if (!$this->user)
        {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

    	$cartObj = new Cart();
    	$cartObj->clear();

        $this->output->set_result("SUCCESS");
        return;
    }

    //设置物品选中
    function toggle_cart()
    {
        if (!$this->user)
        {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

    	$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$type     = IFilter::act(IReq::get('type'));
		$select   = IFilter::act(IReq::get('select'));

        $cartObj = new cart();
        $result = $cartObj->toggle($goods_id, $select, $type);
        if ($result === false)
        {
            $this->output->set_result("CART_GOODS_TOGGLE");
            $this->output->set_prompt($cartObj->getError());
            return;
        }

		$need_calc = IFilter::act(IReq::get('need_calc'), 'int');
        if ($need_calc)
        {
            $calc = $this->_calc_promotion();
            if (is_string($calc))
            {
                $this->output->set_result("CART_CALC_PROMOTION");
                $this->output->set_prompt($calc);
                return;
            }
            $this->output->set_data($calc);
        }

        $this->output->set_result("SUCCESS");
        return;
    }

    //设置物品选中
    function toggle_all()
    {
        if (!$this->user)
        {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

		$select   = IFilter::act(IReq::get('select'));

        $cartObj = new cart();
        $result = $cartObj->toggle_all($select);
        if ($result === false)
        {
            $this->output->set_result("CART_GOODS_TOGGLE");
            $this->output->set_prompt($cartObj->getError());
            return;
        }

		$need_calc = IFilter::act(IReq::get('need_calc'), 'int');
        if ($need_calc)
        {
            $calc = $this->_calc_promotion();
            if (is_string($calc))
            {
                $this->output->set_result("CART_CALC_PROMOTION");
                $this->output->set_prompt($calc);
                return;
            }
            $this->output->set_data($calc);
        }

        $this->output->set_result("SUCCESS");
        return;
    }

    function fill_cart_spec(&$cart_list)
    {
        $product_ids = [];
        foreach ($cart_list as $cart_data)
        {
            if ($cart_data['type'] == "product")
            {
                $product_ids[] = $cart_data['id'];
            }
        }

        $product_spec_array = array();
        if (!empty($product_ids))
        {
            $product_model = new IQuery('products');
            $product_model->where = "id in (". join(',', $product_ids) .")";
            $product_model->fields = "id, spec_array";
            $product_list = $product_model->find();

            foreach ($product_list as $product_data)        
            {
                $product_spec_array[$product_data['id']] = $product_data['spec_array'];
            }
        }

        foreach ($cart_list as &$cart_data)
        {
            if ($cart_data['type'] == "product" && isset($product_spec_array[$cart_data['id']]))
            {
                $cart_data['spec_array'] = JSON::decode($product_spec_array[$cart_data['id']]);
            }
            else
            {
                $cart_data['spec_array'] = [];
            }
        }
    }

    //购物车展示
    function show_cart()
    {
        if (!$this->user)
        {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

    	$cartObj  = new Cart();
    	$cartList = $cartObj->getMyCart();

        $cart_array = array_merge($cartList['goods']['data'],$cartList['product']['data']);
        $this->fill_cart_spec($cart_array);
    	$data['data'] = $cart_array;
    	$data['count']= $cartList['count'];
    	$data['sum']  = $cartList['sum'];

        $select_all = 1;
        foreach ($data['data'] as &$cart_data)
        {
            $cart_data['img'] = GoodsImage::get_order_goods_url($cart_data['img']);
            if ($cart_data['select'] != 1)
            {
                $select_all = 0;
            }
        }
        
        $data['select_all'] = $select_all;
        $this->output->set_result("SUCCESS");
        $this->output->set_data($data);
        return;
    }

    //根据goods_id获取货品
    function get_products()
    {
    	$goods_id   = IFilter::act(IReq::get('goods_id'),'int');
    	$productObj = new IModel('products');

        // 真是的, 7 又是个什么意思... 这什么框架!
    	$products = $productObj->query('goods_id = '.$goods_id,'sell_price,id,spec_array,goods_id','store_nums','desc',7);
		if($products !== null)
		{
			foreach($products as $key => &$val)
			{
				$val['spec_data'] = APPCommon::show_spec($val['spec_array']);
			}
            $this->output->set_result("SUCCESS");
            $this->output->set_data($products);
            return;
		}
        else
        {
            $this->output->set_result("GET_GOODS_PRODUCT");
            $this->output->set_prompt("拉取货品失败");
            return;
        }
    }

    //填写订单信息order_prepare
    function order_prepare()
    {
		if(!$this->user)
		{
            return $this->output->set_result("NEED_LOGIN");
		}

		$id  = IFilter::act(IReq::get('id'),'int');
		$type      = IFilter::act(IReq::get('type'));//goods,product
		$promo     = IFilter::act(IReq::get('promo'));
		$active_id = IFilter::act(IReq::get('active_id'),'int');
		$buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'),'int') : 1;

		//游客的user_id默认为0
    	$user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

		//计算商品
		$countSumObj = new CountSum($user_id);
		$result = $countSumObj->cart_count($id,$type,$buy_num,$promo,$active_id);

		//检查商品合法性或促销活动等有错误
		if(is_string($result))
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt($result);
            return;
		}

    	//获取收货地址
    	$addressObj  = new IModel('address');
    	$addressList = $addressObj->query('user_id = '.$user_id);

		//更新$addressList数据
        $address = json_decode('{}');
        $default_address_key = False;
    	foreach($addressList as $key => $val)
    	{
            if (intval($val['default']) == 1)
            {
                $default_address_key = $key;
                break;
            }
        }
        if ($default_address_key === False && $addressList)
        {
            $default_address_key = 0;
        }

        if ($default_address_key !== False)
        {
            $address = $addressList[$default_address_key];
            $address['country'] = $address['country'] ? $address['country'] : '';

            $address['telephone'] = $address['telphone'];
            unset($address['telphone']);
            unset($address['user_id']);

            $temp = area::name($address['province'], $address['city'], $address['area']);
            if(isset($temp[$address['province']]) && isset($temp[$address['city']]) && isset($temp[$address['area']]))
    		{
	    		$address['province_name'] = $temp[$address['province']];
	    		$address['city_name']     = $temp[$address['city']];
	    		$address['area_name']     = $temp[$address['area']];
    		}
    	}

		//获取用户的道具红包和用户的习惯方式
		$prop = array();
		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('user_id = '.$user_id,'prop,custom');

		if(isset($memberRow['prop']) && ($propId = trim($memberRow['prop'],',')))
		{
			$porpObj = new IModel('prop');
			$prop = $porpObj->query('id in ('.$propId.') and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1');
		}

		if(isset($memberRow['custom']) && $memberRow['custom'])
		{
			$custom = unserialize($memberRow['custom']);
		}
		else
		{
			$custom = array(
				'payment'  => '',  // 支付方式
				'delivery' => '',  // 配送方式
				'takeself' => '',  // 自提点设置
			);
		}

        if ($result['seller'] && ! empty($result['seller']))
        {
            //获取所属商家
            $sellerObj = new IModel('seller');
            $sellerList= $sellerObj->query("id in (".join(",",array_keys($result['seller'])).")");
            if(isset($result['seller'][0]))
            {
                array_unshift($sellerList,array("id" => 0, "true_name" => "商城自营"));
            }
        }
        else
        {
            $sellerList = [];
        }

        foreach ($result['goodsList'] as &$goods)
        {
            $goods['img'] = GoodsImage::get_order_goods_url($goods['img']);
        }

        $this->output->set_data(array(
            'id' => $id, 
            'type' => $type, 
            'num' => $buy_num, 
            'promo' => $promo, 
            'active_id' => $active_id, 
            'final_sum' => $result['final_sum'],  // 优惠后商品总i金额
            'promotion' => $result['promotion'],  // 满足的优惠活动 
            'proReduce' => $result['proReduce'], 
            'sum' => $result['sum'], 
            'goodsList' => $result['goodsList'], 
            'count' => $result['count'], 
            'reduce' => $result['reduce'],        // 优惠总金额 
            'weight' => $result['weight'], 
            'freeFreight' => $result['freeFreight'], 
            'seller' => $result['seller'], 
            'address' => $address, 
            'goodsTax' => $result['tax'], 
            'sellerList' => $sellerList, 
            'prop' => $prop, 
            'custom' => $custom, 
        ));
        $this->output->set_result("SUCCESS");
    }

	/**
	 * 生成订单
	 */
    function order_create()
    {
		if(!$this->user)
		{
            return $this->output->set_result("NEED_LOGIN");
		}
        $user_id = $this->user['user_id'];

    	$address_id    = IFilter::act(IReq::get('address_id'));
    	$accept_time   = IFilter::act(IReq::get('accept_time'));
    	$message       = IFilter::act(IReq::get('order_message'));
    	$need_invoice  = IFilter::act(IReq::get('need_invoice'),'int');
    	$tax_title     = IFilter::act(IReq::get('tax_title'));
        $order_time    = IFilter::act(IReq::get('order_time'), 'int');

        // 以下参数暂时未对 app 提供了
    	$delivery_id   = IFilter::act(IReq::get('delivery_id'),'int');
    	$ticket_id     = IFilter::act(IReq::get('ticket_id'),'int');
    	$gid           = IFilter::act(IReq::get('direct_goods_id'),'int');
    	$num           = IFilter::act(IReq::get('direct_num'),'int');
    	$type          = IFilter::act(IReq::get('direct_type'));//商品或者货品
    	$promo         = IFilter::act(IReq::get('direct_promo'));
    	$active_id     = IFilter::act(IReq::get('direct_active_id'),'int');
    	$takeself      = IFilter::act(IReq::get('takeself'),'int');
    	$ticketUserd   = IFilter::act(IReq::get('ticket_userd'),'int');

    	$promo_type    = 0;
    	$dataArray     = array();

		//防止表单重复提交
    	if($order_time)
    	{
    		if(IFilter::act(ISafe::get('otime_'.$order_time, 'redis'), 'int') == $order_time)
    		{
                $this->output->set_result('DUPLICATE_REQUEST');
                $this->output->set_prompt('订单数据不能被重复提交');
                return;
    		}
    		else
    		{
                // 简单实现, 不用cookies, 10S后取消限制
    			ISafe::set('otime_'.$order_time, $order_time, 10);
    		}
    	}

        if ($address_id <= 0)
        {
            $this->output->set_result('PARAM_INVALID');
            $this->output->set_prompt('请选择收货地址');
            return;
        }

        $addressObj = new IModel('address');
        $addressRow = $addressObj->getObj('user_id = '.$user_id.' and `id` = ' . $address_id);
        if(!$addressRow)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt('不存在的收货地址，请重新选择');
            return;
        }

    	$accept_name = $addressRow['accept_name'];
        $province = $addressRow['province'];
        $city = $addressRow['city'];
        $area = $addressRow['area'];
        $address = $addressRow['address'];
    	$mobile = $addressRow['mobile'];
    	$telphone = $addressRow['telphone'];
    	$zip = $addressRow['zip'];

        if ($province != 440000 || $city != 440300)
        {
            // 目前只支持配送到广东深圳
            $this->output->set_result('ADDRESS_NOT_SERVED');
            return;
        }

        $delivery_id = 1;   # TODO: 暂时定死了, 第三方物流公司配送
    	if($delivery_id == 0)
    	{
            $this->output->set_result('PARAM_INVALID');
            $this->output->set_prompt('请选择配送方式');
            return;
    	}
		//配送方式,判断是否为货到付款
		$deliveryObj = new IModel('delivery');
		$deliveryRow = $deliveryObj->getObj('id = '.$delivery_id);
        $takeself = 0;   # 暂时不支持自提, 先设置为0

		//计算费用
    	$countSumObj = new CountSum($user_id);
		$goodsResult = $countSumObj->cart_count($gid,$type,$num,$promo,$active_id);

    	//判断商品是否存在
    	if(is_string($goodsResult) || empty($goodsResult['goodsList']))
    	{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt('购物车中没有商品，请先添加');
            return;
    	}

    	//加入促销活动
    	if($promo && $active_id)
    	{
    		$activeObject = new Active($promo,$active_id,$user_id,$gid,$type,$num);
    		$promo_type = $activeObject->getOrderType();
    	}

        $payment = 12;
		$paymentObj = new IModel('payment');
		$paymentRow = $paymentObj->getObj('id = '.$payment,'type,name');
		$paymentName= $paymentRow['name'];
		$paymentType= $paymentRow['type'];

		//最终订单金额计算
		$orderData = $countSumObj->countOrderFee($goodsResult,$province,$delivery_id,$payment,$need_invoice,0,$promo,$active_id);
		if(is_string($orderData))
		{
            $this->output->set_result("COUNT_ORDER_FEE");
            $this->output->set_prompt($orderData);
            return;
		}

		//根据商品所属商家不同批量生成订单
		$orderIdArray  = array();
		$orderNumArray = array();
		$final_sum     = 0;
		foreach($orderData as $seller_id => $goodsResult)
		{
			//生成的订单数据
			$dataArray = array(
				'order_no'            => Order_Class::createOrderNum(),
				'user_id'             => $user_id,
				'accept_name'         => $accept_name,
				'pay_type'            => $payment,
				'distribution'        => $delivery_id,
				'postcode'            => $zip,
				'telphone'            => $telphone,
				'province'            => $province,
				'city'                => $city,
				'area'                => $area,
				'address'             => $address,
				'mobile'              => $mobile,
				'create_time'         => ITime::getDateTime(),
				'postscript'          => $message,
				'accept_time'         => $accept_time,
				'exp'                 => $goodsResult['exp'],
				'point'               => $goodsResult['point'],
				'type'                => $promo_type,

				//商品价格
				'payable_amount'      => $goodsResult['sum'],
				'real_amount'         => $goodsResult['final_sum'],

				//运费价格
				'payable_freight'     => $goodsResult['deliveryOrigPrice'],
				'real_freight'        => $goodsResult['deliveryPrice'],

				//手续费
				'pay_fee'             => $goodsResult['paymentPrice'],

				//税金
				'invoice'             => $need_invoice,
				'invoice_title'       => $tax_title,
				'taxes'               => $goodsResult['taxPrice'],

				//优惠价格
				'promotions'          => $goodsResult['proReduce'] + $goodsResult['reduce'],

				//订单应付总额
				'order_amount'        => $goodsResult['orderAmountPrice'],

				//订单保价
				'insured'             => $goodsResult['insuredPrice'],

				//自提点ID
				'takeself'            => $takeself,

				//促销活动ID
				'active_id'           => $active_id,

				//商家ID
				'seller_id'           => $seller_id,

				//备注信息
				'note'                => '',
			);

			//获取红包减免金额
			if($ticket_id && $ticketUserd == $seller_id)
			{
				$memberObj = new IModel('member');
				$memberRow = $memberObj->getObj('user_id = '.$user_id,'prop,custom');

				//游客手动添加或注册用户道具中已有的代金券
                if(ISafe::get('ticket_'.$ticket_id) == $ticket_id ||
                    ($memberRow && stripos(','.trim($memberRow['prop'],',').',',','.$ticket_id.',') !== false))
				{
					$propObj   = new IModel('prop');
					$ticketRow = $propObj->getObj('id = '.$ticket_id.' and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1');
					if(!$ticketRow)
					{
						$this->output->set_result('VOUCHERS_INVALID');
                        $this->output->set_prompt('代金券不可用');
                        return;
					}

					if($ticketRow['seller_id'] == 0 || $ticketRow['seller_id'] == $seller_id)
					{
						$ticketRow['value']         = $ticketRow['value'] >= $goodsResult['final_sum'] ? $goodsResult['final_sum'] : $ticketRow['value'];
						$dataArray['prop']          = $ticket_id;
						$dataArray['promotions']   += $ticketRow['value'];
						$dataArray['order_amount'] -= $ticketRow['value'];
						$goodsResult['promotion'][] = array("plan" => "代金券","info" => "使用了￥".$ticketRow['value']."代金券");

						//锁定红包状态
						$propObj->setData(array('is_close' => 2));
						$propObj->update('id = '.$ticket_id);
					}
				}
			}

			//促销规则
			if(isset($goodsResult['promotion']) && $goodsResult['promotion'])
			{
				foreach($goodsResult['promotion'] as $key => $val)
				{
					$dataArray['note'] .= " 【".$val['info']."】 ";
				}
			}

			$dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'];

			//生成订单插入order表中
			$orderObj = new IModel('order');
			$orderObj->setData($dataArray);
			$order_id = $orderObj->add();

			if($order_id == false)
			{
                $this->output->set_result('CREATE_ORDER');
                $this->output->set_prompt('订单生成错误');
                return;
			}

			/*将订单中的商品插入到order_goods表*/
	    	$orderInstance = new Order_Class();
	    	$orderInstance->insertOrderGoods($order_id,$goodsResult['goodsResult']);

			//订单金额小于等于0直接免单
			if($dataArray['order_amount'] <= 0)
			{
				Order_Class::updateOrderStatus($dataArray['order_no']);
			}
			else
			{
				$orderIdArray[]  = $order_id;
				$orderNumArray[] = $dataArray['order_no'];
				$final_sum      += $dataArray['order_amount'];
			}
		}

		if(!$gid)
		{
			//清空购物车
			IInterceptor::reg("cart@onFinishAction");
		}


		//记录用户默认习惯的数据
		if(!isset($memberRow['custom']))
		{
			$memberObj = new IModel('member');
			$memberRow = $memberObj->getObj('user_id = '.$user_id,'custom');
		}

		$memberData = array(
			'custom' => serialize(
				array(
					'payment'  => $payment,
					'delivery' => $delivery_id,
					'takeself' => $takeself,
				)
			),
		);
		$memberObj->setData($memberData);
		$memberObj->update('user_id = '.$user_id);

		//收货地址的处理
		if($user_id)
		{
			$addressObj = new IModel('address');
			$addressDefRow = $addressObj->getObj('user_id = '.$user_id.' and `default` = 1');
			if(!$addressDefRow)
			{
				$address_id = IFilter::act(IReq::get('address_id'),'int');
				$addressObj->setData(array('default' => 1));
				$addressObj->update('user_id = '.$user_id.' and id = '.$address_id);
			}
		}

		//获取备货时间
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$stockup_hour  = isset($site_config['stockup_time'])?$site_config['stockup_time']:4;

        $order_result = array(
            'order_id'     => join(",", $orderIdArray),
            'final_sum'    => $final_sum,
            'final_no'     => join(",",$orderNumArray),
            'payment'      => $paymentName,
            'paymentType'  => $paymentType,
            'delivery'     => $deliveryRow['name'],
            'tax_title'    => $tax_title,
            'deliveryType' => $deliveryRow['type'],
            'stockup_hour' => $stockup_hour,
            'accept_name'  => $accept_name,
            'accept_time'  => $accept_time,
            'mobile'       => $mobile,
            'is_complete'  => 0,
        );

		//订单金额为0时，订单自动完成
		if($final_sum <= 0)
		{
            $order_result['is_complete'] = 1;
            $this->output->set_result("SUCCESS");
			$this->output->set_prompt('订单确认成功，等待发货');
			$this->output->set_data($order_result);
            return;
		}
		else
		{
            $this->output->set_result("SUCCESS");
			$this->output->set_data($order_result);
            return;
		}
    }

    //到货通知处理动作
	function arrival_notice()
	{
		$user_id  = IFilter::act($this->user['user_id'],'int');
		$email    = IFilter::act(IReq::get('email'));
		$mobile   = IFilter::act(IReq::get('mobile'));
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$register_time = date('Y-m-d H:i:s');

		if(!$goods_id)
		{
            $this->output->set_result("GOODS_NOT_EXISTS");
            return;
		}

		$model = new IModel('notify_registry');
		$obj = $model->getObj("email = '{$email}' and user_id = '{$user_id}' and goods_id = '$goods_id'");
		if(empty($obj))
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time));
			$model->add();
		}
		else
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time,'notify_status'=>0));
			$model->update('id = '.$obj['id']);
		}

        $this->output->set_result("SUCCESS");
	}
}
