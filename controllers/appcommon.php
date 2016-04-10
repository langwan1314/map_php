<?php
/**
 * @file appcommon.php
 * @brief
 * @author misty
 * @date 2015-10-12 21:57
 * @version 
 * @note
 */
/**
 * @brief app专用,公用模块
 * @class APPCommon
 * @note
 */
class APPCommon extends IAPPController
{
	function init()
	{
	}

    //area show
    function area_total()
    {
		$parent_id = intval(IReq::get("aid"));
		$areaDB    = new IModel('areas');
		$areas = $areaDB->query("area_id < 999999",'*','sort','asc');

        /*
        $arr_area = array();
        foreach ($areas as $area)
        {
            $arr_area[] = array(
                "id"=>intval($area['area_id']),
                "name"=>$area['area_name'],
            );
        }
        */

        // 一下这段代码不计性能, 不提供给客户端调用
        $arr_area = array();
        foreach ($areas as $area)
        {
            $parent_id = intval($area['parent_id']);
            if ($parent_id == 0)
            {
                // 省份
                $arr_area[] = array(
                    "id"=>intval($area['area_id']),
                    "name"=>$area['area_name'],
                );

                continue;
            }

            if (intval($area['area_id']) % 100 == 0)
            {
                foreach ($arr_area as &$p_area)
                {
                    if ($p_area['id'] == $parent_id)
                    {
                        $p_area['s'][] = array(
                            'id'=>intval($area['area_id']), 
                            'name'=>$area['area_name'],
                            's'=>array()
                        );
                        break;
                    }
                }
            }
            else foreach ($arr_area as &$pp_area)
            {
                if ($pp_area['id'] == (intval($parent_id / 10000) * 10000))
                {
                    foreach ($pp_area['s'] as &$p_area)
                    {
                        if ($p_area['id'] == $parent_id)
                        {
                            $p_area['s'][] = array(
                                'id'=>intval($area['area_id']), 
                                'name'=>$area['area_name'],
                            );
                            break;
                        }
                    }
                }
            }
        }

        $this->output->set_result("SUCCESS");
        $this->output->set_data($arr_area);
        return;
    }

    //area code
    function area_list()
    {
		$parent_id = intval(IReq::get("aid"));
		$areaDB    = new IModel('areas');
		$areas     = $areaDB->query("parent_id=$parent_id",'*','sort','asc');

        $this->output->set_result("SUCCESS");
        $this->output->set_data($areas);
        return;
    }
}
