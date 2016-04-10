<?php
/**
 * 文件上传
 * User: sasumi
 * Date: 2014/11/19
 * Time: 14:51
 */

class YunUpload{
	const TYPE_IMAGE = 'image';
	const TYPE_CAD = 'cad';
	const YUN_UPLOAD_HOST = 'http://file.crycoder.com';
	private static $default_timeout = 10;

	/**
	 * 获取上传配置
	 * @param string $type
	 * @return mixed
	 */
	public static function getUploadConfig($type = self::TYPE_IMAGE){
		$config = array(
			//todo暂时保留，兼容旧格式
			'host' => 'http://file.crycoder.com/cgi/upload/image',
			'url' => '',
			'upload_size' => 1024*1024*20, //20MB
			'exts' => array(
				'.png',
				'.jpg',
				'.jpeg',
				'.gif',
				'.bmp',
				'.dwg'
			),

			//图片上传配置
			'image' => array(
				'host' => 'http://file.crycoder.com/cgi/upload/image',
				'url' => '',
				'upload_size' => 1024*1024*20, //20MB
				'exts' => array(
					'.png',
					'.jpg',
					'.jpeg',
					'.gif',
					'.bmp',
					'.dwg'
				)
			),

			//CAD上传配置
			'cad' => array(
				'host' => 'http://file.crycoder.com/cgi/upload/image',
				'url' => '',
				'upload_size' => 1024*1024*20, //20MB
				'exts' => array(
					'.dwg'
				)
			)
		);
		return $config[$type];
	}

	private static $map = array(
		'image/bmp'                     => 'bmp',
		'image/gif'                     => 'gif',
		'image/jpeg'                    => 'jpg,jpeg',
		'application/pdf'               => 'pdf',
		'image/png'                     => 'png',

		'application/zip'               => 'zip',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.ms-word'       => 'doc,docx',
		'application/kswps'             => 'doc',
		'application/octet-stream'      => 'dwg'
	);

	/**
	 * 文件类型
	 * @var array
	 */
	private static $file_type_ext_map = array(
		self::TYPE_IMAGE => 'jpg,png,gif,bmp,jpeg',
		self::TYPE_CAD   => 'dwg'
	);

	/**
	 * 检测文件后缀是否与mime信息是否符合
	 * @param  string $ext_list 需要检查的文件后缀
	 * @param  string $mime 文件mime
	 * @return boolean
	 */
	private static function checkByExt($ext_list, $mime){
		$ext_list = strtolower($ext_list);
		$ext_list = explode(',', $ext_list);

		foreach($ext_list as $ext){
			foreach(self::$map as $check_mime => $item){
				if(in_array($ext, explode(',', $item)) && $check_mime == $mime){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 检测文件类型是否符合mime
	 * @param string $file_type
	 * @param $mime
	 * @return bool
	 */
	private static function checkFileType($file_type = self::TYPE_IMAGE, $mime){
		$ext_list = self::$file_type_ext_map[$file_type];
		return self::checkByExt($ext_list, $mime);
	}

	/**
	 * 单个文件上传
	 * @param string $file_type 文件类型
	 * @param string $file 文件路径
	 * @param array $data post数据
	 * @param int $timeout 超时时间
	 * @param array $curl_option
	 * @param int $attach_type 0通用 1商品分类 2运营，广告
	 * @return array
	 * @throws Exception
	 */
	public static function uploadFile($file, $attach_type=0, $file_type=YunUpload::TYPE_IMAGE, $data = array(), $timeout = null, $curl_option = array()){
		$config = self::getUploadConfig($file_type);
		$file_size = $config['upload_size'];
		$host = $config['host'];

		if($file_size < $file['size']){
			throw new Exception('文件大小超出，请重新选择文件上传');
		}
//		if(!self::checkFileType($file_type, $file['type'])){
//			throw new Exception('文件类型不符合，请重新选择文件上传');
//		}

		$timeout = $timeout ?: self::$default_timeout;
		$file_exp = explode('.', $file['name']);
		$ext = array_pop($file_exp);
		$data['ext'] = $ext;
		$data['attach_type'] = $attach_type;


		try{
			$ret_content = Curl::postFiles($host, $data, array('file'=>$file['tmp_name']), $timeout, $curl_option);
		} catch(Exception $ex){
			throw $ex;
		}
		$result = json_decode($ret_content, true);
		return $result;
//		if($result['code'] == '0'){
//			return array(
//				'src'   => $result['output']['url'],
//			);
//		}
//		throw new Exception('上传失败，请稍候重试['.$result['prompt'].']');
	}
}