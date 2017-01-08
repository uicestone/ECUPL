<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class renren_oauth {
	var $params = array('version' => '1.0',
		'format' => 'JSON');
	var $secret;
	var $api_server = 'http://api.renren.com/restserver.do?';

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new renren_oauth();
		}
		return $object;
	}

	function renren_oauth() {
		global $_G;
		loadcache('plugin');
		$this->secret = $_G['cache']['plugin']['renren']['secret'];
		$this->params['api_key'] = $_G['cache']['plugin']['renren']['api_key'];
	}

	function convertEncoding($source, $in, $out){
		$in	= strtoupper($in);
		$out = strtoupper($out);
		if ($in == "UTF8"){
			$in = "UTF-8";
		}
		if ($out == "UTF8"){
			$out = "UTF-8";
		}
		if( $in==$out ){
			return $source;
		}
	
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($source, $out, $in );
		}elseif (function_exists('iconv'))  {
			return iconv($in,$out."//IGNORE", $source);
		}
		return $source;
	}


	function create_post_body($params) {

		$post_params = array();

		foreach ($params as $key => &$val) {
			if(is_array($val)) 
				$val = implode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
		}
		return implode('&', $post_params);
	}

	

	function call_url($params,$access_url) {
		$post_body = $this->create_post_body($params);
		//echo  'post_body:'.$post_body;
		//echo  '$access_url:'.$access_url;
		if(function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')) {
			$request = curl_init();
			curl_setopt($request, CURLOPT_URL, $access_url);
			curl_setopt($request, CURLOPT_POST, 1);
			curl_setopt($request, CURLOPT_POSTFIELDS, $post_body);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);


			$result = curl_exec($request);
			curl_close($request);
		} else {
			require_once './source/plugin/renren/class/fsockopenHttp.class.php';
			$http = new fsockopenHttp();
			$http->setUrl($access_url);
			$http->setData($post_body);
			$result = $http->request('post');
		}
		//echo  'result:'.$result;
		//$result='{"a":1,"b":2,"c":3,"d":4,"e":5}';
		$result = $this->rr_handle_response($result); 
		return $result;
	}
	
	function rr_handle_response($result) {
		if(!function_exists('json_decode')) {
			require_once './source/plugin/renren/class/JSON.class.php';
			$json=new Services_JSON();
			$array = $json->decode($result);
			$json = null;
		} else {
			$array = json_decode($result);
		}
		
		return $array;
	}
}
?>