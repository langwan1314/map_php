/**
 * register页的相关逻辑
 */

var registerManage = {};

/*
 * 入口初始化
 */
registerManage.registerInit = function() {
  
};

/*
 * 获取注册码
 */
registerManage.captcha = function() {
	$("#img").attr("src",_serverURL + _port + "user/captcha?"+Date.parse(new Date()));
}
/*
 * 注册
 */
registerManage.register = function() {
	//inputClick();
	
	
	var registerName = $.trim($("#registerName").val());
	var password =$.md5(($.trim($("#registerPWD").val())));
	var captcha = $.trim($("#captcha").val());
	if (true) {
		$.ajax({
			url : _serverURL + _port + "user/register",
			type : "post",
			timeout : 3000,
			data : {
				"account" : registerName,
				"password" : password,
				"captcha" : captcha
			},
			success : function(data) {
				var e = data;//$.parseJSON(data);
				if ($(e).attr("code") == "0") {
				    window.location.href ="register_success.html";
						  
				} else {
					if ($(e).attr("code") == "10000") {
						jAlert("用户已存在！", null, null, _waitTime, null, "warn");
					} else if ($(e).attr("code") == "10006") {
						jAlert("该手机号码已经注册！", null, null, _waitTime, null, "warn");
					} else if ($(e).attr("code") == "10104") {
						jAlert("无效验证码！", null, null, _waitTime, null, "warn");
					} else if ($(e).attr("code") == "10105") {
						jAlert("验证码过期！", null, null, _waitTime, null, "warn");
					} else if ($(e).attr("code") == "10200") {
						jAlert("用户注册失败！", null, null, _waitTime, null, "warn");
					}else{
						jAlert($(e).attr("prompt"), null, null,
	    						_waitTime, null, "warn"); 
					}
				}
			},
			error : function(e) {
				jAlert("注册异常！" + e, null, null, _waitTime, null, "warn");
			}
		});
	}
}

/* 发送注册码 */
registerManage.sendMsg = function()
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
			"type" : "1" // "1:注册, 2:更换手机, 3:登陆, 4:重设密码"
		},
		success : function(data) {
			var e = data;//$.parseJSON(data);
			if ($(e).attr("code") == "0") {
				time();
				if ($(e).attr("data") != null || $(e).attr("data") != "") {
					$($(e).attr("data")).each(function() {
						var token = $(this).attr("token");
						jAlert("发送注册码成功,请查看短信！"+token, null,  null, _waitTime, null, "info");
					});
				}
			}else{
				if ($(e).attr("code") == "10000") {
					jAlert("用户已存在！", null, null, _waitTime, null, "warn");
				} else if ($(e).attr("code") == "10006") {
					jAlert("该手机号码已经注册！", null, null, _waitTime, null, "warn");
				} else if ($(e).attr("code") == "10104") {
					jAlert("无效验证码！", null, null, _waitTime, null, "warn");
				} else if ($(e).attr("code") == "10105") {
					jAlert("验证码过期！", null, null, _waitTime, null, "warn");
				} else if ($(e).attr("code") == "10200") {
					jAlert("用户注册失败！", null, null, _waitTime, null, "warn");
				}else if ($(e).attr("code") == "5") {
					jAlert("注册码已经发出请稍后再试！", null, null, _waitTime, null, "warn");
				}else{
					jAlert($(e).attr("prompt"), null, null,
    						_waitTime, null, "warn"); 
				} 
			}
		},
		error : function(e) {
			jAlert("发送注册码异常！", null, null, _waitTime, null, "warn");
		}
	});

}