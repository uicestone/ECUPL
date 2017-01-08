<?php
define('APPTYPEID', 0);
define('CURSCRIPT', 'member');

require './source/class/class_core.php';
require './source/function/function_forum.php';

$discuz = & discuz_core::instance();
$discuz->init();
loadforum();
loadcache('plugin');

require_once libfile('class/renren_connect');
require_once libfile('function/renren');
require_once libfile('class/renren_api');
require_once libfile('function/member');
$renren = & renren_connect::instance();
$renren->inits($discuz_uid);
$isbind = rr_is_bind();
$rr_pic = $_G['cache']['plugin']['renren']['rr_pic'];
if($_G['gp_uid']!=''){
	$per=get_per($_G['gp_uid']);
	$rruid=get_rruid($_G['gp_uid']);
	if($rruid){
		if($per==0){
			showmessage("���û�δ����������ҳ",'index.php',1);
		}elseif($per==1){
			require_once libfile('function/friend');
			$isfrend=friend_check($_G['gp_uid']);
			if($isfrend){
				$rruid=get_rruid($_G['gp_uid']);
				Header("Location:http://www.renren.com/profile.do?id=".$rruid."");
			}else{
				showmessage("���û�ֻ����ѿ�����������ҳ",'index.php',1);
			}
		}else{
			$rruid=get_rruid($_G['gp_uid']);
			Header("Location:http://www.renren.com/profile.do?id=".$rruid."");
		}
	}else{
		showmessage("���û�û�а���������",'index.php',1);
	}
}else{
	if($_G['renren']){   //�ж��Ƿ��¼����
		if($isbind['bing']==1){  //��½���˵��˺Ű���̳����½
			$discuz_uid = rr_get_dz_uid();
			$user = getuserbyuid($discuz_uid);
			setloginstatus($user, 2592000);
		}else{
			if($isbind['tag']!=2){ //û�󶨹���̳
				$api= & renren_api::instance();
				$user_info = $api->get_user_info();
				$username = $user_info->name;
				$username = mb_convert_encoding($username,"GBK","UTF8");
				
				if(submitcheck('rradmincreatesubmit')){
					$dz_username=$_POST[username];
					$email=$_POST[email];
					$avatar = $_POST[rr_ava];
					$password = $_POST[password];
					$renren->init($discuz_uid,$dz_username,$email,$avatar,$password);
					showmessage("ע��ɹ�",'renren.php',1);
				}
				
				if(submitcheck('discuz_bind')) {
					$username = getgpc('username');
					$password = getgpc('password');
					$answer = getgpc('answernew');
					$questionid= getgpc('questionidnew');
					if(!function_exists('uc_user_login')) {
						loaducenter();
					}
					if(preg_match('/^[1-9]\d*$/', $username)) {
						$result = uc_user_login($username, $password, 1, 1, $questionid, $answer);
					} elseif(isemail($username)) {
						$result= uc_user_login($username, $password, 2, 1, $questionid, $answer);
					} else {
						$result = uc_user_login($username, $password, 0, 1, $questionid, $answer);
					}
					if(!empty($result)) {
						$uid = $result[0];
						if($uid>0) {
							$rr_uid = rr_get_bind_rr_uid($uid);
							if($rr_uid) {
								showmessage('��ʧ��', 'index.php');
							}
							rr_make_bind($uid);
							showmessage('�󶨳ɹ�', 'renren.php');
						}showmessage('��ʧ��', 'index.php');
					}
				}
			}elseif($isbind['tag']==2){
				$username = $isbind['username'];
				if(submitcheck('discuz_rrbind')){
					rbing($isbind['uid']);
					showmessage('�󶨳ɹ�', 'renren.php');
				}
			}
		}
	}
	include template('renren:rr_login');
}
?>
