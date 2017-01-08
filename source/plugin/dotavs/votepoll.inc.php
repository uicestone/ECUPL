<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$action = $_G[gp_action];//获得按钮名
$timenow = TIMESTAMP;
$uid = $_G[gp_uid];
$tid = $_G[gp_tid];

$dotavs = DB::fetch_first("SELECT * FROM ".DB::table('dotavs')." WHERE tid='$tid'");

/* if($dotavs[polltime] < $timenow){
	$dotavs[status]=3;
	DB::query("UPDATE ".DB::table('dotavs')." SET status = '3' WHERE tid='$tid'");
}elseif($dotavs[attendtime] < $timenow){
	$dotavs[status]=2;
	DB::query("UPDATE ".DB::table('dotavs')." SET status = '2' WHERE tid='$tid'");
}//根据时间更新活动状态 */

if($action==0){//报名按钮
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if(!$uid){//未登陆用户
		showmessage("请登陆以后报名");
	}elseif($dotavs[status]!=1){//检查活动状态
		showmessage("不在报名时间");
	}elseif($dotavs_gamers[uid]){//检查是否已经报名
		showmessage("你已经报过名了！");
	}else{
		$rand = rand(1,88887);
		DB::query("INSERT INTO ".DB::table('dotavs_gamers')." (attendtime,tid,uid,rand) VALUES ('$timenow','$tid','$uid','$rand')");
		$dotavs_gamers = DB::fetch(DB::query("SELECT COUNT(uid) AS 'gamers' FROM ".DB::table('dotavs_gamers')." WHERE tid = '$tid'"));
		if($dotavs_gamers[gamers]==$dotavs[maxgamers]){//正好人满，开始分组改状态
			$maxgamers_perside = $dotavs['maxgamers']/2;
			DB::query("UPDATE ".DB::table('dotavs_gamers')." SET side = '1' WHERE tid = '$tid' order by rand ASC limit $maxgamers_perside");
			//随机数排序，前半红队
			DB::query("UPDATE ".DB::table('dotavs_gamers')." SET side = '2' WHERE tid = '$tid' order by rand DESC limit $maxgamers_perside");
			//随机数排序，后半蓝队
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '2' WHERE tid='$tid'");//更改活动状态
			showmessage("本组配组成功！<br>刷新页面查看分组");
		}
		showmessage("报名成功！");
	}
	
	
}

if ($action==1){//投票红方
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if($dotavs[status]!=2){
	
		showmessage("不在投票时间");
	}elseif(!$dotavs_gamers[uid]){
		showmessage("你无权投票<br>参赛者才有投票权");
	}elseif($dotavs_gamers[rand]==88888){
		showmessage("你已经投过票了！");
	}else{
		$newredscore = $dotavs[redscore]+1;
		DB::query("UPDATE ".DB::table('dotavs')." SET redscore = '$newredscore' WHERE tid='$tid'");
		DB::query("UPDATE ".DB::table('dotavs_gamers')." SET rand = '88888' WHERE tid='$tid' AND uid='$uid'");
		check_result($dotavs['redscore'],$dotavs['bluescore'],$tid);
		showmessage("投票成功！<br>刷新网页以显示新分数");
	}
	
}

if ($action==2){//投票蓝方
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if($dotavs[status]!=2){
		showmessage("不在投票时间");
	}elseif(!$dotavs_gamers[uid]){
		showmessage("你无权投票<br>参赛者才有投票权");
	}elseif($dotavs_gamers[rand]==88888){
		showmessage("你已经投过票了！");
	}else{
		$newbluescore = $dotavs[bluescore]+1;
		DB::query("UPDATE ".DB::table('dotavs')." SET bluescore = '$newbluescore' WHERE tid='$tid'");
		DB::query("UPDATE ".DB::table('dotavs_gamers')." SET rand = '88888' WHERE tid='$tid' AND uid='$uid'");//投过票以后rand设为88888
		check_result($dotavs['redscore'],$dotavs['bluescore'],$tid);
		showmessage("投票成功！<br>刷新页面显示新分数");
	}
}


function check_result($redscore,$bluescore,$tid){
	$dotavs_gamers = DB::fetch(DB::query("SELECT COUNT(uid) AS 'pollers' FROM ".DB::table('dotavs_gamers')." WHERE rand = 88888"));//找出没有投过票的
	if($dotavs_gamers['pollers']> $dotavs['maxgamers']/2+1){//如果全投票了
		if($redscore > $bluescore){
			$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND side ='1'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "20"), false, '', 0, '');
			}
		}//给红方每人加20dota值
		if($redscore < $bluescore){
			$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND side ='2'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "20"), false, '', 0, '');
			}
		}//给蓝方每人加20dota值
		if($redscore == $bluescore){
						$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "-20"), false, '', 0, '');
			}
		}//双方都扣20dota值
		DB::query("UPDATE ".DB::table('dotavs')." SET status = '3' WHERE tid='$tid'");//更改活动状态
		showmessage("投票成功，游戏结束了<br>刷新页面显示结果");
	}
}
?>