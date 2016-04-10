<?php
/**
 * @file output.php
 * @brief 处理数据存放类
 * @author misty
 * @date 2015-10-11 20:47:39
 * @version 
 */


class Output 
{
    private static $arr_error_code = array(
        "FAILED"                 => array(-1, "failed", "操作失败"), // 默认错误码
        "SUCCESS"                => array(0, "success", "操作成功"),
        "REQUEST_METHOD"         => array(1, "request method", "请求方法错误"),
        "PARAM_INVALID"          => array(2, "param invalid", "参数错误"),
        "NEED_LOGIN"             => array(3, "need login", "请先登录"),
        "NEED_RIGHT"             => array(4, "need right", "您没有操作权限"),
        "SMS_SEND"               => array(6, "sms send", "发送短信通知失败"),
        "EMAIL_SEND"             => array(7, "email send", "发送邮件失败"),
        "FORBIDDEN"              => array(8, "forbidden", "本次操作被禁止"),
        "REQUIRED_FIELD_MISSED"  => array(9, "required field missed", "请求参数不全"),
        "CAPTCHA_INVALID"        => array(10, "captcha invalid", "验证码错误"),
        "DUPLICATE_REQUEST"      => array(10, "captcha invalid", "验证码错误"),
        "TARGET_NOT_EXISTS"      => array(12, "target not exists", "请求的对象不存在"),

        // NOTE                  => 100~599保留，为避免与http错误码冲突

        // account (10000~11000)
        "USER_EXISTS"            => array(10000, "user exists", "用户已存在"),
        "USER_NOT_EXISTS"        => array(10001, "user not existing", "用户不存在"),
        "USER_INACTIVE"          => array(10002, "user inactive", "账号未激活"),
        "USER_RESTRICTED"        => array(10003, "user restricted", "账号异常，暂时无法登录，请联系客服"),
        "ACCOUNT_INVALID"        => array(10004, "account invalid", "账号无效"),
        "EMAIL_INVALID"          => array(10005, "email invalid", "email地址无效"),
        "MOBILE_INVALID"         => array(10006, "mobile invalid", "手机号码无效"),
        "MOBILE_USED"            => array(10007, "mobile used", "该手机号码已被使用"),
        "EMAIL_USED"             => array(10008, "email used", "该邮箱已被使用"),

        "INVALID_SKEY"           => array(10101, "invalid skey", "无效的登录信息"),
        "EXPIRED_SKEY"           => array(10102, "expired skey", "登录已过期"),
        "USER_ACTIVATE"          => array(10103, "user activate", "用户已经激活"),
        "TOKEN_INVALID"          => array(10104, "token invalid", "无效验证码"),
        "TOKEN_EXPIRED"          => array(10105, "token expired", "验证码过期"),
        "PASSWORD_INVALID"       => array(10106, "password invalid", "密码无效"),
        "PASSWORD_WRONG"         => array(10107, "password wrong", "密码错误"),

        "REGISTER_USER"          => array(10200, "register user", "用户注册失败"),
        "LOGIN_FAILED"           => array(10201, "login failed", "登陆失败"),
        "GET_USER_INFO"          => array(10202, "get user", "拉取用户信息失败"),

        // product (11000~12000)
        "GET_COMMEND_HOT_GOODS"  => array(11000, "get commend hot goods", "拉取热卖商品"),
        "GET_COMMEND_RECOM_GOODS" => array(11001, "get commend recom goods", "拉取推荐商品"),
        "ADD_GOODS_CART"         => array(11002, "add goods cart", "添加商品到购物车"),
        "DEL_GOODS_CART"         => array(11003, "del goods cart", "删除购物车中商品"),
        "GET_GOODS_PRODUCT"      => array(11004, "get goods product", "拉取商品货品"),
        "ADD_FAVORITE"           => array(11005, "add favorite goods", "收藏商品失败"),
        "PRODUCT_NOT_EXISTS"     => array(11006, "product not exists", "该商品不存在"),
        "PRODUCT_NOT_IN_ACTIVE"  => array(11007, "product not in active", "该商品未参与活动"),
        "VOUCHERS_INVALID"       => array(11008, "vouchers invalid", "代金券无效"),
        "COUNT_ORDER_FEE"        => array(11009, "count order fee", "计算商品金额失败"),
        "CREATE_ORDER"           => array(11010, "create order", "生成订单失败"),
        "CART_GOODS_TOGGLE"      => array(11011, "cart goods toggle", "设置失败"),
        "CART_CALC_PROMOTION"    => array(11012, "cart calc", "计算购物车价格失败"),
        "ADD_GOODS_CART"         => array(11013, "add goods cart", "添加商品到购物车失败"),
        "ADDRESS_NOT_SERVED"     => array(11014, "address not served", "该地区暂不支持配送，请重新选择收货地址"),
        "ORDER_STATUS_FORBIDDEN" => array(11015, "order status forbidden", "该订单当前状态不允许完成本次操作"),
        "GET_REFUNDS"            => array(11016, "get refunds", "拉取退款申请失败"),

        "WISHING"                => array(900000, "wishing", "世间最美的东西，就是等待和希望。")
    );

	private $result = 'SUCCESS';
    private $errinfo = '';
    private $prompt = '';
    private $data = null;

	/**
	 * @brief 构造输出类
	 * @param array $data => array('result' => 错误码, 'errinfo' => 提示信息 , 'prompt' => 提示tips文字, 'data' => 结果数据)
	 */
	public function __construct($data=null)
	{
        $this->fill($data);
	}

	public function fill($data)
	{
        if ($data && isset($data['result']))
        {
            $this->result = $data['result'];
        }
        if ($data && isset($data['errinfo']))
        {
            $this->errinfo = $data['errinfo'];
        }
        if ($data && isset($data['prompt']))
        {
            $this->prompt = $data['prompt'];
        }

        if ($data && isset($data['data']))
        {
            $this->data = $data['data'];
        }
	}

    public function set_result($result)
    {
        $this->result = $result;
    }

    public function set_errinfo($errinfo)
    {
        $this->errinfo = $errinfo;
    }

    public function set_prompt($prompt)
    {
        $this->prompt = $prompt;
    }

    public function set_data($data)
    {
        $this->data = $data;
    }

	//返回json.encode后的字符串
	public function to_string()
	{
        if (isset(self::$arr_error_code[$this->result]))
        {
            $error = self::$arr_error_code[$this->result];
        }
        else
        {
            $error = self::$arr_error_code["FAILED"];
        }

        if (!empty($this->errinfo))
        {
            $error[1] = $this->errinfo;
        }
        if (!empty($this->prompt))
        {
            $error[2] = $this->prompt;
        }

        $rsp = array("code"=>$error[0], "message"=>$error[1], "prompt"=>$error[2]);
        if ($this->data !== null)
        {
            $rsp["output"] = $this->data;
        }

        return JSON::encode($rsp);
	}
}
