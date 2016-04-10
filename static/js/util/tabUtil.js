/**
 * @require Jquery.js locationUtil.js
 * 处理右侧页面的选项卡切换问题
 *
 * data-tab : 指定要切换的页，对应该页的id属性
 * data-callprev ：可指定切页前调用的函数
 * data-callback : 可指定切页后回调函数
 */
var _TabCallPrev = null;
var _TabLastObj = null;
var _TabObj = null;
$(document).ready(function () {
    $(".slider-title ul li").off().on("click", function () {
        var result = true;
        if (_TabObj && $(_TabObj).attr("data-tab") == $(this).attr("data-tab")) {
            return;
        }
        if (_TabCallPrev) {
            result = eval(_TabCallPrev + "()");
        }
        if (result) {
            _TabObj = $(this);
            _TabLastObj = null;
            if ($(this).attr("data-callprev")) {
                _TabCallPrev = $(this).attr("data-callprev");
            } else {
                _TabCallPrev = null;
            }
            $(".slider-title ul li").removeClass("menuOn");
            $(this).addClass("menuOn");
            $(".slider").attr("class", "slider-none");
            $("#" + $(this).attr("data-tab")).attr("class", "slider");
            if ($(this).attr("data-callback")) {
                eval($(this).attr("data-callback") + "()");
            }
        } else {
            _TabLastObj = $(this);
        }
    });
    if ($("html").attr("data-tab")) {
        $(".slider-title ul li").eq($("html").attr("data-tab")).trigger("click");
    } else {
        $(".slider-title ul li").eq(0).trigger("click");
    }
});

/**
 * 继续TAB触发事件，主要用于阻挡了Tab切换的情况
 */
function _TabContinue() {
    if (_TabLastObj) {
        $(_TabLastObj).trigger("click");
    }
}