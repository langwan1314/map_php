/**
 * index的相关逻辑
 */

var indexManage = {};

/*
 * 主页初始化
 */
indexManage.indexInit = function() {

}

/*
 * go-top点击
 */
indexManage.gotop = function(){ 
	$("html,body").animate({scrollTop:"0px"},500,"linear");
};