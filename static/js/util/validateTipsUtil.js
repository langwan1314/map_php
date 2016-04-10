/**
 * 通用提示函数
 */

var validateTipsUtil = {};

/**
 * 错误提示
 *
 * @param suffix
 * @param errorTxt
 */
validateTipsUtil.errorTips = function (suffix, errorTxt) {
	console.log(errorTxt);
    $("input[name='" + suffix + "']").attr("data-title", errorTxt).tooltip("show").closest(".control-group").addClass("error");
    setTimeout(function(){
    	$("input[name='" + suffix + "']").attr("data-title", "").tooltip("destroy");
    }, 5000);
}

/**
 * 普通提示
 *
 * @param suffix
 * @param normalTxt
 */
validateTipsUtil.normalTips = function (suffix, normalTxt) {
	$("input[name='" + suffix + "']").attr("data-title", normalTxt).tooltip("show").closest(".control-group").addClass("warning");
}

/**
 * 清除提示（指定元素）
 *
 * @param suffix
 */
validateTipsUtil.clearTips = function (suffix) {
    $("input[name='" + suffix + "']").closest(".control-group").removeClass("error");
}

/**
 * 清除提示（所有）
 *
 */
validateTipsUtil.clearAllTips = function () {
    $(".control-group").removeClass("error");

}