<?php
/**
 * Created by PhpStorm.
 * User: windy
 * Date: 15/11/2
 * Time: 下午10:36
 */
class H5 extends IController
{
	public $layout = 'h5';

	//商品展示
	function product()
	{
		$goods_id = IFilter::act(IReq::get('id'), 'int');

		if (!$goods_id) {
			IError::show(403, "传递的参数不正确");
			exit;
		}

		//使用商品id获得商品信息
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id=' . $goods_id . " AND is_del=0");
		if (!$goods_info) {
			IError::show(403, "这件商品不存在");
			exit;
		}
		$this->goods_info = $goods_info;

		$content = $goods_info['content'];

		$pattern = '/width(.*);|line-height(.*);|height(.*);|float(.*);|margin(.*);|padding(.*);/';
		$content = preg_replace($pattern, '', $content);

		$this->content = $content;
		$this->title = $goods_info['name'];

		$this->redirect('product');
	}

	function about()
	{
		$tb_help = new IModel("help");
		$help_row = $tb_help->query("id=44");
		//dump($help_row,1);
		if(!$help_row || !is_array($help_row))
		{
			IError::show(404,"您查找的页面已经不存在了");
		}
		$this->content = $help_row[0]['content'];
		$this->redirect('about');
	}

	function protocol()
	{
		$this->redirect('protocol');
	}

}