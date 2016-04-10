<?php
namespace plugins\payments\pay_weixin;
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2015/12/2
 * Time: 13:28
 */
interface ILogHandler
{
	public function write($msg);

}

class CLogFileHandler implements ILogHandler
{
	private $handle = null;

	public function __construct($file = '')
	{
		$this->handle = fopen($file,'a');
	}

	public function write($msg)
	{
		fwrite($this->handle, $msg, 4096);
	}

	public function __destruct()
	{
		fclose($this->handle);
	}
}