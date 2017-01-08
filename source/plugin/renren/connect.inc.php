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
$renren = & renren_connect::instance();
$renren->inits($discuz_uid);
$isbind = rr_is_bind();
$rr_pic = $_G['cache']['plugin']['renren']['rr_pic'];
if($_G['gp_uid']!='') {
	$per = get_per($_G['gp_uid']);
	$rruid = get_rruid($_G['gp_uid']);
	if($_G['uid']) {
		if($rruid) {
			if($per == 0) {
				showmessage("renren:rr_nhome",'index.php',1);
			} elseif($per == 1) {
				require_once libfile('function/friend');
				$isfrend=friend_check($_G['gp_uid']);
				if($isfrend) {
					Header("Location:http://www.renren.com/profile.do?id=".$rruid."");
				} else {
					showmessage("renren:rr_fhome",'index.php',1);
				}
			} else {
				Header("Location:http://www.renren.com/profile.do?id=".$rruid."");
			}
		} else {
			showmessage("renren:rr_nobind",'index.php',1);
		}
	} else {
		showmessage('not_loggedin', NULL, array(), array('login' => 1));
	}
} else {
	if($_G['renren']) {   //判断是否登录人人
		$divuser = FALSE;
		if($_G['uid'] != '' && get_rruid($_G['uid']) != rr_get_rr_uid() && !$_GET[chglogin]) {
			$divuser = TRUE;
		}
		if($isbind['bing'] == 1 && !$divuser) {  //登陆人人的账号绑定论坛，登陆
			$discuz_uid = rr_get_dz_uid();
			//showmessage($discuz_uid,'index.php');
			//echo "<script>alert('$discuz_uid');</script>";
			$user = getuserbyuid($discuz_uid);
			setloginstatus($user, 2592000);
		} else {
			if($isbind['tag'] != 2) { //没绑定过论坛
				$api = & renren_api::instance();
				$user_info = $api->get_user_info();
				$username = $user_info->name;
				if(submitcheck('rradmincreatesubmit')) {
					$dz_username = $_POST[username];
					$email = $_POST[email];
					$avatar = $_POST[rr_ava];
					$password = $_POST[password];
					$renren->init($discuz_uid,$dz_username,$email,$avatar,$password);
					showmessage("renren:rr_confsuccess",'plugin.php?id=renren:connect',1);
				}
				if(submitcheck('discuz_bind')) {
					$username = getgpc('username');
					$password = getgpc('password');
					$answer = getgpc('answernew');
					$questionid = getgpc('questionidnew');
					if(!function_exists('uc_user_login')) {
						loaducenter();
					}
					if(preg_match('/^[1-9]\d*$/', $username)) {
						$result = uc_user_login($username, $password, 1, 1, $questionid, $answer);
					} elseif(isemail($username)) {
						$result = uc_user_login($username, $password, 2, 1, $questionid, $answer);
					} else {
						$result = uc_user_login($username, $password, 0, 1, $questionid, $answer);
					}
					if(!empty($result)) {
						$uid = $result[0];
						if($uid > 0) {
							$rr_uid = rr_get_bind_rr_uid($uid);
							if($rr_uid) {
								showmessage('renren:rr_binddis', 'index.php');
							}
							rr_make_bind($uid);
							showmessage('renren:rr_bindsuccess', 'plugin.php?id=renren:connect');
						}showmessage('renren:rr_binddis', 'index.php');
					}
				}
			} elseif($isbind['tag'] == 2) {
				$username = $isbind['username'];
				if(submitcheck('discuz_rrbind')) {
					rbing($isbind['uid']);
					showmessage('renren:rr_bindsuccess', 'plugin.php?id=renren:connect');
				}
			}
		}
	}
	include template('renren:rr_login');
	
}
?>