/**
 * Created by sf
 *
 * 忘记密码
 */

/*
 * 配置appEntrance依赖逻辑
 */
require.config({
    baseUrl : "js",
    paths : {
        'forgetPwdManage' : "modules/entrance/forgetPwdManage"
    }
});

require(['jquery',
    'bootstrap',
    'const',
    'jquery-alerts',
    'jquery-md5',
    'validateUtil',
    'validateTipsUtil',
    'forgetPwdManage'
], function(){
    //TODO
	$(document).ready(function () {
		 
	});
	 
	//绑定注册按钮点击
	$("#token").on("click", function(){
		forgetPwdManage.sendMsg();
	});
	
	
	$("#sumbit").on("click", function(){
		forgetPwdManage.resetpswd();
	});
 
	//下一步按钮点击
	$("#nextSec").on("click", function(){
		window.location.href ="forget_pwd_success.html";
	});
 
	
	
	
});
 