<?php
/*
���ļ�Ϊ��������������ҳ�Ĵ����ļ�
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once './source/plugin/renren/class/connect.class.php';
require_once './source/plugin/renren/renren.function.php';

global $_G;								//��ʼ��ȫ�ֱ���G
$renren = & renren_connect::instance();
$renren->inits($_G['uid']);
$rr=$_G['gp_rr'];
$iffeed=$_G['cache']['plugin']['renren']['rr_feed'];
/*�жϴ��˺��Ƿ����̳*/
$isbind = rr_is_bind();
$per = get_per($_G['uid']);
$feed_s = get_rrfeed($_G['uid']);
if($isbind['bing']) {
	$dz_name = $_G['member']['username'];
	require_once './source/plugin/renren/class/api.class.php';
	$api = & renren_api::instance();
	$dz_uid = $_G['uid'];
	$rr_uid = DB::result_first("SELECT rr_uid FROM ".DB::table('renren_connect')." WHERE dz_uid='$dz_uid' ORDER BY tag DESC");
	$user_info = $api->get_user_info_without_session($rr_uid);
	$rr_name = $user_info->name;
	if($_G['renren']) {
		$user_info = $api->get_user_info();
		$avatar = array('tinyurl'=>'', 
			'headurl'=>'', 
			'mainurl'=>'',
			);
		foreach(array_keys($avatar) as $k) {
			$avatar[$k] = $user_info->$k;
		}

		$headurl = $avatar['mainurl'];
		$uid = $_G['uid'];

		if(submitcheck('renren_avatar_submit')) {
			$faceTempPath[1]=$avatar['mainurl'];
			$faceTempPath[2]=$avatar['headurl'];
			$faceTempPath[3]=$avatar['tinyurl'];
			upload_avatar($uid, $faceTempPath);
			showmessage('renren:rr_syncsuccess','home.php?mod=spacecp&ac=plugin&id=renren:spacecp&rr=upava',1);
		}
	}
	if(submitcheck('renren_setper')) {
		$per = $_POST['RadioGroup1'];
		set_per($per);
		if($iffeed) {
			$feed = array();
			if($_POST['feednew']) {
				$feed[0]=$_POST['feednew'];
			} else {
				$feed[0]=0;
			}
			if($_POST['feedre']) {
				$feed[1]=$_POST['feedre'];
			} else {
				$feed[1]=0;
			}
			if($_POST['blognew']) {
				$feed[2]=$_POST['blognew'];
			} else {
				$feed[2]=0;
			}
			if($_POST['statusnew']) {
				$feed[3]=$_POST['statusnew'];
			} else {
				$feed[3]=0;
			}
			if($_POST['sharenew']) {
				$feed[4]=$_POST['sharenew'];
			} else {
				$feed[4]=0;
			}
			if($_POST['articalnew']) {
				$feed[5]=$_POST['articalnew'];
			} else {
				$feed[5]=0;
			}
			$feed_str = implode(",",$feed);
			set_rrfeed($feed_str);
		}
		showmessage('renren:rr_confsuccess','home.php?mod=spacecp&ac=plugin&id=renren:spacecp',1);
	}	
}

/****�жϰ���ʽ*****/

/*����̳�˺ţ�������*/
if(submitcheck('renren_bind')) {  	
	$rr_uid = rr_get_cookie('user');	//�õ�����cookie�е�����ID	
	$dz_uid = $_G['uid'];				//�õ���ǰ��̳uid 
	if($isbind['bing']) {
		showmessage('renren:rr_binddis', 'home.php?mod=spacecp&ac=plugin&id=renren:spacecp');
	}
	//echo "<script>alert('$rr_uid');</script>";
	$dzr_uid=get_dzuid($rr_uid);
	//echo "<script>alert('$dzr_uid');</script>";
	if($dzr_uid){
		showmessage('renren:rr_isbebind', 'home.php?mod=spacecp&ac=plugin&id=renren:spacecp');
	}
	/**�жϽ���**/

	/**�󶨿�ʼ**/
	if($isbind['tag']!=2) {
		rr_make_bind();
	} else {
		rbing($dz_uid);
	}
	$bind_status_cookiename = 'renren_bind_status'. $_G['uid'];
	$bind_status = 1;  //2δ��״̬��1��״̬
	dsetcookie($bind_status_cookiename, $bind_status, 604800);
	showmessage('renren:rr_bindsuccess', 'home.php?mod=spacecp&ac=plugin&id=renren:spacecp');
	
	/**�󶨽���**/
}

/*�����*/
if(submitcheck('renren_unbind')) {
	rr_unbind();	//ɾ�������������ݱ��е�������Ϣ
	$bind_status_cookiename = 'renren_bind_status'. $_G['uid'];
	$bind_status = 2;  //2δ��״̬��1��״̬
	dsetcookie($bind_status_cookiename, $bind_status, 604800);
	showmessage('renren:rr_unbindsuccess', 'home.php?mod=spacecp&ac=plugin&id=renren:spacecp');
}
?>