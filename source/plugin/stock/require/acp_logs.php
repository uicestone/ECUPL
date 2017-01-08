<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-09-22
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
!defined('IN_DISCUZ') && exit('Access Denied');
class Logs
{
	public function getLogList()
	{
		global $baseScript, $_G;
		$page = $_G['gp_page'];
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_smlog'));
		$readperpage = 30;
		if ( $cnt > 0 )
		{
			if ( $page <= 1 )
			{
				$page = 1;
				$start = 0;
			}
			$numofpage = ceil($cnt/$readperpage);
			if ( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$pages = foxpage($page,$numofpage,"?$baseScript&mod=logs&");
			$logdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_smlog')." ORDER BY timestamp DESC LIMIT $start, $readperpage");
			while ( $rslog = DB::fetch($query) )
			{
				$rslog['timestamp'] = dgmdate($rslog['timestamp']);
				$logdb[] = $rslog;
			}
		}
		return array($logdb, $cnt, $readperpage, $pages);
	}
	public function deleteLogs()
	{
		global $baseScript, $_G;
		$lid	= $_G['gp_lid'];
		$value	= $_G['gp_value'];
		$ttlnum = count($lid);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $lid as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfsm_smlog')." WHERE id IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('��־����', '{$_G[username]}', 'ɾ��ϵͳ��־ {$ttlnum} ��', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=logs';
		cpmsg("�ѳɹ�ɾ�� {$ttlnum} ��ϵͳ��־��", $baseScript, 'succeed');
	}
}
?>
