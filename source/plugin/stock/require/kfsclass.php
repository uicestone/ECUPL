<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-09-23
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
!defined('IN_DISCUZ') && exit('Access Denied');
class kfsclass
{
	public $version	= '9.5.4';
	public $build_date = '2011-09-23';
	public $website = '<a href="http://www.kilofox.net" target="_blank">www.Kilofox.Net</a>';
	public function auto_run()
	{
		global $_G;
		if ( $_G['adminid'] <> '1' ) $this->checkMarketState();
		$td			= DB::fetch_first("SELECT todaydate FROM ".DB::table('kfsm_sminfo'));
		$lastDay	= dgmdate($td['todaydate'], 'd');
		$currDay	= dgmdate($_G['timestamp'], 'd');
		$lastDay <> $currDay && $this->kfsm_reset();
	}
	private function checkMarketState()
	{
		global $_G, $db_smifopen, $db_whysmclose, $db_guestview, $db_smiftime, $db_smtimer11, $db_smtimer11, $db_smtimer12, $db_smtimer21, $db_smtimer22, $db_smtimer31, $db_smtimer32, $db_smtimer41, $db_smtimer42;
		if ( $db_smifopen == '0' )
		{
			if ( $db_guestview<>'1' && !$_G['uid'] )
			{
				showmessage('�Բ��𣬹���δ���οͿ��ţ������ȵ�¼��̳��');
			}
			else
			{
				if ( $db_smiftime )
				{
					$t_h = dgmdate($_G['timestamp'],'G');
					if ( ( $t_h < $db_smtimer11 || $t_h >= $db_smtimer12 ) && ( $t_h < $db_smtimer21 || $t_h >= $db_smtimer22 ) && ( $t_h< $db_smtimer31 || $t_h >= $db_smtimer32 ) && ( $t_h < $db_smtimer41 || $t_h >= $db_smtimer42 ) )
						showmessage("�װ��Ĺ������ã������еĽ���ʱ��Ϊ��<br/>{$db_smtimer11}:00 - {$db_smtimer12}:00<br />{$db_smtimer21}:00 - {$db_smtimer22}:00<br />{$db_smtimer31}:00 - {$db_smtimer32}:00<br />{$db_smtimer41}:00 - {$db_smtimer42}:00<br />��������ʱ�ι��٣�лл������");
					else
						return true;
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			showmessage($db_whysmclose);
		}
	}
	public function calculatefund($user_id,$stock_id)
	{
		if ( is_numeric($user_id) )
		{
			if ( $user_id > 0 )
				$query = DB::query("SELECT uid FROM ".DB::table('kfsm_user')." WHERE uid='$user_id'");
			else
				$query = DB::query("SELECT uid FROM ".DB::table('kfsm_user')." ORDER BY uid");
			while ( $rsuser = DB::fetch($query) )
			{
				$mystnum	= 0;
				$mystcost	= 0;
				$mystvalue	= 0;
				$totalfund	= 0;
				$stockkinds	= 0;
				$query = DB::query("SELECT c.*,s.currprice FROM ".DB::table('kfsm_customer')." c INNER JOIN ".DB::table('kfsm_stock')." s ON c.sid=s.sid WHERE c.cid='$rsuser[uid]'");
				while ( $rsst = DB::fetch($query) )
				{
					$mystnum	+= $rsst['stocknum_ava'];
					$mystcost	+= $rsst['stocknum_ava'] * $rsst['averageprice'];
					$mystvalue	+= $rsst['stocknum_ava'] * $rsst['currprice'];
					$stockkinds++;
				}
				DB::query("UPDATE ".DB::table('kfsm_user')." SET asset=fund_ava+{$mystvalue}, stocksort='{$stockkinds}', stocknum='{$mystnum}', stockcost='{$mystcost}', stockvalue='{$mystvalue}' WHERE uid='$rsuser[uid]'");
			}
		}
		if ( $stock_id )
		{
			$rsst = DB::fetch_first("SELECT currprice, lowprice, highprice FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
			if ( $rsst )
			{
				if ( $rsst['currprice'] > $rsst['highprice'] )
					$newstockpricea = 'highprice=currprice';
				else
					$newstockpricea = 'highprice=highprice';
				if ( $rsst['currprice'] < $rsst['lowprice'] )
					$newstockpriceb = 'lowprice=currprice';
				else
					$newstockpriceb = 'lowprice=lowprice';
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET $newstockpricea, $newstockpriceb WHERE sid='$stock_id'");
			}
		}
	}
	public function kfsm_reset()
	{
		global $_G;
		loadcache('plugin');
		$db_trustlog	= $_G['cache']['plugin']['stock']['trustlog'];
		$db_tradecharge	= $_G['cache']['plugin']['stock']['tradecharge'];
		$db_stampduty	= $_G['cache']['plugin']['stock']['stampduty'];
		DB::query("UPDATE ".DB::table('kfsm_user')." SET todaybuy='0', todaysell='0'");
		$query = DB::query("SELECT * FROM ".DB::table('kfsm_deal')." WHERE ok='0' OR ok='2'");
		while ( $tbrs = DB::fetch($query) )
		{
			if ( $tbrs['direction'] == 1 )
			{
				if ( $tbrs['ok'] == 0 || $tbrs['ok'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+fund_war, fund_war=0 WHERE uid='{$tbrs['uid']}'");
				}
			}
			else if ( $tbrs['direction'] == 2 )
			{
				if ( $tbrs['ok'] == 0 || $tbrs['ok'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+stocknum_war, stocknum_war=0 WHERE uid='{$tbrs['uid']}' AND sid='{$tbrs['sid']}'");
				}
			}
		}
		DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='4', hide='1' WHERE hide='0'");
		$trustLogNum = is_numeric($db_trustlog) && $db_trustlog > 0 ? $db_trustlog*86400 : 2592000;
		DB::query("DELETE FROM ".DB::table('kfsm_deal')." WHERE time_deal < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfsm_transaction')." WHERE ttime < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfsm_customer')." WHERE stocknum_ava=0 AND stocknum_war=0");
		DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy='0', todaysell='0', todaytotal='0', todaydate='$_G[timestamp]', ain_y=ain_t");
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET state='1' WHERE openprice<=1");
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET state='0' WHERE state='2' OR state='3'");
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET openprice=currprice, todaytradenum='0', todaybuynum='0', todaysellnum='0', todaywave='0' WHERE todaytradenum>0");
		$this->checkNewStock();
	}
	private function checkNewStock()
	{
		global $baseScript, $_G;
		loadcache('plugin');
		$db_issuedays	= $_G['cache']['plugin']['stock']['issuedays'];
		$query = DB::query("SELECT aid, sid, stockname, userid, stockprice, stocknum, surplusnum, capitalisation, issuetime FROM ".DB::table('kfsm_apply')." WHERE state='1'");
		while ( $aprs = DB::fetch($query) )
		{
			$db_issuedays = is_numeric($db_issuedays) && $db_issuedays>0 ? $db_issuedays*86400 : 259200;
			if ( $_G['timestamp'] + $aprs['issuetime'] > $db_issuedays )
			{
				$pricedata = "$aprs[stockprice]";
				$i = 0;
				do
				{
					$pricedata .= "|$aprs[stockprice]";
					$i++;
				}
				while ( $i < 23 );
				$issue_price = round($aprs['capitalisation'] / $aprs['stocknum'], 2);
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET openprice='$issue_price', currprice='$issue_price', lowprice='$issue_price', highprice='$issue_price', issueprice='$issue_price', issuetime='$_G[timestamp]', pricedata='$pricedata', state='0' WHERE sid='{$aprs['sid']}'");
				DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$aprs[surplusnum]}, buytime='{$_G[timestamp]}' WHERE cid='{$aprs[userid]}' AND sid='{$aprs['sid']}'");
				$subject = "�¹� $aprs[stockname] ������ʽ����";
				$content = "������ [url=plugin.php?id=stock:index&mod=stock&act=showinfo&sid=$aprs[sid]]{$aprs['stockname']}[/url] ��ʽ���еĵ�һ�죬���й�����ɰ��չ��й������ɽ��ס�";
				DB::query("INSERT INTO ".DB::table('kfsm_news')." (subject, content, color, author, addtime) VALUES('$subject', '$content', '', 'StockIns', '{$_G[timestamp]}')");
				DB::query("UPDATE ".DB::table('kfsm_apply')." SET state='3' WHERE aid='{$aprs['aid']}'");
				$s = explode('|', $pricedata);
				$stock_id = $aprs['sid'];
				include_once 'chart.php';
				$this->calculatefund($aprs['userid'],0);
				$this->resetcid();
			}
		}
	}
	public function resetcid()
	{
		$query = DB::query("SELECT sid FROM ".DB::table('kfsm_stock')." ORDER BY sid");
		$cid = 1;
		while ( $rs = DB::fetch($query) )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET cid='$cid' WHERE sid='$rs[sid]'");
			$cid++;
		}
	}
}
function foxpage( $page, $numofpage, $url )
{
	if ( $numofpage <= 1 || !is_numeric($page) )
	{
		return '';
	}
	else
	{
		$pages = "<div class=\"pg\">";
		if ( $page <> 1 )
		{
			$pages.="<a href=\"{$url}page=1\" class=\"prev\">&lsaquo;&lsaquo;</a>";
		}
		for ( $i=$page-3; $i<=$page-1; $i++ )
		{
			if ( $i < 1 ) continue;
			$pages.="<a href=\"{$url}page=$i\">$i</a>";
		}
		$pages.="<strong>$page</strong>";
		if ( $page < $numofpage )
		{
			$flag = 0;
			for ( $i=$page+1; $i<=$numofpage; $i++ )
			{
				$pages.="<a href=\"{$url}page=$i\">$i</a>";
				$flag++;
				if ( $flag == 6 ) break;
			}
		}
		$pages.="<a href=\"{$url}page=$numofpage\" class=\"nxt\">&rsaquo;&rsaquo;</a></div>";
		return $pages;
	}
}
?>
