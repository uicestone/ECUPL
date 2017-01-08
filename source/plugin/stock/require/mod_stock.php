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
class Stock
{
	public function __construct( $action )
	{
		$this->processAction( $action );
	}
	private function processAction( $action )
	{
		global $kfsclass, $_G;
		$actArray = array('call', 'showinfo', 'quote', 'search');
		if ( empty($action) || !in_array($action, $actArray) )
			showmessage('Messages from Kilofox StockIns: Invalid action');
		switch ( $action )
		{
			case 'quote':
				$this->showStockQuotation();
			break;
			case 'showinfo':
				$this->showStockInfo($_G['gp_sid']);
			break;
			case 'search':
				$this->searchStock($_G['gp_sid']);
			case 'call':
				continue;
			break;
		}
	}
	private function showStockQuotation()
	{
		global $baseScript, $_G, $db_smname, $db_marketpp;
		$page = $_G['gp_page'];
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_stock')." WHERE state<>4");
		$cnt = $cnt<=0 ? 1 : $cnt;
		$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
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
		$pages = foxpage($page,$numofpage,"$baseScript&mod=stock&act=quote&");
		list($stockdb, $nextby) = $this->getStocks($_G['gp_by'], $start, $readperpage);
		include template('stock:stock_quote');
	}
	private function getStocks($orderby, $start, $readperpage)
	{
		global $baseScript, $db_ss, $db_stop, $db_stoprise, $db_stopfall;
		switch ($orderby)
		{
			case 'sid_a':
				$by = 'sid';
				$nextby = '_d';
			break;
			case 'sid_d':
				$by = 'sid DESC';
				$nextby = '_a';
			break;
			case 'openprice_a':
				$by = 'openprice';
				$nextby = '_d';
			break;
			case 'openprice_d':
				$by = 'openprice DESC';
				$nextby = '_a';
			break;
			case 'currprice_a':
				$by = 'currprice';
				$nextby = '_d';
			break;
			case 'currprice_d':
				$by = 'currprice DESC';
				$nextby = '_a';
			break;
			case 'todaywave_a':
				$by = "todaywave";
				$nextby = '_d';
			break;
			case 'todaywave_d':
				$by = 'todaywave DESC';
				$nextby = '_a';
			break;
			case 'todaytradenum_a':
				$by = 'todaytradenum';
				$nextby = '_d';
			break;
			case 'todaytradenum_d':
				$by = 'todaytradenum DESC';
				$nextby = '_a';
			break;
			default:
				$by = $db_ss;
				$nextby = '_d';
			break;
		}
		$query = DB::query("SELECT * FROM ".DB::table('kfsm_stock')." WHERE state<>4 ORDER BY $by LIMIT $start, $readperpage");
		$stocks = array();
		while ( $rs = DB::fetch($query) )
		{
			if ( $rs['holder_id'] > 0 )
				$rs['holder_name'] = "<a href=\"$baseScript&mod=member&act=showinfo&uid=$rs[holder_id]\">$rs[holder_name]</a>";
			else
				$rs['holder_name'] = '-';
			if ( $rs['openprice'] < 1 )
				$rs['color'] = "#f50";
			else if ( $rs['openprice'] == $rs['currprice'] )
				$rs['color'] = "";
			else if ( $rs['openprice'] < $rs['currprice'] )
				$rs['color'] = "#FF0000";
			else if ( $rs['openprice'] > $rs['currprice'] )
				$rs['color'] = "#008000";
			if ( $rs['state'] == 0 )
				$rs['state'] = '����';
			else if ( $rs['state'] == 1 )
				$rs['state'] = '<span style="color:#f50">ͣ��</span>';
			else if ( $rs['state'] == 2 )
				$rs['state'] = '<span style="color:#FF0000">��ͣ</span>';
			else if ( $rs['state'] == 3 )
				$rs['state'] = '<span style="color:#008000">��ͣ</span>';
			else
				$rs['state'] = '<span style="color:#0000FF">�쳣</span>';
			$rs['openprice'] = number_format($rs['openprice'],2);
			$rs['currprice'] = number_format($rs['currprice'],2);
			$rs['todaywave'] = number_format($rs['todaywave'],2)." %";
			$stocks[] = $rs;
		}
		return array($stocks,$nextby);
	}
	private function showStockInfo( $stock_id )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_klcolor, $db_otherpp;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		if ( !$rs )
		{
			showmessage('û���ҵ�ָ�������й�˾�����ܸ����й�˾�Ѿ�����');
		}
		else
		{
			if ( $rs['state'] == 4 )
			{
				require_once 'mod_member.php';
				$user = new Member($_G['uid'], 'subscribe');
				$user->processAction('subscribe');
				exit;
			}
			$totalnum = DB::result_first("SELECT SUM(stocknum_ava) FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
			if ( $rs['issuer_id'] )
				$issuer = "<a href=\"$baseScript&mod=member&act=showinfo&uid={$rs['issuer_id']}\">{$rs['issuer_name']}</a>";
			else
				$issuer = '-';
			if ( $rs['holder_id'] )
				$holder = "<a href=\"$baseScript&mod=member&act=showinfo&uid={$rs['holder_id']}\">{$rs['holder_name']}</a>";
			else
				$holder = '-';
			$currentprice = $rs['currprice'];
			$rs['issuetime']	= dgmdate($rs['issuetime']);
			$rs['openprice']	= number_format($rs['openprice'], 2);
			$rs['currprice']	= number_format($rs['currprice'], 2);
			$rs['todaypoint']	= number_format($rs['currprice'] - $rs['openprice'], 2);
			$rs['totalpoint']	= number_format($rs['currprice'] - $rs['openprice'], 2);
			$rs['todaywave']	= number_format($rs['todaywave'], 2);
			$rs['totalwave']	= number_format($rs['totalwave'], 2);
			$rs['highprice']	= number_format($rs['highprice'], 2);
			$rs['lowprice']		= number_format($rs['lowprice'], 2);
			if ( $rs['state'] == 0 )
			{
				$state_show		= '������˾��Ƭ�·��İ�ť���й�Ʊ����';
				$btn_disabled	= '';
			}
			else
			{
				$state_show		= '<span style="color:#FF0000">��Ʊ״̬�쳣��Ŀǰ�޷�����</span>';
				$btn_disabled	= 'disabled';
			}
			$last_hour		= dgmdate($rs['uptime'],'G');
			$current_hour	= dgmdate($_G['timestamp'],'G');
			if ( $last_hour <> $current_hour )
			{
				$this->updateTSP($rs['sid'], $rs['currprice']);
				$s = explode('|', $rs['pricedata']);
				require_once 'chart.php';
			}
			$usernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
			$topdb = array();
			if ( $usernum > 0 )
			{
				$query = DB::query("SELECT cid, username, stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' ORDER BY stocknum_ava DESC LIMIT 0,5");
				while ( $rt = DB::fetch($query) )
				{
					$rt['proportion']	= round($rt['stocknum_ava']/$totalnum,4);
					$rt['percent']		= $rt['proportion'] * 100;
					$rt['imgwidth']		= intval($rt['proportion']*500) + 5;
					$topdb[] = $rt;
				}
			}
			$cnt = $usernum;
			if ( $cnt > 0 )
			{
				$readperpage = is_numeric($db_otherpp) && $db_otherpp > 0 ? $db_otherpp : 20;
				$page = $_G['gp_page'];
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
				$pages = foxpage($page,$numofpage,"$baseScript&mod=stock&act=showinfo&sid=$stock_id&");
				$rsusdb = $this->getStockholdersList($stock_id, $currentprice, $totalnum, $start, $readperpage);
			}
		}
		include template('stock:stock_showinfo');
	}
	// �����������ֺ��õ�
	public function getStockholdersList($stock_id, $currentprice, $totalnum, $start, $readperpage)
	{
		$shldb = array();
		if ( $stock_id > 0 )
		{
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' ORDER BY stocknum_ava DESC LIMIT $start, $readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$averageprice	= $rs['averageprice'];
				$stockcost		= $averageprice * $rs['stocknum_ava'];
				$totalprofit	= ( $currentprice - $averageprice ) * $rs['stocknum_ava'];
				$rs['possessratio']	= number_format($rs['stocknum_ava']*100/$totalnum,2);
				$rs['averageprice']	= number_format($averageprice,2);
				$rs['currentprice']	= number_format($currentprice,2);
				$rs['stockcost']	= number_format($stockcost,2);
				$rs['stockvalue']	= number_format($currentprice*$rs['stocknum_ava'],2);
				$rs['totalprofit']	= number_format($totalprofit,2);
				$rs['profit_p']		= number_format($totalprofit/$stockcost*100,2);
				if ( $totalprofit > 0 )
					$rs['color'] = '#FF0000';
				else if ( $totalprofit < 0 )
					$rs['color'] = '#008000';
				else if ( $totalprofit == 0 )
					$rs['color'] = '#000000';
				$shldb[] = $rs;
			}
		}
		return $shldb;
	}
	private function updateTSP($stock_id,$price)
	{
		global $_G, $db_klcolor;
		if ( $stock_id && $price )
		{
			$klcolor = $db_klcolor;
			$pd = DB::fetch_first("SELECT pricedata FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
			$pricedata = $pd['pricedata'];
			$pricedata = substr($pricedata, strpos($pricedata,'|') + 1) . '|' . round($price,2);
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET uptime='$_G[timestamp]', pricedata='$pricedata' WHERE sid='$stock_id'");
		}
	}
	public function getRisedTop($num=0)
	{
		$query = DB::query("SELECT sid, stockname, todaywave FROM ".DB::table('kfsm_stock')." WHERE todaywave>=0 AND state<>4 ORDER BY todaywave DESC LIMIT 0,$num");
		$order = 1;
		while ( $rs = DB::fetch($query) )
		{
			$rs['order'] = $order;
			$rs['todaywave'] = number_format($rs['todaywave'],2);
			$rtdb[] = $rs;
			$order++;
		}
		return $rtdb;
	}
	public function getFalledTop($num=0)
	{
		$query = DB::query("SELECT sid, stockname, todaywave FROM ".DB::table('kfsm_stock')." WHERE todaywave<=0 AND state<>4 ORDER BY todaywave ASC LIMIT 0,$num");
		$order = 1;
		while ( $rs = DB::fetch($query) )
		{
			$rs['order'] = $order;
			$rs['todaywave'] = number_format($rs['todaywave'],2);
			$ftdb[] = $rs;
			$order++;
		}
		return $ftdb;
	}
	private function searchStock()
	{
		global $_G, $baseScript;
		$keyword = $_G['gp_keyword'];
		if ( empty($keyword) )
		{
			showmessage('�������Ʊ������߹�Ʊ���ƹؼ���');
		}
		else
		{
			if ( is_numeric($keyword) )
				$sql = "sid='$keyword'";
			else
				$sql = "stockname LIKE '%{$keyword}%'";
			$rs = DB::fetch_first("SELECT sid FROM ".DB::table('kfsm_stock')." WHERE $sql");
			if ( !$rs )
				showmessage("û���ҵ�ָ���Ĺ�Ʊ�����ܸ����й�˾�Ѿ�����");
			else
				header("Location:$baseScript&mod=stock&act=showinfo&sid=$rs[sid]");
		}
	}
}
?>
