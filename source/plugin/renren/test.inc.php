<?php

require_once './source/class/class_core.php';
require_once './source/function/function_forum.php';
require_once './source/function/function_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
loadforum();
loadcache('plugin');

require_once './source/plugin/renren/class/api.class.php';
require_once './source/plugin/renren/class/connect.class.php';
require_once './source/plugin/renren/renren.function.php';
require_once libfile('function/member');
require_once './source/plugin/renren/class/oauth.class.php';




$code_url='http://graph.renren.com/oauth/token';
$oauth = & renren_oauth::instance();
$post_params = array('client_id'=>$_G['cache']['plugin']['renren']['api_key'],
		'client_secret'=>$_G['cache']['plugin']['renren']['secret'],
		'redirect_uri'=>$_G['siteurl'].'plugin.php?id=renren:auth',
		'grant_type'=>'authorization_code',
		'code'=>$code
		);

$access_info=$oauth->call_url($post_params,$code_url);
		if(isset($access_info))
		{
			echo 'post '.$code_url.' success!<br>';
		}
		else
		{
			echo 'post '.$code_url.' fail!<br>';
		}

$access_url='http://graph.renren.com/renren_api/session_key';
$access_info=$oauth->call_url($post_params,$access_url);
		if(isset($access_info))
		{
			echo 'post '.$access_url.' success!<br>';
		}
		else
		{
			echo 'post '.$access_url.' fail!<br>';
		}
$api_url='http://api.renren.com/restserver.do';
$access_info=$oauth->call_url($post_params,$api_url);
		if(isset($access_info))
		{
			echo 'post '.$api_url.' success!<br>';
		}
		else
		{
			echo 'post '.$api_url.' fail!<br>';
		}
$qq_url='http://openapi.qzone.qq.com/user/get_user_info';
$access_info=$oauth->call_url($post_params,$qq_url);
		if(isset($access_info))
		{
			echo 'post '.$qq_url.' success!<br>';
		}
		else
		{
			echo 'post '.$qq_url.' fail!<br>';
		}
$discuz_url='http://api.discuz.qq.com/site.php';
$access_info=$oauth->call_url($post_params,$discuz_url);
		if(isset($access_info))
		{
			echo 'post '.$discuz_url.' success!<br>';
		}
		else
		{
			echo 'post '.$discuz_url.' fail!<br>';
		}
$a1='http://connect.discuz.qq.com';
$access_info=$oauth->call_url($post_params,$a1);
		if(isset($access_info))
		{
			echo 'post '.$a1.' success!<br>';
		}
		else
		{
			echo 'post '.$a1.' fail!<br>';
		}
		$a2='http://api.discuz.qq.com';
$access_info=$oauth->call_url($post_params,$a2);
		if(isset($access_info))
		{
			echo 'post '.$a2.' success!<br>';
		}
		else
		{
			echo 'post '.$a2.' fail!<br>';
		}
		$a3='http://avatar.connect.discuz.qq.com';
$access_info=$oauth->call_url($post_params,$a3);
		if(isset($access_info))
		{
			echo 'post '.$a3.' success!<br>';
		}
		else
		{
			echo 'post '.$a3.' fail!<br>';
		}
				$a4='http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey';
$access_info=$oauth->call_url($post_params,$a4);
		if(isset($access_info))
		{
			echo 'post '.$a4.' success!<br>';
		}
		else
		{
			echo 'post '.$a4.' fail!<br>';
		}
?>