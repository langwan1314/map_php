<?php
/**
 * @file controller_app_class.php
 * @brief app控制器类,控制action动作,返回的数据均为json格式
 * @author misty
 * @date 2015-10-11
 * @version 
 */

/**
 * @class IAPPController
 * @brief 控制器
 */
class IAPPController extends IControllerBase
{
	protected $module            = null;               //隶属于模块的对象
	protected $ctrlId            = null;               //控制器ID标识符

    protected $output      = null;                     //存放返回json数据

	private $action;                                   //当前action对象
	private $defaultAction = 'index';                  //默认执行的action动作
	private $format        = null;                     //返回数据的格式(json/html)

	/**
	 * @brief 构造函数
	 * @param string $ctrlId 控制器ID标识符
	 * @param string $module 控制器所包含的模块
	 */
	public function __construct($module,$controllerId)
	{
		$this->module = $module;
		$this->ctrlId = $controllerId;

        $this->output = new Output();
	}

	/**
	 * @brief 获取当前控制器的id标识符
	 * @return 控制器的id标识符
	 */
	public function getId()
	{
		return $this->ctrlId;
	}

	/**
	 * @brief 初始化controller对象
	 */
	public function init()
	{
	}

	/**
	 * @brief 过滤函数
	 * @return array 初始化
	 */
	public function filters()
	{
		return array();
	}

	/**
	 * @brief 获取当前action对象
	 * @return object 返回当前action对象
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @brief 设置当前action对象
	 * @param object $actionObj 对象
	 */
	public function setAction($actionObj)
	{
		$this->action = $actionObj;
	}

	/**
	 * @brief 执行action方法
	 */
	public function run()
	{
        header("content-type:application/json;charset=".$this->module->charset);

		//初始化控制器
		$this->init();

		//创建action对象
		$actionObj = $this->createAction();
		IInterceptor::run("onCreateAction");
		$actionObj->run();
		IInterceptor::run("onFinishAction");

        echo $this->output->to_string();
	}

	/**
	 * @brief 创建action动作
	 * @return object 返回action动作对象
	 */
	public function createAction()
	{
		//获取action的标识符
		$actionId = IUrl::getInfo('action');

		//设置默认的action动作
		if($actionId == '')
		{
			$actionId = $this->defaultAction;
		}

		/*创建action对象流程
		 *1,控制器内部动作
		 *2,配置动作*/

		//1,控制器内部动作
		if(method_exists($this,$actionId))
		{
			$this->action = new IInlineAction($this,$actionId);
		}
		//2,配置动作
		else if(($actions = $this->actions()) && isset($actions[$actionId]))
		{
			//自定义类名
			$className = $actions[$actionId]['class'];
			$this->action = new $className($this,$actionId);
		}

		return $this->action;
	}

	/**
	 * @brief 预定义的action动作
	 * @return array 动作信息
	 */
	public function actions()
	{
		return array();
	}

	/**
	 * @brief 获取当前语言包方案的路径
	 * @return string 语言包路径
	 */
	public function getLangPath()
	{
		if(!isset($this->_langPath))
		{
			$langPath        = $this->langDir();
			$this->_langPath = $this->module->getBasePath().$langPath.DIRECTORY_SEPARATOR.$this->lang.DIRECTORY_SEPARATOR;
		}
		return $this->_langPath;
	}
}
