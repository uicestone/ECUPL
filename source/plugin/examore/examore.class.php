<?php
/////////////////////////////////////
//        Examore����è���        //
//��ǰ�汾��1.3.110711             //
//�ٷ���վ��www.examore.com        //
//��Ȩ����(c)2009-2011, Examore.com//
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
$examtime = (empty($timestamp)?TIMESTAMP:$timestamp) + 0*60;//�Ӻź���Ϊʱ�����ڲ�����0Ϊ����
$subject=array('1'=>'����Ա','6'=>'����','9'=>'����һ��','13'=>'����');
?>