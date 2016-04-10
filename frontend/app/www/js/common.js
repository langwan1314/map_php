/**
 * Created by windy on 15/11/8.
 */
define('www/common', function(require){
    var $ = require('jquery');
    var msg = require('ywj/msg');
    var net = require('ywj/net');
    var $body = $("body");

    var showPopup = function(conf, onSuccess, onError){
        require.async('ywj/popup', function(Pop){
            var p = new Pop(conf);
            if(onSuccess){
                p.listen('onSuccess', onSuccess);
            }
            if(onError){
                p.listen('onError', onError);
            }
            p.show();
        });
    };

    //自动slide
    $('.g-slide[data-autostart]').each(function(){
        var _this = this;
        seajs.use(['ywj/slide', 'jquerycolor'], function(S){
            var $list = $('.g-slide-list',_this);
            if($list.children()['length'] > 1){

                //init
                var setted_color = false;
                $list.children().each(function(idx){
                    if(!setted_color && $(this).data('color')){
                        setted_color = true;
                        $('.slide-bg-wrap').animate({backgroundColor: $(this).data('color')});
                    }
                    if(idx){
                        $(this).animate({opacity:1},0).hide();
                    } else {
                        $(this).show();
                    }
                });

                //nav
                var $nav = $('.g-slide-nav', _this);
                var s = new S($list);
                if($nav.size()){
                    $nav.children().click(function(){
                        return false;
                    });
                    s.addControl($('.g-slide-nav', _this));
                }

                //ctrl2
                var $ctrl = $('.g-slide-ctrl', _this);
                if($ctrl.size()){
                    $('a', $ctrl).click(function(){
                        return false;
                    });
                    $('.g-slide-prev', $ctrl).mousedown(function(event){
                        s.pause();
                        s.switchToPre();
                    });
                    $('.g-slide-next', $ctrl).mousedown(function(event){
                        s.pause();
                        s.switchToNext();
                    });
                }

                //event
                s.onSwitchTo = function($from, $to){
                    /**
                     var k = 'data-background-image';
                     var bg = $to.attr(k);
                     if(bg){
							$to.css('background-image', 'url('+bg+')');
							$to.attr(k, '');
						}
                     **/
                    $('.slide-bg-wrap').animate({
                        backgroundColor: $to.data('color')
                    }, 100);
                    $nav.children().each(function(){
                        $(this).removeClass('active');
                    });
                    $nav.children().eq($to.index()).addClass('active');
                };

                s.start(0);
            }
        });
    });

    //自动slide
    $('.main-banner').each(function(){
        var _this = this;
        seajs.use(['ywj/slide', 'jquerycolor'], function(S){
            var $list = $('.slides',_this);
            if($list.children()['length'] > 1){

                //init
                var setted_color = false;
                $list.children().each(function(idx){
                    if(!setted_color && $(this).data('color')){
                        setted_color = true;
                        $('.slide-bg-wrap').animate({backgroundColor: $(this).data('color')});
                    }
                    if(idx){
                        $(this).animate({opacity:1},0).hide();
                    } else {
                        $(this).show();
                    }
                });

                //nav
                var $nav = $('.slide-nav', _this);
                var s = new S($list);
                if($nav.size()){
                    $nav.children().click(function(){
                        return false;
                    });
                    s.addControl($('.slide-nav', _this));
                }

                //ctrl2
                var $ctrl = $('.g-slide-ctrl', _this);
                if($ctrl.size()){
                    $('a', $ctrl).click(function(){
                        return false;
                    });
                    $('.g-slide-prev', $ctrl).mousedown(function(event){
                        s.pause();
                        s.switchToPre();
                    });
                    $('.g-slide-next', $ctrl).mousedown(function(event){
                        s.pause();
                        s.switchToNext();
                    });
                }

                //event
                s.onSwitchTo = function($from, $to){
                    $('.slide-bg-wrap').animate({
                        backgroundColor: $to.data('color')
                    }, 100);
                    $nav.children().each(function(){
                        $(this).removeClass('active');
                    });
                    $nav.children().eq($to.index()).addClass('active');
                };

                s.start(0);
            }
        });
    });

    function initJoinCart()
    {
        //加入购物车动画
        function joinCartAnimate(count, sum, $joinCartBtn)
        {
            var $joinCartAnimate = $("#joinCartAnimate");
            var $topMyCartCount = $("#topMyCartCount");

            $joinCartAnimate.find("img").attr("src", $joinCartBtn.data("img_src"));
            $joinCartAnimate.css({width:60, height:60});
            var scrollTop = $(document).scrollTop();
            var x = $joinCartBtn.offset().left;
            var y = $joinCartBtn.offset().top - scrollTop;

            var aH = $joinCartAnimate.outerHeight();
            var tcX = $topMyCartCount.offset().left;
            var tcY = $topMyCartCount.offset().top - scrollTop;

            $joinCartAnimate.css({"left":x, "top":y, "height": 0}).show();

            y = y - aH;
            $joinCartAnimate.animate({'top':y, "height":aH}, 300);
            $joinCartAnimate.animate({"left":tcX, 'top':tcY, "height":5, "width": 5}, 1000, function(){
                $joinCartAnimate.hide();
                $('[name="mycart_count"]').text(count);
                $('[name="mycart_sum"]').text(sum);
            });
        }

        /**
         * 检查规格选择是否符合标准
         * @return bool
         */
        function checkSpecSelected()
        {
            if($('[name="specCols"]').length === $('[name="specCols"] .current').length) {
                return true;
            }
            return false;
        }

        //[ajax]加入购物车
        function joinCart_ajax(id, type, $joinCartBtn)
        {
            $.getJSON(WP_CONFIG.url.join_cart,{"goods_id":id,"type":type,"random":Math.random()},function(content){
                if(content.isError == false)
                {
                    var $cartCount = $('[name="mycart_count"]');
                    var count = parseInt($cartCount.html()) + 1;
                   // $cartCount.html(count);
                    joinCartAnimate(count, 0, $joinCartBtn);
                   // msg.show(content.message, "succ");
                }
                else
                {
                    msg.show(content.message, "err");
                }
            });
        }

        $body.delegate('*[rel=join_cart]', 'click', function(){
            var _this = $(this);

            if(!checkSpecSelected())
            {
                msg.show("请先选择商品的规格", "tip");
                return;
            }

            var buyNum   = parseInt($.trim($('#buyNums').val())) || 1;
            var productId = this.data("goods_id");
            var type      = productId ? 'product' : 'goods';
            var goods_id  = (type == 'product') ? productId : 2;//todo:未实现

            $.getJSON(WP_CONFIG.url.join_cart,{
                "goods_id":goods_id,
                "type":type,
                "goods_num":buyNum,
                "random":Math.random
            },function(content){
                if(content.isError == false)
                {
                    //获取购物车信息
                    $.getJSON(WP_CONFIG.url.show_cart, {"random":Math.random},function(json)
                    {
                        joinCartAnimate(json.count, json.sum);

                        //暂闭加入购物车按钮
                        _this.attr('disabled','disabled');
                    });
                }
                else
                {
                    msg.show(content.message, "err");
                }
            });
        });

        $body.delegate("*[rel=join_cart_list]", "click", function(){
            var _this = $(this);
            var id = _this.data("id");

            $.getJSON(WP_CONFIG.url.get_product, {"id":id},function(content){
                if(!content)
                {
                    joinCart_ajax(id,'goods', _this);
                }
                else
                {
                    var url = WP_CONFIG.url.block_goods_list;
                    url = url.replace('@goods_id@',id);
                    artDialog.open(url,{
                        id:'selectProduct',
                        title:'选择货品到购物车',
                        okVal:'加入购物车',
                        ok:function(iframeWin, topWin)
                        {
                            var goodsList = $(iframeWin.document).find('input[name="id[]"]:checked');

                            //添加选中的商品
                            if(goodsList.length == 0)
                            {
                                msg('请选择要加入购物车的商品', 'err');
                                return false;
                            }
                            var temp = $.parseJSON(goodsList.attr('data'));

                            //执行处理回调
                            joinCart_ajax(temp.product_id, 'product', _this);
                            return true;
                        }
                    })
                }
            });
        });
    }

    // init join favorite
    function initJoinFavorite()
    {
        $body.delegate("*[rel=join_favorite]", "click", function(){
            var _this = $(this);
            var id = _this.data("id");
            net.get(WP_CONFIG.url.join_favorite, {goods_id:id,nocache:((new Date()).valueOf())}, function(r){
                msg.show(r.message, "succ");
            });
        });
    }

    $(document).ready(function(){
        initJoinCart();
        initJoinFavorite();
    });

});