
/**
 * 字符串处理工具类
 * @returns {String}
 */
var StringUtil = {};

/**
 * 字符串转换为日期对象
 * 
 * @param date
 *            Date 格式为yyyy-MM-dd HH:mm:ss，必须按年月日时分秒的顺序，中间分隔符不限制
 */
StringUtil.strToDate = function(dateStr) {
	var data = dateStr;
	var reCat = /(\d{1,4})/gm;
	var t = data.match(reCat);
	t[1] = t[1] - 1;
	eval('var d = new Date(' + t.join(',') + ');');
	return d;
};

/**
 * 把指定格式的字符串转换为日期对象yyyy-MM-dd HH:mm:ss
 * 
 */
StringUtil.strFormatToDate = function(formatStr, dateStr) {
	var year = 0;
	var start = -1;
	var len = dateStr.length;
	if ((start = formatStr.indexOf('yyyy')) > -1 && start < len) {
		year = dateStr.substr(start, 4);
	}
	var month = 0;
	if ((start = formatStr.indexOf('MM')) > -1 && start < len) {
		month = parseInt(dateStr.substr(start, 2)) - 1;
	}
	var day = 0;
	if ((start = formatStr.indexOf('dd')) > -1 && start < len) {
		day = parseInt(dateStr.substr(start, 2));
	}
	var hour = 0;
	if (((start = formatStr.indexOf('HH')) > -1 || (start = formatStr
			.indexOf('hh')) > 1)
			&& start < len) {
		hour = parseInt(dateStr.substr(start, 2));
	}
	var minute = 0;
	if ((start = formatStr.indexOf('mm')) > -1 && start < len) {
		minute = dateStr.substr(start, 2);
	}
	var second = 0;
	if ((start = formatStr.indexOf('ss')) > -1 && start < len) {
		second = dateStr.substr(start, 2);
	}
	return new Date(year, month, day, hour, minute, second);
};

/**
 * 去空格
 * 
 * @param str
 * @returns
 */
function trim(str) {
	return str.replace(/(^\s*)|(\s*$)/g, "");
}
