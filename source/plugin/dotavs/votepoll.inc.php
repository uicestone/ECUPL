<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$action = $_G[gp_action];//��ð�ť��
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
}//����ʱ����»״̬ */

if($action==0){//������ť
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if(!$uid){//δ��½�û�
		showmessage("���½�Ժ���");
	}elseif($dotavs[status]!=1){//���״̬
		showmessage("���ڱ���ʱ��");
	}elseif($dotavs_gamers[uid]){//����Ƿ��Ѿ�����
		showmessage("���Ѿ��������ˣ�");
	}else{
		$rand = rand(1,88887);
		DB::query("INSERT INTO ".DB::table('dotavs_gamers')." (attendtime,tid,uid,rand) VALUES ('$timenow','$tid','$uid','$rand')");
		$dotavs_gamers = DB::fetch(DB::query("SELECT COUNT(uid) AS 'gamers' FROM ".DB::table('dotavs_gamers')." WHERE tid = '$tid'"));
		if($dotavs_gamers[gamers]==$dotavs[maxgamers]){//������������ʼ�����״̬
			$maxgamers_perside = $dotavs['maxgamers']/2;
			DB::query("UPDATE ".DB::table('dotavs_gamers')." SET side = '1' WHERE tid = '$tid' order by rand ASC limit $maxgamers_perside");
			//���������ǰ����
			DB::query("UPDATE ".DB::table('dotavs_gamers')." SET side = '2' WHERE tid = '$tid' order by rand DESC limit $maxgamers_perside");
			//��������򣬺������
			DB::query("UPDATE ".DB::table('dotavs')." SET status = '2' WHERE tid='$tid'");//���Ļ״̬
			showmessage("��������ɹ���<br>ˢ��ҳ��鿴����");
		}
		showmessage("�����ɹ���");
	}
	
	
}

if ($action==1){//ͶƱ�췽
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if($dotavs[status]!=2){
	
		showmessage("����ͶƱʱ��");
	}elseif(!$dotavs_gamers[uid]){
		showmessage("����ȨͶƱ<br>�����߲���ͶƱȨ");
	}elseif($dotavs_gamers[rand]==88888){
		showmessage("���Ѿ�Ͷ��Ʊ�ˣ�");
	}else{
		$newredscore = $dotavs[redscore]+1;
		DB::query("UPDATE ".DB::table('dotavs')." SET redscore = '$newredscore' WHERE tid='$tid'");
		DB::query("UPDATE ".DB::table('dotavs_gamers')." SET rand = '88888' WHERE tid='$tid' AND uid='$uid'");
		check_result($dotavs['redscore'],$dotavs['bluescore'],$tid);
		showmessage("ͶƱ�ɹ���<br>ˢ����ҳ����ʾ�·���");
	}
	
}

if ($action==2){//ͶƱ����
	$dotavs_gamers = DB::fetch_first("SELECT * FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND uid = '$uid'");
	if($dotavs[status]!=2){
		showmessage("����ͶƱʱ��");
	}elseif(!$dotavs_gamers[uid]){
		showmessage("����ȨͶƱ<br>�����߲���ͶƱȨ");
	}elseif($dotavs_gamers[rand]==88888){
		showmessage("���Ѿ�Ͷ��Ʊ�ˣ�");
	}else{
		$newbluescore = $dotavs[bluescore]+1;
		DB::query("UPDATE ".DB::table('dotavs')." SET bluescore = '$newbluescore' WHERE tid='$tid'");
		DB::query("UPDATE ".DB::table('dotavs_gamers')." SET rand = '88888' WHERE tid='$tid' AND uid='$uid'");//Ͷ��Ʊ�Ժ�rand��Ϊ88888
		check_result($dotavs['redscore'],$dotavs['bluescore'],$tid);
		showmessage("ͶƱ�ɹ���<br>ˢ��ҳ����ʾ�·���");
	}
}


function check_result($redscore,$bluescore,$tid){
	$dotavs_gamers = DB::fetch(DB::query("SELECT COUNT(uid) AS 'pollers' FROM ".DB::table('dotavs_gamers')." WHERE rand = 88888"));//�ҳ�û��Ͷ��Ʊ��
	if($dotavs_gamers['pollers']> $dotavs['maxgamers']/2+1){//���ȫͶƱ��
		if($redscore > $bluescore){
			$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND side ='1'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "20"), false, '', 0, '');
			}
		}//���췽ÿ�˼�20dotaֵ
		if($redscore < $bluescore){
			$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid' AND side ='2'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "20"), false, '', 0, '');
			}
		}//������ÿ�˼�20dotaֵ
		if($redscore == $bluescore){
						$query = DB::query("SELECT uid FROM ".DB::table('dotavs_gamers')." WHERE tid='$tid'" );
			while($fetch = DB::fetch($query)) { 
				$array[] = $fetch;
			}
			foreach ($array as $k => $v) { 
				updatemembercount($v['uid'], array('extcredits5' => "-20"), false, '', 0, '');
			}
		}//˫������20dotaֵ
		DB::query("UPDATE ".DB::table('dotavs')." SET status = '3' WHERE tid='$tid'");//���Ļ״̬
		showmessage("ͶƱ�ɹ�����Ϸ������<br>ˢ��ҳ����ʾ���");
	}
}
?>