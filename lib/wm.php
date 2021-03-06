<?php
/**
 * @copyright Copyright(c) 2015 crycoder.com
 * @file wm.php
 * @brief 引用内核入口文件
 * @author windy
 * @date 2010-12-02
 * @version 1.4.1
 */
//内核路径
defined('WM_PATH') or define('WM_PATH',dirname(__file__).DIRECTORY_SEPARATOR);
/**
 * @brief wm内核的基类
 * @class WM
 */
class WM
{
	/**
	 * @var WMApplication $app 当前应用的对象
	 */
	public static $app;

	/**
	 * @brief 控制器所在位置
	 */
	public static $_classes = array('controllers.*');

	/**
	 * @brief 获取内核版本号
	 */
	public static function getVersion()
	{
		return '1.7.0';
	}

    /**
     * @brief 创建WebApplication应用
     * @param array $config
     * @return object Application对象
     */
    public static function createWebApp($config = null)
    {
	    return new WMApplication($config);
    }
    /**
     * @brief 创建Application应用
     * @param string $className
     * @param array $config
     * @return object Application对象
     */
    public static function createApp($className, $config)
    {
        return new $className($config);
    }

	/**
	 * @brief 实现系统类的自动加载
	 * @param String $className 类名称
	 * @return bool true
	 */
	public static function autoload($className)
	{

		$current_class = $className;
        $pathPos = strrpos($className, '\\');
        if ($pathPos !== false)
        {
            $className = substr($className, $pathPos+1);
        }
		if(!preg_match('|^\w+$|',$className))
		{
			die('the class name is inaccurate');
		}

		//内核定义类
		if(isset(self::$_coreClasses[$className]))
		{
			include(WM_PATH.self::$_coreClasses[$className]);
			return true;
		}

		//应用扩展类
		if(isset(self::$_classes))
		{
            if(isset(self::$_classes[$className]) && self::$_classes[$className])
            {
            	$filePath = self::parseAlias(self::$_classes[$className]).strtolower( $className ) .'.php';
            	if(is_file($filePath))
            	{
	                include($filePath);
	                return true;
            	}
            }
            else
            {
                foreach(self::$_classes as $classPath)
                {
                    $filePath = self::parseAlias($classPath).strtolower( $className ) .'.php';
                    if(!is_file($filePath))
                    {
                    	$filePath = self::parseAlias($classPath).$className.'.php';
                    }

                    if(is_file($filePath))
                	{
	                    include($filePath);
	                    return true;
                	}
                }
            }
		}

		$php_file = self::$app->getBasePath().$current_class.'.php';
		$php_file = str_replace('\\', DIRECTORY_SEPARATOR, $php_file);
		if (is_file($php_file)) {
			include_once($php_file);
			return true;
		}

		return false;
	}

	/**
	 * 把别名路径转化成真实路径
	 * 路径别名，在config配置逻辑中运用，例如:classes.api.goods
	 * 真实路径，在程序运行时运用，例如:classes/api/goods
	 * @param string $alias 别名路径
	 * @return string 真实路径
	 */
	public static function parseAlias($alias)
	{
		return self::$app->getBasePath().strtr(strtolower(trim($alias,'*')),'.','/');
	}

    /**
     * @brief 用户自定义类的注册入口
     * @param array $classes 如:array('system.net.load.*','system.net.ftp.*');
     */
    public static function setClasses($classes)
    {
	    if(is_string($classes) || is_array($classes))
	    {
	        self::$_classes = array_merge(self::$_classes, (array)$classes);
	    }
    }
    /**
     * 设置当前框架正在运行的应用
     * @param Object $exeApp 应用对象
     */
    public static function setApplication($exeApp)
    {
    	self::$app = $exeApp;
    }

    //系统内核所有类文件注册信息
	public static $_coreClasses = array(
		'IApplication'      =>  'core/application_class.php',
		'WMApplication'     =>  'core/webapplication_class.php',
        'IObject'			=>	'core/object_class.php',
        'ITag'              =>  'core/tag_class.php',
        'IQuery'            =>  'core/query_class.php',
		'IError'			=>	'core/util/exception_class.php',
		'IException'		=>	'core/util/exception_class.php',
        'IPaging'			=>	'core/paging_class.php',
		'IHash'				=>	'core/util/hash_class.php',
		'ITime'				=>	'core/util/time_class.php',
		'IValidate'			=>	'core/util/validate_class.php',
		'IServer'			=>	'core/util/server_class.php',
		'IReq'			    =>	'core/util/req_class.php',
		'IFile'				=>	'core/util/file_class.php',
		'IUrl'				=>	'core/util/urlmanager_class.php',
		'IClient'			=>	'core/util/client_class.php',
		'IFilter'			=>	'core/util/filter_class.php',
		'IString'			=>	'core/util/string_class.php',
		'ISmtp'				=>	'core/util/smtp_class.php',
		'IXML'				=>	'core/util/xml_class.php',
		'IUpload'			=>	'core/util/upload_class.php',
		'Curl'			=>	'core/util/Curl.class.php',
		'YunUpload'			=>	'core/util/YunUpload.class.php',
		'IRedis'			=>	'core/util/redis_class.php',
		'ICookie'			=>	'core/util/cookie_class.php',
		'ISession'			=>	'core/util/session_class.php',
		'JSON'				=>	'core/util/json_class.php',
		'Captcha'	        =>	'core/util/captcha_class.php',
		'IImage'			=>	'core/util/image_class.php',
		'ILanguage'			=>	'core/util/language_class.php',
		'ISafe'             =>	'core/util/safe_class.php',
		'ICrypt'            =>	'core/util/crypt_class.php',
		'HashGenerator'     =>	'core/util/Hashids/HashGenerator.php',
		'Hashids'           =>	'core/util/Hashids/Hashids.php',
		'IFileLog'			=>	'log/filelog_class.php',
		'ILog'				=>	'log/log_inte.php',
		'IDBLog'			=>	'log/dblog_class.php',
		'ILogFactory'       =>  'log/log_factory_class.php',
		'IMysqli'			=>  'db/driver/mysqli_class.php',
		'IMysql'			=>  'db/driver/mysql_class.php',
		'IDBFactory' 		=>  'db/dbfactory_class.php',
        'IDB'               =>  'db/db_class.php',
        'IModel'			=>	'web/model/model_class.php',
        'IController'		=>	'web/controller/controller_class.php',
        'IAPPController'	=>	'web/controller/controller_app_class.php',
        'IControllerBase'	=>	'web/controller/controllerbase_class.php',
		'IAction'			=>	'web/action/action.php',
		'IInlineAction'     =>  'web/action/inline_action.php',
		'IViewAction'		=>	'web/action/view_action.php',
        'IJSPackage'        =>  'web/js/jspackage_class.php',
        'ICacheInte'        =>  'core/cache/cache_inte.php',
        'ICache'            =>  'core/cache/cache_class.php',
        'IFileCache'        =>  'core/cache/filecache_class.php',
        'IMemCache'         =>  'core/cache/memcache_class.php',
		'IInterceptor'		=>	'core/interceptor_class.php',
		'GD'                =>	'core/util/phpthumb/GD.php',
		'Output'            =>	'core/util/output.php',
		'GoodsImage'        =>	'core/util/goods_image.php',
	);
}

spl_autoload_register(array('WM','autoload'));
include_once './lib/function/array.php';
