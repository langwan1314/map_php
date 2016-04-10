<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2015/10/12
 * Time: 17:33
 */

/**
 * Array group by function
 * group array(); by keystr
 * @author windy
 * @param array $array
 * @param string $keystr
 * @param boolean $limit
 * @return array $array handle result
 */
function array_group($array, $keystr, $limit = false) {
	if (empty ($array) || !is_array($array)){
		return $array;
	}

	$_result = array ();
	foreach ($array as $item) {
		$sub_keys = array_keys($item);
		if (in_array($keystr, $sub_keys)) {
			$_result[$item[$keystr]][] = $item;
		} else {
			$_result[count($_result)][] = $item;
		}
	}
	if (!$limit) {
		return $_result;
	}

	$result = array ();
	foreach ($_result as $key => $item) {
		$result[$key] = $item[0];
	}
	return $result;
}

function dump(){
	if(!headers_sent()){
		header('Content-Type: text/html; charset=utf-8');
	}
	echo "\r\n\r\n".'<pre style="background-color:#ddd; font-size:12px">'."\r\n";
	$args = func_get_args();
	$last = array_slice($args, -1, 1);
	$die = $last[0] === 1;
	if($die){
		$args = array_slice($args, 0, -1);
	}
	if($args){
		foreach($args as $arg){
			var_dump($arg);
			echo str_repeat('-',50)."\n";
		}
	}
	$info = debug_backtrace();
	echo $info[0]['file'].' ['.$info[0]['line']."] \r\n</pre>";
	if($die){
		die;
	}
}