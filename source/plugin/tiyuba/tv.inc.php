<?php

/*
	[DISCUZ!] tv.inc.php
	Version For Discuz X1
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$w = www; $t = tiyuba; $n = net; $h = http; $m = html; $a = api;
$tiyuba_plugin = $_G['cache']['plugin']['tiyuba'];
@include DISCUZ_ROOT.'./data/cache/plugin_tiyuba.php';

if(!$discuz_user && !$tiyuba_plugin['guest'])showmessage('group_nopermission', NULL, 'NOPERM');

if($tiyuba_plugin['close'] && $adminid != 1)showmessage($tiyuba_plugin[message]);

 
include template('tiyuba:index');
?>