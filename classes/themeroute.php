<?php

/**
 * @copyright (c) 2014 crycoder
 * @file themeroute.php
 * @brief 主题皮肤选择路由类
 * @author nswe
 * @date 2014/7/15 18:50:48
 * @version 2.6
 *
 * config.php 中的theme和skin多种写法，只用theme举例说明
 * 1, 'theme' => 'default' #所有客户端平台都用default主题
 * 2, 'theme' => array('pc' => 'default','mobile' => 'mobile') #pc端用default主题；mobile端用mobile主题
 */
class themeroute extends IInterceptorBase
{
	const SCENE_SYSDEFAULT = 'sysdefault';
	const SCENE_SYSSELLER = 'sysseller';
	const SCENE_SITE = 'site';

	//后台管理
	private static $sysTheme = 'sysdefault';
	private static $sysSkin = 'default';

	//卖家管理
	private static $sysSellerTheme = 'sysseller';
	private static $sysSellerSkin = 'default';

	//后台管理的控制器
	private static $syscontroller = array(
		'pic', 'block', 'brand', 'comment', 'goods', 'market', 'member', 'message', 'order', 'system', 'systemadmin', 'tools'
	);

	//卖家管理的控制器
	private static $sellercontroller = array(
		'seller', 'systemseller'
	);

	/**
	 * @brief theme和skin进行选择
	 */
	public static function onCreateController()
	{
		$controller = func_num_args() > 0 ? func_get_arg(0) : WM::$app->controller;

		//判断是否为后台管理控制器
		if (in_array($controller->getId(), self::$syscontroller)) {
			defined("WM_SCENE") ?: define("WM_SCENE", self::SCENE_SYSDEFAULT);
			$controller->theme = self::$sysTheme;
			$controller->skin = self::$sysSkin;
		} //判断是否为卖家管理控制器
		elseif (in_array($controller->getId(), self::$sellercontroller)) {
			defined("WM_SCENE") ?: define("WM_SCENE", self::SCENE_SYSSELLER);
			$controller->theme = self::$sysSellerTheme;
			$controller->skin = self::$sysSellerSkin;
		} else {
			defined("WM_SCENE") ?: define("WM_SCENE", self::SCENE_SITE);
			if (isset(WM::$app->config['theme'])) {
				//根据不同的客户端进行智能选择
				if (is_array(WM::$app->config['theme'])) {
					$client = IClient::getDevice();
					$controller->theme = isset(WM::$app->config['theme'][$client]) ? WM::$app->config['theme'][$client] : current(WM::$app->config['theme']);
				} else {
					$controller->theme = WM::$app->config['theme'];
				}
			}

			if (isset(WM::$app->config['skin'])) {
				//根据不同的客户端进行智能选择
				if (is_array(WM::$app->config['skin'])) {
					$client = IClient::getDevice();
					$controller->skin = isset(WM::$app->config['skin'][$client]) ? WM::$app->config['skin'][$client] : current(WM::$app->config['skin']);
				} else {
					$controller->skin = WM::$app->config['skin'];
				}
			}
		}

		//修正runtime配置
		WM::$app->runtimePath = WM::$app->getRuntimePath() . $controller->theme . '/';
		WM::$app->webRunPath = WM::$app->getWebRunPath() . $controller->theme . '/';
	}
}