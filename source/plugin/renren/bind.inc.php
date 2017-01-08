<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

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



$code = $_GET["code"];
if($code)
	{
$code_url='http://graph.renren.com/oauth/token';
$oauth = & renren_oauth::instance();
$post_params = array('client_id'=>$_G['cache']['plugin']['renren']['api_key'],
		'client_secret'=>$_G['cache']['plugin']['renren']['secret'],
		'redirect_uri'=>$_G['siteurl'].'plugin.php?id=renren:bind',
		'grant_type'=>'authorization_code',
		'code'=>$code
		);

$access_info=$oauth->call_url($post_params,$code_url);
$access_token=$access_info->access_token;
$access_url='http://graph.renren.com/renren_api/session_key';
$post_params = array('oauth_token'=>$access_token);


$access_info=$oauth->call_url($post_params,$access_url);
$sessionkey=$access_info->renren_token->session_key;
//$GLOBALS['_G']['renren']['sessionkey'] = $sessionkey;
dsetcookie($_G['cache']['plugin']['renren']['api_key']."_session_key",$sessionkey,31536000);

$api2 = & renren_api::instance();
$userInfo2 = $api2->get_user_info();
$userId = $userInfo2->uid;
//$GLOBALS['_G']['renren']['rr_user'] = $userId;
dsetcookie($_G['cache']['plugin']['renren']['api_key']."_user",$userId,31536000);
include template('renren:rr_bind');
}
else
{
//获取网址参数 
//echo 'error:<br>'.$_SERVER["QUERY_STRING"]."<br>"; 
showmessage('error:<br>'.$_SERVER["QUERY_STRING"], 'home.php?mod=spacecp&ac=plugin&id=renren:spacecp');
}
?>