<?php
namespace plugins\payments\pay_weixin;

class WxAppPayNotifyCallBack extends WxPayNotify
{
	public $pay_result = false;
	public $call_back_info = [];

	//查询订单
	public function QueryOrder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
        try {
            $result = WxAppPayApi::orderQuery($input);
        } catch (Exception $e) {
            WxPayLog::INFO("query exception:" . $e->getMessage());
        }
		WxPayLog::INFO("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			$this->pay_result = true;
			return true;
		}

        $this->pay_result = false;
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		$this->call_back_info = $data;
		WxPayLog::INFO("weixin apppay NotifyProcess:" . json_encode($data));

		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->QueryOrder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}
