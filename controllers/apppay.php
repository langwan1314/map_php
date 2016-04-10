<?php
use plugins\payments\pay_weixin\CLogFileHandler;
use plugins\payments\pay_weixin\WxPayLog;
use plugins\payments\pay_weixin\WxPayApi;
use plugins\payments\pay_weixin\WxAppPayApi;
use plugins\payments\pay_weixin\WxPayNativePay;
use plugins\payments\pay_weixin\WxAppPayNotifyCallBack;
use plugins\payments\pay_weixin\WxPayUnifiedOrder;
use plugins\payments\pay_weixin\WxPayConfig;

/**
 * 15/12/04 19:05
 * @class APPPay
 */
class APPPay extends IAPPController
{
	public $layout = '';

	public function init()
	{
		CheckRights::checkAppUserRights();
        if ($this->user['user_id'] == 1)
        {
            $this->debug = True;
        }
        else 
        {
            $this->debug = False;
        }
	}

	private static function to_url_params($arr_data)
	{
		$buff = "";
		foreach ($arr_data as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}

    private static function make_weixin_sign($arr_data)
    {
		//签名步骤一：按字典序排序参数
		ksort($arr_data);
		$str_data = self::to_url_params($arr_data);
		//签名步骤二：在string后加入KEY
		$str_data = $str_data. "&key=".WxPayConfig::APP_KEY;
		//签名步骤三：MD5加密
		$str_data = md5($str_data);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($str_data);
		return $result;
    }

	public function weixin()
	{
		//获得相关参数
		$order_no = IReq::get('order_no');

		$orderRow = array();
		if(!$order_no)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("支付的订单信息有误");
            return;
        }

        //获取订单信息
        $orderDB  = new IModel('order');
        $orderRow = $orderDB->getObj('order_no = '.$order_no);
        if(empty($orderRow) || $orderRow['user_id'] != $this->user['user_id'])
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("支付的订单信息不存在");
            return;
        }

        if ($orderRow['pay_status'] != 0 || $orderRow['status'] != 1)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("该订单已经支付");
            return;
        }

		$logHandler= new CLogFileHandler(WM::$app->getBasePath()."/backup/weixin_applog/".date('Y-m-d').'.log');
		WxPayLog::Init($logHandler, 15);

        $uid = $this->user['user_id'];
		$order_fee = $orderRow['real_amount'];
        WxPayLog::INFO("uid[$uid] weixin prepay start, order_no[$order_no] total_fee[$order_fee]");

		$notify = new WxPayNativePay();
		$order_body = "订单:".$orderRow['order_no'];
		$order_create_time = date("YmdHis", strtotime($orderRow['create_time']));

		$input = new WxPayUnifiedOrder();
		$input->SetAppid(WxPayConfig::APP_APPID);
		$input->SetMch_id(WxPayConfig::APP_MCHID);
		$input->SetBody($order_body);
		$input->SetAttach($this->user['user_id']."@kxcaiyuan");
		$input->SetOut_trade_no($orderRow['order_no']);
		$input->SetTotal_fee($orderRow['real_amount']*100);
		//$input->SetTime_start($order_create_time);
		//$input->SetTime_expire($order_create_time+86400*12);
		$input->SetNotify_url("http://www.crycoder.com/apppay/weixin_callback");
		$input->SetTrade_type("APP");
		$input->SetProduct_id($orderRow['order_no']);

        try {
            $result = WxPayApi::unifiedOrder($input, WxPayConfig::APP_KEY);
        } catch (Exception $e) {
            $err_msg = $e->getMessage();
            $this->output->set_result("WEIXIN_UNIFIED_ORDER");
            $this->output->set_errinfo($err_msg);
            $this->output->set_prompt("生成支付订单失败，请重新确认订单");
            WxPayLog::ERROR("uid[$uid] start weixin prepay failed, err_msg[$err_msg] order_no[$order_no] total_fee[$order_fee]");
            return;
        }

        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') 
        {
            if (isset($result['err_code_des']))
            {
                $this->output->set_errinfo($result['err_code'].':'.$result['err_code_des']);
            }

            $return_code = isset($result['return_code']) ? $result['return_code'] : '';
            $result_code = isset($result['result_code']) ? $result['result_code'] : '';
            $err_msg = isset($result['err_code_des']) ? $result['err_code_des'] : '';

            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("支付的订单信息有误，请重新确认订单支付状态");
            WxPayLog::ERROR("uid[$uid] start weixin prepay failed, return_code[$return_code] result_code[$result_code] err_msg[$err_msg] order_no[$order_no] total_fee[$order_fee]");
            return;
		}

        $uid = $this->user['user_id'];
		$order_fee = $orderRow['real_amount'];
        $prepay_id = $result['prepay_id'];
        WxPayLog::INFO("uid[$uid] weixin prepay success, prepay_id[$prepay_id] order_no[$order_no] total_fee[$order_fee]");

        $app_data = array(
            'appid'=>WxPayConfig::APP_APPID,
            'partnerid'=>WxPayConfig::APP_MCHID, 
            'prepayid'=>$result['prepay_id'],
            'noncestr'=>$input->GetNonce_str(),
            'package'=>'Sign=WXPay',
            'timestamp'=>time(),
        );

        $app_sign = self::make_weixin_sign($app_data);
        $package_value = $app_data['package'];
        unset($app_data['package']);
        $app_data['str_package'] = $package_value;
        //$app_data['prepay_id'] = $app_data['prepayid'];
        $app_data['sign'] = $app_sign;

        $this->output->set_result("SUCCESS");
        $this->output->set_data($app_data);
	}

	public function weixin_callback()
	{
		//初始化日志
		$logHandler= new CLogFileHandler(WM::$app->getBasePath()."/backup/weixin_applog/".date('Y-m-d').'.log');
		WxPayLog::Init($logHandler, 15);

		$notify = new WxAppPayNotifyCallBack();
		$notify->Handle(false, WxPayConfig::APP_KEY);

        if (isset($notify->call_back_info['out_trade_no']))
            $order_no = $notify->call_back_info['out_trade_no'];
        else
            $order_no = '';

        $notify_return_code = $notify->GetReturn_code();
        $notify_return_msg = $notify->GetReturn_msg();
        WxPayLog::INFO("weixin pay callback finish, order_no[$order_no] return_code[$notify_return_code] return_msg[$notify_return_msg]");
		if ($notify->pay_result) {
            $transaction_id = $notify->call_back_info['transaction_id'];
            WxPayLog::INFO("weixin pay callback success! order_no[$order_no]");
			Order_Class::updateOrderStatus($order_no, '', '', $transaction_id);
		}
	}

	public function is_pay()
	{
		$order_no = IReq::get('order_no');
		if (!$order_no)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("订单信息有误");
            return;
        }

        //获取订单信息
        $orderDB = new IModel('order');
        $orderRow = $orderDB->getObj('order_no = '.$order_no);

        if(empty($orderRow) || $orderRow['user_id'] != $this->user['user_id'])
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("订单信息有误，请重新确认");
        } else if($orderRow && $orderRow['pay_status'] == 1) {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('is_pay'=>1));
        } else {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('is_pay'=>0));
        }
        return;
	}
}
