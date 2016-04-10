<?php
namespace plugins\payments\pay_weixin;
/**
 *
 * 接口调用结果类
 * @author widyhu
 *
 */
class WxPayResults extends WxPayData
{
	/**
	 *
	 * 检测签名
	 */
	public function CheckSign($mch_key = null)
	{
		//fix异常
		if(!$this->IsSignSet()){
			throw new WxPayException("签名错误！");
		}

		$sign = $this->MakeSign($mch_key);
		if($this->GetSign() == $sign){
			return true;
		}
		throw new WxPayException("签名错误！");
	}

	/**
	 *
	 * 使用数组初始化
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}

	/**
	 *
	 * 使用数组初始化对象
	 * @param array $array
	 * @param bool|$noCheckSign 是否检测签名
	 * @return WxPayResults
	 * @throws WxPayException
	 */
	public static function InitFromArray($array, $noCheckSign = false, $mch_key = null)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign($mch_key);
		}
		return $obj;
	}

	/**
	 *
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}

	/**
	 * 将xml转为array
	 * @param string $xml
	 * @return array
	 * @throws WxPayException
	 */
	public static function Init($xml, $mch_key = null)
	{
		$obj = new self();
		$obj->FromXml($xml);
		//fix bug 2015-06-29
		if($obj->values['return_code'] != 'SUCCESS'){
			return $obj->GetValues();
		}
		$obj->CheckSign($mch_key);
		return $obj->GetValues();
	}
}
