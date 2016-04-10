<?php
/**
 * @brief ��̬��������ͼ��
 */
class GoodsImage
{
    // ������Ʒ
    public static $hot_goods_thumb_width = 176;
    public static $hot_goods_thumb_height = 176;

    // ��ҳ�Ƽ�
    public static $rcmd_goods_thumb_width = 176;
    public static $rcmd_goods_thumb_height = 176;

    // ��Ʒ�б�
    public static $list_goods_thumb_width = 176;
    public static $list_goods_thumb_height = 176;

    // ��Ʒ����ҳbanner��ͼ
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
	 * @brief ��ȡ������Ʒ����ͼ
	 */
	public static function get_hot_goods_url($img)
	{
        return self::get_url($img, 
            self::$hot_goods_thumb_width, 
            self::$hot_goods_thumb_height);
	}

	/**
	 * @brief ��ȡ��ҳ�Ƽ���Ʒ����ͼ
	 */
	public static function get_rcmd_goods_url($img)
	{
        return self::get_url($img, 
            self::$rcmd_goods_thumb_width, 
            self::$rcmd_goods_thumb_height);
	}

	/**
	 * @brief ��ȡ�����б���Ʒ����ͼ
	 * @param string $img ��ƷͼƬ·��
	 * @return string ��Ʒ����ͼ����url
	 */
    public static function get_search_goods_url($img)
    {
        return self::get_url($img, 
            self::$list_goods_thumb_width, 
            self::$list_goods_thumb_height);
    }

	/**
	 * @brief ��ȡ����ҳ��ƷͼƬ
	 * @param string $img ��ƷͼƬ·��
	 * @return string ��Ʒͼ����url
	 */
    public static function get_detail_goods_url($img)
    {
        return self::get_url($img, 
            self::$detail_goods_thumb_width, 
            self::$detail_goods_thumb_height);
    }

	/**
	 * @brief ��ȡ����ҳ��ƷͼƬ
	 * @param string $img ��ƷͼƬ·��
	 * @return string ��Ʒͼ����url
	 */
    public static function get_order_goods_url($img)
    {
        return self::get_url($img, 
            self::$list_goods_thumb_width, 
            self::$list_goods_thumb_height
        );
    }
}
