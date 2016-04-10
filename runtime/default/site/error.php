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
	<?php $msg = IReq::get('msg') ? IReq::get('msg') : '发生错误'?>
<div class="error wrapper clearfix">
	<table class="form_table prompt_3 f_l">
		<col width="250px" />
		<col />
		<tr>
			<th valign="top"><img src="<?php echo IUrl::creatUrl("")."views/".$this->theme."/skin/".$this->skin."/images/front/cry.gif";?>" width="122" height="98" /></th>
			<td>
				<p class="mt_10"><strong class="f14 gray"><?php echo htmlspecialchars($msg,ENT_QUOTES);?></strong></p>
				<p class="gray">您可以：</p>
				<p class="gray">1.检查刚才的输入</p>
				<p class="gray">2.到<a class="blue" href="<?php echo IUrl::creatUrl("/site/help_list");?>">帮助中心</a>寻求帮助</p>
				<p class="gray">3.去其他地方逛逛：<a href='javascript:void(0)' class='blue' onclick='window.history.go(-1);'>返回上一级操作</a>|<a class="blue" href="<?php echo IUrl::creatUrl("");?>">网站首页</a>|<a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/order");?>">我的订单</a>|<a class="blue" href="<?php echo IUrl::creatUrl("/simple/cart");?>">我的购物车</a></p>
			</td>
		</tr>
	</table>
</div>

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
