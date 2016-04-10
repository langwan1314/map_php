<?php
/**
 * 配置项
 */
return array(
	//日志
	'logs'        =>
		array(
			'path' => 'backup/logs/log',
			'type' => 'file',
		),
	//数据库
	'DB'          =>
		array(
			'type'     => 'mysqli',
			'tablePre' => '',
			'read'     =>
				array(
					0 =>
						array(
							'host'   => '192.168.1.32:3306',
							'user'   => 'jiongmap',
							'passwd' => 'jiongmap@leaves',
							'name'   => 'db_jiongmap',
						),
				),
			'write'    =>
				array(
					'host'   => '192.168.1.32:3306',
					'user'   => 'jiongmap',
					'passwd' => 'jiongmap@leaves',
					'name'   => 'db_jiongmap',
				),
		),
	//缓存
	'Redis'          =>
		array(
            'host'   => '192.168.1.32:6379',
            'passwd' => '',
            'timeout'=> 3,
		),
	'interceptor' =>
		array(
			0 => 'themeroute@onCreateController',
			1 => 'layoutroute@onCreateView',
			2 => 'hookCreateAction@onCreateAction',
			3 => 'hookFinishAction@onFinishAction',
		),
	'langPath'    => 'language',
	'viewPath'    => 'views',
	'skinPath'    => 'skin',
	'classes'     => 'classes.*',
	'rewriteRule' => 'pathinfo',
	'theme'       =>
		array(
			'pc'     => 'default',
			'mobile' => 'default',
		),
	'skin'        =>
		array(
			'pc'     => 'red',
			'mobile' => 'red',
		),
	'timezone'    => 'Etc/GMT-8',
	'upload'      => 'upload',
	'dbbackup'    => 'backup/database',
	'safe'        => 'cookie',
	'lang'        => 'zh_sc',
	'debug'       => true,
	'configExt'   =>
		array(
			'site_config' => 'config/site_config.php',
		),
	'encryptKey'  => '84546e4fd932580fa4f43221dcf4d50f',
);
