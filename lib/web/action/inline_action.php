<?php
/**
 * @copyright (c) 2015 crycoder.com
 * @file inline_action.php
 * @brief 控制器内部action
 * @author windy
 * @date 2015-12-15
 * @version 1.0
 */

/**
 * @class IInlineAction
 * @brief 控制器内部action
 */
class IInlineAction extends IAction
{
	/**
	 * @brief 内部action动作执行方法
	 */
	public function run()
	{
		$controller=$this->getController();
		$methodName=$this->getId();
		$controller->$methodName();
	}
}
