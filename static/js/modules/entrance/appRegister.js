/**
 * Created by ljy on 2015/7/23.
 *
 * Register页的相关逻辑依赖
 */

/*
 * 配置appEntrance依赖逻辑
 */
require.config({
    baseUrl : "js",
    paths : {
        'registerManage' : "modules/entrance/registerManage"
    }
});

require(['jquery',
    'bootstrap',
    'const',
    'jquery-alerts',
    'jquery-md5',
    'validateUtil',
    'validateTipsUtil',
    'registerManage'
], function(){
    //TODO
	$(document).ready(function () {
		//初始化第一次加载图片验证码 
		registerManage.captcha();
	});
	 
	//绑定注册按钮点击
	$("#registerSubmit").on("click", function(){
		registerManage.register();
		//点击触发跟换
		registerManage.captcha();
	});
	 
	//点击更换验证码图片
	$("#change").on("click", function(){
		registerManage.captcha();
	});
	$("#imgChange").on("click", function(){
		registerManage.captcha();
	});
	
	
	
});
 