/**
 * 用来处理Cookie的工具类
 */
var CookieUtil = {};

/* 检测浏览器是否打开了cookie */
CookieUtil.check = function() {
	if (!(document.cookie || navigator.cookieEnabled)) {
		alert('cookie 未打开!');
	}
}

/**
 * 增加Cookie项
 * 
 * @param {String}
 *            name
 * @param {String}
 *            value
 * @param {Int}
 *            expiresHours 过期时间（单位Hour），如果小于等于0，表示忽略此数据
 * @param {String}
 *            path 可访问路径
 */
CookieUtil.setCookie = function(name, value, expiresHours, path) {
	try {
		CookieUtil.check();
		var cookieStr = name + "=" + escape(value);
		if (Validate.isValidObj(expiresHours) && expiresHours > 0) {
			var date = new Date();
			date.setTime(date.getTime() + expiresHours * 3600 * 1000);
			cookieStr += ("; expires=" + date.toGMTString());
		} else if (Validate.isValidObj(path)) {
			cookieStr += ("; path=" + escape(path));
		}
		document.cookie = cookieStr;
	} catch (e) {
		throw new Error("SetCookies: " + e.message);
	}
};

/**
 * 获取Cookie项
 * 
 * @param {String}
 *            name
 * @return cookieValue cookie值
 */
CookieUtil.getCookie = function(name) {
	var strCookie = document.cookie;
	var arrCookie = strCookie.split("; ");
	for (var i = 0; i < arrCookie.length; i++) {
		var arr = arrCookie[i].split("=");
		if (arr[0] == name)
			return unescape(arr[1]);
	}
	return null;
};

/**
 * 删除Cookie项
 * 
 * @param {String}
 *            name
 */
CookieUtil.delCookie = function(name) {
	var value = CookieUtil.getCookie(name);
	if (value) {
		document.cookie = name + "=; expires=Thu, 01-Jan-70 00:00:01 GMT;";
	}
};

/**
 * 清空所有Cookie项
 */
CookieUtil.clearCookie = function() {
	var strCookie = document.cookie;
	var arrCookie = strCookie.split("; ");
	var names = new Array();
	for (var i = 0; i < arrCookie.length; i++) {
		var arr = arrCookie[i].split("=");
		names.push(arr[0]);
	}
	for (var i = 0; i < names.length; i++) {
		CookieUtil.delCookie(names[i]);
	}
};