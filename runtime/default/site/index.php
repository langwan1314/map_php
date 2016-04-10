<!DOCTYPE html>
<?php 
$myCartObj  = new Cart();
$myCartInfo = $myCartObj->getMyCart();
$siteConfig = new Config("site_config");
$callback   = IReq::get('callback') ? urlencode(IFilter::act(IReq::get('callback'),'url')) : '';
?>
<html>
<head>
	<!--[if lt IE 9]>
<noscript>
	<style>
		.html5-wrappers {display: none !important;  }
	</style>
	<div class="ie-noscript-warning">您的浏览器禁用了脚本，请<a href="">查看这里</a>来启用脚本!或者<a href="/?noscript=1">继续访问</a>
	</div>
</noscript>
<![endif]-->
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $siteConfig->name;?></title>
<link type="image/x-icon" href="/favicon.ico" rel="icon">
<link rel="stylesheet" href="<?php echo IUrl::creatUrl("")."views/".$this->theme."/skin/".$this->skin."/css/index.css";?>" />
<link rel="stylesheet" href="<?php echo IUrl::creatUrl("")."frontend/app/www/css/common.css";?>" />
<script type="text/javascript">
	var WP_CONFIG = {
		url:{
			join_cart:"<?php echo IUrl::creatUrl("/simple/joinCart");?>",
			show_cart:"<?php echo IUrl::creatUrl("/simple/showCart");?>",
			get_product:"<?php echo IUrl::creatUrl("/simple/getProducts");?>",
			block_goods_list:"<?php echo IUrl::creatUrl("/block/goods_list/goods_id/@goods_id@/type/radio/is_products/1");?>",
			join_favorite:"<?php echo IUrl::creatUrl("/simple/favorite_add");?>"
		}
	};
</script>
<?php $STATIC_GLOBAL_VERSION = '20151020'?>
<script>
	var FRONTEND_HOST = 'http://www.crycoder.com/frontend/';
	var STATIC_GLOBAL_VERSION = <?php echo isset($STATIC_GLOBAL_VERSION)?$STATIC_GLOBAL_VERSION:"";?>;
</script>
<script type="text/javascript" src="/frontend/jquery/jquery-1.8.3.min.js?v<?php echo isset($STATIC_GLOBAL_VERSION)?$STATIC_GLOBAL_VERSION:"";?>"></script>
<script type="text/javascript" src="/frontend/seajs/sea.js?v<?php echo isset($STATIC_GLOBAL_VERSION)?$STATIC_GLOBAL_VERSION:"";?>"></script>
<script type="text/javascript" src="/frontend/seajs/config.js?v<?php echo isset($STATIC_GLOBAL_VERSION)?$STATIC_GLOBAL_VERSION:"";?>"></script>
<script>
	seajs.use('www/common');
	seajs.use('ywj/auto');
</script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type='text/javascript' src="<?php echo IUrl::creatUrl("")."views/".$this->theme."/javascript/common.js";?>"></script>
<script type='text/javascript' src='<?php echo IUrl::creatUrl("")."views/".$this->theme."/javascript/site.js";?>'></script>
<link rel="shortcut icon" href="/favicon.ico}">
<link rel="stylesheet" href="<?php echo IUrl::creatUrl("")."frontend/app/www/css/proj-theme.css";?>"/>
<link rel="stylesheet" href="<?php echo IUrl::creatUrl("")."frontend/app/www/css/animate.css";?>"/>
<?php $sonline = new Sonline();$sonline->show($siteConfig->phone,$siteConfig->service_online);?>
</head>
<body>
<header class="p-header">
	<div class="p-container">
		<div class="h-left h-nav">
			<p class="h-index pull-left"><a href="/">首页</a><span>欢迎来到开心菜园！</span></p>
			<?php if($this->user){?>
			<p class="h-login"><a href="<?php echo IUrl::creatUrl("/ucenter/index?callback=".$callback."");?>"><?php echo $this->user['username'];?></a><a href="<?php echo IUrl::creatUrl("/simple/logout");?>">安全退出</a></p>
			<?php }else{?>
			<p class="h-login">请<a href="<?php echo IUrl::creatUrl("/simple/login?callback=".$callback."");?>">登录</a><a href="<?php echo IUrl::creatUrl("/simple/reg?callback=".$callback."");?>">注册</a></p>
			<?php }?>

		</div>
		<div class="h-right h-nav">
			<div class="mycart" id="headMyCart">
				<dl class="cart-info">
					<dt><a href="<?php echo IUrl::creatUrl("/simple/cart");?>">购物车<b name="mycart_count" id="topMyCartCount"><?php echo isset($myCartInfo['count'])?$myCartInfo['count']:"";?></b>件</a></dt>
					<dd><a href="<?php echo IUrl::creatUrl("/simple/cart");?>">去结算>></a></dd>
				</dl>

				<!--购物车浮动div 开始-->
				<div class="shopping" id='div_mycart' style='display:none;'>
				</div>
				<!--购物车浮动div 结束-->

				<!--购物车模板 开始-->
				<script type='text/html' id='cartTemplete'>
					<dl class="cartlist">
						<%for(var item in goodsData){%>
						<%var data = goodsData[item]%>
						<dd id="site_cart_dd_<%=item%>">
							<div class="pic f_l"><img width="55" height="55" src="<%=data['img']%>"></div>
							<h3 class="title f_l"><a href="<?php echo IUrl::creatUrl("/site/products/id/<%=data['goods_id']%>");?>"><%=data['name']%></a></h3>
							<div class="price f_r t_r">
								<b class="block">￥<%=data['sell_price']%> x <%=data['count']%></b>
								<input class="del" type="button" value="删除" onclick="removeCart('<?php echo IUrl::creatUrl("/simple/removeCart");?>','<%=data['id']%>','<%=data['type']%>');$('#site_cart_dd_<%=item%>').hide('slow');" />
							</div>
						</dd>
						<%}%>

						<dd class="static"><span>共<b name="mycart_count"><%=goodsCount%></b>件商品</span>金额总计：<b name="mycart_sum">￥<%=goodsSum%></b></dd>

						<%if(goodsData){%>
						<dd class="static">
							<a href="<?php echo IUrl::creatUrl("/simple/cart");?>" class="go_cart fr">去购物车结算</a>
						</dd>
						<%}%>
					</dl>
				</script>
				<!--购物车模板 结束-->
			</div>
			<p class="h-app"><a>APP下载</a></p>
		</div>
	</div>
	<script type="text/javascript">
		<?php $word = IReq::get('word') ? IFilter::act(IReq::get('word'),'text') : '输入关键字...'?>

		seajs.use(['jquery', 'ywj/msg'], function($, msg){

			//init search input function
			function initSearch()
			{
				var $searchInput = $("#searchInput");
				$searchInput.val("<?php echo isset($word)?$word:"";?>");
				$searchInput.bind({
					keyup:function(){autoComplete('<?php echo IUrl::creatUrl("/site/autoComplete");?>','<?php echo IUrl::creatUrl("/site/search_list/word/@word@");?>','<?php echo isset($siteConfig->auto_finish)?$siteConfig->auto_finish:"";?>');}
				});
			}
			initSearch();

			var mycartLateCall = new lateCall(200,function(){showCart('<?php echo IUrl::creatUrl("/simple/showCart");?>')});

			//购物车div层
			$("#headMyCart").hover(
					function(){
						$(this).addClass('active');
						mycartLateCall.start();
					},
					function(){
						mycartLateCall.stop();
						$('#div_mycart').hide('slow');
						$(this).removeClass('active');
					}
			);
		});
	</script>
</header>
<div class="p-container p-search clear">
	<div class="s-logo">
		<a href="<?php echo IUrl::creatUrl("/");?>">
			<img alt="开心菜园" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/logo.png";?>">
		</a>
	</div>

	<div class="s-search">
		<div class="center-block">
			<form class="s-form" action="<?php echo IUrl::creatUrl("/");?>">
				<input type='hidden' name='controller' value='site' />
				<input type='hidden' name='action' value='search_list' />
				<input type="text" name="word" class="s-input" autocomplete="off" id="searchInput" value="输入关键字..." />
				<input type="submit" value="搜索" class="s-btn" onclick="checkInput('word','输入关键字...');" id="searchBtn"/>
				<label for="searchInput" class="search-ico"></label>
				<p class="hot-word"><span>热门关键字：</span>
					<?php foreach(Api::run('getKeywordList') as $key => $item){?>
					<?php $tmpWord = urlencode($item['word']);?>
					<a href="<?php echo IUrl::creatUrl("/site/search_list/word/".$tmpWord."");?>"><?php echo isset($item['word'])?$item['word']:"";?></a>
					<?php }?>
				</p>
			</form>
		</div>
	</div>

	<div class="s-right" style="display:none;">
		<a href="#"><img alt="one-key" src="/frontend/app/www/img/common/one.png"><span>一站式购齐</span></a>
		<a href="#"><img alt="88" src="/frontend/app/www/img/common/88.png"><span>满88包邮</span></a>
	</div>
</div>
<div class="p-nav">
	<div class="p-container">
		<ul class="nav">
			<li class="nav-li nav-category">商品分类</li>
			<li class="nav-li">积分商城</li>
			<li class="nav-li">开心菜园</li>
			<li class="nav-li">关于我们</li>
		</ul>
	</div>
</div>
<div class="p-container">
	<?php echo Ad::show(1);?>
	<link rel="stylesheet" href="<?php echo IUrl::creatUrl("")."frontend/app/www/css/index.css";?>"/>
<!-- END: nav-header -->
<!-- BEGIN: nav-left top and banner -->
<section class="p-container clearfix">
	<nav class="nav-left col-left">
		<ul class="nav-ul1">
			<?php $category_top_list = Api::run('getCategoryListTop');?>
			<?php foreach($category_top_list as $key => $first){?>
			<li class="nav-content item<?php echo isset($key)?$key:"";?>">
				<figure><img src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/category_$first[id].png";?>" alt="" class="ico"><span><?php echo isset($first['name'])?$first['name']:"";?></span><span class="ri">></span></figure>
				<ul class="nav-ul2">
					<?php $second_items = Api::run('getCategoryByParentid',array('#parent_id#',$first['id']));?>
					<?php foreach($second_items as $k => $second){?>
					<li><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a></li>
					<?php if($k >= 3) break;?>
					<?php }?>
				</ul>
				<p class="pull-right"><i class="glyphicon glyphicon-menu-right"></i></p>
				<div class="nav-aside">
					<h4 class="green"><?php echo isset($first['name'])?$first['name']:"";?></h4>
					<ul>
						<?php foreach($second_items as $key => $second){?>
						<li><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a></li>
						<?php }?>
					</ul>
				</div>
			</li>
			<?php }?>
		</ul>
	</nav>
	<figure id="main-banner" class="col-middle flexslider main-banner" data-autostart="1">
		<ul class="slides no-offset" id="slideContent">
			<?php $ad_list = Ad::getAdList(11);?>
			<?php foreach($ad_list as $key => $item){?>
			<li><a href="<?php echo IUrl::creatUrl("".$item['link']."");?>"><img alt="<?php echo isset($item['name'])?$item['name']:"";?>" src="<?php echo $this->getImg($item['content']);?>"/></a></li>
			<?php }?>
		</ul>
		<ul class="slide-nav">
			<?php $ad_list = Ad::getAdList(11);?>
			<?php foreach($ad_list as $k => $item){?>
			<li <?php if($k==0){?> class="active" <?php }?>><a href="<?php echo IUrl::creatUrl("".$item['link']."");?>"></a></li>
			<?php }?>
		</ul>
	</figure>
	<figure class="r-aside01 col-right nav-ad-right">
		<?php $ad_list2 = Ad::getAdList(13);?>
		<?php foreach($ad_list2 as $key => $item){?>
		<p><a href="<?php echo IUrl::creatUrl("".$item['link']."");?>"><img alt="<?php echo isset($item['name'])?$item['name']:"";?>" src="<?php echo $this->getImg($item['content']);?>"/></a></p>
		<?php }?>
	</figure>
</section>
<!-- END: nav-left top and banner -->
<!-- BEGIN: goods list block -->
<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
<?php $category_ad_list = array_group(Ad::getAdList(14, -1), 'goods_cat_id', true);?>
<?php $hot_goods_list = array_group(Api::run('getCommendHot',50), 'goods_cat_id');?>
<section id="list01" class="goods-list-block p-container clearfix">
	<header class="row">
		<h3 class="col-left"><?php echo isset($first['name'])?$first['name']:"";?></h3>
		<ul class="col-middle">
			<!-- <li><a>最新</a></li>
			<li><a>最热</a></li> -->
		</ul>
		<p class="col-right t-right"><a class="pull-right" href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>">更多&gt;&gt;</a></p>
	</header>
	<figure class="goods-tags-block01 col-left category_ad">
		<p><img alt="banner_left01" src="<?php echo $this->getImg($category_ad_list[$first['id']]['content']);?>"></p>
	</figure>
	<div class="col-middle goods-list">
		<ul>
			<?php foreach(Api::run('getCategoryExtendList',array('#categroy_id#',$first['id']),8) as $key => $item){?>
			<li>
				<div class="goods-show">
					<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>">
						<img alt="<?php echo isset($item['name'])?$item['name']:"";?>" src="<?php echo $this->getImg($item['img'], 165, 165);?>">
					</a>
					<p class="goods-describe"><a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
					<p class="shop-widget"><span><i>&yen;<?php echo isset($item['market_price'])?$item['market_price']:"";?></i></span><span>&yen;<i><?php echo isset($item['sell_price'])?$item['sell_price']:"";?></i></span></p>

				</div>
			</li>
			<?php }?>
		</ul>
	</div>
	<figure class="hot-sale col-right">
		<div class="hot-sale-title01"><span class="hot_write">热销榜</span></div>
		<ul>
			<?php foreach(Api::run('getCommendHotByCategoryId', array('#category_id#',$first['id']), 4) as $k => $item){?>
			<?php $pic_num = $k+1;?>
			<li class="hot-li">
				<div class="fl img">
					<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>">
						<img src="<?php echo $this->getImg($item['img'], 58, 58);?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>">
					</a>
				</div>

				<div class="fl desc">
					<p class="hot_orange">NO.<?php echo isset($pic_num)?$pic_num:"";?></p>
					<p class="desc_word"><a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
				</div>
			</li>
			<?php }?>
		</ul>
	</figure>
</section>
<?php }?>
<!-- END: goods list block -->
</section>
<!-- END: MAIN -->
<!-- BEGIN: brand promote -->
<section class="brand-promote clearfix">
<div class="self-container">
	<ul>
		<li><img src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/common/promote_1.png";?>"><p>品类齐全 轻松购物</p></li>
		<li><img src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/common/promote_2.png";?>"><p>多仓直发 极速配送</p></li>
		<li><img src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/common/promote_3.png";?>"><p>正品行货 精城服务</p></li>
		<li><img src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/common/promote_4.png";?>"><p>天天低价 畅选无优</p></li>
	</ul>
</div>
</section>
<!-- BEGIN: brand promote -->
<!-- BEGIN: sider fix bar -->
<nav class="side-fix-nav affix" style="display:none;">
<div class="vertical-center-box">
	<a class="cart"><img alt="cart" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/ico_fix_cart.png";?>"><span>购物车</span></a>
	<i class="divide"></i>
	<ul>
		<li><a><img alt="ico_fix_balance.png" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/ico_fix_balance.png";?>"></a>
		<p><a>账户余额</a><i></i></p></li>
		<li><a><img alt="ico_fix_favorite.png" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/ico_fix_favorite.png";?>"></a>
		<p><a>关注</a><i></i></p></li>
		<li><a><img alt="ico_fix_collect.png" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/ico_fix_collect.png";?>"></a>
		<p><a>收藏</a><i></i></p></li>
		<li><a><img alt="ico_fix_history.png" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/icons/ico_fix_history.png";?>"></a>
		<p><a>历史记录</a><i></i></p></li>
	</ul>
	<a class="go-top"><img alt="gotop" src="<?php echo IUrl::creatUrl("")."frontend/app/www/img/common/gotop.png";?>"></a>
</div>
</nav>
<script>
	seajs.use(['www/index']);
</script>
</body>
</html>

</div>
<div class="p-container">
<div class="help m_10">
	<div class="cont clearfix">
		<?php foreach(Api::run('getHelpCategoryFoot') as $key => $helpCat){?>
		<dl>
			<dt><a href="<?php echo IUrl::creatUrl("/site/help_list/id/".$helpCat['id']."");?>"><?php echo isset($helpCat['name'])?$helpCat['name']:"";?></a></dt>
			<?php foreach(Api::run('getHelpListByCatidAll',array('#cat_id#',$helpCat['id'])) as $key => $item){?>
			<dd><a href="<?php echo IUrl::creatUrl("/site/help/id/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></dd>
			<?php }?>
		</dl>
		<?php }?>
	</div>
</div>
<?php echo IFilter::stripSlash($siteConfig->site_footer_code);?>
</div>
<!--public template-->
<div id="joinCartAnimate">
	<img src="">
</div>
<!--end public template-->
</body>
</html>
