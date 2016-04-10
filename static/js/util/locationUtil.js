/**
 * require validate.js
 * 路径的工具类
 */
var LocationUtil = {};

var appName = "x-web/";

/**
 * 从Location获取参数
 * @param {String} name
 * @return {String}
 */
LocationUtil.getParameter = function(name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null)
		return unescape(r[2]);
	return null;
};

/**
 * 通过指定的URL获取对应的参数
 * @param {String} url
 * @param {String} name
 * @return {String}
 */
LocationUtil.getUrlParameter = function(url, name) {
	if (url.indexOf("?") != -1) {
		var str = url.split("?")[1];
		var strs = str.split("&");
		for (var i = 0; i < strs.length; i++) {
			if (name == strs[i].split("=")[0]) {
				return strs[i].split("=")[1];
			}
		}
	}
	return null;
};

/**
 * 从Location获取服务器的域名
 * @return {String}
 */
LocationUtil.getServerUrl = function() {
	var host = document.location.host;//服务器，包括端口
	var pathname = document.location.pathname;//当前 URL 的路径部分
	var base = "/";
	if (pathname.toString().indexOf(appName) == 1) {
		base += appName;
	}
	return ("http://" + host + base);
};

/**
 * 
 * @param {String} url
 * @return {String}
 */
LocationUtil.encodeUrl = function(url) {
	if (Validate.isValid(url) && Validate.isString(url)) {
		var surl = new StringBuffer();
		if (url.toString().contains("?")) {
			var u = url.split("?");
			surl.append(u[0]);
			surl.append(";");
			surl.append("sid=");
			surl.append($("#sessionuuid").val());
			surl.append("?");
			surl.append(u[1]);
		} else {
			surl.append(url);
			surl.append(";");
			surl.append("sid=");
			surl.append($("#sessionuuid").val());
		}
		return surl.toString();
	}
	return null;
};