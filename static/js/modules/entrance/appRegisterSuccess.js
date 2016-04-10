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
    'validateUtil',
    'validateTipsUtil',
    'registerManage'
], function(){
    //TODO
	$(document).ready(function () { 
	});
	  
});
 