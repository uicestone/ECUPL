<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}//禁止直接调用


require_once './source/class/class_core.php';
require_once './source/function/function_home.php';
$discuz = & discuz_core::instance();
$discuz->init();//初始化全局变量


class threadplugin_dotavs {

	var $name = 'DOTA竞技';			//主题类型名称
	var $iconfile = 'dotavs.gif';		//images/icons/ 目录下新增的主题类型图片文件名
	var $buttontext = '发布DOTA竞技';	//发帖时按钮文字


	function newthread($fid) {
		GLOBAL $_G;
		$dotavs['maxgamers'] =10;//设置默认最大人数
		//$dotavs['attendtime'] = TIMESTAMP + 3600;//默认一小时结束报名
		$dotavs['polltime'] = TIMESTAMP + 86400;//默认一天结束投票
		$dotavs['gametime'] = TIMESTAMP + 4500;//默认一小时一刻钟以后约定开始游戏
		$dotavs['attendtime'] = dgmdate($dotavs['attendtime']);//转化为时间格式
		$dotavs['polltime'] = dgmdate ($dotavs['polltime']);
		$dotavs['gametime'] = dgmdate ($dotavs['gametime']);
		include template('dotavs:post_dotavs'); //载入发帖模板
		return $return;
	}	

	function newthread_submit($fid) {
		
	}

	function newthread_submit_end($fid, $tid) {
		GLOBAL $_G;
		$polltime = @strtotime($_G['gp_polltime']);
		//$attendtime = @strtotime($_G['gp_attendtime']);//时间整数化
		$gametime = @strtotime($_G['gp_gametime']);
		$timenow = TIMESTAMP;
		$maxgamers = 10;//$_G[gp_maxgamers];
		$uid = $_G['uid'];
		DB::query("INSERT INTO ".DB::table('dotavs')."
		(tid, maxgamers,starttime,attendtime,gametime,polltime,starterid,status) VALUES
		('$tid', '$maxgamers','$timenow','$attendtime','$gametime','$polltime', '$uid','1')");//插入新活动信息

	}

	function editpost($fid, $tid) {
		GLOBAL $_G;
		$dotavs = DB::fetch_first("SELECT * FROM ".DB::table('dotavs')." WHERE tid='$tid'");
		$dotavs['attendtime'] = dgmdate($dotavs['attendtime']);//转化为时间格式
		$dotavs['polltime'] = dgmdate ($dotavs['polltime']);
		$dotavs['gametime'] = dgmdate ($dotavs['gametime']);		
		include template('dotavs:post_dotavs'); //载入发帖模板
		return $return;
	}

	function editpost_submit($fid, $tid) {

	}

	function editpost_submit_end($fid, $tid) {
		GLOBAL $_G;
		$polltime = @strtotime($_G['gp_polltime']);
		$attendtime = @strtotime($_G['gp_attendtime']);//时间整数化
		$gametime = @strtotime($_G['gp_gametime']);
		DB::query("UPDATE ".DB::table('dotavs')." SET attendtime='$attendtime',gametime = '$gametime',polltime ='$polltime' WHERE tid = '$tid'");//更新

	}

	function newreply_submit_end($fid, $tid) {
		showmessage($post[message]);
	}

	function viewthread($tid) {
		global $_G;
		//$post = DB::fetch_first("SELECT * FROM ".DB::table('forum_post')." WHERE tid='$tid'");//调用帖子内容
		$dotavs = DB::fetch_first("SELECT * FROM ".DB::table('dotavs')." WHERE tid='$tid'");
		$dotavs['gametime']= dgmdate($dotavs['gametime']);
		/* if($dotavs[polltime] < $timenow){
			$dotavs[status]=3;
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '3' WHERE tid='$tid'");
		}elseif($dotavs[attendtime] < $timenow){
			$dotavs[status]=2;
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '2' WHERE tid='$tid'");
		}//根据时间更新活动状态	 */
		
		if($dotavs['status']==1){
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid = '$tid')");
			while($attended = DB::fetch($query)) {
				if(!isset($dotavs['redside'][$attended['uid']])) {
					$attended['avatar'] = avatar($attended['uid'], 'small');
					$dotavs['attended'][$attended['uid']] = $attended;
				}
			}//收集报名未分组选手信息
		}else{
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE side = '1' AND tid = '$tid')");
			while($redside = DB::fetch($query)) {
				if(!isset($dotavs['redside'][$redside['uid']])) {
					$redside['avatar'] = avatar($redside['uid'], 'small');
					$dotavs['redside'][$redside['uid']] = $redside;
				}
			}//收集红方选手信息
			$query = DB::query("SELECT uid,username FROM ".DB::table('common_member')." WHERE uid IN(SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE side = '2' AND tid = '$tid')");
			while($blueside = DB::fetch($query)) {
				if(!isset($dotavs['blueside'][$blueside['uid']])) {
					$blueside['avatar'] = avatar($blueside['uid'], 'small');
					$dotavs['blueside'][$blueside['uid']] = $blueside;
				}
			}////收集蓝方选手信息
		}
		


	
		if($dotavs['redscore'] && $dotavs['redscore'] > $dotavs['bluescore']) {
			$dotavs['redheight'] = 100;
			$dotavs['blueheight'] = intval($dotavs['bluescore'] / $dotavs['redscore'] * 100);
		}elseif($dotavs['bluescore'] && $dotavs['bluescore'] > $dotavs['redscore']) {
			$dotavs['blueheight'] = 100;
			$dotavs['redheight'] = intval($dotavs['redscore'] / $dotavs['bluescore'] * 100);
		}//红蓝水柱高度
		
		include template('dotavs:viewthread_dotavs');
		return $return;
	}
	

}

?>

