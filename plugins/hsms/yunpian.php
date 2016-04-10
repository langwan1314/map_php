<?php
/**
 * @copyright (c) 2015 crycoder.com
 * @file yunpian.php
 * @brief **短信发送接口
 * @author misty
 * @date 2015/10/10 18:24:38
 * @version 1.0
 */

 /**
 * @class yunpian
 * @brief 短信发送接口 http://www.yunpian.com/api/sms.html
 */
class yunpian extends hsmsBase
{
	private $submitUrl = "http://yunpian.com/v1/sms/send.json";

	/**
	 * @brief 获取config用户配置
	 * @return array
	 */
	private function getConfig()
	{
		//如果后台没有设置的话，这里手动配置也可以
		$siteConfigObj = new Config("site_config");

		return array(
			'apikey' => $siteConfigObj->sms_apikey,
		);
	}

	/**
	 * @brief 发送短信
	 * @param string $mobile
	 * @param string $content
	 * @return
	 */
	public function send($mobile,$content)
	{
		$config = self::getConfig();

		$post_data = array(
			'apikey' => $config['apikey'],
			'mobile' => $mobile,
			'text' => $content,
		);

		$url    = $this->submitUrl;
		$string = '';
		foreach ($post_data as $k => $v)
		{
		   $string .="$k=".urlencode($v).'&';
		}

		$post_string = substr($string,0,-1);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
		$result = curl_exec($ch);
		return $this->response($result);
	}

	/**
	 * @brief 解析结果
	 * @param $result 发送结果
	 * @return string success or fail
	 */
	public function response($result)
	{
        $jsonData = JSON::decode($result);
        if(!$jsonData || $jsonData['code'] != 0)
        {
            return 'fail:'.$jsonData['code'].',msg:'.$jsonData['msg'];
        }

        return 'success';
	}

	/**
	 * @brief 配置文件
	 */
	public function getParam()
	{
		return array(
			"sms_userid"   => "商户ID",
			"sms_username" => "用户名",
			"sms_pwd"      => "密码",
		);
	}
}
