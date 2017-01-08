<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}//��ֱֹ�ӵ���


require_once './source/class/class_core.php';
require_once './source/function/function_home.php';
$discuz = & discuz_core::instance();
$discuz->init();//��ʼ��ȫ�ֱ���


class threadplugin_dotavs {

	var $name = 'DOTA����';			//������������
	var $iconfile = 'dotavs.gif';		//images/icons/ Ŀ¼����������������ͼƬ�ļ���
	var $buttontext = '����DOTA����';	//����ʱ��ť����


	function newthread($fid) {
		GLOBAL $_G;
		$dotavs['maxgamers'] =10;//����Ĭ���������
		//$dotavs['attendtime'] = TIMESTAMP + 3600;//Ĭ��һСʱ��������
		$dotavs['polltime'] = TIMESTAMP + 86400;//Ĭ��һ�����ͶƱ
		$dotavs['gametime'] = TIMESTAMP + 4500;//Ĭ��һСʱһ�����Ժ�Լ����ʼ��Ϸ
		$dotavs['attendtime'] = dgmdate($dotavs['attendtime']);//ת��Ϊʱ���ʽ
		$dotavs['polltime'] = dgmdate ($dotavs['polltime']);
		$dotavs['gametime'] = dgmdate ($dotavs['gametime']);
		include template('dotavs:post_dotavs'); //���뷢��ģ��
		return $return;
	}	

	function newthread_submit($fid) {
		
	}

	function newthread_submit_end($fid, $tid) {
		GLOBAL $_G;
		$polltime = @strtotime($_G['gp_polltime']);
		//$attendtime = @strtotime($_G['gp_attendtime']);//ʱ��������
		$gametime = @strtotime($_G['gp_gametime']);
		$timenow = TIMESTAMP;
		$maxgamers = 10;//$_G[gp_maxgamers];
		$uid = $_G['uid'];
		DB::query("INSERT INTO ".DB::table('dotavs')."
		(tid, maxgamers,starttime,attendtime,gametime,polltime,starterid,status) VALUES
		('$tid', '$maxgamers','$timenow','$attendtime','$gametime','$polltime', '$uid','1')");//�����»��Ϣ

	}

	function editpost($fid, $tid) {
		GLOBAL $_G;
		$dotavs = DB::fetch_first("SELECT * FROM ".DB::table('dotavs')." WHERE tid='$tid'");
		$dotavs['attendtime'] = dgmdate($dotavs['attendtime']);//ת��Ϊʱ���ʽ
		$dotavs['polltime'] = dgmdate ($dotavs['polltime']);
		$dotavs['gametime'] = dgmdate ($dotavs['gametime']);		
		include template('dotavs:post_dotavs'); //���뷢��ģ��
		return $return;
	}

	function editpost_submit($fid, $tid) {

	}

	function editpost_submit_end($fid, $tid) {
		GLOBAL $_G;
		$polltime = @strtotime($_G['gp_polltime']);
		$attendtime = @strtotime($_G['gp_attendtime']);//ʱ��������
		$gametime = @strtotime($_G['gp_gametime']);
		DB::query("UPDATE ".DB::table('dotavs')." SET attendtime='$attendtime',gametime = '$gametime',polltime ='$polltime' WHERE tid = '$tid'");//����

	}

	function newreply_submit_end($fid, $tid) {
		showmessage($post[message]);
	}

	function viewthread($tid) {
		global $_G;
		//$post = DB::fetch_first("SELECT * FROM ".DB::table('forum_post')." WHERE tid='$tid'");//������������
		$dotavs = DB::fetch_first("SELECT * FROM ".DB::table('dotavs')." WHERE tid='$tid'");
		$dotavs['gametime']= dgmdate($dotavs['gametime']);
		/* if($dotavs[polltime] < $timenow){
			$dotavs[status]=3;
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '3' WHERE tid='$tid'");
		}elseif($dotavs[attendtime] < $timenow){
			$dotavs[status]=2;
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '2' WHERE tid='$tid'");
		}//����ʱ����»״̬	 */
		
		if($dotavs['status']==1){
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid = '$tid')");
			while($attended = DB::fetch($query)) {
				if(!isset($dotavs['redside'][$attended['uid']])) {
					$attended['avatar'] = avatar($attended['uid'], 'small');
					$dotavs['attended'][$attended['uid']] = $attended;
				}
			}//�ռ�����δ����ѡ����Ϣ
		}else{
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE side = '1' AND tid = '$tid')");
			while($redside = DB::fetch($query)) {
				if(!isset($dotavs['redside'][$redside['uid']])) {
					$redside['avatar'] = avatar($redside['uid'], 'small');
					$dotavs['redside'][$redside['uid']] = $redside;
				}
			}//�ռ��췽ѡ����Ϣ
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE side = '2' AND tid = '$tid')");
			while($blueside = DB::fetch($query)) {
				if(!isset($dotavs['blueside'][$blueside['uid']])) {
					$blueside['avatar'] = avatar($blueside['uid'], 'small');
					$dotavs['blueside'][$blueside['uid']] = $blueside;
				}
			}////�ռ�����ѡ����Ϣ
		}
		


	
		if($dotavs['redscore'] && $dotavs['redscore'] > $dotavs['bluescore']) {
			$dotavs['redheight'] = 100;
			$dotavs['blueheight'] = intval($dotavs['bluescore'] / $dotavs['redscore'] * 100);
		}elseif($dotavs['bluescore'] && $dotavs['bluescore'] > $dotavs['redscore']) {
			$dotavs['blueheight'] = 100;
			$dotavs['redheight'] = intval($dotavs['redscore'] / $dotavs['bluescore'] * 100);
		}//����ˮ���߶�
		
		include template('dotavs:viewthread_dotavs');
		return $return;
	}
	

}

?>

