/**
 * login页的相关逻辑
 */

var loginManage = {};

/*
 * 入口初始化
 */
loginManage.entranceInit = function() {

};

/*
 * 登录
 */
loginManage.login = function() {
	var loginName = $.trim($("#loginName").val());
	var loginPassword = $.md5($.trim($("#loginPassword").val())); 
	if (true) {
		$.ajax({
			url : _serverURL + _port + "user/login",
			type : "post",
			timeout : 3000,
			data : {
				 "account": loginName,
		          "password":loginPassword,
		          "platform": 21,     // 平台来源, android或ios登录, cookie有效期延长至1个月
			},
			success : function(data) {
				var e = data;//$.parseJSON(data);
				if ($(e).attr("code") == "0") {
					jAlert("登陆成功！", null, function() {
						// 跳转首页
						window.location.href = "index.html";
					}, _waitTime, null, "info");
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
				jAlert("登陆异常！" + e, null, null, _waitTime, null, "warn");
			}
		});
	}
}


/* 退出登陆 */
loginManage.logout = function() {
	    $.ajax({
	        url: _serverURL + "user/logout",
	        type: "get",
	        timeout: 3000,
	        data: {},
	        success: function (data) { 
	        	
	        },
	        error: function () {
	            jAlert("退出登陆异常！", null, null,
	                _waitTime, null, "warn");
	        }
	    });
}
