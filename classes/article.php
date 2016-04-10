<?php
/**
 * @copyright (c) 2015 crycoder.com
 * @file article.php
 * @brief 关于文章管理
 * @author windy
 * @date 2015-02-14
 * @version 0.6
 */

 /**
 * @class article
 * @brief 文章管理模块
 */
class Article
{
	//显示标题
	public static function showTitle($title,$color=null,$fontStyle=null)
	{
		$str='<span style="';
		if($color!=null) $str.='color:'.$color.';';
		if($fontStyle!=null)
		{
			switch($fontStyle)
			{
				case "1":
				$str.='font-weight:bold;';
				break;

				case "2":
				$str.='font-style:oblique;';
				break;
			}
		}
		$str.='">'.$title.'</span>';
		return $str;
	}
}