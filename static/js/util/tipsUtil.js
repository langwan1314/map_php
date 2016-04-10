/**
 * 通用提示函数
 */

var TipsUtil = new Object();

/**
 * 错误提示
 *
 * @param suffix
 * @param errorTxt
 */
TipsUtil.errorTips = function (suffix, errorTxt) {
    $("#tips_" + suffix).text(errorTxt);
    $("input[name='" + suffix + "']").addClass("error");
    $(".input-group > div").animate({
        height: '75px'
    }, 300, function () {
        $("#tips_" + suffix).addClass("inline-block");
    });
    $("#tips_" + suffix).addClass("animated pulse");
    $("#tips_" + suffix).addClass("tip-error");
}

TipsUtil.errorTips4Textarea = function (suffix, errorTxt) {
    $("#tips_" + suffix).text(errorTxt);
    $("textarea[name='" + suffix + "']").addClass("error");
    $(".input-group > div").animate({
        height: '75px'
    }, 300, function () {
        $("#tips_" + suffix).addClass("inline-block");
    });
    $("#tips_" + suffix).addClass("animated pulse");
    $("#tips_" + suffix).addClass("tip-error");
}
/**
 * 正常提示
 *
 * @param suffix
 * @param normalTxt
 */
TipsUtil.normalTips = function (suffix, normalTxt) {
    $("#tips_" + suffix).text(normalTxt);
    $(".input-group > div").animate({
        height: '75px'
    }, 300, function () {
        $("#tips_" + suffix).addClass("inline-block");
    });
    $("#tips_" + suffix).addClass("animated pulse");
    $("#tips_" + suffix).addClass("tip-common");
}

/**
 * 清除提示（指定元素）
 *
 * @param suffix
 */
TipsUtil.clearTips = function (suffix) {
    $("#tips_" + suffix).text("");
    $("input[name='" + suffix + "']").removeClass("error");
    $("#tips_" + suffix).removeClass("inline-block").hide();
    if (!$(".input-group > div input").hasClass("error")) {
        $(".input-group > div").animate({
            height: '55px'
        });
    }
}

/**
 * 清除提示（指定元素）
 *
 * @param suffix
 */
TipsUtil.clearTips4Textarea = function (suffix) {
    $("#tips_" + suffix).text("");
    $("textarea[name='" + suffix + "']").removeClass("error");
    $("#tips_" + suffix).removeClass("inline-block").hide();
    if (!$(".input-group > div textarea").hasClass("error")) {
        $(".input-group > div").animate({
            height: '55px'
        });
    }
}
/**
 * 清除提示（所有）
 *
 */
TipsUtil.clearAllTips = function () {
    $(".input-group input").text("").removeClass("error");
    $(".input-group > div div").removeClass("inline-block").hide();
    $(".input-group > div").animate({
        height: '55px'
    });
}