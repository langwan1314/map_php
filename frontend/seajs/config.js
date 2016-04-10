/**
 * patch on after resolve function
 * @private
 */
var __seajs_org_resolve__ = seajs.resolve;
var __seajs_after_resolve_list = [];

/**
 * add after resolve event
 * @param callback
 */
seajs.onAfterResolve = function(callback){
	__seajs_after_resolve_list.push(callback);
};

/**
 * override resolve method
 * @param id
 * @param refUri
 * @returns {*}
 */
seajs.resolve = function(id, refUri){
	var url = __seajs_org_resolve__(id, refUri);
	for(var i=0; i<__seajs_after_resolve_list.length; i++){
		var new_url = __seajs_after_resolve_list[i].call(null, url);
		if(new_url){
			url = new_url;
		}
	}
	return url;
};


var FRONTEND_HOST = window.FRONTEND_HOST || '/frontend';

//log patch
if(!window['console']){
	window['console'] = {
		'info': function(){},
		'log': function(){},
		'error': function(){},
		'warn': function(){}
	};
}

//静态资源版本，缺省使用
(function(){
	var ver = window['STATIC_GLOBAL_VERSION'] ? 'v'+window['STATIC_GLOBAL_VERSION'] : 'v201509231552';
	var map = window['STATIC_VERSION_MAP'];
	seajs.onAfterResolve(function(url){
		if(url.substring(url.length-3) == '.js'
			||url.substring(url.length-4) == '.css'){
			if(map && map[url.tolowercase()]){
				return url + '?'+map[url.tolowercase()];
			} else {
				return url + '?'+ver;
			}
		}
		return url;
	});
})();

seajs.config({
	alias: {
		"jquery": "jquery/jquery-1.8.3.min.js",
		"jquery-1.11.2": "jquery/jquery-1.11.2.min.js",
		"jquerycolor": "jquery/jquerycolor.js",
		"jqueryMd5": "jquery/jquery.md5.js",
		"jquery-cookie":"jquery/jquery.cookie.min.js",
		"jquery/cookie":"jquery/jquery.cookie.min.js",
		"jqueryanchor": "jquery/jqueryanchor.js",
		"jquery-bxSlider":"jquery/jquery.bxSlider/jquery.bxSlider.min.js",
		"jquery-zoom":"jquery/jquery.jqzoom/js/jquery.jqzoom.js",
		"jquery/ui": "jquery/ui/jquery-ui.min.js",
		'jquery-flexslider': "jquery/jquery.flexslider-min.js",
		"jquery/ui/timepicker": "jquery/ui/jquery-ui-timepicker-addon.js",
		"jquery/ui/tooltip": "jquery/ui/jquery-ui-tooltip-addon.js",
		"swiper": "swiper/swiper.min.js",
		"waterfall": "waterfall/waterfall.js",
		"ueditor": FRONTEND_HOST+"/ueditor/ueditor.all.js",
		"ueditor_admin_config": FRONTEND_HOST+"/ueditor/ueditor.admin.js"
	},
	paths: {
		"ywj": FRONTEND_HOST+"ywj/component",
		"ywjui": FRONTEND_HOST+"ywj/ui",
		"www": FRONTEND_HOST+"app/www/js"
	},
	preload: [
		!window.jQuery ? 'jquery' : ''
	],

	charset: 'utf-8'
});
