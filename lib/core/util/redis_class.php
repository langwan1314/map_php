<?php
/**
 * @file redis_class.php
 * @brief redis缓存应用
 * @author misty
 * @date 2015/10/17 02:54:26
 * @version 
 */

/**
 * @class IRedis
 * @brief redis应用
 */
class IRedis 
{
	private static $instance = false;

	/**
	 * @brief redis单例连接
	 * @param array $redis_info redis连接配制信息 [0]ip地址 [1]密码 
	 * @return bool or resource 值: false:链接失败; resource类型:链接的资源句柄;
	 */
    public static function instance($redis_info=null)
    {
        if (self::$instance == null)
        {
            if ($redis_info == null)
            {
                $redis_info = WM::$app->config['Redis'];
            }

            $hostArray = explode(':',$redis_info['host']);
            $hostPort  = isset($hostArray[1]) ? $hostArray[1] : '6379';
            $timeout   = isset($redis_info['timeout']) ? $redis_info['timeout'] : 1;

            self::$instance = new Redis();
            self::$instance->connect($hostArray[0], $hostPort, $timeout);
        }

        return self::$instance;
    }

    /**
     * @brief 取得redis字段值的方法
     * @param string $name 字段名
     * @return mixed 对应的值
     */
	public static function get($name)
	{
        $value = self::instance()->get($name);
        if (preg_match('/^[Oa]:\d+:.*/', $value))
        {
            return unserialize($value);
        }
        else
        {
            return $value;
        }
	}

    /**
     * @brief 设置redis的方法
     * @param string $name 字段名
     * @param string $value 对应的值
     * @param string $expire 有效时间, >0的秒数
     */
    public static function set($name, $value='', $expire=0)
    {
        if (is_array($value) || is_object($value))
        {
            $value = serialize($value);
        }

        if ($expire && $expire > 0)
        {
            return self::instance()->setex($name, $expire, $value);
        }
        else
        {
            return self::instance()->set($name, $value);
        }
    }

    /**
     * @brief 清除redis值的方法
     * @param string $name 字段名
     */
	public static function clear($name)
	{
        self::instance()->del($name);
	}

    /**
     * @brief 清除所有的cookie数据
     */
	public static function clearAll()
	{
        return;
	}
}
