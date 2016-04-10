<?php
/**
 * @file appaccount.php
 * @brief
 * @author misty
 * @date 2015-10-11 22:33
 * @version
 * @note
 */

define('TOKEN_TYPE_REGISTER', 1);
define('TOKEN_TYPE_VERIFY', 2);
define('TOKEN_TYPE_BINDMOBI', 3);

/**
 * @brief app专用
 * @class APPAccount
 * @note
 */
class APPAccount extends IAPPController
{
    public static $arr_valid_token = array(
        TOKEN_TYPE_REGISTER => '注册',
        TOKEN_TYPE_VERIFY => '校验',
        TOKEN_TYPE_BINDMOBI => '绑定手机',
    );

    function init()
    {
        CheckRights::checkAppUserRights();
    }

    //用户注册
    function register()
    {
        $mobile = IFilter::act(IReq::get('mobile', 'post'));
        $password = IFilter::act(IReq::get('password', 'post'));
        $nick_name = IFilter::act(IReq::get('nick_name', 'post'));
        $token = IFilter::act(IReq::get('token', 'post'));

        //获取注册配置参数
        $siteConfig = new Config('site_config');
        $reg_option = $siteConfig->reg_option;
        /*注册信息校验*/
        $this->output->set_result("PARAM_INVALID");
        if ($reg_option == 2) {
            $this->output->fill(array('result' => 'FORBIDDEN', 'prompt' => '当前禁止新用户注册'));
            return;
        }
        if (IValidate::mobi($mobile) == false) {
            $this->output->fill(array('result' => 'MOBILE_INVALID', 'prompt' => '手机号码格式不正确'));
            return;
        }
//    	if($nick_name && IValidate::account($nick_name) == false)
//    	{
//            $this->output->fill(array('result'=>'EMAIL_INVALID', 'prompt'=>'昵称格式不正确'));
//            return;
//    	}
        if (!$token || IValidate::token($token) == false) {
            $this->output->fill(array('result' => 'TOKEN_INVALID', 'prompt' => '短信校验码不正确'));
            return;
        }
        echo $nick_name;
        if (!Util::is_username($nick_name)) {
            $message = '用户名必须是由2-20个字符，可以为字数，数字下划线和中文';
            $this->output->set_prompt($message);
            return;
        }

        if (!preg_match('|\S{6,32}|', $password)) {
            $message = '密码必须是字母，数字，下划线组成的6-32个字符';
            $this->output->set_prompt($message);
            return;
        }
//        $token_data = IRedis::get('token_' . $mobile);
//        if ($token_data && is_array($token_data)) {
//            //短信验证码已经过期
//            if (time() > $token_data['expire_time']) {
//                $this->output->set_result("TOKEN_EXPIRED");
//                $this->output->set_prompt("您的短信校验码已经过期，请重新获取");
//                return;
//            }
//        } else {
//            $this->output->set_result("TOKEN_INVALID");
//            $this->output->set_prompt("您输入的短信校验码错误");
//            return;
//        }
        $userObj = new IModel('IMUser');
        $where = '(mobile = "' . $mobile . '" or nick_name = "' . $nick_name . '")';
//        if ($email && !empty($email))
//        {
//            $where .= ' or email = "'.$email;
//        }
        $userRow = $userObj->getObj($where);

        if ($userRow) {
            if ($mobile == $userRow['mobile'] || $mobile == $userRow['username']) {
                $this->output->set_result("MOBILE_USED");
                $message = "此手机号已经被注册过，请重新更换";
            } else {
                $this->output->set_result("EMAIL_USED");
                $message = '此昵称已经被其他账号关联，请重新更换';
            }
            $this->output->set_prompt($message);
            return;
        }

        //IMUser表
        $userArray = array(
            'mobile' => $mobile,
            'nick_name' => $nick_name,
            'password' => md5($password),
            'created' => ITime::getDateTime(),
            'updated' => ITime::getDateTime(),
        );
//        if ($email && !empty($email))
//        {
//            $userArray['email'] = $email;
//        }
        $userObj->setData($userArray);
        $user_id = $userObj->add();

        if ($user_id) {
//            //member表
//            $memberArray = array(
//                'user_id' => $user_id,
//                'time'    => ITime::getDateTime(),
//                'status'  => $reg_option == 1 ? 3 : 1,
//            );
//
//            $memberObj = new IModel('member');
//            $memberObj->setData($memberArray);
//            $memberObj->add();

            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('user_id' => $user_id));
            return;
        }

        $message = '对不起，注册失败';
        $this->output->set_result("REGISTER_USER");
        $this->output->set_prompt($message);
    }

    //用户登录
    function login()
    {
        if ($this->user != null) {
            $this->output->set_result("SUCCESS");
            $this->output->set_data($this->user);
            return;
        }
        $mobile = IFilter::act(IReq::get('mobile', 'post'));
        $password = IReq::get('password', 'post');
        //$mobile = IFilter::act(IReq::get('mobile'));
        //$password = IReq::get('password');
        $password = md5($password);
        $message = '';

        if ($mobile == '' || !IValidate::mobi($mobile)) {
            $message = '请填写正确的登录手机号';
            $this->output->set_result("MOBILE_INVALID");
            $this->output->set_prompt($message);
            return;
        }
        if (!preg_match('|\S{6,32}|', $password)) {
            $message = '密码格式不正确,请输入6-32个字符';
            $this->output->set_result("PASSWORD_INVALID");
            $this->output->set_prompt($message);
            return;
        }

        if ($userRow = CheckRights::isValidUser($mobile, $password)) {
            $user = CheckRights::appLoginAfter($userRow);
            $this->output->set_result("SUCCESS");
            $this->output->set_data($user);
            return;
        } else {
            //是否状态问题
            $message = '登录失败';
            $userDB = new IModel('user as u,member as m');
            $userRow = $userDB->getObj(" u.mobile = '{$mobile}' and password = '{$password}' and u.id = m.user_id");
            if ($userRow) {
                $siteConfig = new Config('site_config');
                if ($userRow['status'] == 3) {
                    $message = '您的账号已经被锁定';
                } else if ($userRow['status'] == 2) {
                    $message = '您的账号已经被放置回收站内';
                }
                $this->output->set_result("USER_RESTRICTED");
                $this->output->set_prompt($message);
            } else {
                $message = '用户名和密码不匹配';
                $this->output->set_result("PASSWORD_INVALID");
                $this->output->set_prompt($message);
            }
            return;
        }
    }

    //退出登录
    function logout()
    {
        if ($this->user) {
            checkRights::appLogoutAfter();
        }
        $this->output->set_result("SUCCESS");
        return;
    }

    /**
     * @brief 邮箱找回密码进行
     */
    function find_password_email()
    {
        $email = IReq::get("email");
        if ($email === null || !IValidate::email($email)) {
            $this->output->fill(array("result" => "EMAIL_INVALID", "prompt" => "请输入正确的邮箱地址"));
            return;
        }

        $tb_user = new IModel("user");
        $email = IFilter::act($email);
        $user = $tb_user->getObj(" email='{$email}' ");
        if (!$user) {
            return $this->output->fill(array("result" => "USER_NOT_EXISTS", "prompt" => "该用户不存在"));
        }
        $hashids = new Hashids\Hashids(WM::$app->config['encryptKey'], 16);
        $hash = $hashids->encode(microtime(true) . mt_rand());

        //重新找回密码的数据
        IRedis::set('pswd_' . $hash, $user['id'], 24 * 3600);
        $url = IUrl::getHost() . IUrl::creatUrl("/appaccount/restore_password/hash/{$hash}");
        $content = mailTemplate::findPassword(array("{url}" => $url));

        $smtp = new SendMail();
        $result = $smtp->send($user['email'], "您的密码找回", $content);

        if ($result === false) {
            return $this->output->fill(array("result" => "EMAIL_SEND"));
        }

        $message = "密码重置邮件已经发送，请到您的邮箱中去激活";
        $this->output->fill(array("result" => "SUCCESS", "prompt" => $message));
    }

    //手机短信找回密码
    function find_password_mobile()
    {
        return $this->moblie_hash();
    }

    function mobile_hash()
    {
        $mobile = IReq::get('mobile');
        if ($mobile === null || !IValidate::mobi($mobile)) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("请输入正确的电话号码");
            return;
        }

        $token = IReq::get('token');
        if ($token === null || !IValidate::token($token)) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("请输入正确的短信验证码");
            return;
        }

        $userDB = new IModel('user');
        $userRow = $userDB->getObj('mobile = "' . $mobile . '"');
        if ($userRow) {
            $exist_token = IRedis::get('token_' . $mobile);
            if (!$exist_token || !is_array($exist_token)) {
                $this->output->set_result("TOKEN_INVALID");
                $this->output->set_prompt("短信验证码无效");
                return;
            }

            if ($exist_token['token'] != $token) {
                $this->output->set_result("TOKEN_INVALID");
                $this->output->set_prompt("您输入的短信校验码错误");
                return;
            }

            if (time() > $exist_token['expire_time']) {
                $this->output->set_result("TOKEN_EXPIRED");
                $this->output->set_prompt("您的短信校验码已经过期了，请重新找回密码");
                return;
            }


            $hashids = new Hashids\Hashids(WM::$app->config['encryptKey'], 16);
            $hash = $hashids->encode(time() + intval($token));
            IRedis::set('hash_' . $hash, $userRow['id'], 3600);  // 一个小时小效

            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('hash' => $hash));
            return;
        } else {
            $this->output->set_result("MOBILE_NOT_EXISTS");
            $this->output->set_prompt("该手机号码尚未绑定账户");
            return;
        }
    }

    //绑定手机号
    function bind_mobile()
    {
        if (!$this->user) {
            return $this->output->set_result("NEED_LOGIN");
        }

        $mobile = IReq::get('mobile');
        if ($mobile === null || !IValidate::mobi($mobile)) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("请输入正确的电话号码");
            return;
        }

        $token = IReq::get('token');
        if ($token === null || !IValidate::token($token)) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("请输入正确的短信验证码");
            return;
        }

        $userDB = new IModel('user');
        $userRow = $userDB->getObj('mobile = "' . $mobile . '"');
        var_dump($userRow);
        if ($userRow === false || !empty($userRow)) {
            $this->output->set_result("MOBILE_EXISTS");
            $this->output->set_prompt("该手机号码已绑定账户");
            return;
        }

        $exist_token = IRedis::get('token_' . $mobile);
        if (!$exist_token || !is_array($exist_token)) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("短信验证码无效");
            return;
        }

        if ($exist_token['token'] != $token) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("您输入的短信校验码错误");
            return;
        }

        if (time() > $exist_token['expire_time']) {
            $this->output->set_result("TOKEN_EXPIRED");
            $this->output->set_prompt("您的短信校验码已经过期了，请重新获取");
            return;
        }

        $userObj = new IModel('user');
        $userObj->setData(array("mobile" => $mobile));
        $userObj->update('id = ' . $this->user['user_id']);

        $this->output->set_result("SUCCESS");
        return;
    }

    /**
     * @brief 执行密码重置操作
     */
    function reset_password()
    {
        $hash = IFilter::act(IReq::get("hash"));
        if (!$hash) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("本次操作校验码无效");
            return;
        }

        $user_id = IRedis::get('hash_' . $hash);
        if (!$user_id) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("本次操作校验码有误或已失效，请重新获取");
            return;
        }

        //开始修改密码
        $pwd = IReq::get("password");
        if ($pwd == null || strlen($pwd) < 6) {
            $this->output->set_result("PATH_TRANSLATED");
            $this->output->set_prompt("新密码格式有误，请重新输入");
            return;
        }

        $pwd = md5($pwd);
        $tb_user = new IModel("user");
        $tb_user->setData(array("password" => $pwd));
        $ret = $tb_user->update("id={$user_id}");
        if ($ret !== false) {
            IRedis::clear('pswd_' . $hash);
            // 如果用户已登录, 重置密码后需注销之前的登录态
            checkRights::appLogoutAfter($user_id);
            $this->output->set_result("SUCCESS");
            $this->output->set_prompt("修改密码成功");
            return;
        }

        $this->output->set_result("RESET_PASSWORD");
        $this->output->set_prompt("修改密码失败，请重试");
        return;
    }

    /**
     * @brief 执行密码修改操作
     */
    function change_password()
    {
        if (!$this->user) {
            return $this->output->set_result("NEED_LOGIN");
        }

        $user_id = $this->user['user_id'];

        //$mobile = IFilter::act(IReq::get('mobile','post'));
        //$password = IReq::get('password','post');
        $old_password = IReq::get('old_password');
        $new_password = IReq::get('new_password');
        $old_password = md5($old_password);
        $new_password = md5($new_password);

        if (!preg_match('|\S{6,32}|', $old_password)) {
            $this->output->set_result("PASSWORD_INVALID");
            $this->output->set_prompt("旧密码格式不正确，请输入6-32个字符");
            return;
        }
        if (!preg_match('|\S{6,32}|', $old_password)) {
            $this->output->set_result("PASSWORD_INVALID");
            $this->output->set_prompt("新密码格式不正确，请输入6-32个字符");
            return;
        }

        $userObj = new IModel('user');
        $where = 'id = ' . $user_id;
        $userRow = $userObj->getObj($where);

        if ($old_password != $userRow['password']) {
            $this->output->set_result("PASSWORD_INVALID");
            $this->output->set_prompt("原始密码错误，请确认后重新输入");
            return;
        }

        $dataArray = array('password' => $new_password);
        $userObj->setData($dataArray);
        $result = $userObj->update($where);
        if ($result) {
            checkRights::appLogoutAfter();
            $this->output->set_result("SUCCESS");
            $this->output->set_prompt("密码修改成功，请使用新密码重新登录");
            return;
        } else {
            $this->output->set_result("SUCCESS");
            $this->output->set_prompt("密码修改失败");
            return;
        }
    }

    /**
     * @brief 修改手机号
     */
    function change_mobile()
    {
        if (!$this->user) {
            return $this->output->set_result("NEED_LOGIN");
        }

        $mobile = IReq::get("mobile");
        if (!$mobile || IValidate::mobi($mobile) == false) {
            $this->output->set_result("MOBILE_INVALID");
            $this->output->set_prompt("请填写正确的手机号码");
            return;
        }

        $hash = IFilter::act(IReq::get("hash"));
        if (!$hash) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("本次操作校验码无效");
            return;
        }

        $user_id = IRedis::get('hash_' . $hash);
        if (!$user_id) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("本次操作校验码有误或已失效，请重新获取");
            return;
        }

        $token = IFilter::act(IReq::get("token"));
        if (!$token || IValidate::token($token) == false) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("短信校验码无效");
            return;
        }

        $token_data = IRedis::get('token_' . $mobile);
        if ($token_data && is_array($token_data)) {
            //短信验证码已经过期
            if (time() > $token_data['expire_time']) {
                $this->output->set_result("TOKEN_EXPIRED");
                $this->output->set_prompt("您的短信校验码已经过期，请重新获取");
                return;
            }
        } else {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("您输入的短信校验码错误");
            return;
        }

        $userObj = new IModel('user');
        $where = '(mobile = "' . $mobile . '" or username = "' . $mobile . '")';
        $userRow = $userObj->getObj($where);

        if ($userRow) {
            $this->output->set_result("MOBILE_USED");
            $this->output->set_prompt("此手机号已被使用");
            return;
        }

        $userObj->setData(array('mobile' => $mobile));
        $userObj->update('id = ' . $user_id);

        $this->output->set_result("SUCCESS");
    }

    //发送手机验证码短信
    function token()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        $token_type = IFilter::act(IReq::get('type'), 'int');
        if ($mobile === null || !IValidate::mobi($mobile)) {
            $this->output->set_result("MOBILE_INVALID");
            $this->output->set_prompt("请输入正确的手机号码");
            return;
        }

        if ($token_type === null || !is_numeric($token_type) || !isset(self::$arr_valid_token[$token_type])) {
            $this->output->set_result("TOKEN_INVALID");
            $this->output->set_prompt("验证码类型无效");
            return;
        }

        $user_id = 0;
        if ($token_type == TOKEN_TYPE_VERIFY) {
            $userDB = new IModel('user as u');
            $userRow = $userDB->getObj('u.mobile = "' . $mobile . '"');
            if (!$userRow) {
                $this->output->set_result("USER_NOT_EXISTS");
                $this->output->set_prompt("该手机号尚未注册");
                return;
            }

            $user_id = $userRow['id'];
        }

        $exist_token = IRedis::get('token_' . $mobile);
        if ($exist_token && (time() + 60 < $exist_token['expire_time'])) {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("申请验证码的时间间隔过短，请稍候再试");
            return;
        }

        $token = rand(10000, 99999);

        //if ($token_type == self::TOKEN_TYPE_REGISTER)
        $expire = 60; // 先统一 60 秒后过期
        if (IRedis::set('token_' . $mobile, array('token' => $token, 'expire_time' => time() + $expire), $expire)) {
            $content = smsTemplate::findPassword(array('{mobile_code}' => $token));
            $result = Hsms::send($mobile, $content);
            if ($result == 'success') {
                $this->output->set_result("SUCCESS");
                return;
            }
        }

        $this->output->set_result("SMS_SEND");
        $this->output->set_errinfo($result);
        $this->output->set_prompt("发送短信验证码失败，请稍候重试");
        return;
    }

    public function oauth_login()
    {
        $oauth = IFilter::act(IReq::get('oauth'));
        $openid = IFilter::act(IReq::get('openid'));
        $nickname = IFilter::act(IReq::get('nickname'));
        $headico = IFilter::act(IReq::get('nickname'));
        if ($userRow = CheckRights::isValidUser($mobile, $password)) {
            $user = CheckRights::appLoginAfter($userRow);
            $this->output->set_result("SUCCESS");
            $this->output->set_data($user);
            return;
        } else {
            //是否状态问题
            $message = '登录失败';
            $userDB = new IModel('user as u,member as m');
            $userRow = $userDB->getObj(" u.mobile = '{$mobile}' and password = '{$password}' and u.id = m.user_id");
            if ($userRow) {
                $siteConfig = new Config('site_config');
                if ($userRow['status'] == 3) {
                    $message = '您的账号已经被锁定';
                } else if ($userRow['status'] == 2) {
                    $message = '您的账号已经被放置回收站内';
                }
                $this->output->set_result("USER_RESTRICTED");
                $this->output->set_prompt($message);
            } else {
                $message = '用户名和密码不匹配';
                $this->output->set_result("PASSWORD_INVALID");
                $this->output->set_prompt($message);
            }
            return;
        }

        if (!$oauth) {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("未知的第三方登录方式");
            return;
        }

        $oauthObj = new Oauth($oauth);
        $result = $oauthObj->checkStatus($_GET);
    }

    //同步绑定用户数据
    public function bind_user($userInfo, $oauthId)
    {
        $oauthUserObj = new IModel('oauth_user');
        $oauthUserRow = $oauthUserObj->getObj("oauth_user_id = '{$userInfo['id']}' and oauth_id = '{$oauthId}' ", 'user_id');

        //没有绑定账号
        if (empty($oauthUserRow)) {
            $userObj = new IModel('user');
            $userCount = $userObj->getObj("username = '{$userInfo['name']}'", 'count(*) as num');

            //没有重复的用户名
            if ($userCount['num'] == 0) {
                $username = $userInfo['name'];
            } else {
                //随即分配一个用户名
                $username = $userInfo['name'] . $userCount['num'];
            }

            ISafe::set('oauth_username', $username);
            ISession::set('oauth_id', $oauthId);
            ISession::set('oauth_userInfo', $userInfo);

            $this->redirect('bind_user');
        } //存在绑定账号
        else {
            $userObj = new IModel('user');
            $tempRow = $userObj->getObj("id = '{$oauthUserRow['user_id']}'");
            $userRow = CheckRights::isValidUser($tempRow['username'], $tempRow['password']);
            CheckRights::loginAfter($userRow);

            //自定义跳转页面
            $callback = ISafe::get('callback');

            if ($callback && !strpos($callback, 'reg') && !strpos($callback, 'login')) {
                $this->redirect($callback);
            } else {
                $this->redirect('/ucenter/index');
            }
        }
    }

    //绑定已存在用户
    public function bind_exists_user()
    {
        $mobile = IReq::get('mobile');
        $password = IReq::get('password');
        $oauth_id = IFilter::act(ISession::get('oauth_id'));
        $oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

        if (!$oauth_id || !isset($oauth_userInfo['id'])) {
            $this->output->set_result("NEED_LOGIN");
            return;
        }

        if ($userRow = CheckRights::isValidUser($mobile, md5($password))) {
            $oauthUserObj = new IModel('oauth_user');

            //插入关系表
            $oauthUserData = array(
                'oauth_user_id' => $oauth_userInfo['id'],
                'oauth_id' => $oauth_id,
                'user_id' => $userRow['user_id'],
                'datetime' => ITime::getDateTime(),
            );
            $oauthUserObj->setData($oauthUserData);
            $oauthUserObj->add();

            CheckRights::loginAfter($userRow);

            //自定义跳转页面
            $this->output->set_result("SUCCESS");
            return;
        } else {
            $this->output->set_result('MOBILE_USED');
            $this->output->set_prompt('用户名和密码不匹配');
            return;
        }
    }

    //绑定不存在用户
    public function bind_nexists_user()
    {
        $mobile = IFilter::act(IReq::get('mobile'));
        $oauth_id = IFilter::act(ISession::get('oauth_id'));
        $oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

        /*注册信息校验*/
        if (IValidate::mobi($mobile) == false) {
            $message = '手机号码格式不正确';
            $this->output->set_result("MOBILE_INVALID");
            $this->output->set_prompt($message);
            return;
        }

        $userObj = new IModel('user');
        $where = ' mobile = "' . $mobile;
        $userRow = $userObj->getObj($where);

        if (!empty($userRow)) {
            if ($mobile == $userRow['mobile']) {
                $message = '此手机号码已经被注册过，请重新更换';
                $this->output->set_result("MOBILE_USED");
                $this->output->set_prompt($message);
            }
        } else {
            $userData = array(
                'mobile' => $moible,
                'password' => md5(ITime::getDateTime()),
            );
            $userObj->setData($userData);
            $user_id = $userObj->add();

            $memberObj = new IModel('member');
            $memberData = array(
                'user_id' => $user_id,
                'true_name' => $oauth_userInfo['name'],
                'last_login' => ITime::getDateTime(),
                'sex' => isset($oauth_userInfo['sex']) ? $oauth_userInfo['sex'] : 1,
                'time' => ITime::getDateTime(),
            );
            $memberObj->setData($memberData);
            $memberObj->add();

            $oauthUserObj = new IModel('oauth_user');

            //插入关系表
            $oauthUserData = array(
                'oauth_user_id' => $oauth_userInfo['id'],
                'oauth_id' => $oauth_id,
                'user_id' => $user_id,
                'datetime' => ITime::getDateTime(),
            );
            $oauthUserObj->setData($oauthUserData);
            $oauthUserObj->add();

            $userRow = CheckRights::isValidUser($userData['mobile'], $userData['password']);
            CheckRights::loginAfter($userRow);

            $this->output->set_result("SUCCESS");
        }
    }
}
