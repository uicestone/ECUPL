<?php
/////////////////////////////////////
//        Examore考试猫插件        //
//当前版本：1.3.110711             //
//使用范围：Discuz! X2 GBK         //
//官方网站：www.examore.com        //
//版权所有(c)2009-2011, Examore.com//
/////////////////////////////////////
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$examore_version = "1.3.110711";
require_once('examore.class.php');

$em_sid = $_G['gp_em_sid'];
$em_eid = $_G['gp_em_eid'];
$em_page = $_G['gp_em_page'];
$em_action = $_G['gp_em_action'];
empty($em_sid) && $em_sid = 0;
empty($em_eid) && $em_eid = 0;
empty($em_page) && $em_page = 1;
$query = DB::query("SELECT e_a FROM ".DB::table('examore')." WHERE i_d IN (2,3,4)");
while($rt = DB::fetch($query)) {
	$ems[] = $rt;
}
$emid = $ems[0][e_a];
$emunit = $ems[1][e_a];
$emscore = $ems[2][e_a];
if ($emid==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emids = explode('|', $emid);
}
if ($emunit==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emunits = explode('|', $emunit);
}
if ($emscore==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emscores = explode('|', $emscore);
}

$basename = $boardurl . "plugin.php?id=" . $identifier . ":index";
$pluginname = DB::result_first("SELECT name FROM ".DB::table('common_plugin')." WHERE identifier = '{$identifier}'");
$examparam=array('examopen'=>'1','examlevel'=>'0','examcredit1'=>'1','examfee1'=>'0','examcredit2'=>'1','examfee2'=>'0','examcredit3'=>'1','examfee3'=>'0');
$examparam['examopen'] = $_G['cache']['plugin']['examore']['examopen'];
$examparam['examlevel'] = $_G['cache']['plugin']['examore']['examlevel'];
$examparam['examcredit1'] = $_G['cache']['plugin']['examore']['examcredit1'];
$examparam['examfee1'] = $_G['cache']['plugin']['examore']['examfee1'];
$examparam['examcredit2'] = $_G['cache']['plugin']['examore']['examcredit2'];
$examparam['examfee2'] = $_G['cache']['plugin']['examore']['examfee2'];
$examparam['examcredit3'] = $_G['cache']['plugin']['examore']['examcredit3'];
$examparam['examfee3'] = $_G['cache']['plugin']['examore']['examfee3'];

$examore = DB::result_first("SELECT e_a FROM ".DB::table('examore')." WHERE i_d = 1");
if($examore!="" && !is_numeric($examore)){
	$examinfo = explode("||",$examore);
	$examparam['examopen']=="0" && $examore=$timestamp;
	$examparam['examlevel']=="1" && !$_G['uid'] && showmessage('not_loggedin', NULL, array(), array('login' => 1));
	$examinfo[3]=$examparam['examopen'];
	$examinfo[4]=$examparam['examlevel'];
	if (count($examinfo)>7){
		DB::query("ALTER TABLE ".DB::table('examore')." CHANGE `e_a` `e_a` VARCHAR(500) NOT NULL DEFAULT ''"); 
		$examinfo[5] = $examinfo[7];
		$examinfo[6] = $examinfo[8];
	}else{
		$examinfo[5]=="" && $examinfo[5] = "0";
		$examinfo[6]=="" && $examinfo[6] = "0";
	}
	DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$examinfo[0]}||{$examinfo[1]}||{$examinfo[2]}||{$examinfo[3]}||{$examinfo[4]}||{$examinfo[5]}||{$examinfo[6]}' WHERE i_d = 1");
	for ($i=0; $i<count($emids); $i++){
		if ($_G[group][groupid] == $emids[$i]){
			$emgid = $i;
		}
	}
	if ($em_action == "examwrong"){
		$examunit = $emunits[$emgid*3+1];
		$examfee = $emscores[$emgid*3+1];
		$feetype = "错题挑战";
	}else if ($em_action == "examrandom"){
		$examunit = $emunits[$emgid*3+2];
		$examfee = $emscores[$emgid*3+2];
		$feetype = "随机抽题";
	}else{
		$examunit = $emunits[$emgid*3];
		$examfee = $emscores[$emgid*3];
		$feetype = "正常考试";
	}
}
$usrmoney = getuserprofile('extcredits'.$examunit);

$examenum = $emclass->create_sig('examenum', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examqnum = $emclass->create_sig('examqnum', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examlist = $emclass->create_sig('examlist', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examlog = $emclass->create_sig('examlog', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examwrong = $emclass->create_sig('examwrong', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examrandom = $emclass->create_sig('examrandom', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examtop = $emclass->create_sig('examtop', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examreset = $emclass->create_sig('examreset', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
$examdo = $emclass->create_sig('', $_G['uid'], $examore_version, $examinfo[0], $examinfo[1], $examtime);
if ($em_action == "exampay"){
	if ($usrmoney >= $examfee){
		batchupdatecredit(array(('extcredits'.$examunit)=>$examfee), $_G['uid'],NULL,-1);
		$examinfo = explode("||",$examore);
		$examinfo[5]++;
		$examinfo[6]+=$examfee;
		DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$examinfo[0]}||{$examinfo[1]}||{$examinfo[2]}||{$examinfo[3]}||{$examinfo[4]}||{$examinfo[5]}||{$examinfo[6]}' WHERE i_d = 1");
	}else{
		showmessage("对不起！<br/><span style='text-decoration:underline;'>您只有<span style='color:red; font-weight:bold;'>".$extcredits[$examunit][title].$_G['extcredits'.$examunit].$extcredits[$examunit][unit]."</span>无法进行考试！</span>");
	}
}

include template('examore:index');
?>