/**
 * 这是一个验证数据格式的工具类
 */
var Validate = new Object();

/*
 *自动检测input是否合法:依赖jquery
 *例子:<input type="text" vali="isValidObj,isValidMobile" data-name="电话号码" name="t1">
 */
Validate.valiForm = function(form){
	var result=true;
	var $form=$(form);
	var $chids= $form.find("input[vali]");
	$chids.each(function(){
		var $this=$(this);
		var v_name=$this.attr("name");
		var d_name=$this.attr("data-name");
		var $val=$this.val();
		var vali=$this.attr("vali");
		if(Validate.isValidObj(vali)){
			var strs=vali.split(",");
			for(var i=0;i<strs.length ;i++){
				var str=strs[i];
				$this.removeClass("error");
				var $tips=$("#tips_"+v_name);
				if($tips.length!=0){
					$tips.remove();
				}
				if(!Validate[str]($val)){
					$this.after("<div id='tips_"+v_name+"' class='tip-error' data-tool='tip'>输入值有误</div>");
					$tips=$("#tips_"+v_name);
					$tips.text(d_name+"输入有误");
					$this.addClass("error");
					$tips.css("display","table");
					$tips.addClass("animated pulse");
					result=false;
					break;
				}
			}
		}
	});
	return result;
}

/**
 * 判断元素是否合法（或为空）
 * 
 * @returns {Boolean}
 */
Validate.isValidObj = function(obj) {
	if (typeof (obj) == "undefined" || obj == null || obj == "") {

		return false;
	}
	return true;
};

/**
 * 判断密码是否小于6位
 * 
 * @returns {Boolean}
 */
Validate.isValidPassword = function(obj) {
	if (typeof (obj) == "undefined" || obj == null || obj == "") {
		return false;
	} else if (obj.length < 6) {
		return false;
	}
	return true;
};

/**
 * 判断是否为手机号码
 * 
 * @returns {Boolean}
 */
Validate.isValidMobile = function(obj) {
	if (typeof (obj) == "undefined" || obj == null || obj == "") {
		return false;
	} else if (!Validate.isNumber(obj)) {
		return false;
	} else if (!Validate.isMobile(obj)) {
		return false;
	}
	return true;
};

/**
 * 是否空对象
 * 
 * @param obj
 *            {Object}
 * @return {Boolean}
 */
Validate.isEmptyObject = function(obj) {
	for ( var name in obj) {
		return false;
	}
	return true;
};

/**
 * 判断是否是方法
 * 
 * @returns {Boolean}
 */
Validate.isFunction = function(fn) {
	if (typeof fn != "function") {
		return false;
	}
	return true;
};

/**
 * 判断是否字符串
 * 
 * @returns {Boolean}
 */
Validate.isString = function(str) {
	if (typeof str != "string") {
		return false;
	}
	return true;
};

/**
 * 判断是否JSON格式字符串
 * 
 * @param jsonstr
 *            {String} 字符串参数
 * @return {Boolean} 验证结果
 */
Validate.isJsonString = function(jsonstr) {
	var flag = false;
	if (Validate.isValidObj(jsonstr)) {
		try {
			eval("(" + jsonstr + ")");
			flag = true;
		} catch (e) {
			flag = false;
		}
	}
	return flag;
};

/**
 * 判断是否全部是中文
 * 
 * @param str
 * @returns {Boolean}
 */
Validate.isAllChn = function(str) {
	var reg = /^[\u4E00-\u9FA5]+$/;
	if (!reg.test(str)) {
		return false;
	}
	return true;
};

/**
 * 判断字符是否有中文字符
 * 
 * @param s
 * @returns {Boolean}
 */
Validate.isHasChn = function(s) {
	var patrn = /[\u4E00-\u9FA5]|[\uFE30-\uFFA0]/gi;
	if (!patrn.exec(s)) {
		return false;
	} else {
		return true;
	}
};

/**
 * 判断是否是数字
 * 
 * @param num
 *            {Object}
 * @return {Boolean}
 */
Validate.isNumber = function(num) {
	return /^\d+$/.test(num);
};

/**
 * 判断是否为正负数
 */
Validate.isReal = function(num) {
	return /^(-)?[1-9][0-9]*$/.test(num);
};
Validate.isReal1 = function(num) {
	return /^(-)?[0-9][0-9]*$/.test(num);
};
Validate.isReal2 = function(num) {
	return /^[1-9][0-9]*$/.test(num);
};

/**
 * 判断是不是IE浏览器
 */
Validate.isIE = function() {
	return navigator.userAgent.indexOf("MSIE") > 0
			|| navigator.userAgent.indexOf(".NET") > -1;
};

/**
 * 判断是不是Chrome浏览器
 */
Validate.isChrome = function() {
	return navigator.userAgent.indexOf("Chrome") > 0;
};

/**
 * 判断是不是Opera浏览器
 */
Validate.isOpera = function() {
	return navigator.userAgent.indexOf("OPR") > 0;
};

/**
 * 是否是IP
 */
Validate.isIP = function(strIP) {
	var flag = false;
	if (Validate.isValidObj(strIP)) {
		var re = /^(\d+)\.(\d+)\.(\d+)\.(\d+)$/g; // 匹配IP地址的正则表达式
		if (re.test(strIP)) {
			if (RegExp.$1 < 256 && RegExp.$2 < 256 && RegExp.$3 < 256
					&& RegExp.$4 < 256)
				flag = true;
		}
	}
	return flag;
};

/**
 * 判断是否手机号码格式
 */
Validate.isMobile = function(phone) {
	var flag = false;
	if (Validate.isValidObj(phone)) {
		var re = /(^1[3|5|8][0-9]{9}$)/;
		flag = re.test(phone);
	}
	return flag;
};

/**
 * 是否是邮箱
 */
Validate.isEmail = function(str) {
	var myReg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/;
	if (myReg.test(str))
		return true;
	return false;
};

/**
 * 是否是电话支持带区号
 */
Validate.isPhone = function(strPhone) {
	var phoneRegWithArea = /^[0][1-9][0-9]{1,2}-[0-9]{5,10}$/;
	var phoneRegWithArea1 = /^[0][1-9][0-9]{1,2}[0-9]{5,10}$/;
	var phoneRegNoArea = /^[1-9]{1}[0-9]{5,8}$/;
	if (strPhone.length > 9) {
		if (phoneRegWithArea.test(strPhone)) {
			return true;
		} else {
			if (phoneRegWithArea1.test(strPhone)) {
				return true;
			}
			return false;
		}
	} else {
		if (phoneRegNoArea.test(strPhone)) {
			return true;
		} else {
			return false;
		}
	}
};

/**
 * 判断是否是电话(支持区号)固话、移动号码通用
 * 
 * @param num
 *            {Object}
 * @return {Boolean}
 */
Validate.isTel = function(num) {
	if (Validate.isNumber(num)) {// 先判断是否是数字
		if (num.length == 11) {// 如果是11位则为移动电话
			if (Validate.isMobile(num)) {
				return true;
			} else {
				return false
			}
		} else {// 固话
			if (Validate.isPhone(num)) {
				return true;
			} else {
				return false
			}
		}
	} else {
		return false;
	}

};

/**
 * 是否是合法身份证
 */
Validate.isIDCard = function(idCard) {

	idCard = trim(idCard.replace(/ /g, "")); // 去掉字符串头尾空格
	if (idCard.length == 18) {
		var a_idCard = idCard.split(""); // 得到身份证数组
		if (isValidityBrithBy18IdCard(idCard)
				&& isTrueValidateCodeBy18IdCard(a_idCard)) { // 进行18位身份证的基本验证和第18位的验证
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
};

/**
 * 判断身份证号码为18位时最后的验证位是否正确
 * 
 * @param a_idCard
 *            身份证号码数组
 * @return
 */
function isTrueValidateCodeBy18IdCard(a_idCard) {
	var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ]; // 加权因子
	var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ]; // 身份证验证位值.10代表X
	var sum = 0; // 声明加权求和变量
	if (a_idCard[17].toLowerCase() == 'x') {
		a_idCard[17] = 10; // 将最后位为x的验证码替换为10方便后续操作
	}
	for (var i = 0; i < 17; i++) {
		sum += Wi[i] * a_idCard[i]; // 加权求和
	}
	valCodePosition = sum % 11; // 得到验证码所位置
	if (a_idCard[17] == ValideCode[valCodePosition]) {
		return true;
	} else {
		return false;
	}
}

/**
 * 验证18位数身份证号码中的生日是否是有效生日
 * 
 * @param idCard
 *            18位书身份证字符串
 * @return
 */
function isValidityBrithBy18IdCard(idCard18) {
	var year = idCard18.substring(6, 10);
	var month = idCard18.substring(10, 12);
	var day = idCard18.substring(12, 14);
	var temp_date = new Date(year, parseFloat(month) - 1, parseFloat(day));
	// 这里用getFullYear()获取年份，避免千年虫问题
	if (temp_date.getFullYear() != parseFloat(year)
			|| temp_date.getMonth() != parseFloat(month) - 1
			|| temp_date.getDate() != parseFloat(day)) {
		return false;
	} else {
		return true;
	}
}

/**
 * 是否是图片文本类型
 */
Validate.isImage = function(strImage) {
	return (/image/i).test(strImage);
};

/**
 * 判断特殊字符
 * 
 * @param str
 * @returns
 */
Validate.isContainSpecialChar = function(str) {
	var rtn = {
		flag : false,
		message : "not"
	};
	var reg = /[~$%^￥]+/;
	if (str != "") {
		if (reg.test(str)) {
			rtn.flag = true;
			rtn.message = "请勿输入：~、$、%、^、￥ 等特殊字符！";
		}
	}
	return rtn;
}
/**
 * 判断是否是手机移动端登陆
 */
Validate.isMobilecheck = function() {
	var check = false;
	(function(a) {
		if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i
				.test(a)
				|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i
						.test(a.substr(0, 4)))
			check = true
	})(navigator.userAgent || navigator.vendor || window.opera);
	return check;
}