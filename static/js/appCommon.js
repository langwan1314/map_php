/**
 * 配置系统通用JS依赖管理
 */

require.config({
    baseUrl: "js",
    paths: {
        'jquery': "lib/jquery/jquery.min",
        'jquery-ajaxfileupload': "lib/jquery/jquery.ajaxfileupload",
        'jquery-alerts': "lib/jquery/jquery.alerts",
        'jquery-paging': "lib/jquery/jquery.paging",
        'jquery-easing': "lib/jquery/jquery.easings",
        'jquery-flexslider': "lib/jquery/jquery.flexslider-min",
        'jquery-ui': "lib/jquery/jquery.ui.core",
        'jquery-ui-widget': "lib/jquery/jquery.ui.widget",
        'jquery-ui-rcarousel': "lib/jquery/jquery.ui.rcarousel",
        'jquery-md5':"lib/jquery/jquery.md5",
        'bootstrap': "lib/bootstrap/bootstrap.min",
        'const': "util/const",
        'cookieUtil': "util/cookieUtil",
        'createBaseUtil': "util/createBaseUtil",
        'dateUtil': "util/dateUtil",
        'locationUtil': "util/locationUtil",
        'storageUtil': "util/storageUtil",
        'stringUtil': "util/stringUtil",
        'tabUtil': "util/tabUtil",
        'tipsUtil': "util/tipsUtil",
        'uuidUtil': "util/uuidUtil",
        'validateUtil': "util/validateUtil",
        'validateTipsUtil': "util/validateTipsUtil",
        "highcharts": "lib/pulgins/highcharts/highcharts",
        "exporting": "lib/pulgins/highcharts/exporting"
    },
    shim: {
        'jquery-ajaxfileupload': {
            deps: ['jquery']
        },
        'jquery-alerts': {
            deps: ['jquery']
        },
        'jquery-easing': {
            deps: ['jquery']
        },
        'jquery-flexslider': {
            deps: ['jquery']
        },
        'jquery-ui': {
            deps: ['jquery']
        },
        'jquery-md5': {
            deps: ['jquery']
        },
        'jquery-ui-widget': {
            deps: ['jquery', 'jquery-ui']
        },
        'jquery-ui-rcarousel': {
            deps: ['jquery', 'jquery-ui', 'jquery-ui-widget']
        },
        'bootstrap': {
            deps: ['jquery']
        },
        'bootstrapPage': {
            deps: ['jquery', 'bootstrap']
        },
        'cookieUtil': {
            deps: ['jquery']
        },
        'createBaseUtil': {
            deps: ['jquery']
        },
        'dateUtil': {
            deps: ['jquery']
        },
        'locationUtil': {
            deps: ["jquery"]
        },
        'storageUtil': {
            deps: ['jquery']
        },
        'stringUtil': {
            deps: ['jquery']
        },
        'tabUtil': {
            deps: ['jquery']
        },
        'tipsUtil': {
            deps: ['jquery']
        },
        'uuidUtil': {
            deps: ['jquery']
        },
        'validateUtil': {
            deps: ["jquery"]
        },
        'highcharts': {
            deps: ["jquery"]
        }
    }
});
