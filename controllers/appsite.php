<?php
/**
 * @file appsite.php
 * @brief
 * @author misty
 * @date 2015-10-12 11:22
 * @version 
 * @note
 */
/**
 * @brief app专用
 * @class APPSite
 * @note
 */
class APPSite extends IAPPController
{
    public static $banner_position_name = "app首页banner位";

	function init()
	{
		CheckRights::checkAppUserRights();
	}

    function _get_banner()
    {
		$position = AD::getPositionInfo(self::$banner_position_name);
		if ($position)
		{
			$adList = AD::getAdList($position['id']);
            /*
			foreach($adList as $key => $val)
			{
				$val['width']  = $position['width'];
				$val['height'] = $position['height'];
			}
             */

            foreach ($adList as &$banner)
            {
                unset($banner['position_id']);
                $banner['start_time'] = strtotime($banner['start_time']);
                $banner['end_time'] = strtotime($banner['end_time']);
                $banner['content'] = GoodsImage::$file_url_host.'/'.$banner['content'];
            }

            return $adList;
		}
        else
        {
            // 返回个空数据(记个log吧...)
            return array();
        }
    }

    function _get_commend_goods($resource, $where=null, $limit=null)
    {
        if ($limit && is_numeric($limit) && intval($limit) >= 0)
        {
            $arr_goods = Api::run($resource, intval($limit));
        }
        else
        {
            $arr_goods = Api::run($resource);
        }

        // 拉失败就置空吧
        return $arr_goods === null ? array() : $arr_goods;
    }

    //热卖商品
    function commend_hot()
    {
        $limit = IReq::get('limit', 'get');
        $arr_goods = $this->_get_commend_goods("getCommendHot", null, $limit);
        if ($arr_goods === null)
        {
            $this->output->fill(array('result'=>'GET_COMMEND_HOT_GOODS', 'prompt'=>'拉取热卖商品失败'));
            return;
        }

        foreach ($arr_goods as &$goods)
        {
            $goods['img'] = GoodsImage::get_hot_goods_url($goods['img']);
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_goods);
    }

    //推荐商品
    function commend_recom()
    {
        $limit = IReq::get('limit', 'get');
        $arr_goods = $this->_get_commend_goods("getCommendRecom", null, $limit);
        if ($arr_goods === null)
        {
            $this->output->fill(array('result'=>'GET_COMMEND_RECOM_GOODS', 'prompt'=>'拉取推荐商品失败'));
            return;
        }

        foreach ($arr_goods as &$goods)
        {
            $goods['img'] = GoodsImage::get_rcmd_goods_url($goods['img']);
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_goods);
    }

    // TODO(misty): remove it later
    function _add_category_img(&$arr_category)
    {
        foreach ($arr_category as &$category)
        {
            $category['img'] = GoodsImage::get_category_url($category['ico_url']);
            unset($category['ico_url']);
            continue;
            if (!isset($category['img']))
            {
                $category['img'] = 'http://www.crycoder.com/frontend/app/www/img/common/promote_1.png';
            }
        }
    }

    //商品分类列表
    function category_list()
    {
        $limit = IReq::get('limit', 'get');
        if ($limit && is_numeric($limit) && intval($limit) >= 0)
        {
            $arr_cats = Api::run('getcategoryList', intval($limit));
        }
        else
        {
            $arr_cats = Api::run('getcategoryList');
        }
        $this->_add_category_img($arr_cats);

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_cats);
    }

    //顶层商品分类列表
    function top_category_list()
    {
        $arr_cats = Api::run('getCategoryListTop');
        $this->_add_category_img($arr_cats);

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_cats);
    }

    //下层商品分类列表
    function sub_category_list()
    {
        $parent_id = IReq::get('parent_id', 'get');
        if ($parent_id === null || !is_numeric($parent_id) || intval($parent_id) < 0)
        {
            $this->output->set_result("PARAM_INVALID");
            return;
        }

        $arr_cats = Api::run('getCategoryByParentid', array('#parent_id#', $parent_id));
        $this->_add_category_img($arr_cats);

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_cats);
    }

    function index()
    {
        $banners = $this->_get_banner();
        $top_category_list = Api::run('getCategoryListTop');
        $commend_hot_goods = $this->_get_commend_goods('getCommendHot', 10);
        $commend_recom_goods = $this->_get_commend_goods('getCommendRecom', 10);

        foreach ($commend_hot_goods as &$goods)
        {
            $goods['img'] = GoodsImage::get_hot_goods_url($goods['img']);
        }

        foreach ($commend_recom_goods as &$goods)
        {
            $goods['img'] = GoodsImage::get_hot_goods_url($goods['img']);
        }

        if (empty($commend_recom_goods))
        {
            // TODO: for debug
            $commend_recom_goods = $commend_hot_goods;
        }

        $this->_add_category_img($top_category_list);
        $data = array(
            'banners'=>$banners, 
            'top_category_list'=>$top_category_list, 
            'commend_hot_goods'=>$commend_hot_goods, 
            'commend_recom_goods'=>$commend_recom_goods
        );
        $this->output->set_result("SUCCESS");
        $this->output->set_data($data);
        return;
    }

    function _after_search(&$result)
    {
        $result['img'] = GoodsImage::get_search_goods_url($result['img']);
        $result['ad_img'] = $result['ad_img'] == null ? '' : $result['ad_img'];

        if ($result["spec_array"] == "")
        {
            $result["spec_array"] = JSON::decode("{}");
        }
        else
        {
            $result["spec_array"] = JSON::decode($result["spec_array"]);
            $result["spec_array"] = array_values($result["spec_array"]);
        }

        unset($result['up_time']);
        unset($result['down_time']);
        unset($result['create_time']);
        unset($result['cost_price']);
        unset($result['content']);
    }

    function _get_condition_goods($goods_query)
    {
        # 关闭按页分页的模式
        unset($goods_query->page);

        $goods_query->fields = "count(go.id) as total";
        $count_total = $goods_query->find();
        if ($count_total === false)
        {
            $this->output->set_result("GET_GOODOS");
            $this->output->set_prompt("拉取商品失败");
            return;
        }

        $total = intval($count_total[0]['total']);
        if ($total == 0)
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('total'=>0, 'goods'=>array()));
            return;
        }

        $start = IFilter::act(IReq::get('start'), 'int');
        $num = IFilter::act(IReq::get('num'), 'int');
        $num = $num > 0 ? $num : 10;

        $goods_query->fields = 'go.*';
		$goods_query->limit = $start.', '.$num;
        $arr_result = $goods_query->find();

        foreach ($arr_result as &$result)
        {
            $this->_after_search($result);
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data(array('total'=>$total, 'goods'=>$arr_result));
        return;
    }

	function search()
	{
		$word   = IFilter::act(IReq::get('word'),'text');
		$cat_id = IFilter::act(IReq::get('cat'),'int');
		if(preg_match("|^[\w\x7f\s*-\xff*]+$|",$word))
		{
			//搜索关键字
			$tb_sear = new IModel('search');
			$search_info = $tb_sear->getObj('keyword = "'.$word.'"','id');

			//如果是第一页，相应关键词的被搜索数量才加1
			if($search_info && intval(IReq::get('start')) == 0)
			{
                $tb_sear->setData(array('num'=>'num + 1'));
                $tb_sear->update('id='.$search_info['id'],'num');
			}
			elseif(!$search_info)
			{
				//如果数据库中没有这个词的信息，则新添
				$tb_sear->setData(array('keyword'=>$word,'num'=>1));
				$tb_sear->add();
			}
		}
		else
		{
            $this->output->set_result("WORDS_INVALID");
            $this->output->set_prompt("请输入正确的查询关键词");
            return;
		}
        
        $cond_where = array('search' => $word , 'category_extend' => $cat_id);
        $goods_query = search_goods::find($cond_where);

        return $this->_get_condition_goods($goods_query);
	}

	function goods_list()
	{
		$cat_id = IFilter::act(IReq::get('cat'),'int');
        if ($cat_id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("缺少分类ID");
            return;
        }

        $cond_where = array('category_extend' => $cat_id);
        $goods_query = search_goods::find($cond_where);

        return $this->_get_condition_goods($goods_query);
	}

    //添加收藏夹
    function favorite_add()
    {
        if (!$this->user)
        {
            return $this->output->set_result("NEED_LOGIN");
        }

    	$goods_id = IFilter::act(IReq::get('goods_id'),'int');
    	$message  = '';

    	if($goods_id == 0)
    	{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("商品id不能为空");
            return;
    	}

    	if(!isset($this->user['user_id']) || !$this->user['user_id'])
    	{
    		$message = '请先登录';
            $this->output->set_result("NEED_LOGIN");
            return;
    	}

        $favoriteObj = new IModel('favorite');
        $goodsRow    = $favoriteObj->getObj('user_id = '.$this->user['user_id'].' and rid = '.$goods_id);
        if($goodsRow)
        {
            $this->output->set_result("ADD_FAVORITE");
            $this->output->set_prompt("您已经收藏过此件商品");
            return;
        }

        $catObj = new IModel('category_extend');
        $catRow = $catObj->getObj('goods_id = '.$goods_id);
        $cat_id = $catRow ? $catRow['category_id'] : 0;

        $dataArray   = array(
            'user_id' => $this->user['user_id'],
            'rid'     => $goods_id,
            'time'    => ITime::getDateTime(),
            'cat_id'  => $cat_id,
        );
        $favoriteObj->setData($dataArray);
        $favorite_id = $favoriteObj->add();
        if ($favorite_id === False)
        {
            $this->output->set_result("ADD_FAVORITE");
            $this->output->set_prompt("收藏商品失败");
            return;
        }

        //商品收藏信息更新
        $goodsDB = new IModel('goods');
        $goodsDB->setData(array("favorite" => "favorite + 1"));
        $goodsDB->update("id = ".$goods_id,'favorite');

        $this->output->set_result("SUCCESS");
        $this->output->set_prompt("收藏成功");
        $this->output->set_data(array('id'=>$favorite_id));
    }

    //[收藏夹]删除
    function favorite_del()
    {
        if (!$this->user)
        {
            return $this->output->set_result("NEED_LOGIN");
        }

    	$user_id = $this->user['user_id'];
    	$id      = IReq::get('id');

		if(empty($id))
		{
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt('请选择要删除的数据');
            return;
        }

        $id = IFilter::act($id,'int');
        $favoriteObj = new IModel('favorite');

        if(is_array($id))
        {
            $idStr = join(',',$id);
            $where = 'user_id = '.$user_id.' and id in ('.$idStr.')';
        }
        else
        {
            $where = 'user_id = '.$user_id.' and id = '.$id;
        }

        $favoriteObj->del($where);

        $this->output->set_result("SUCCESS");
        return;
    }

    //[收藏夹]获取收藏夹数据
	function favorite()
    {
        if (!$this->user)
        {
            //return $this->output->set_result("NEED_LOGIN");
            $this->user = array('user_id'=>1);
        }

		//获取收藏夹信息
	    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
	    $num = isset($_GET['num']) ? intval($_GET['num']) : 10;

		$favoriteObj = new IQuery("favorite");
		$cat_id = intval(IReq::get('cat_id'));
		$where = '';
		if($cat_id != 0)
		{
			$where = ' and cat_id = '.$cat_id;
		}

		$favoriteObj->where = "user_id = ".$this->user['user_id'].$where;
        $favoriteObj->fields = "count(id) as total";

        $count_total = $favoriteObj->find();
        if ($count_total === false)
        {
            $this->output->set_result("GET_FAVORITE_GOODS");
            $this->output->set_prompt("拉取收藏商品失败");
            return;
        }

        $total = intval($count_total[0]['total']);
        if ($total == 0)
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('total'=>0, 'favorites'=>array()));
            return;
        }

        $favoriteObj->fields = '*';
		$favoriteObj->limit = $start.', '.$num;
		$items = $favoriteObj->find();

		$goodsIdArray = array();
		foreach($items as $val)
		{
			$goodsIdArray[] = $val['rid'];
		}

		//商品数据
		if(!empty($goodsIdArray))
		{
			$goodsIdStr = join(',',$goodsIdArray);
			$goodsObj   = new IModel('goods');
			$goodsList  = $goodsObj->query('id in ('.$goodsIdStr.')');
		}

		foreach($items as $key => $val)
		{
			foreach($goodsList as $gkey => $goods)
			{
				if($goods['id'] == $val['rid'])
				{
					$items[$key]['data'] = $goods;

					//效率考虑,让goodsList循环次数减少
					unset($goodsList[$gkey]);
				}
			}

			//如果相应的商品或者货品已经被删除了，
			if(!isset($items[$key]['data']))
			{
				$favoriteModel = new IModel('favorite');
				$favoriteModel->del("id={$val['id']}");
				unset($items[$key]);
			}
		}

        $result = array();
        foreach ($items as $key=>$val)
        {
            $this->_after_search($val['data']);
            $val['summary'] = $val['summary'] ? $val['summary'] : '';
            $val['time'] = strtotime($val['time']);

            $result[] = $val;
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data(array('total'=>$total, 'favorites'=>$result));
    }

    // 根据商品id拉取配送区域
    function get_receive_area()
    {
        // 当前只送广东深圳
        $arr_provinces = [440000];
        $arr_cities = [440300];

        $goods_id = IFilter::act(IReq::get('goods_id'), 'int');
        if ($goods_id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("请选择商品");
            return;
        }

		$areaDB = new IModel('areas');

        $str_province = join(",", $arr_provinces);
		$receive_provinces = $areaDB->query("area_id in ( $str_province ) ",'*','sort','asc');

        $str_city = join(",", $arr_cities);
		$receive_cites = $areaDB->query("area_id in ( $str_city ) ",'*','sort','asc');

        $provicnce_city_map = array();
        foreach ($receive_cites as $city)
        {
            if (!isset($provicnce_city_map[$city['parent_id']]))
            {
                $provicnce_city_map[$city['parent_id']] = [];
            }

            $provicnce_city_map[$city['parent_id']][] = $city;
        }

		$receive_areas = $areaDB->query("parent_id in ( $str_city ) ",'*','sort','asc');

        $city_area_map = array();
        foreach ($receive_areas as $area)
        {
            if (!isset($city_area_map[$area['parent_id']]))
            {
                $city_area_map[$area['parent_id']] = [];
            }

            $city_area_map[$area['parent_id']][] = $area;
        }

        $arr_receive = [];
        foreach ($receive_provinces as $province)
        {
            $receive_province = array(
                'id'=>$province['area_id'],
                'name'=>$province['area_name'],
            );

            if (isset($provicnce_city_map[$province['area_id']]))
            {
                $province_cities = $provicnce_city_map[$province['area_id']];
                $receive_province['s'] = [];

                foreach ($province_cities as $province_city)
                {
                    $receive_city = array(
                        'id'=>$province_city['area_id'],
                        'name'=>$province_city['area_name'],
                    );

                    if (isset($city_area_map[$province_city['area_id']]))
                    {
                        $city_areas = $city_area_map[$province_city['area_id']];
                        $receive_city['s'] = [];

                        foreach ($city_areas as $city_area)
                        {
                            $receive_area = array(
                                'id'=>$city_area['area_id'],
                                'name'=>$city_area['area_name']
                            );

                            $receive_city['s'][] = $receive_area;
                        }
                    }

                    $receive_province['s'][] = $receive_city;
                }
            }

            $arr_receive[] = $receive_province;
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_receive);
        return;
    }

    function check_store()
    {
        $id = IFilter::act(IReq::get('id'), 'int');
        $type = IFilter::act(IReq::get('type'));
        $area_id = IFilter::act(IReq::get('area_id'), 'int');

        $area_shenzhen = 440300;

        if ($id <= 0)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("商品id为空");
            return;
        }

        if (!$type || !in_array($type, array('goods', 'products')))
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("商品类型无效");
            return;
        }

        if (!$area_id)
        {
            $this->output->set_result("PARAM_INVALID");
            $this->output->set_prompt("地区码无效");
            return;
        }

        if (intval($area_id / 100) != intval($area_shenzhen / 100))
        {
            $this->output->set_result("SUCCESS");
            $this->output->set_data(array('store_nums'=>0));
            return;
        }

		//商品方式
		if($type == 'goods')
		{
			$goodsObj = new IModel('goods');
			$dataArray = $goodsObj->getObj('id = '.$id.' and is_del = 0','sell_price,store_nums');
		}
		//货品方式
		else
		{
			$productObj = new IQuery('products as pro, goods as go');
			$dataArray = $productObj->getObj(" pro.id = $id and go.is_del = 0 and pro.goods_id = go.id", " pro.sell_price, pro.store_nums");
		}

        if (!$dataArray)
        {
            $this->output->set_result("GOODS_NOT_EXISTS");
            $this->output->set_prompt("查询的商品部存在");
            return;
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data(array('store_nums'=>$dataArray['store_nums']));
    }
}
