<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class renren_api {
	var $params = array('version' => '1.0',
		'format' => 'JSON');
	var $secret;
	var $api_server = 'http://api.renren.com/restserver.do?';

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new renren_api();
		}
		return $object;
	}

	function renren_api() {
		global $_G;
		loadcache('plugin');
		$this->secret = $_G['cache']['plugin']['renren']['secret'];
		$this->params['api_key'] = $_G['cache']['plugin']['renren']['api_key'];
	}

	function get_user_info() {
		global $_G;
		$params = array('fields'=>array('uid', 'name','sex','birthday', 'mainurl','hometown_location', 'work_history', 'university_history', 'tinyurl', 'headurl', 'headurl_with_logo', 'tinyurl_with_logo', 'mainurl_with_logo'));
		$params['session_key'] = rr_get_cookie('session_key');
		$aaa=rr_get_cookie('session_key');
		//echo "<script>alert('$aaa');</script>";
		if($aaa)
		{
		$user_info = $this->call_api('users.getInfo', $params);
		$result=$user_info[0];
		$username=$result->name;
		$hometown = $result->hometown_location;
		$birthprovince = $hometown->province;
		$city = $hometown->city;
		$company = $result->work_history[0]->company_name;
		$graduateschool = $result->university_history[0]->name;
		if(strtolower($_G['charset']) != 'utf-8') {
			$result->name=$this->convertEncoding($username, "UTF8", "GBK");
			$result->hometown_location->province= $this->convertEncoding($birthprovince, "UTF8", "GBK");
			$result->hometown_location->city = $this->convertEncoding($city, "UTF8", "GBK");
			$result->work_history[0]->company_name = $this->convertEncoding($company, "UTF8", "GBK");
			$result->university_history[0]->name = $this->convertEncoding($graduateschool, "UTF8", "GBK");
		}
		$result->rrname = $result->name;
		$uid = DB::result_first("select uid from ".DB::table('common_member')." where username='$result->name'");
		if(!empty($uid)) {
			$result->name = $result->name.$result->uid;
			$result->parden = 1;  	
		} else {
			$result->parden = 0;
		}
		}
		return $result;
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

	function get_user_info_without_session($uid) {
		global $_G;
		$params = array('uids'=>$uid);
		$user_info= $this->call_api('users.getInfo',$params);
		$result=$user_info[0];
		$username=$result->name;
		if($username) {
			if(strtolower($_G['charset']) != 'utf-8') {
				$result->name=$this->convertEncoding($username, "UTF8", "GBK");
			} else {
				$result->name=$username;
			}
		}
		return $result;
	}

	function get_user_friend() {
		$params = array();
		$params['session_key'] = rr_get_cookie('session_key');
		$result = $this->call_api('friends.getFriends', $params);
		return $result;
	}

	function pushfeed($title_data,$body_data,$template_id) {
		if(!function_exists('json_encode')) {
			require_once './source/plugin/renren/class/JSON.class.php';
			$json=new Services_JSON();
			$params['title_data']=$json->encode($title_data);
			$params['body_data']=$json->encode($body_data);
			$json = null;
		} else {
			$params['title_data']=json_encode($title_data);
			$params['body_data']=json_encode($body_data);
		}
		$params['template_id'] = $template_id;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('feed.publishTemplatizedAction',$params);
		return $result;
	}

	function pushcustomfeed($name,$description,$url,$image,$action_name,$action_link,$feed_message) {
		if($description==null||$description=='')
		{
			$description=$name;
		}
		$params['name']=$name;
		$params['description']=$description;
		$params['url']=$url;
		$params['image']=$image;
		$params['action_name']=$action_name;
		$params['action_link']=$action_link;
		$params['message']=$feed_message;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('feed.publishFeed',$params);
		return $result;
	}
	function pushblog($name,$description) {
		if($description==null||$description=='')
		{
			$description=$name;
		}
		$params['title']=$name;
		$params['content']=$description;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('blog.addBlog',$params);
		return $result;
	}
	function pushstatus($status) {

		$params['status']=$status;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('status.set',$params);
		return $result;
	}
	function pushshare($url,$description) {

		$params['url']=$url;
		$params['comment']=$description;
		$params['type']=6;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('share.publish',$params);
		
		return $result;
	}

	function has_perm($ext_perm) {
		$params['ext_perm'] = $ext_perm;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('users.hasAppPermission',$params);
		return $result;
	}

	function call_api($method, $params) {
		if(!empty($params) && is_array($params)) {
			foreach($params as $k => $v) {
				$this->params[$k] = $v;
			}
		}
		$this->params['method'] = $method;

		$post_body = $this->create_post_body();
		//echo  'post_body:'.$post_body.'-----------';
		if(function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')) {
			$request = curl_init();
			curl_setopt($request, CURLOPT_URL, $this->api_server);
			curl_setopt($request, CURLOPT_POST, 1);
			curl_setopt($request, CURLOPT_POSTFIELDS, $post_body);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($request);
			curl_close($request);
		} else {
			require_once './source/plugin/renren/class/fsockopenHttp.class.php';
			$http = new fsockopenHttp();
			$http->setUrl($this->api_server);
			$http->setData($post_body);
			$result = $http->request('post');
		}

		//$result = $this->convertEncoding($result,"UTF-8","GBK");
		//echo "<script>alert('$result');</script>";
		$result = $this->rr_handle_response($result); 
		return $result;
	}

	function call_url($access_url) {

		if(function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')) {
			$request = curl_init();
			curl_setopt($request, CURLOPT_URL, access_url);
			curl_setopt($request, CURLOPT_POST, 1);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($request);
			curl_close($request);
		} else {
			$context =array('http' => array('method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
				'User-Agent: Facebook API PHP5 Client 1.1 '."\r\n"));
			$contextid=stream_context_create($context);
			$sock=fopen($this->api_server, 'r', false, $contextid);
			if($sock) {
				$result='';
				while (!feof($sock))
				  $result.=fgets($sock, 4096);
					fclose($sock);
			}
		}
		$result = $this->rr_handle_response($result); 
		return $result;
	}
	function create_post_body() {
		$this->params['call_id'] = time();

		$post_params = array();

		foreach ($this->params as $key => &$val) {
			if(is_array($val)) 
			{
				$val = implode(',', $val);
			}
			$post_params[] = $key.'='.urlencode($val);
		}
		
		$post_params[] = 'sig='.$this->rr_generate_sig($this->params, $this->secret);
		return implode('&', $post_params);
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
		$this->rr_check_response($array);
		return $array;
	}

	function rr_check_response(&$result) {
		global $_G;
		$msg='';
		if($result->error_code) {
			if($result->error_code) {
				$msg .= $result->error_code.'<br>';
			}
			if($result->error_msg) {
				$msg .= $result->error_msg.'<br>';
			}
		}

		if($msg!='' && $result->error_code!='10702' && $result->error_code!='10600' && $result->error_code!='303') {
			if(strtolower($_G['charset']) != 'utf-8') {
				$msg=$this->convertEncoding($msg, "UTF8", "GBK");
			}
			echo $msg;
			exit;
		}
	}
	
	function rr_generate_sig($params, $secret) {
		ksort($params);
		$sig = '';
		foreach($params as $key=>$value) {
			if(is_array($value)) 
			{
				$value = implode(',', $value);
			}
			$sig .= "$key=$value";
		}
		$sig .= $secret;
		return md5($sig);
	}
}
?>