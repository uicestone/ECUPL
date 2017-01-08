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
class Index
{
	public function subscribeCountDown()
	{
		global $baseScript, $_G, $db_issuedays;
		$rs = DB::fetch_first("SELECT sid, stockname, applytime FROM ".DB::table('kfsm_apply')." WHERE state='1' AND surplusnum>0 ORDER BY aid LIMIT 1");
		$leftTime = $rs['applytime'] + $db_issuedays * 86400 - $_G['timestamp'];
		if ( $leftTime > 0 )
		{
			$leftTime = intval($leftTime / 3600);
			$ret = "<a href=\"$baseScript&mod=member&act=subscribe&sid=$rs[sid]\">���� <span class=\"xi1\">$rs[stockname]</span> ��ʽ���л��� $leftTime ��Сʱ�����ڲ�����������ʱ��</a>";
		}
		else
			$ret = '��ʱû�й�˾���е���Ϣ';
		return $ret;
	}
	public function getNewStocks($num=0)
	{
		$query = DB::query("SELECT aid, sid, stockname, stockprice, stocknum, surplusnum, applytime, issuetime FROM ".DB::table('kfsm_apply')." WHERE state='1' ORDER BY aid DESC LIMIT 0,$num");
		while ( $rs = DB::fetch($query) )
		{
			$rs['stockprice']	= number_format($rs['stockprice'],2);
			$rs['applytime']	= dgmdate($rs['applytime'],'Y-m-d');
			$rs['issuetime']	= dgmdate($rs['issuetime'],'Y-m-d');
			$nsdb[] = $rs;
		}
		return $nsdb;
	}
	public function getFoxAIN()
	{
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_sminfo'));
		$ain_y = $rs['ain_y'];
		$ain_t = $rs['ain_t'];
		// Compute Kilofox Stock Market Aggregative Index Number
		$ainp = round(($ain_t-$ain_y)*100/$ain_y,3);
		$foxsm = array();
		$foxain = number_format($ain_t, 2, '.', '');
		if ( $ainp > 0 )
			$foxain .= ' [<span style="color:#FF0000">+' . number_format($ainp,2) . '%</span>]';
		else if ( $ainp < 0 )
			$foxain .= ' [<span style="color:#008000">-' . number_format(abs($ainp),2) . '%</span>]';
		else
			$foxain .= ' [' . number_format($ainp,2) . '%]';
		$sminfo['foxain']		= $foxain;
		$sminfo['bargainmoney']	= number_format($rs['todaytotal'],2);
		$sminfo['bargainnum']	= $rs['todaybuy'] + $rs['todaysell'];
		$sminfo['stampduty']	= number_format($rs['stampduty'],2);
		return $sminfo;
	}
}
?>
