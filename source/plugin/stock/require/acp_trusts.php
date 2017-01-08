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
class Trusts
{
	public function getDealList()
	{
		global $db;
		$i = 0;
		$qd = DB::query("SELECT d.*, u.uid, u.username FROM ".DB::table('kfsm_deal')." d LEFT JOIN ".DB::table('kfsm_user')." u ON d.uid=u.uid ORDER BY d.did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			$i++;
			$rsd['no'] = $i;
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">����</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal'] = dgmdate($rsd['time_deal'],'Y-m-j H:i:s');
			else
				$rsd['time_deal'] = '-';
			if ( $rsd['time_tran'] )
				$rsd['time_tran'] = dgmdate($rsd['time_tran'],'Y-m-j H:i:s');
			else
				$rsd['time_tran'] = '-';
			if ( $rsd['ok'] == 0 )
				$rsd['ok'] = 'δ�ɽ�';
			else if ( $rsd['ok'] == 1 )
				$rsd['ok'] = '<span style="color:#008000">�ɽ�</span>';
			else if ( $rsd['ok'] == 2 )
				$rsd['ok'] = '<span style="color:#FFA500">���ֳɽ�</span>';
			else if ( $rsd['ok'] == 3 )
				$rsd['ok'] = '<span style="color:#0000FF">�û�����</span>';
			else if ( $rsd['ok'] == 4 )
				$rsd['ok'] = '<span style="color:#A52A2A">ϵͳ����</span>';
			else
				$rsd['ok'] = '<span style="color:#FF0000">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			$ddb[] = $rsd;
		}
		return $ddb;
	}
	public function getTranList()
	{
		global $db;
		$i = 0;
		$qt = DB::query("SELECT t.*, u.uid, u.username FROM ".DB::table('kfsm_transaction')." t LEFT JOIN ".DB::table('kfsm_user')." u ON t.uid=u.uid ORDER BY t.tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			$i++;
			$rst['no'] = $i;
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">����</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">�쳣</span> <a href="http://www.kilofox.net" target="_blank">����</a>';
			if ( $rst['ttime'] )
				$rst['ttime'] = dgmdate($rst['ttime'],'Y-m-j H:i:s');
			else
				$rst['ttime'] = '-';
			$tdb[] = $rst;
		}
		return $tdb;
	}
	public function deleteDeals()
	{
		global $baseScript, $_G;
		$did	= $_G['gp_did'];
		$value	= $_G['gp_value'];
		$ttlnum = count($did);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $did as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfsm_deal')." WHERE did IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('ί�м�¼����', '{$_G[username]}', 'ɾ��ί�м�¼ {$ttlnum} ��', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("�ѳɹ�ɾ�� {$ttlnum} ��ί�м�¼��", $baseScript, 'succeed');
	}
	public function deleteTrans()
	{
		global $baseScript, $_G;
		$tid	= $_G['gp_tid'];
		$value	= $_G['gp_value'];
		$ttlnum = count($tid);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $tid as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfsm_transaction')." WHERE tid IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('�ɽ���¼����', '{$_G[username]}', 'ɾ���ɽ���¼ {$ttlnum} ��', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("�ѳɹ�ɾ�� {$ttlnum} ���ɽ���¼��", $baseScript, 'succeed');
	}
}
?>
