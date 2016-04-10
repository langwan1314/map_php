<?php
/**
 * @file appproducts.php
 * @brief
 * @author misty
 * @date 2015-10-14 01:47
 * @version 
 * @note
 */
/**
 * @brief app专用, 商品详情页数据
 * @class APPProducts
 * @note
 */
class APPProducts extends IAPPController
{
	function init()
	{
		CheckRights::checkAppUserRights();
	}

    function _filter_detail(&$result)
    {
        $result['img'] = GoodsImage::get_detail_goods_url($result['img']);
        $result['ad_img'] = $result['ad_img'] == null ? '' : $result['ad_img'];

        // 处理属性值
        if ($result['spec_array'])
        {
            $specs = array();
            $spec_array = JSON::decode($result['spec_array']);
            foreach($spec_array as $spec)
            {
                $spec_value = explode(',', trim($spec['value']));
                $specs[] = array('id'=>$spec['id'], 'name'=>$spec['name'], 'type'=>$spec['type'], 'value'=>$spec_value);
            }

            $result['spec_array'] = $specs;
        }
        else
        {
            $result['spec_array'] = array();
        }

        // 商品详情走 H5 页面, 不返回 content 信息了
        $result['show_url'] = GoodsImage::$site_url_host . '/h5/product/id/' . $result['id'];
        unset($result['content']);

        unset($result['is_del']);
        unset($result['up_time']);
        unset($result['down_time']);
        unset($result['create_time']);
        unset($result['cost_price']);
    }

	//商品展示
	function detail()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		if(!$goods_id)
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("缺少商品ID");
            return;
		}

		//使用商品id获得商品信息
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
		if(!$goods_info)
		{
            $this->output->set_result("GOODS_NOT_EXISTS");
            $this->output->set_prompt("该商品不存在");
            return;
		}

		//品牌名称
		if($goods_info['brand_id'])
		{
			$tb_brand = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
			if($brand_info)
			{
				$goods_info['brand'] = $brand_info['name'];
			}
		}

		//获取商品分类
		$categoryObj = new IModel('category_extend as ca,category as c');
		$categoryRow = $categoryObj->getObj('ca.goods_id = '.$goods_id.' and ca.category_id = c.id','c.id,c.name');
		$goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;

		//商品图片
		$tb_goods_photo = new IQuery('goods_photo_relation as g');
		$tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
		$tb_goods_photo->join = 'left join goods_photo as p on p.id=g.photo_id ';
		$tb_goods_photo->where =' g.goods_id='.$goods_id;
		$goods_info['photo'] = $tb_goods_photo->find();
		foreach($goods_info['photo'] as $key => $val)
		{
			//对默认第一张图片位置进行前置
			if($val['img'] == $goods_info['img'])
			{
				$temp = $goods_info['photo'][0];
				$goods_info['photo'][0] = $val;
				$goods_info['photo'][$key] = $temp;
			}
		}

		//商品是否参加促销活动(团购，抢购)
		$goods_info['promo']     = IReq::get('promo')     ? IReq::get('promo') : '';
		$goods_info['active_id'] = IReq::get('active_id') ? IFilter::act(IReq::get('active_id'),'int') : '';
		if($goods_info['promo'])
		{
			switch($goods_info['promo'])
			{
				//团购
				case 'groupon':
				{
					$goods_info['regiment'] = Api::run("getRegimentRowById",array("#id#",$goods_info['active_id']));
					if(isset($goods_info['regiment']['goods_id']) && $goods_info['regiment']['goods_id'] != $goods_id)
					{
                        $this->output->set_result("PRODUCT_NOT_IN_ACTIVE");
                        $this->output->set_prompt("该商品未参与活动");
                        return;
					}
				}
				break;

				//抢购
				case 'time':
				{
					$goods_info['promotion'] = Api::run("getPromotionRowById",array("#id#",$goods_info['active_id']));
					if(isset($goods_info['regiment']['goods_id']) && $goods_info['promotion']['condition'] != $goods_id)
					{
                        $this->output->set_result("PRODUCT_NOT_IN_ACTIVE");
                        $this->output->set_prompt("该商品未参与活动");
                        return;
					}
				}
				break;

				default:
				{
                    $this->output->set_result("PRODUCT_NOT_IN_ACTIVE");
                    $this->output->set_prompt("活动不存在或者已经过期");
                    return;
				}
			}
		}

		//获得扩展属性
		$tb_attribute_goods = new IQuery('goods_attribute as g');
		$tb_attribute_goods->join  = 'left join attribute as a on a.id=g.attribute_id ';
		$tb_attribute_goods->fields=' a.name,g.attribute_value ';
		$tb_attribute_goods->where = "goods_id='".$goods_id."' and attribute_id!=''";
		$tb_attribute_goods->order = "g.id asc";
		$goods_info['attribute'] = $tb_attribute_goods->find();

		//[数据挖掘]最终购买此商品的用户ID列表
		$tb_good = new IQuery('order_goods as og');
		$tb_good->join   = 'left join order as o on og.order_id=o.id ';
		$tb_good->fields = 'DISTINCT o.user_id';
		$tb_good->where  = 'og.goods_id = '.$goods_id;
		$tb_good->limit  = 5;
		$bugGoodInfo = $tb_good->find();
		if($bugGoodInfo)
		{
			$shop_goods_array = array();
			foreach($bugGoodInfo as $key => $val)
			{
				$shop_goods_array[] = $val['user_id'];
			}
			$goods_info['buyer_id'] = join(',',$shop_goods_array);
		}

		//购买记录
		$tb_shop = new IQuery('order_goods as og');
		$tb_shop->join = 'left join order as o on o.id=og.order_id';
		$tb_shop->fields = 'count(*) as totalNum';
		$tb_shop->where = 'og.goods_id='.$goods_id.' and o.status = 5';
		$shop_info = $tb_shop->find();
		$goods_info['buy_num'] = 0;
		if($shop_info)
		{
			$goods_info['buy_num'] = $shop_info[0]['totalNum'];
		}

		//购买前咨询
		$tb_refer    = new IModel('refer');
		$refeer_info = $tb_refer->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['refer'] = 0;
		if($refeer_info)
		{
			$goods_info['refer'] = $refeer_info['totalNum'];
		}

		//网友讨论
		$tb_discussion = new IModel('discussion');
		$discussion_info = $tb_discussion->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['discussion'] = 0;
		if($discussion_info)
		{
			$goods_info['discussion'] = $discussion_info['totalNum'];
		}

		//获得商品的货品及价格区间
		$tb_product = new IModel('products');
        $products_info = $tb_product->query('goods_id='.$goods_id);

        $goods_info['products'] = [];
        $goods_info['minSellPrice'] = '';
        $goods_info['maxSellPrice'] = '';
        $goods_info['minMarketPrice'] = '';
        $goods_info['maxMarketPrice'] = '';

        $is_recommend = 0;
        foreach ($products_info as $product_info)
        {
            $goods_info['minSellPrice'] = ($goods_info['minSellPrice'] == '' ? $product_info['sell_price'] : 
                min($goods_info['minSellPrice'], $product_info['sell_price']));
            $goods_info['minMarketPrice'] = ($goods_info['minMarketPrice'] == '' ? $product_info['market_price'] : 
                min($goods_info['minMarketPrice'], $product_info['market_price']));
            $goods_info['maxSellPrice'] = max($goods_info['maxSellPrice'], $product_info['sell_price']);
            $goods_info['maxMarketPrice'] = max($goods_info['maxMarketPrice'], $product_info['market_price']);

            $arr_product = array(
                'id' => $product_info['id'],
                'products_no' => $product_info['products_no'],
                'spec_array' => JSON::decode($product_info['spec_array']),
                'store_nums' => $product_info['store_nums'],
                'market_price' => $product_info['market_price'],
                'sell_price' => $product_info['sell_price'],
                'weight' => $product_info['weight'],
                'is_recommend' => 0,
            );

            if ($is_recommend == 0)
            {
                // 现在没有推荐算法, 默认推荐第一个
                $is_recommend = 1;
                $arr_product['is_recommend'] = 1;
            }

            $goods_info['products'][] = $arr_product;
        }

		//获得会员价
		$countsumInstance = new countsum();
		$goods_info['group_price'] = $countsumInstance->getGroupPrice($goods_id,'goods');
        $goods_info['group_price'] = $goods_info['group_price'] ? $goods_info['group_price'] : '';

		//获取商家信息
		if($goods_info['seller_id'])
		{
			$sellerDB = new IModel('seller');
			$goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
		}

        $goods_info['favorite_id'] = 0;
        if ($this->user)
        {
            //获取收藏信息
            $favoriteDB = new IModel('favorite');
            $user_favorite = $favoriteDB->getObj('user_id = '.$this->user['user_id'] . ' and rid = '.$goods_id);
            if ($user_favorite)
            {
                $goods_info['favorite_id'] = $user_favorite['id'];
            }
        }

		//增加浏览次数
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit,$checkStr) !== false)
		{
		}
		else
		{
			$tb_goods->setData(array('visit' => 'visit + 1'));
			$tb_goods->update('id = '.$goods_id,'visit');
			$visit = $visit === null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit',$visit);
		}

        $this->_filter_detail($goods_info);

        $this->output->set_result("SUCCESS");
        $this->output->set_data($goods_info);
        return;
	}

	//获取货品数据
	function get_product()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
        if (!$goods_id || $goods_id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("需要选定商品id");
            return;
        }

		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj("id = ".$goods_id);
        if (!$goods_info)
        {
            $this->output->set_result("GOODS_NOT_EXISTS");
            $this->output->set_prompt("该商品不存在");
            return;
        }

        $goods_spec_array = JSON::decode($goods_info["spec_array"]);

		$arr_spec_id = IFilter::act(IReq::get('spec_id'));
		$arr_spec_value = IFilter::act(IReq::get('spec_value'));
		if(!$arr_spec_id || !$arr_spec_value)
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("类型规格值不符合标准");
            return;
		}
        if (!is_array($arr_spec_id))
        {
            $arr_spec_id = explode(',', $arr_spec_id);
        }
        if (!is_array($arr_spec_value))
        {
            $arr_spec_value = explode(',', $arr_spec_value);
        }

        if (count($arr_spec_id) != count($arr_spec_value))
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("类型规格值不符合标准");
            return;
		}

        $arr_spec = [];
        foreach($goods_spec_array as $spec_id=>$spec_array)
        {
            $index = array_search($spec_id, $arr_spec_id);
            if ($index === False)
            {
                $this->output->set_result("PARAM_INVALID");
                $this->output->set_prompt("缺少必须的属性值");
                return;
            }

            $spec_type = $spec_array['type'];
            $spec_name = $spec_array['name'];
            $spec_value = $arr_spec_value[$index];
            $arr_spec[] = '{"id":"'.$spec_id.'","type":"'.$spec_type.'","value":"'.$spec_value.'","name":"'.$spec_name.'"}';
        }
        $str_spec = '['.implode(',', $arr_spec).']';

		//获取货品数据
		$tb_products = new IModel('products');
		$products_info = $tb_products->getObj("goods_id = ".$goods_id." and spec_array = '".$str_spec."'");

		//匹配到货品数据
		if(!$products_info)
		{
            $this->output->set_result("PRODUCT_NOT_EXISTS");
            $this->output->set_data("没有找到相关货品");
            return;
		}

		//获得会员价
		$countsumInstance = new countsum();
		$group_price = $countsumInstance->getGroupPrice($products_info['id'],'product');

		//会员价格
		if($group_price !== null)
		{
			$products_info['group_price'] = $group_price;
		}

        $products_info['spec_array'] = JSON::decode($products_info['spec_array']);

        // 不输出成本价
        unset($products_info['cost_price']);

        $this->output->set_result("SUCCESS");
        $this->output->set_data($products_info);
        return;
    }

	//顾客评论
	function get_comment()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$start = IFilter::act(IReq::get('start'),'int') ? IReq::get('start') : 0;
		$num = IFilter::act(IReq::get('num'),'int') ? IReq::get('num') : 5;

        if ($goods_id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("商品id无效");
            return;
        }

		$commentDB = new IQuery('comment as c');
		$commentDB->join   = 'left join goods as go on c.goods_id = go.id AND go.is_del = 0 left join user as u on u.id = c.user_id';
		$commentDB->fields = 'count(c.id) as total';
		$commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
		$commentDB->order  = 'c.id desc';

		$count_total = $commentDB->find();
        if ($count_total === false)
        {
            $this->output->set_result("GET_GOODS_COMMENT");
            $this->output->set_prompt("拉取评论失败");
            return;
        }

        $total = intval($count_total[0]['total']);
        if ($total == 0)
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('total'=>0, 'comments'=>array()));
            return;
        }

		$commentDB->fields = 'u.head_ico,u.username,c.*';
		$commentDB->limit = $start.','.$num;

		$comments = $commentDB->find();

        foreach ($comments as &$comment)
        {
            $comment['head_ico'] = $comment['head_ico'] != null ? $comment['head_ico'] : '';
            $comment['contents'] = $comment['contents'] != null ? $comment['contents'] : '';
            $comment['recontents'] = $comment['recontents'] != null ? $comment['recontents'] : '';
            $comment['buy_time'] = $comment['time'] != '0000-00-00 00:00:00' ? strtotime($comment['time']) : 0;
            $comment['comment_time'] = $comment['comment_time'] != '0000-00-00' ? strtotime($comment['comment_time']) : 0;
            $comment['recomment_time'] = $comment['recomment_time'] != '0000-00-00' ? strtotime($comment['recomment_time']) : 0;

            unset($comment['time']);   // time 这个词太不好理解意思了
            unset($comment['status']);
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data(array('total'=>$total, 'comments'=>$comments));
	}

	/**
	 * @brief 进行商品评论
	 */
	public function add_comment()
	{
		if(!$this->user || $this->user['user_id']===null)
		{
            $this->output->set_result("NEED_LOGIN");
            $this->output->set_prompt("未登录用户不能评论");
            return;
		}

		if(IReq::get('id')===null)
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请指定商品评论");
            return;
		}

		$id               = IFilter::act(IReq::get('id'),'int');
		$data             = array();
		$data['point']    = IFilter::act(IReq::get('point'),'float');
		$data['contents'] = IFilter::act(IReq::get("contents"),'content');
		$data['status']   = 1;

		if($data['point']==0)
		{
			// die("请选择分数");
		}

		$can_submit = Comment_Class::can_comment($id,$this->user['user_id']);
		if($can_submit[0]!=1)
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("您不能评论此件商品");
            return;
		}

		$data['comment_time'] = date("Y-m-d",ITime::getNow());

		$tb_comment = new IModel("comment");
		$tb_comment->setData($data);
		$re=$tb_comment->update("id={$id}");

		if($re)
		{
			//同步更新goods表,comments,grade
			$commentRow = $tb_comment->getObj('id = '.$id);

			$goodsDB = new IModel('goods');
			$goodsDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$goodsDB->update('id = '.$commentRow['goods_id'],array('grade','comments'));
            
            $this->output->set_result("SUCCESS");
            return;
		}
		else
		{
            $this->output->set_result("ADD_COMMENT");
            $this->output->set_prompt("评论失败");
            return;
		}
	}
}
