/**
 * Created by ljy on 2015/7/23.
 *
 * entrance页的相关逻辑依赖
 */

/*
 * 配置appLogin依赖逻辑
 */
require.config({
    baseUrl : "js",
    paths : {
        'loginManage' : "modules/entrance/loginManage"
    }
});

require(['jquery',
    'bootstrap',
    'const',
    'jquery-md5',
    'jquery-alerts',
    'validateUtil',
    'validateTipsUtil',
    'loginManage'
], function(){
    //TODO
	$(document).ready(function () { 
	});
	
	
	//绑定登陆按钮点击
	$("#loginSubmit").on("click", function(){
		loginManage.login();
	});
	
});