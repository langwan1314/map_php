/**
 * Created by ljy on 2015/7/23.
 *
 * index页的相关逻辑依赖
 */

/*
 * 配置appIndex依赖逻辑
 */
require.config({
    baseUrl: "js",
    paths: {
        'indexManage': "modules/index/indexManage"
    }
});

require(['jquery',
    'jquery-alerts',
    'bootstrap',
    'jquery-flexslider',
    'jquery-easing',
    'const',
	'indexManage'
], function () {

	/*
	 * UI效果和初始化
	 **/

    /* $("#footer").load("footer.html"); */

    //开启banner轮播
    $("#main-banner").flexslider({
        animation: "slide",
        useCSS: true,
        touch: true,
        controlNav: false,
        animationLoop: true,
        smoothHeight: true
    });

    //nav-left鼠标放上的效果
	var _leftImgPath = 'images/repo/nav-left/';
	
    $('.nav-left ul li').hover(function () {
					
		var _imgOriginName = $(this).find('img').attr('alt');
		
        $(this).addClass('hover').find('img').attr('src', _leftImgPath + 'hover_' + _imgOriginName);
        $(this).find('div.nav-aside').stop().show();
		
    }, function () {
	
		var _imgOriginName = $(this).find('img').attr('alt');
	
        $(this).removeClass('hover').find('img').attr('src', _leftImgPath + _imgOriginName);
        $(this).find('div.nav-aside').stop().hide();
		
    });
	
	//nav-fixed鼠标放上去的效果
	var _fixedImgPath = 'images/icons/';
	
    $('.side-fix-nav div.vertical-center-box ul li').hover(function () {
					
		var _imgOriginName = $(this).find('img').attr('alt');
		
        $(this).addClass('hover').find('img').attr('src', _fixedImgPath + 'hover_' + _imgOriginName);
        $(this).find('p').stop().show();		
		
    }, function () {
	
		var _imgOriginName = $(this).find('img').attr('alt');
	
        $(this).removeClass('hover').find('img').attr('src', _fixedImgPath + _imgOriginName);
        $(this).find('p').stop().hide();
		
    });
	
	//go-top初始化
	$(window).scroll(function(){ 
		var scrollt = document.documentElement.scrollTop + document.body.scrollTop; //获取滚动后的高度
		if( scrollt > 200 ){  //判断滚动后高度超过100px,就显示  
			$("a.go-top").fadeIn(400); //淡出     
		}else{      
			$("a.go-top").stop().fadeOut(400); //如果返回或者没有超过,就淡入.必须加上stop()停止之前动画,否则会出现闪动   
		}
	});
	
	/*
	 * 事件绑定
	 **/
	 
	//绑定向上按钮点击
	$("a.go-top").on("click", function(){
		indexManage.gotop();
	});
	 
});



