/**
 * register页的相关逻辑
 */

var forgetPwdManage = {};
var token="";//短信验证注册码

/*
 * 入口初始化
 */
forgetPwdManage.Init = function() {
  
};
 

/* 发送注册码 */
forgetPwdManage.sendMsg = function()
{ 
	var telephoneNum = $.trim($("#telephoneNum").val());
	/*if (!Validate.isValidMobile(telephoneNum)) {
		TipsUtil.errorTips($("#regName").attr("name"), $("#regName").attr(
				"data-title")
				+ "输入有误！");
		return;
	}*/
	jQuery.support.cors=true;
	$.ajax({
		url : _serverURL  +  "user/token",
		type : "get",
		timeout : 3000,
		xhrFields: {
            withCredentials: true
        }, 
        crossDomain: true,
		data : {
			"mobile" : telephoneNum,
			"type" : "4" // "1:注册, 2:更换手机, 3:登陆, 4:重设密码"
		},
		success : function(data) {
			var e = data;//$.parseJSON(data);
			if ($(e).attr("code") == "0") {
				time();
				if ($(e).attr("data") != null || $(e).attr("data") != "") {
					$($(e).attr("data")).each(function() {
						 token = $(this).attr("token");
						jAlert("发送注册码成功,请查看短信！"+token, null,  null, _waitTime, null, "info");
					});
				}
			}else{
					jAlert($(e).attr("prompt"), null, null,
    						_waitTime, null, "warn"); 
			}
		},
		error : function(e) {
			jAlert("发送注册码异常！", null, null, _waitTime, null, "warn");
		}
	});
}
 
	/* 重置密码*/
	forgetPwdManage.resetpswd = function()
	{ 
		var telephoneNum = $.md5($.trim($("#telephoneNum").val()));
		var new_password = $.md5($.trim($("#registerPWD").val()));
		var verifiNum =  $.trim($("#verifiNum").val());
		jQuery.support.cors=true;
		$.ajax({
			url : _serverURL  +  "user/resetpswd",
			type : "get",
			timeout : 3000,
			xhrFields: {
	            withCredentials: true
	        }, 
	        crossDomain: true,
			data : {
				"mobile": telephoneNum,
	            "password": new_password,
	            "token": verifiNum
			},
			success : function(data) {
				var e = data;//$.parseJSON(data);
				if ($(e).attr("code") == "0") {
					window.location.href ="forget_pwd_success.html";
				}else{
			       jAlert($(e).attr("prompt"), null, null,_waitTime, null, "warn"); 
				}
			},
			error : function(e) {
				jAlert("重置密码异常！", null, null, _waitTime, null, "warn");
			}
		});

}