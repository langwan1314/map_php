/**
 * Created by windy on 2015/4/28.
 */
define('www/index', function(require){

	var $ = require('jquery');

	function initNavAnimate()
	{
		//nav-left鼠标放上的效果
		$('.nav-left ul li').hover(function () {
			$(this).addClass('hover');
			$(this).find('div.nav-aside').stop().show();

		}, function () {
			$(this).removeClass('hover');
			$(this).find('div.nav-aside').stop().hide();

		});

		//nav category 鼠标放上去的效果

		$('.nav-left .nav-content').hover(function () {
			var _imgOriginName = $(this).find('img').attr('src').replace('.png', '_write.png');

			$(this).addClass('hover').find('img').attr('src', _imgOriginName);
			$(this).find('p').stop().show();

		}, function () {
			var _imgOriginName = $(this).find('img').attr('src').replace('_write.png', '.png');

			$(this).removeClass('hover').find('img').attr('src', _imgOriginName);
			$(this).find('p').stop().hide();
		});
	}

	function initGoTop()
	{
		//go-top初始化
		$(window).scroll(function(){
			var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //获取滚动后的高度
			if( scrollt > 200 ){  //判断滚动后高度超过100px,就显示
				$("a.go-top").fadeIn(400); //淡出
			}else{
				$("a.go-top").stop().fadeOut(400); //如果返回或者没有超过,就淡入.必须加上stop()停止之前动画,否则会出现闪动
			}
		});

		//绑定向上按钮点击
		$("a.go-top").on("click", function(){
			indexManage.gotop();
		});
	}

	$(document).ready(function(){
		initNavAnimate();
		initGoTop();
	});
});

