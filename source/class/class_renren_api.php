<?php

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
		$params = array('fields'=>array('uid', 'name','sex','birthday', 'mainurl','hometown_location', 'work_history', 'university_history', 'tinyurl', 'headurl', 'headurl_with_logo', 'tinyurl_with_logo', 'mainurl_with_logo'));
		$params['session_key'] = rr_get_cookie('session_key');
		$result = $this->call_api('users.getInfo', $params);
		return $result[0];
	}       
	function get_user_info_without_session($uid) {
                $params = array('uids'=>$uid);
                $user_info= $this->call_api('users.getInfo',$params);
                $result=$user_info[0];
                $username=$result->name;
                if($username)
                {
                        $result->name=iconv("utf-8", "gbk",$username);
                }
                return $result;
        }
	function get_user_friend() {
		$params = array();
		$params['session_key'] = rr_get_cookie('session_key');
		$result = $this->call_api('friends.getFriends', $params);
		return $result;
	}  
	
	function pushfeed($title_data,$body_data,$template_id){
		if (!function_exists('json_encode')){
			require_once './source/plugin/renren/JSON.class.php';
			$json=new Services_JSON();
			$params['title_data']=$json->encode($title_data);
			$params['body_data']=$json->encode($body_data);
		}else{
			$params['title_data']=json_encode($title_data);
			$params['body_data']=json_encode($body_data);
		}
		$params['template_id'] = $template_id;
		$params['session_key'] = rr_get_cookie('session_key');
		$result=$this->call_api('feed.publishTemplatizedAction',$params);
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
		if (function_exists('curl_init')) {
		  $request = curl_init();
		  curl_setopt($request, CURLOPT_URL, $this->api_server);
		  curl_setopt($request, CURLOPT_POST, 1);
		  curl_setopt($request, CURLOPT_POSTFIELDS, $post_body);
		  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		  
		  $result = curl_exec($request);
		  curl_close($request);
		} else {
			$context =array('http' => array('method' => 'POST',
											'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
														'User-Agent: Facebook API PHP5 Client 1.1 '."\r\n".
														'Content-length: ' . strlen($post_body),
											'content' => $post_body));
			$contextid=stream_context_create($context);
			$sock=fopen($this->api_server, 'r', false, $contextid);
			if ($sock) {
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
		  	$val = implode(',', $val);
		  $post_params[] = $key.'='.urlencode($val);
		}
		
		$post_params[] = 'sig='.rr_generate_sig($this->params, $this->secret);
		return implode('&', $post_params);
	}
	
	function rr_handle_response($result) {
		if (!function_exists('json_decode')){
			require_once './source/plugin/renren/JSON.class.php';
			$json=new Services_JSON();
			$array = $json->decode($result);
			$json = null;
		}else{
			$array = json_decode($result);
		}
		$this->rr_check_response($array);
		return $array;
	}
	
	function rr_check_response(&$result) {
		$msg='';
		if($result->error_code)
		{
			if($result->error_code)
			{
				$msg .= $result->error_code.'<br>';
			}
			if($result->error_msg)
			{
				$msg .= $result->error_msg.'<br>';
			}
		}
		
		if($msg!='' && $result->error_code!='10702' && $result->error_code!='10600' ){
			$msg=iconv("UTF-8","GBK",$msg);
			echo $msg;
			exit;
		}
	}
		 
		
}

?>
