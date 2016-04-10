<?php
use plugins\payments\pay_weixin\CLogFileHandler;
use plugins\payments\pay_weixin\WxPayLog;
use plugins\payments\pay_weixin\WxPayNativePay;
use plugins\payments\pay_weixin\WxPayPayNotifyCallBack;
use plugins\payments\pay_weixin\WxPayUnifiedOrder;

/**
 * Created by PhpStorm.
 * User: windy
 * Date: 15/11/30
 * Time: 下午10:38
 * @brief 公共模块
 * @class Pay
 */
class Pay extends IController
{
	public $layout = '';

	public function init()
	{
		CheckRights::checkUserRights();
	}

	public function weixin()
	{
		//获得相关参数
		$order_no   = IReq::get('order_no');

		$orderRow = array();
		if($order_no)
		{
			//获取订单信息
			$orderDB  = new IModel('order');
			$orderRow = $orderDB->getObj('order_no = '.$order_no);

			if(empty($orderRow) || $orderRow['user_id'] != ISafe::get('user_id'))
			{
				IError::show(403,'要支付的订单信息不存在');
			}

			if ($orderRow['status'] != 1 || $orderRow['pay_status'] != 0  ) {
				IError::show(403,'订单状态出错');
			}

		}else{
			IError::show(403,'要支付的订单信息出错');
		}

		$notify = new WxPayNativePay();
		$order_body = "订单:".$orderRow['order_no'];
		$order_create_time = date("YmdHis", strtotime($orderRow['create_time']));
		$input = new WxPayUnifiedOrder();
		$input->SetBody($order_body);
		$input->SetAttach("web");
		$input->SetOut_trade_no($orderRow['order_no']);
		$input->SetTotal_fee($orderRow['real_amount']*100);
		$input->SetTime_start($order_create_time);
		$input->SetTime_expire($order_create_time+86400*12);
		$input->SetNotify_url("http://www.crycoder.com/pay/weixin_callback");
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id($orderRow['order_no']);
		$result = $notify->GetPayUrl($input);
		if ($result['return_code'] != 'SUCCESS') {
			IError::show(403,'要支付的订单信息出错');
		}
		if ($result['result_code'] != 'SUCCESS') {
			IError::show(403, $result['err_code_des']);
		}

		$this->user_name = ISafe::get('user_name');
		$this->order_info = $orderRow;
		$this->pay_url = $result["code_url"];
		$this->redirect('weixin');
	}


	public function weixin_callback()
	{
		$uuid = time();
		$log_file = WM::$app->getBasePath()."/backup/weixin_log/".date('Y-m-d').'.log';

		$log = new IFileLog($log_file);
		$log->write("[{$uuid}]weixin call back start...");
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
		$log_data = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));

		$log->write("[{$uuid}]".$log_data);

		//初始化日志
		$logHandler= new CLogFileHandler($log_file);
		WxPayLog::Init($logHandler, 15);

		$notify = new WxPayPayNotifyCallBack();

		$notify->Handle(false);
		if ($notify->pay_result) {
			Order_Class::updateOrderStatus($notify->call_back_info['out_trade_no'], '', '', $notify->call_back_info['transaction_id']);
		}
	}

	//todo:重构json输出
	public function orderIsPay()
	{
		$order_no   = IReq::get('order_no');

		if($order_no)
		{
			//获取订单信息
			$orderDB  = new IModel('order');
			$orderRow = $orderDB->getObj('order_no = '.$order_no);

			if(empty($orderRow) || $orderRow['user_id'] != ISafe::get('user_id'))
			{
				$r = array(
					'code' => 404,
					'is_pay' => 0,
					'msg' => '订单信息出错'
				);
			}else if($orderRow && $orderRow['pay_status'] == 1){
				$r = array(
					'code' => 200,
					'is_pay' => 1
				);
			}else{
				$r = array(
					'code' => 200,
					'is_pay' => 0
				);
			}

		}else{
			$r = array(
				'code' => 404,
				'is_pay' => 0,
				'msg' => '订单信息出错'
			);
		}

		die(JSON::encode($r));
	}

}