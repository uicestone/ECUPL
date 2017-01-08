<?php
/////////////////////////////////////
//        Examore考试猫插件        //
//当前版本：1.3.110711             //
//官方网站：www.examore.com        //
//版权所有(c)2009-2011, Examore.com//
/////////////////////////////////////
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$examore_version!='1.3.110711' && exit('Forbidden');
class examore_class{
	function create_sig($action, $uid, $ev, $apikey, $secretkey, $dt){
		$param = array('em_action='.$action, 'em_uid='.$uid, 'em_ka='.$apikey, 'em_ev='.$ev, 'em_dt='.$dt);
		$url = join('&',$param).'&em_sig='.md5(join('',$param).$secretkey);
		return $url;
	}
	function check_sig($action, $uid, $ev, $apikey, $secretkey, $dt, $sig){
		$param = array('em_action='.$action, 'em_uid='.$uid, 'em_ka='.$apikey, 'em_ev='.$ev, 'em_dt='.$dt);
		return $sig==md5(join('',$param).$secretkey);
	}
	function check_time($action, $dt, $time){
		return $action=='emresult'?($time-$dt)<180*60:($time-$dt)<1*60;
	}
}
$examv = "exam1.3";
$examore_charset = "gbk";
$emclass = new examore_class();
$examtime = (empty($timestamp)?TIMESTAMP:$timestamp) + 0*60;//加号后面为时间差调节参数，0为分钟
$subject=array('1'=>'公务员','6'=>'外语','9'=>'轻松一刻','13'=>'驾照');
?>