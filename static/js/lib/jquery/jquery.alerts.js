// jQuery Alert Dialogs Plugin
//
// Version 1.0
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 29 December 2008
//
// Visit http://abeautifulsite.net/notebook/87 for more information
//
// Usage:
//		jAlert( message, [title, callback] )
//		jConfirm( message, [title, callback] )
//		jPrompt( message, [value, title, callback] )
// 
// History:
//
//		1.00 - Released (29 December 2008)
//
// License:
// 
//		This plugin is licensed under the GNU General Public License: http://www.gnu.org/licenses/gpl.html
//
;(function($) {

	$.alerts = {

		// These properties can be read/written by accessing
		// $.alerts.propertyName from your scripts at any time

		verticalOffset : -75, // vertical offset of the dialog from center
		// screen, in pixels
		horizontalOffset : 0, // horizontal offset of the dialog from center
		// screen, in pixels
		repositionOnResize : true, // re-centers the dialog on window resize
		overlayOpacity : .01, // transparency level of overlay
		overlayColor : '#FFF', // base color of overlay
		draggable : true, // make the dialogs draggable (requires UI
		// Draggables plugin)
		okButton : '&nbsp;确认&nbsp;', // text for the OK button
		cancelButton : '&nbsp;取消&nbsp;', // text for the Cancel button
		yesButton : '&nbsp;确定&nbsp;', // text for the OK button
		noButton : '&nbsp;取消&nbsp;', // text for the Cancel button
		customButton : null, // custom text for the message button (is OK
		// button)
		dialogClass : null, // if specified, this class will be applied to all
		// dialogs
		timeoutHandle : null, // 
		offsetLeft : null, // the offset left of the dialog from center screen,
		// in pixels
		offsetTop : null, // the offset top of the dialog from center screen,
		// in pixels

		// Public methods

		loading : function(message, title, callback, timeout, offsetLeft,
				offsetTop) {
			if (typeof title == "undefined" || title == null)
				title = '提示';
			if (typeof offsetLeft == "undefined" || offsetLeft == null)
				offsetLeft = 0;
			if (typeof offsetTop == "undefined" || offsetTop == null)
				offsetTop = 0;
			this.offsetLeft = offsetLeft;
			this.offsetTop = offsetTop;
			this.dialogClass = 'loading';
			$.alerts._show(title, message, null, 'loading', function(result) {
				if (callback)
					callback(result);
			}, timeout);
		},

		alert : function(message, title, callback, timeout, btntxt) {
			if (typeof title == "undefined" || title == null)
				title = '提示';
			if (typeof btntxt != "undefined" && btntxt != null) {
				this.customButton = btntxt;
			}
			$.alerts._show(title, message, null, 'alert', function(result) {
				if (callback)
					callback(result);
			}, timeout);
		},

		popup : function(message, title, callback, timeout, btntxt,
				dialogClass, offsetLeft, offsetTop) {
			if (typeof title == "undefined" || title == null)
				title = '提示';
			if (typeof offsetLeft == "undefined" || offsetLeft == null)
				offsetLeft = 0;
			if (typeof offsetTop == "undefined" || offsetTop == null)
				offsetTop = 0;
			if (typeof dialogClass != "undefined" && dialogClass != null)
				this.dialogClass = dialogClass;
			if (typeof btntxt != "undefined" && btntxt != null) {
				this.customButton = btntxt;
			}
			this.offsetLeft = offsetLeft;
			this.offsetTop = offsetTop;
			$.alerts._show(title, message, null, 'alert', function(result) {
				if (callback)
					callback(result);
			}, timeout);
		},

		confirm : function(message, title, callback, timeout, offsetLeft,
				offsetTop) {
			if (typeof title == "undefined" || title == null)
				title = '确认';
			if (typeof offsetLeft == "undefined" || offsetLeft == null)
				offsetLeft = 0;
			if (typeof offsetTop == "undefined" || offsetTop == null)
				offsetTop = 0;
			this.offsetLeft = offsetLeft;
			this.offsetTop = offsetTop;
			this.dialogClass = 'confirm';
			$.alerts._show(title, message, null, 'confirm', function(result) {
				if (callback)
					callback(result);
			}, timeout);
		},

		prompt : function(message, value, title, callback, timeout, offsetLeft,
				offsetTop) {
			if (typeof title == "undefined" || title == null)
				title = '输入';
			if (typeof offsetLeft == "undefined" || offsetLeft == null)
				offsetLeft = 0;
			if (typeof offsetTop == "undefined" || offsetTop == null)
				offsetTop = 0;
			this.dialogClass = 'confirm';
			this.offsetLeft = offsetLeft;
			this.offsetTop = offsetTop;
			$.alerts._show(title, message, value, 'prompt', function(result) {
				if (callback)
					callback(result);
			}, timeout);
		},

		// Private methods

		_show : function(title, msg, value, type, callback, timeout) {
			$.alerts._hide();
			$.alerts._overlay('show');

			$("BODY")
					.append(
							'<div id="popup_container">'
									+ '<div id="popup_close" class="close" style="border-radius: 4px; display: none"></div>'
									+ '<h1 id="popup_title"></h1>'
									+ '<div id="popup_content">'
									+ '<div id="popup_message"></div>'
									+ '</div>' + '</div>');

			if ($.alerts.dialogClass) {
				$("#popup_container").addClass($.alerts.dialogClass);
			}

			// IE8+ Absolute
			var pos = 'absolute';

			$("#popup_container").css({
				position : 'absolute',
				zIndex : 200000,
				padding : 0,
				margin : 0
			});

			$("#popup_title").text(title);
			$("#popup_message").text(msg);
			$("#popup_message").html(
					$("#popup_message").text().replace(/\n/g, '<br />'));

			$("#popup_container").css({
				minWidth : $("#popup_container").outerWidth(),
				maxWidth : $("#popup_container").outerWidth()
			});

			$.alerts._reposition();
			$.alerts._maintainPosition(true);

			switch (type) {
			case 'loading':
				$("#popup_message")
						.after(
								'<div id="popup_panel" style="display:none;"><input type="button" value="'
										+ $.alerts.okButton
										+ '" id="popup_ok"/></div>');
				$("#popup_ok").click(function() {
					$.alerts._clear();
					$.alerts._hide();
					callback(true);
				});
				$("#popup_ok").focus().keypress(function(e) {
					if (e.keyCode == 13 || e.keyCode == 27)
						$("#popup_ok").trigger('click');
				});
				$("#popup_close").click(function() {
					jOk();
				});
				break;
			case 'alert':
				if (typeof this.customButton == "undefined"
						|| this.customButton == null || this.customButton == "") {
					$("#popup_message")
							.after(
									'<div id="popup_panel" style="display:none;"><input type="button" class="btn btn-primary" value="'
											+ $.alerts.okButton
											+ '" id="popup_ok" /></div>');
				} else {
					$("#popup_message")
							.after(
									'<div id="popup_panel" style="display:none;"><input type="button" class="btn btn-primary" value="'
											+ $.alerts.customButton
											+ '" id="popup_ok" /></div>');
				}
				$("#popup_ok").click(function() {
					$.alerts._clear();
					$.alerts._hide();
					callback(true);
				});
				$("#popup_ok").focus().keypress(function(e) {
					if (e.keyCode == 13 || e.keyCode == 27)
						$("#popup_ok").trigger('click');
				});
				$("#popup_close").css("display", "");
				$("#popup_close").click(function() {
					jOk();
				});
				break;
			case 'confirm':
				$("#popup_close").css("display", "");
				$("#popup_message")
						.after(
								'<div id="popup_panel"><input type="button" class="btn btn-primary" value="'
										+ $.alerts.yesButton
										+ '" id="popup_ok" /> <input type="button" class="btn" style="margin-left:20px;" value="'
										+ $.alerts.noButton
										+ '" id="popup_cancel" /></div>');
				$("#popup_ok").click(function() {
					$.alerts._clear();
					$.alerts._hide();
					if (callback)
						callback(true);
				});
				$("#popup_cancel").click(function() {
					$.alerts._hide();
					if (callback)
						callback(false);
				});
				$("#popup_cancel").focus();
				$("#popup_ok, #popup_cancel").keypress(function(e) {
					if (e.keyCode == 13)
						$("#popup_ok").trigger('click');
					if (e.keyCode == 27)
						$("#popup_cancel").trigger('click');
				});
				$("#popup_close").click(function() {
					jCancel();
				});
				break;
			case 'prompt':
				$("#popup_close").css("display", "");
				$("#popup_message")
						.append(
								'<br /><input type="text" size="30" id="popup_prompt" />')
						.after(
								'<div id="popup_panel"><input type="button" class="btn btn-primary" value="'
										+ $.alerts.okButton
										+ '" id="popup_ok" /> <input type="button" class="btn" style="margin-left:20px;" value="'
										+ $.alerts.cancelButton
										+ '" id="popup_cancel" /></div>');
				$("#popup_prompt").width($("#popup_message").width());
				$("#popup_ok").click(function() {
					$.alerts._clear();
					var val = $("#popup_prompt").val();
					$.alerts._hide();
					if (callback)
						callback(val);
				});
				$("#popup_cancel").focus();
				$("#popup_cancel").click(function() {
					$.alerts._hide();
					if (callback)
						callback(null);
				});
				$("#popup_prompt, #popup_ok, #popup_cancel").keypress(
						function(e) {
							if (e.keyCode == 13)
								$("#popup_ok").trigger('click');
							if (e.keyCode == 27)
								$("#popup_cancel").trigger('click');
						});
				if (value)
					$("#popup_prompt").val(value);
				$("#popup_prompt").focus().select();
				$("#popup_close").click(function() {
					jCancel();
				});
				break;
			}
			// Make draggable
			if ($.alerts.draggable) {
				try {
					$("#popup_container").draggable({
						handle : $("#popup_title")
					});
					$("#popup_title").css({
						cursor : 'move'
					});
				} catch (e) { /* requires jQuery UI draggables */
				}
			}

			// setTimeout
			if (!(typeof timeout == "undefined" || timeout == null || parseInt(timeout) == 0)) {
				$.alerts._clear();
				$.alerts.timeoutHandle = setTimeout(function() {
					$.alerts._hide();
					if (callback)
						callback();
				}, timeout);
			}
		},

		_clear : function() {
			if ($.alerts.timeoutHandle) {
				clearTimeout($.alerts.timeoutHandle);
			}
		},

		_hide : function() {
			$("#popup_container").remove();
			$.alerts._overlay('hide');
			$.alerts._maintainPosition(false);
		},

		_overlay : function(status) {
			switch (status) {
			case 'show':
				$.alerts._overlay('hide');
				$("BODY").append('<div id="popup_overlay"></div>');
				$("#popup_overlay").css({
					position : 'absolute',
					zIndex : 99998,
					top : '0px',
					left : '0px',
					width : '100%',
					height : $(document).height(),
					background : $.alerts.overlayColor,
					opacity : $.alerts.overlayOpacity
				});
				break;
			case 'hide':
				$("#popup_overlay").remove();
				break;
			}
		},

		_reposition : function() {
			var py = top.pageYOffset;

			var o = this;
			var oTopPare = o.offsetTop;
			while (o.offsetParent != null) {
				oParent = o.offsetParent;
				oTopPare += oParent.offsetTop; // Add parent top position
				o = oParent;
			}
			o = this;
			var oLeftPare = o.offsetLeft;
			while (o.offsetParent != null) {
				oParent = o.offsetParent;
				oLeftPare += oParent.offsetLeft; // Add parent top position
				o = oParent;
			}

			var top1 = ((window.screen.height - $("#popup_container")
					.outerHeight()) / 2)
					+ $.alerts.verticalOffset + oTopPare;
			var left = ((window.screen.width - $("#popup_container")
					.outerWidth()) / 2);
			top1 = top1 + py;
			left = left;
			if (top < 0)
				top1 = 0;
			if (left < 0)
				left = 0;

			$("#popup_container").css({
				top : top1 + 'px',
				left : left + 'px'
			});
			$("#popup_overlay").height($(document).height());
		},

		_maintainPosition : function(status) {
			if ($.alerts.repositionOnResize) {
				switch (status) {
				case true:
					$(window).bind('resize', function() {
						$.alerts._reposition();
					});
					break;
				case false:
					$(window).unbind('resize');
					break;
				}
			}
		}

	};

	// Shortuct functions
	jLoading = function(message, title, callback, timeout, offsetLeft,
			offsetTop) {
		$.alerts.loading(message, title, callback, timeout, offsetLeft,
				offsetTop);
	};

	// Shortuct functions
	/**
	 * 统一弹出框显示
	 * 
	 * @param message
	 *            显示的消息(必须的)
	 * @param [title]
	 *            标题
	 * @param [callback]
	 *            回调方法
	 * @param [timeout]
	 *            超时时间(毫秒)
	 * @param [okBtnTxt]
	 *            确认按钮显示的自定义文本
	 * @param [dialogClass]
	 *            自定义样式(info、notify、warn……)
	 * @param [offsetLeft]
	 *            居左偏移量(px)
	 * @param [offsetTop]
	 *            居上偏移量(px)
	 */
	jAlert = function(message, title, callback, timeout, okBtnTxt, dialogClass,
			offsetLeft, offsetTop) {
		$.alerts.popup(message, title, callback, timeout, okBtnTxt,
				dialogClass, offsetLeft, offsetTop);
	};

	// Shortuct functions
	jConfirm = function(message, title, callback, timeout, offsetLeft,
			offsetTop) {
		$.alerts.confirm(message, title, callback, timeout, offsetLeft,
				offsetTop);
	};

	// Shortuct functions
	jPrompt = function(message, value, title, callback, timeout, offsetLeft,
			offsetTop) {
		$.alerts.prompt(message, value, title, callback, timeout, offsetLeft,
				offsetTop);
	};

	// Shortuct functions
	/**
	 * 统一弹出框显示
	 * 
	 * @param message
	 *            显示的消息(必须的)
	 * @param [title]
	 *            标题
	 * @param [callback]
	 *            回调方法
	 * @param [timeout]
	 *            超时时间
	 * @param [okBtnTxt]
	 *            确认按钮显示的自定义文本
	 * @param [dialogClass]
	 *            自定义样式(info、notify、warn……)
	 * @param [offsetLeft]
	 *            居左偏移量(px)
	 * @param [offsetTop]
	 *            居上偏移量(px)
	 */
	jPopup = function(message, title, callback, timeout, okBtnTxt, dialogClass,
			offsetLeft, offsetTop) {
		$.alerts.popup(message, title, callback, timeout, okBtnTxt,
				dialogClass, offsetLeft, offsetTop);
	};

	// Shortuct functions
	jOk = function() {
		$("#popup_ok").trigger('click');
	};

	// Shortuct functions
	jCancel = function() {
		$("#popup_cancel").trigger('click');
	};

})(jQuery);