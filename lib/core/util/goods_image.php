<?php
/**
 * @brief 动态生成缩略图类
 */
class GoodsImage
{
    // 热门商品
    public static $hot_goods_thumb_width = 176;
    public static $hot_goods_thumb_height = 176;

    // 首页推荐
    public static $rcmd_goods_thumb_width = 176;
    public static $rcmd_goods_thumb_height = 176;

    // 商品列表
    public static $list_goods_thumb_width = 176;
    public static $list_goods_thumb_height = 176;

    // 商品详情页banner大图
    public static $detail_goods_thumb_width = 750;
    public static $detail_goods_thumb_height = 624;

    public static $site_url_host = "http://www.crycoder.com";
    public static $file_url_host = "http://file.crycoder.com";

    public static function get_url($img, $width, $height)
    {
        if (empty($img))
        {
            return "";
        }

        $img_pathinfo = pathinfo($img);
        $img_url = $img_pathinfo['dirname'].'/'.$img_pathinfo['filename'].'_'.$width.'x'.$height.'.'.$img_pathinfo['extension'];
        return self::$file_url_host . $img_url;
    }

    public static function get_category_url($img)
    {
        return self::$file_url_host . '/' . $img;
    }

	/**
	 * @brief 获取热门商品缩略图
	 */
	public static function get_hot_goods_url($img)
	{
        return self::get_url($img, 
            self::$hot_goods_thumb_width, 
            self::$hot_goods_thumb_height);
	}

	/**
	 * @brief 获取首页推荐商品缩略图
	 */
	public static function get_rcmd_goods_url($img)
	{
        return self::get_url($img, 
            self::$rcmd_goods_thumb_width, 
            self::$rcmd_goods_thumb_height);
	}

	/**
	 * @brief 获取搜索列表商品缩略图
	 * @param string $img 商品图片路径
	 * @return string 商品缩略图完整url
	 */
    public static function get_search_goods_url($img)
    {
        return self::get_url($img, 
            self::$list_goods_thumb_width, 
            self::$list_goods_thumb_height);
    }

	/**
	 * @brief 获取详情页商品图片
	 * @param string $img 商品图片路径
	 * @return string 商品图完整url
	 */
    public static function get_detail_goods_url($img)
    {
        return self::get_url($img, 
            self::$detail_goods_thumb_width, 
            self::$detail_goods_thumb_height);
    }

	/**
	 * @brief 获取订单页商品图片
	 * @param string $img 商品图片路径
	 * @return string 商品图完整url
	 */
    public static function get_order_goods_url($img)
    {
        return self::get_url($img, 
            self::$list_goods_thumb_width, 
            self::$list_goods_thumb_height
        );
    }
}
