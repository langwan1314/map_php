<?php
/**
 * @file appucenter.php
 * @brief
 * @author misty
 * @date 2015-10-13 13:44
 * @version 
 * @note
 */
/**
 * @brief app专用, 用户中心
 * @class APPUcenter
 * @note
 */
class APPUcenter extends IAPPController
{
	function init()
	{
		CheckRights::checkAppUserRights();
	}

    function user_login()
    {
		if(!$this->user)
		{
            $this->output->set_result("NEED_LOGIN");
            return false;
		}
        return true;
    }

	//[用户头像]上传
	function avatar_upload()
	{
        if ($this->user_login() == false)
        {
            return;
        }

		if(!isset($_FILES['attach']['name']) || empty($_FILES['attach']['name']))
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请选择图片");
            return;
        }

        $photoObj = new PhotoUpload();
        $photo    = $photoObj->run();

        if($photo['attach']['img'])
        {
            $user_id   = $this->user['user_id'];
            $user_obj  = new IModel('user');
            $dataArray = array(
                'head_ico' => $photo['attach']['img'],
            );
            $user_obj->setData($dataArray);
            $where  = 'id = '.$user_id;
            $succ = $user_obj->update($where);

            if($succ !== false)
            {
                ISafe::set('head_ico', $dataArray['head_ico']);
                $avatar_url = IUrl::creatUrl().$photo['attach']['img'];
                $avatar_url = GoodsImage::$file_url_host . $avatar_url;

                $this->output->set_result("SUCCESS");
                $this->output->set_data(array('avatar'=>$avatar_url));
                return;
            }
        }

        $this->output->set_result("UPLOAD_FILE");
        $this->output->set_prompt("上传失败");
        return;
	}
    
    /**
     * @brief 我的地址
     */
    public function address()
    {
        if ($this->user_login() == false)
        {
            return;
        }

		//取得自己的地址
		$query = new IQuery('address');
        $query->where = 'user_id = '.$this->user['user_id'];
        $query->order = '`default` desc';
		$address = $query->find();

		if($address)
		{
			foreach($address as &$ad)
			{
                $ad['country'] = $ad['country'] == null ? '' : $ad['country'];
				$temp = area::name($ad['province'],$ad['city'],$ad['area']);
				if(isset($temp[$ad['province']]) && isset($temp[$ad['city']]) && isset($temp[$ad['area']]))
				{
                    $ad['province_name'] = $temp[$ad['province']];
                    $ad['city_name'] = $temp[$ad['city']];
                    $ad['area_name'] = $temp[$ad['area']];
				}

                $ad['telephone'] = $ad['telphone'];
                $ad['is_default'] = $ad['default'];
                unset($ad['telphone']);
                unset($ad['default']);
			}
		}

        $this->output->set_result("SUCCESS");
        $this->output->set_data($address);
    }

    /**
     * @brief 收货地址编辑
     */
	public function address_edit()
	{
        if ($this->user_login() == false)
        {
            return;
        }

		$id = intval(IReq::get('id'));
		$accept_name = IFilter::act(IReq::get('accept_name'));
		$province = intval(IReq::get('province'));
		$city = intval(IReq::get('city'));
		$area = intval(IReq::get('area'));
		$address = IFilter::act(IReq::get('address'));
		$zip = IFilter::act(IReq::get('zip'));
		$telphone = IFilter::act(IReq::get('telephone'));
		$mobile = IFilter::act(IReq::get('mobile'));
		$default = IReq::get('default') != 1 ? 0 : 1;
        $user_id = $this->user['user_id'];

        if (!$accept_name)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请填写收货人");
            return;
        }

        if (!$province or !$city or !$area)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请填写省份城市区域");
            return;
        }

        if (!$telphone && !$mobile)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请填写联系电话");
            return;
        }

		$model = new IModel('address');
		$data = array('user_id'=>$user_id,'accept_name'=>$accept_name,'province'=>$province,'city'=>$city,'area'=>$area,'address'=>$address,'zip'=>$zip,'telphone'=>$telphone,'mobile'=>$mobile,'default'=>$default);

        //如果设置为首选地址则把其余的都取消首选
        if($default==1)
        {
            $model->setData(array('default'=>0));
            $model->update("user_id = ".$this->user['user_id']);
        }

		$model->setData($data);

		if($id == 0)
		{
			$model->add();
		}
		else
		{
			$model->update('id = '.$id);
		}

        $this->output->set_result("SUCCESS");
        return;
	}

    /**
     * @brief 收货地址删除处理
     */
	public function address_del()
	{
        if ($this->user_login() == false)
        {
            return;
        }

		$id = IFilter::act( IReq::get('id'),'int' );
		$model = new IModel('address');
		$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);

        $this->output->set_result("SUCCESS");
        return;
	}

    /**
     * @brief 设置默认的收货地址
     */
    public function address_default()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $default = IFilter::string(IReq::get('default'));
        $model = new IModel('address');
        if($default == 1)
        {
            $model->setData(array('default'=>0));
            $model->update("user_id = ".$this->user['user_id']);
        }
        $model->setData(array('default'=>$default));
        $model->update("id = ".$id." and user_id = ".$this->user['user_id']);

        $this->output->set_result("SUCCESS");
        return;
    }

    /**
     * @brief 拉取个人信息
     */ 
    public function info()
    {
        if ($this->user_login() == false)
        {
            return;
        }

    	$user_id = $this->user['user_id'];

    	$userObj = new IModel('user');
    	$where   = 'id = '.$user_id;
    	$userRow = $userObj->getObj($where);

    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;
    	$memberRow = $memberObj->getObj($where);

		$userGroupRow = array();
		if(isset($memberRow['group_id']) && $memberRow['group_id'])
		{
	    	$userGroupObj = new IModel('user_group');
	    	$where        = 'id = '.$memberRow['group_id'];
	    	$userGroupRow = $userGroupObj->getObj($where);
		}

        $user_info = array(
            'user_id'=>$userRow['id'],
            'username'=>$userRow['username'],
            'mobile'=>$userRow['mobile'],
            'email'=>$userRow['email'] ? $userRow['email'] : '',
            'head_ico'=>$userRow['head_ico'] ? GoodsImage::$file_url_host.$userRow['head_ico'] : '',
            'exp'=>$memberRow['exp'], 
            'point'=>$memberRow['point'], 
            "prop"=>$memberRow['prop'] ? $memberRow['prop'] : [],
            "balance"=>$memberRow['balance'],
            "last_login"=>$memberRow['last_login'] == '0000-00-00 00:00:00' ? 0 : strtotime($memberRow['last_login']),
            "status"=>$memberRow['status'],
        );

        $this->output->set_result("SUCCESS");
        $this->output->set_data($user_info);
        return;
    }

    public function info_edit()
    {
        if ($this->user_login() == false)
        {
            return;
        }

        $user_id = $this->user['user_id'];

    	$username = IFilter::act(IReq::get('username','post'));
    	$email = IFilter::act(IReq::get('email','post'));

        if (!$username && !$email)
        {
            return $this->output->set_result("SUCCESS");
        }

        if ($email && IValidate::email($email) == false)
        {
            $this->output->set_result("EMAIL_INVALID");
            return;
        }

        if ($username && strlen($username) >= 20)
        {
            $this->output->set_result("USERNAME_INVALID");
            $this->output->set_prompt("用户名无效，请重新输入");
            return;
        }

        $userObj = new IModel('user');

        $set_data = array();
        if ($username)
        {
            $get_user = $userObj->getObj('email = "'.$email.'"');
            if($get_user === false)
            {
                $this->output->set_result("GET_USER");
                $this->output->set_prompt("保存失败，请重试");
                return;
            }

            if($get_user)
            {
                if ($get_user['id'] != $user_id)
                {
                    $this->output->set_result("EMAIL_USED");
                    $this->output->set_prompt("该用户名已被使用");
                    return;
                }
            }
            else
            {
                $set_data['username'] = $username;
            }
        }

        if ($email)
        {
            $get_user = $userObj->getObj('email = "'.$email.'"');
            if($get_user === false)
            {
                $this->output->set_result("GET_USER");
                $this->output->set_prompt("保存失败，请重试");
                return;
            }

            if($get_user)
            {
                if ($get_user['id'] != $user_id)
                {
                    $this->output->set_result("EMAIL_USED");
                    $this->output->set_prompt("该邮箱已被使用");
                    return;
                }
            }
            else 
            {
                $set_data['email'] = $email;
            }
        }

        if ($set_data)
        {
            $userObj->setData($set_data);
            $userObj->update('id = '.$user_id);
        }

        $this->output->set_result("SUCCESS");
    }

    /**
     * @brief 用户反馈(建议)
     */
    public function feedback()
    {
        if ($this->user_login() == false)
        {
            return;
        }

        $id = IFilter::act(IReq::get('id'),'int');
        $title = IFilter::act(IReq::get('title'),'string');
        $content = IFilter::act(IReq::get('content'),'string' );

        $user_id = $this->user['user_id'];
        $model = new IModel('suggestion');
        $model->setData(array('user_id'=>$user_id,'title'=>$title,'content'=>$content,'time'=>date('Y-m-d H:i:s')));
        if($id =='')
        {
            $model->add();
        }
        else
        {
            $model->update('id = '.$id.' and user_id = '.$this->user['user_id']);
        }

        $this->output->set_result("SUCCESS");
        return;
    }

    function order_list()
    {
        if ($this->user_login() == false)
        {
            return;
        }

        $user_id = $this->user['user_id'];

        // 订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款,7部分退款
        $complete_status_map = array(1=>array(1, 2), 2=>array(3, 4, 5, 6)); // 1:未完成, 2:已完成

        $complete_status = IFilter::act(IReq::get('complete_status'), 'int');

        $start = IFilter::act(IReq::get('start'),'int');
        $num = IFilter::act(IReq::get('num'),'int');
        $num = $num > 0 ? $num : 8;

        $query_order = new IQuery('order');
        $query_order_where = "user_id = $user_id and if_del= 0";
        if (isset($complete_status_map[$complete_status]))
        {
            $str_status = join(",", $complete_status_map[$complete_status]);
            $query_order_where .= " and status in ($str_status)";
        }
        $query_order->where = $query_order_where;
        $query_order->order = "id desc";

        $query_order->fields = "count(id) as total";
        $count_total = $query_order->find();
        if ($count_total === false)
        {
            $this->output->set_result("GET_ORDER_LIST");
            $this->output->set_prompt("拉取订单列表失败");
            return;
        }

        $total = intval($count_total[0]['total']);
        if ($total == 0)
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('total'=>0, 'orders'=>[]));
            return;
        }

        $query_order->fields = '*';
		$query_order->limit = $start.', '.$num;
		$orders = $query_order->find();

        $payment_map = $this->get_payment_map();

        $order_id_no_map = array();
        foreach ($orders as $order)
        {
            $order_id_no_map[$order['id']] = $order['order_no'];
        }
        $str_order_ids = join(',', array_keys($order_id_no_map));

        $query_order_goods = new IQuery('order_goods');
        $query_order_goods->where = "order_id in ($str_order_ids)";
        $order_goods_list = $query_order_goods->find();

        $arr_order_no = array_values($order_id_no_map);
        $query_order_comment = new IQuery('comment');
        $query_order_comment->where = "order_no in (". join(",", $arr_order_no).")";
        $query_order_comment->fields = "id, order_no, goods_id, time, status";
        $arr_order_goods_comment = $query_order_comment->find();

        $arr_order_goods_comment_map = array();
        foreach ($arr_order_goods_comment as $order_goods_comment)
        {
            if (! isset($arr_order_goods_comment_map[$order_goods_comment['order_no']]))
            {
                $arr_order_goods_comment_map[$order_goods_comment['order_no']] = array();
            }

            $comment_time = $order_goods_comment['time'];
            $comment_status = intval($order_goods_comment['status']);
            $comment_goods_id = $order_goods_comment['goods_id'];

            if (time() - strtotime($comment_time) < 3600 * 24 * 30 * 6 && $comment_status == 0)
            {
                $arr_order_goods_comment_map[$order_goods_comment['order_no']][$comment_goods_id] = $order_goods_comment['id'];
            }
        }

        $order_goods_map = array();
        foreach ($order_goods_list as $order_goods)
        {
            $order_id = $order_goods['order_id'];
            if (!isset($order_goods_map[$order_id]))
            {
                $order_goods_map[$order_id] = [];
            }

            $order_goods['img'] = GoodsImage::get_order_goods_url($order_goods['img']);
            $goods_array = JSON::decode($order_goods['goods_array']);
            unset($order_goods['goods_array']);
            $order_goods['goods_name'] = $goods_array['name'];
            $order_goods['goods_spec'] = $goods_array['value'];

            unset($order_goods['id']);
            unset($order_goods['order_id']);
            unset($order_goods['is_send']);
            unset($order_goods['delivery_id']);
            unset($order_goods['seller_id']);

            $order_goods['comment_id'] = 0;
            $order_no = $order_id_no_map[$order_id];
            if (isset($arr_order_goods_comment_map[$order_no]))
            {
                $order_goods_comment_map = $arr_order_goods_comment_map[$order_no];
                if (isset($order_goods_comment_map[$order_goods['goods_id']]))
                {
                    $order_goods['comment_id'] = $order_goods_comment_map[$order_goods['goods_id']];
                }
            }

            $order_goods_map[$order_id][] = $order_goods;
        }

        $arr_rsp_order = [];
        foreach ($orders as $order)
        {
            $combine_status = Order_Class::getOrderStatus($order);
            $combine_status_text = Order_Class::orderStatusText($combine_status);

            $pay_status_text = Order_Class::getOrderPayStatusText($order);
            $distribution_status_text = Order_Class::getOrderDistributionStatusText($order);

            $rsp_order = array(
                "order_id" => $order['id'],
                "order_no" => $order['order_no'],
                "status" => $order['status'],
                "status_text" => $combine_status_text,
                "pay_type" => $order['pay_type'],
                "pay_type_text" => $payment_map[$order['pay_type']]['name'],
                "pay_status" => $order['pay_status'],            // 支付状态 0:未支付; 1:已支付
                "pay_status_text" => $pay_status_text,           // 支付状态文字说明
                "distribution_status" => $order['distribution_status'],
                "distribution_status_text" => $distribution_status_text,  // 发货状态
                "payable_amount" => $order['payable_amount'],    // 商品价格(商品原始标注价格)
                "real_amount" => $order['real_amount'],          // 商品最终价格(活动、折扣计算后)
                "payable_freight" => $order['payable_freight'],  // 运费价格
                "real_freight" => $order['real_freight'],        // 实际运费
                "promotions" => $order['promotions'],            // 优惠价格
                //"order_amount" => $order['order_amount'],        // 订单应付总额, 现在app用real_amount就行了, 不计算其它费用--misty
                "trade_no" => $order['trade_no'] ? $order['trade_no'] : '', // 支付平台交易号
            );

            if (isset($order_goods_map[$order['id']]))
            {
                $rsp_order["goods"] = $order_goods_map[$order['id']];
            }

            $arr_rsp_order[] = $rsp_order;
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data(array('total'=>$total, 'orders'=>$arr_rsp_order));
    }

    /**
     * @brief 订单详情
     * @return String
     */
    public function order_detail()
    {
        if ($this->user_login() == false)
        {
            return;
        }

        $order_id = IFilter::act(IReq::get('order_id'),'int');

        $orderObj = new order_class();
        $order = $orderObj->getOrderShow($order_id, $this->user['user_id']);

        if(!$order)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("订单信息不存在");
            return;
        }

        $payment_map = $this->get_payment_map();

        $status = Order_Class::getOrderStatus($order);
        $status_text = Order_Class::orderStatusText($status);
        $pay_status_text = Order_Class::getOrderPayStatusText($order);
        $distribution_status_text = Order_Class::getOrderDistributionStatusText($order);

        $order['status_text'] = $status_text;
        $order['pay_type_text'] = $payment_map[$order['pay_type']]['name'];
        $order['pay_status_text'] = $pay_status_text;
        $order['distribution_status_text'] = $distribution_status_text;

        $order['telephone'] = $order['telphone']; unset($order['telphone']);

        // app 上暂时不需要以下信息
        unset($order['id']);
        unset($order['user_id']);
        unset($order['distribution']);
        unset($order['postcode']);
        unset($order['country']);
        unset($order['province']);
        unset($order['city']);
        unset($order['area']);
        unset($order['username']);
        unset($order['email']);
        unset($order['u_mobile']);
        unset($order['contact_addr']);
        unset($order['true_name']);
        unset($order['payment']);
        unset($order['pay_time']);
        unset($order['send_time']);
        unset($order['completion_time']);
        unset($order['prop']);
        unset($order['trade_no']);
        unset($order['checkcode']);
        unset($order['paynote']);
        unset($order['payable_freight']);
        unset($order['real_freight']);
        unset($order['postscript']);
        unset($order['note']);
        unset($order['if_del']);
        unset($order['insured']);
        unset($order['pay_fee']);
        unset($order['taxes']);
        unset($order['exp']);
        unset($order['point']);
        unset($order['type']);
        unset($order['takeself']);
        unset($order['active_id']);
        unset($order['seller_id']);
        unset($order['is_checkout']);
        unset($order['delivery']);
        unset($order['goods_amount']);
        unset($order['goods_weight']);
        unset($order['order_amount']);

        $query_order_comment = new IQuery('comment');
        $query_order_comment->where = "order_no = '". $order['order_no']. "'";
        $query_order_comment->fields = "id, order_no, goods_id, time, status";
        $arr_order_goods_comment = $query_order_comment->find();

        $arr_goods_comment_map = array();
        foreach ($arr_order_goods_comment as $order_goods_comment)
        {
            $comment_time = $order_goods_comment['time'];
            $comment_status = intval($order_goods_comment['status']);
            $comment_goods_id = $order_goods_comment['goods_id'];

            if (time() - strtotime($comment_time) < 3600 * 24 * 30 * 6 && $comment_status == 0)
            {
                $arr_goods_comment_map[$comment_goods_id] = $order_goods_comment['id'];
            }
        }

        $query_order_goods = new IQuery('order_goods');
        $query_order_goods->where = "order_id = $order_id";
        $order_goods_list = $query_order_goods->find();

        foreach ($order_goods_list as &$order_goods)
        {
            $order_goods['img'] = GoodsImage::get_order_goods_url($order_goods['img']);
            $goods_array = JSON::decode($order_goods['goods_array']);
            unset($order_goods['goods_array']);
            $order_goods['goods_name'] = $goods_array['name'];
            $order_goods['goods_spec'] = $goods_array['value'];
            $order_goods['comment_id'] = 0;
            if (isset($arr_goods_comment_map[$order_goods['goods_id']]))
            {
                $order_goods['comment_id'] = $arr_goods_comment_map[$order_goods['goods_id']];
            }

            unset($order_goods['id']);
            unset($order_goods['order_id']);
            unset($order_goods['is_send']);
            unset($order_goods['delivery_id']);
            unset($order_goods['seller_id']);
        }

        $order['goods'] = $order_goods_list;

        $query_order_comment = new IQuery('comment');
        $query_order_comment->where = "order_no = '". $order['order_no']. "'";
        $query_order_comment->fields = "id, order_no, goods_id, time, status";
        $arr_order_goods_comment = $query_order_comment->find();

        $arr_goods_comment_map = array();
        foreach ($arr_order_goods_comment as $order_goods_comment)
        {
            $comment_time = $order_goods_comment['time'];
            $comment_status = $order_goods_comment['status'];
            $comment_goods_id = $order_goods_comment['goods_id'];

            if (time() - strtotime($comment_time) < 3600 * 24 * 30 * 6 && $status == 0)
            {
                $arr_goods_comment_map[$comment_goods_id] = $order_goods_comment['id'];
            }
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($order);
    }

    //操作订单状态
	public function order_cancel()
	{
        if ($this->user_login() == false)
        {
            return;
        }

        $user_id = $this->user['user_id'];

		$order_id = IFilter::act( IReq::get('order_id'),'int' );
		$model = new IModel('order');

        $model->setData(array('status' => 3));
        if($model->update("id = ".$order_id." and distribution_status = 0 and status = 1 and user_id = ".$user_id))
        {
            //修改红包状态
            $prop_obj = $model->getObj('id='.$order_id,'prop');
            $prop_id = isset($prop_obj['prop'])?$prop_obj['prop']:'';
            if($prop_id != '')
            {
                $prop = new IModel('prop');
                $prop->setData(array('is_close'=>0));
                $prop->update('id='.$prop_id);
            }

            $this->output->set_result("SUCCESS");
            return;
        }
        else
        {
            $this->output->set_result("ORDER_STATUS_FORBIDDEN");
            $this->output->set_prompt("对不起，该订单无法取消。如有问题，请联系本站客服人员");
            return;
        }
	}

    //操作订单状态
	public function order_confirm()
	{
        if ($this->user_login() == false)
        {
            return;
        }

        $user_id = $this->user['user_id'];

		$order_id = IFilter::act( IReq::get('order_id'),'int' );
		$model = new IModel('order');

        $model->setData(array('status' => 5,'completion_time' => date('Y-m-d h:i:s')));
        if($model->update("id = ".$order_id." and distribution_status = 1 and user_id = ".$user_id))
        {
            $orderRow = $model->getObj('id = '.$order_id);

            //确认收货后进行支付
            Order_Class::updateOrderStatus($orderRow['order_no']);

            //增加用户评论商品机会
            Order_Class::addGoodsCommentChange($order_id);

            $this->output->set_result("SUCCESS");
            return;
        }
        else
        {
            $this->output->set_result("ORDER_STATUS_FORBBIDEN");
            $this->output->set_prompt("订单确认收货失败。如有问题，请联系本站客服人员");
            return;
        }
	}

    //删除订单
	public function order_del()
	{
        if ($this->user_login() == false)
        {
            return;
        }

        $user_id = $this->user['user_id'];
		$order_id = IFilter::act(IReq::get('order_id'), 'int');
        if (! $order_id)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请选择有效的订单");
            return;
        }

		$tb_order = new IModel('order');
        $order = $tb_order->getObj("id = ".$order_id." and user_id = ".$user_id." and if_del = 0");
        if (!$order)
        {
            $this->output->set_result("ORDER_STATUS_FORBBIDEN");
            $this->output->set_prompt("订单不存在或已被删除");
            return;
        }

        if (! Order_Class::ifUserCanDel($order))
        {
            $order_status = Order_Class::getOrderStatus($order);
            $status_text = Order_Class::orderStatusText($order_status);
            $this->output->set_result("ORDER_STATUS_FORBBIDEN");
            $this->output->set_prompt("订单处于".$status_text."，暂时不能被删除");
            return;
        }

    	$tb_order->setData(array('if_del'=>1));
        if ($tb_order->update("id = ".$order_id." and if_del = 0 and user_id = ".$user_id))
        {
            $this->output->set_result("SUCCESS");
            return;
        }
        else
        {
            $this->output->set_result("ORDER_STATUS_FORBBIDEN");
            $this->output->set_prompt("订单不存在或已被删除");
            return;
        }
	}

    private function get_payment_map()
    {
        $query_payment = new IQuery('payment');
        $query_payment->fields = 'id,name,type';
        $payments = $query_payment->find();
        $payment_map = array();
        foreach($payments as $pay)
        {
            $payment_map[$pay['id']]['name'] = $pay['name'];
            $payment_map[$pay['id']]['type'] = $pay['type'];
        }
        return $payment_map;
    }

    /**
     * @brief 退款申请页面
     */
    public function refunds_apply()
    {
        if ($this->user_login() == false)
        {
            return;
        }

        $order_goods_id = IFilter::act(IReq::get('order_goods_id'),'int');
        $order_id       = IFilter::act(IReq::get('order_id'),'int');
        $content        = IFilter::act(IReq::get('content'),'text');
        $user_id        = $this->user['user_id'];

        $debug = IFilter::act(IReq::get('debug'), 'int');
        if ($debug) $user_id = 19;

        if(!$content)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请完整填写内容(退款原因)");
            return;
        }

        $arr_photo_url = [];
        if (!empty($_FILES))
        {
            $upObj = new IUpload();
            $dir = WM::$app->config['upload'].'/order/'.date('Y')."/".date('m')."/".date('d');
            $upObj->setDir($dir);
            $upState = $upObj->execute();

            //检查上传状态
            foreach($upState['attach'] as $val)
            {
                if($val['flag'] == 1)
                {
                    $arr_photourl[] = GoodsImage::$site_url_host.'/'.$val['fileSrc'];
                }
            }
        }

        $orderDB = new IModel('order');
        $goodsOrderDB = new IModel('order_goods');

        $orderRow = $orderDB->getObj("id = ".$order_id." and user_id = ".$user_id);

        //判断订单状态, 是否允许退款申请
        if($orderRow && Order_Class::isRefundmentApply($orderRow))
        {
        	$goodsOrderRow = $goodsOrderDB->getObj('goods_id = '.$order_goods_id.' and order_id = '.$order_id);

        	//判断商品是否已经退货
        	if($goodsOrderRow && $goodsOrderRow['is_send'] != 2)
        	{
        		$refundsDB = new IModel('refundment_doc');

        		//判断是否重复提交申请
        		if($refundsDB->getObj('order_id = '.$order_id.' and goods_id = '.$goodsOrderRow['goods_id'].' and product_id = '.$goodsOrderRow['product_id'].' and if_del = 0 and pay_status = 0'))
        		{
                    $this->output->set_result("APPLY_ALREADY");
                    $this->output->set_prompt('您已经对此商品提交了退款申请，请耐心等待');
                    return;
        		}

        		//判断是否已经生成了结算申请或者已经结算了
        		$billObj = new IModel('bill');
        		$billRow = $billObj->getObj('FIND_IN_SET('.$order_id.',order_ids)');
        		if($billRow)
        		{
                    $this->output->set_result("SETTLEMENT_ALREADY");
        			$this->output->set_prompt('此订单金额已被商家结算完毕，请直接与商家联系');
                    return;
        		}

				//退款单数据
        		$updateData = array(
					'order_no' => $orderRow['order_no'],
					'order_id' => $order_id,
					'user_id'  => $user_id,
					'time'     => ITime::getDateTime(),
					'content'  => $content,
					'goods_id' => $goodsOrderRow['goods_id'],
					'product_id' => $goodsOrderRow['product_id'],
					'seller_id'  => $goodsOrderRow['seller_id'],
				);

        		//写入数据库
        		$refundsDB->setData($updateData);
        		$doc_id = $refundsDB->add();
                if ($doc_id === False)
                {
                    $this->output->set_result("REFUNDS_ALREADY_FAILED");
                    $this->output->set_prompt("申请订单退款失败，请稍候重试");
                    return;
                }

                if (!empty($arr_photo_url))
                {
                    // 有退款申请说明图片
                    $refundsPhotoDB = new IModel('refundment_photo');
                    foreach ($arr_photo_url as $photo_url)
                    {
                        $refundsPhotoData = array('doc_id' => $doc_id, 'img' => $photo_url);
                        // 输入照片失败的话, 忽略吧
                        $refundsPhotoDB->setData($refundsPhotoData);
                    }
                }

                $this->output->set_result("SUCCESS");
                $this->output->set_data(array('id'=>$doc_id));
                return;
        	}
        	else
        	{
                $this->output->set_result("REFUNDS_ALREADY");
        		$this->output->set_prompt('此商品已经做了退款处理，请耐心等待');
                return;
        	}
        }
        else
        {
        	$message = '订单未付款';
            $this->output->set_result("ORDER_NOT_PAY");
            $this->output->set_prompt('此订单尚未付款');
            return;
        }
    }

    /**
     * @brief 退款申请删除
     */
    public function refunds_del()
    {
        if (!$this->user_login())
        {
            return;
        }

        $id = IFilter::act(IReq::get('id'),'int');
        $model = new IModel("refundment_doc");
        $model->del("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->output->set_result("SUCCESS");
        return;
    }

    /**
     * @brief 查看退款申请详情
     */
    public function refunds_detail()
    {
        if (!$this->user_login())
        {
            return;
        }

        $id = IFilter::act(IReq::get('id'),'int');
        $refundDB = new IModel("refundment_doc");
        $refundRow = $refundDB->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($refundRow)
        {
        	//获取商品信息
        	$orderGoodsDB = new IModel('order_goods');
        	$orderGoodsRow = $orderGoodsDB->getObj('order_id = '.$refundRow['order_id'].' and goods_id = '.$refundRow['goods_id'].' and product_id = '.$refundRow['product_id']);
        	if($orderGoodsRow && $orderGoodsRow['goods_array'])
        	{
        		$refundRow['goods'] = $orderGoodsRow;
                if (isset($refundRow['goods']['goods_array']) && $refundRow['goods']['goods_array'])
                {
                    $refundRow['goods']['goods_array'] = JSON::decode($refundRow['goods']['goods_array']);
                }
                
                unset($refundRow['user_id']);
                unset($refundRow['admin_id']);
                unset($refundRow['if_del']);

                $refundRow['pay_status_text'] = Order_Class::refundmentText($refundRow['pay_status']);

                $refundRow['photo_urls'] = [];

                $refundsPhotoDB = new IModel('refundment_photo');
                $refundsPhotoDB->where = "doc_id=$id";
                $refundsPhotos = $refundsPhotoDB->find();
                if ($refundsPhotos and !empty($refundsPhotos))
                {
                    foreach ($refundsPhotos as $refundsPhoto)
                    {
                        $refundRow['photo_urls'][] = $refundsPhoto['img'];
                    }
                }

                $this->output->set_result("SUCCESS");
                $this->output->set_data($refundRow);
                return;
        	}
        	else
        	{
                $this->output->set_result("TARGET_NOT_EXISTS");
	        	$this->output->set_prompt("没有找到要退款的商品");
                return;
        	}
        }
        else
        {
            $this->output->set_result("TARGET_NOT_EXISTS");
            $this->output->set_prompt("退款信息不存在");
            return;
        }
    }

    function refunds()
    {
        if (!$this->user_login())
        {
            return;
        }
        $start = IFilter::act(IReq::get('start'),'int');
        $num = IFilter::act(IReq::get('num'),'int');
        $num = $num > 0 ? $num : 8;

        $query = new IQuery('refundment_doc');
        $query->where = "user_id = ".$this->user['user_id'];
        $query->order = "id desc";

        $query->fields = "count(id) as total";
        $count_total = $query->find();
        if ($count_total === false)
        {
            $this->output->set_result("GET_REFUNDS");
            $this->output->set_prompt("拉取退款申请失败");
            return;
        }

        $total = intval($count_total[0]['total']);
        if ($total == 0)
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('total'=>0, 'refunds'=>array()));
            return;
        }

        $query->fields = '*';
        $query->limit = "$start, $num";
		$arr_refunds = $query->find();

        foreach ($arr_refunds as &$refunds)
        {
            unset($refunds['user_id']);
            unset($refunds['admin_id']);
            unset($refunds['if_del']);
            $refunds['pay_status_text'] = Order_Class::refundmentText($refunds['pay_status']);
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_refunds);
        return;
    }
}
