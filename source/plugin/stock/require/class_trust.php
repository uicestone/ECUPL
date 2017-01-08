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
class Trust
{
	public function __construct( $member, $section )
	{
		$this->process( $member, $section );
	}
	private function process( $user, $section )
	{
		if ( empty($section) )
			$this->showMyDeals( $user );
		else if ( $section == 'tran' )
			$this->showMyTrans( $user );
		else if ( $section == 'trade' )
			$this->stockTrade( $user );
		else if ( $section == 'canceltt' )
		{
			global $_G;
			$this->cancelDeal( $user, $_G['gp_did'] );
		}
	}
	private function stockTrade( $user )
	{
		global $kfsclass, $_G, $db_usertrade;
		if ( $db_usertrade == '1' )
		{
			if ( $_G['gp_sid'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_sid'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else if ( $_G['gp_sid'] && $_G['gp_tradetype'] == 's' )
				$this->sellStock($user, $_G['gp_sid'], $_G['gp_price_sell'], $_G['gp_num_sell']);
			else
				$this->showTradeForm($user, $_G['gp_sid']);
		}
		else
			showmessage('��ʱֹͣ���ף������Ժ�����');
	}
	private function showTradeForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_wavemax, $db_dutyrate, $db_dutymin, $db_tradenummin;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$openprice	= $rs['openprice'] > 0 ? $rs['openprice'] : 0;
			$currprice	= $rs['currprice'] > 0 ? $rs['currprice'] : 0;
			$dutyRate	= $db_dutyrate > 0 ? $db_dutyrate : 0;
			$dutyMin	= $db_dutymin > 0 ? $db_dutymin : 0;
			$buyMin = $sellMin = is_numeric($db_tradenummin) && $db_tradenummin > 0 ? $db_tradenummin : 0;
			$buyMax = intval( $user['fund_ava'] / ( $openprice * ( 1 + $dutyRate / 100 ) ) );
			$buyMax = $buyMax > $rs['issuenum'] ? $rs['issuenum'] : $buyMax;
			if ( $buyMin > $buyMax )
			{
				$buyMinInit	= $buyMin;
				$buyMin		= $buyMax;
				$buyMax		= $buyMinInit;
			}
			$possessnum	= DB::result_first("SELECT stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE uid='{$user['id']}' AND sid='$stock_id'");
			$sellMin = $possessnum < $sellMin ? (int)$possessnum : $sellMin;
			$sellMax = $possessnum > 0 ? $possessnum : 0;
			if ( $sellMin > $sellMax )
			{
				$sellMinInit	= $sellMin;
				$sellMin		= $sellMax;
				$sellMax		= $sellMinInit;
			}
			if ( $_G['timestamp'] - $rs['issuetime'] < 86400 )
			{
				$priceLow	= 1;
				$priceHigh	= 999.99;
			}
			else
			{
				$priceLow	= $openprice * ( 1 - $db_wavemax / 100 );
				$priceHigh	= $openprice * ( 1 + $db_wavemax / 100 );
			}
			$priceLow >= 1000 && $priceLow		= 999.99;
			$priceHigh >= 1000 && $priceHigh	= 999.99;
			$priceLow	= number_format($priceLow,2);
			$priceHigh	= number_format($priceHigh,2);
			$dutyRate	= number_format($dutyRate,2);
			$dutyMin	= number_format($dutyMin,2);
			$currprice	= number_format($currprice,2);
			if ( $buyMax <= 0 )
				$btn_buy = 'disabled';
			else
				$btn_buy = '';
			if ( $sellMax <= 0 )
				$btn_sell = 'disabled';
			else
				$btn_sell = '';
			$dsdb = $this->getDealSell($stock_id, $openprice);
			$dbdb = $this->getDealBuy($stock_id, $openprice);
			include template('stock:member_trade');
		}
		else
		{
			showmessage('�ù�Ʊ״̬�쳣����ʱ�޷����ף�');
		}
	}
	private function getDealSell($sid, $openprice)
	{
		$i = 1;
		$qds = DB::query("SELECT price_deal, SUM(quant_deal-quant_tran) AS num FROM ".DB::table('kfsm_deal')." WHERE sid='$sid' AND direction='2' AND ( ok='O' OR ok='2' ) AND hide='0' AND quant_deal-quant_tran>0 GROUP BY price_deal ORDER BY price_deal LIMIT 5");
		while ( $rsds = DB::fetch($qds) )
		{
			if ( $i == 1 )
				$rsds['i'] = '��һ';
			else if ( $i == 2 )
				$rsds['i'] = '����';
			else if ( $i == 3 )
				$rsds['i'] = '����';
			else if ( $i == 4 )
				$rsds['i'] = '����';
			else if ( $i == 5 )
				$rsds['i'] = '����';
			if ( $rsds['price_deal'] > $openprice )
				$rsds['color'] = 'ff0000';
			else if ( $rsds['price_deal'] < $openprice )
				$rsds['color'] = '008000';
			else
				$rsds['color'] = '000000';
			$i++;
			$dsdb[] = $rsds;
		}
		$dsdbR = array();
		$i = count($dsdb)-1;
		foreach( $dsdb as $v )
		{
			$dsdbR[] = $dsdb[$i];
			$i--;
		}
		return $dsdbR;
	}
	private function getDealBuy($sid, $openprice)
	{
		$i = 1;
		$qdb = DB::query("SELECT price_deal, SUM(quant_deal-quant_tran) AS num FROM ".DB::table('kfsm_deal')." WHERE sid='$sid' AND direction='1' AND ( ok='O' OR ok='2' ) AND hide='0' AND quant_deal-quant_tran>0 GROUP BY price_deal ORDER BY price_deal DESC LIMIT 5");
		while ( $rsdb = DB::fetch($qdb) )
		{
			if ( $i == 1 )
				$rsdb['i'] = '��һ';
			else if ( $i == 2 )
				$rsdb['i'] = '���';
			else if ( $i == 3 )
				$rsdb['i'] = '����';
			else if ( $i == 4 )
				$rsdb['i'] = '����';
			else if ( $i == 5 )
				$rsdb['i'] = '����';
			if ( $rsds['price_deal'] > $openprice )
				$rsds['color'] = 'ff0000';
			else if ( $rsds['price_deal'] < $openprice )
				$rsds['color'] = '008000';
			else
				$rsds['color'] = '000000';
			$i++;
			$dbdb[] = $rsdb;
		}
		return $dbdb;
	}
	private function checkStock( $stock_id=0 )
	{
		$rs = DB::fetch_first("SELECT sid, stockname, openprice, currprice, issueprice, issuenum, issuer_id, issuetime, state FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		if ( !$rs )
		{
			showmessage('û���ҵ�ָ���Ĺ�Ʊ�����ܸ����й�˾�Ѿ�����');
		}
		else
		{
			$rs['state'] <> 0 && showmessage('�ù�Ʊ�쳣���޷�����');
		}
		return $rs;
	}
	private function buyStock( $user, $stock_id=0, $buyPrice=0, $buyNum=0 )
	{
		global $baseScript, $_G, $kfsclass, $db_tradenummin, $db_wavemax, $db_dutyrate, $db_tradedelay, $db_dutymin, $db_iplimit;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$buytime = DB::result_first("SELECT MAX(time_deal) FROM ".DB::table('kfsm_deal')." WHERE uid='{$user['id']}' AND sid='$stock_id' AND direction='1'");
			if ( is_numeric($db_tradedelay) && $db_tradedelay > 0 && $_G['timestamp'] - $buytime < $db_tradedelay * 60 )
			{
				$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$buytime)/60 );
				showmessage("�������ƣ��������ٴ�����ù�Ʊ���� $timedelay ���ӣ�");
			}
			if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
			{
				$ipq = "SELECT buytime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND buytime>{$_G['timestamp']}-$db_iplimit*60";
				$sameIp = false;
				while( $rsip = DB::fetch($ipq) )
				{
					$rsip['ip'] == $user['ip'] && $sameIp = true;
				}
				if ( $sameIp )
				{
					$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['buytime'])/60 );
					Showmsg("�������ƣ�ͬһIP�û�����ͬһ��Ʊ���� $timedelay ���ӣ�");
				}
			}
			if ( !is_numeric($buyPrice) || $buyPrice < 1 ) showmessage('����ȷ��������۸�');
			if ( !is_numeric($buyNum) || $buyNum < 1 ) showmessage('����ȷ������������');
			$buyNum = (int)$buyNum;
			if ( is_numeric($db_tradenummin) && $db_tradenummin > 0 && $buyNum < $db_tradenummin )
				showmessage("�����й涨��ÿ�����ٽ�����Ϊ $db_tradenummin �ɣ�");
			$needMoney	= $buyPrice * $buyNum;
			$needFees	= $needMoney * $db_dutyrate / 100;
			$needFees	= $needFees >= $db_dutymin ? $needFees : $db_dutymin;
			if ( $user['fund_ava'] < $needMoney + $needFees )
				showmessage('�����ʻ���û���㹻���ʽ����������Ʊ');
			else
			{
				DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-$needMoney-$needFees, fund_war=fund_war+$needMoney+$needFees WHERE uid='{$user['id']}'");
				$dealData = array(
					'uid'		=> $user['id'],
					'username'	=> $user['username'],
					'sid'		=> $stock_id,
					'direction'	=> '1',
					'quant_deal'=> $buyNum,
					'price_deal'=> $buyPrice,
					'time_deal'	=> $_G['timestamp'],
					'ok'		=> '0'
				);
				$newdid = DB::insert('kfsm_deal', $dealData, true);
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_deal')." WHERE sid='$stock_id' AND direction='2' AND ( ok='0' OR ok='2' ) AND uid<>$user[id] AND hide='0' ORDER BY price_deal");
				while ( $dsrs = DB::fetch($query) )
				{
					$quant = $dsrs['quant_deal'] - $dsrs['quant_tran'];
					if ( $dsrs['price_deal'] <= $buyPrice && $quant > 0 && $sellNum > 0 )
					{
						if ( $quant >= $buyNum )
						{
							$worth		= $dsrs['price_deal'] * $buyNum;
							$stampduty	= $worth * $db_dutyrate / 100;
							$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
							// �򷽹�Ʊ��������
							$rsc = DB::fetch_first("SELECT cid, stocknum_ava, averageprice FROM ".DB::table('kfsm_customer')." WHERE uid='{$user['id']}' AND sid='$stock_id'");
							if ( !$rsc )
							{
								$this->changeoptb($stock_id,$user['id'],$user['username'],$buyNum);
								$priceCost = round( ($worth+$stampduty)/$buyNum, 2 );
								$psData = array(
									'uid'			=> $user['id'],
									'username'		=> $user['username'],
									'sid'			=> $stock_id,
									'buyprice'		=> $dsrs['price_deal'],
									'averageprice'	=> $priceCost,
									'stocknum_ava'	=> $buyNum,
									'buytime'		=> $_G['timestamp'],
									'ip'			=> $user['ip']
								);
								DB::insert('kfsm_customer', $psData);
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_war=fund_war-{$worth}-{$stampduty}, stocksort=stocksort+1, todaybuy=todaybuy+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
							}
							else
							{
								$leftNum = $rsc['stocknum_ava'];
								$this->changeoptb($stock_id, $user['id'], $user['username'], intval($buyNum+$leftNum));
								$priceCost = round( ( $worth + $rsc['averageprice']*$leftNum + $stampduty ) / ( $buyNum + $leftNum ), 2 );
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$buyNum}, buyprice='{$dsrs['price_deal']}', averageprice='$priceCost', buytime='{$_G[timestamp]}', ip='{$user['ip']}' WHERE cid='{$rsc['cid']}'");
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_war=fund_war-{$worth}-{$stampduty}, todaybuy=todaybuy+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
							}
							// �����ʽ𡢽�����������
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$worth}, todaysell=todaysell+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dsrs[uid]'");
							// ������Ʊ��������
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_war=stocknum_war-$buyNum, selltime='{$_G[timestamp]}' WHERE uid='{$dsrs[uid]}' AND sid='$stock_id'");
							$kfsclass->calculatefund($user['id'], $stock_id);
							$kfsclass->calculatefund($dsrs['uid'], $stock_id);
							// �������ֳɽ����п�����ȫ�ɽ�
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$buyNum}, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='2' WHERE did='$dsrs[did]'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 2, '{$buyNum}', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='{$dsrs['did']}'");
							if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='{$dsrs['did']}'");
							// ����ȫ�ɽ�
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran='{$dsrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='$newdid'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user['id']}', '{$dsrs[sid]}', '{$rs[stockname]}', 1, '{$buyNum}', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// ���¹�Ʊ���ɽ��ۡ�
							$this->computeNewPrice( $stock_id, $dsrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
							// ���¹�Ʊ���ɽ�����
							DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$buyNum} WHERE sid='$stock_id'");
							// ���¹�����Ϣ
							DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$buyNum}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
							showmessage('��Ʊ����ɹ���', "$baseScript&mod=member&act=trustsmng");
						}
						else
						{
							$worth		= $dsrs['price_deal'] * $quant;
							$stampduty	= $worth * $db_dutyrate / 100;
							$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
							// �򷽹�Ʊ��������
							$rsc = DB::fetch_first("SELECT cid, stocknum_ava, averageprice FROM ".DB::table('kfsm_customer')." WHERE uid='{$user['id']}' AND sid='$stock_id'");
							if ( !$rsc )
							{
								$this->changeoptb($stock_id, $user['id'], $user['username'], $quant);
								$priceCost = round( ($worth+$stampduty)/$quant, 2 );
								$psData = array(
									'uid'			=> $user['id'],
									'username'		=> $user['username'],
									'sid'			=> $stock_id,
									'buyprice'		=> $dsrs['price_deal'],
									'averageprice'	=> $priceCost,
									'stocknum_ava'	=> $quant,
									'buytime'		=> $_G['timestamp'],
									'ip'			=> $user['ip']
								);
								DB::insert('kfsm_customer', $psData);
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_war=fund_war-{$worth}-{$stampduty}, stocksort=stocksort+1, todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
							}
							else
							{
								$leftNum = $rsc['stocknum_ava'];
								$this->changeoptb($stock_id, $user['id'], $user['username'], intval($quant+$leftNum));
								$priceCost = round( ( $worth + $rsc['averageprice']*$leftNum + $stampduty ) / ( $quant + $leftNum ), 2 );
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$quant}, buyprice='{$dsrs['price_deal']}', averageprice='$priceCost', buytime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$rsc['cid']}'");
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_war=fund_war-{$worth}-{$stampduty}, todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
							}
							// �����ʽ𡢽�����������
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$worth}, todaysell=todaysell+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dsrs[uid]'");
							// ������Ʊ��������
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_war=stocknum_war-$quant, selltime='{$_G[timestamp]}' WHERE uid='{$dsrs[uid]}' AND sid='$stock_id'");
							$kfsclass->calculatefund($user['id'],$stock_id);
							$kfsclass->calculatefund($dsrs['uid'],$stock_id);
							// ������ȫ�ɽ�
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='1' WHERE did='$dsrs[did]'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 2, '$quant', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// �򷽲��ֳɽ����п�����ȫ�ɽ�
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$quant}, price_tran='{$dsrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='2' WHERE did='$newdid'");
							$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='$newdid'");
							if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='$newdid'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 1, '$quant', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// ���¹�Ʊ���ɽ��ۡ�
							$this->computeNewPrice( $stock_id, $dsrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
							// ���¹�Ʊ���ɽ�����
							DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$quant} WHERE sid='$stock_id'");
							// ���¹�����Ϣ
							DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$quant}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
							$buyNum -= $quant;
							continue;
						}
					}
				}
				showmessage('ί�������Ʊ�ѳɹ��ҵ���', "$baseScript&mod=member&act=trustsmng");
			}
		}
	}
	private function sellStock( $user, $stock_id=0, $sellPrice=0, $sellNum=0 )
	{
		global $baseScript, $_G, $kfsclass, $db_wavemax, $db_tradenummin, $db_dutyrate, $db_dutymin, $db_tradedelay, $db_iplimit;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$rss = DB::fetch_first("SELECT cid, stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE uid='{$user['id']}' AND sid='$stock_id'");
			if ( !$rss )
				showmessage('��û����ֻ��Ʊ���޷�������������');
			else
			{
				$selltime = DB::result_first("SELECT MAX(time_deal) FROM ".DB::table('kfsm_deal')." WHERE uid='{$user['id']}' AND sid='$stock_id' AND direction='2'");
				if ( is_numeric($db_tradedelay) && $db_tradedelay > 0 && $_G['timestamp'] - $selltime < $db_tradedelay * 60 )
				{
					$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$selltime)/60 );
					showmessage("�������ƣ��������ٴ������ù�Ʊ���� $timedelay ���ӣ�");
				}
				if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
				{
					$ipq = "SELECT selltime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND selltime>{$_G['timestamp']}-$db_iplimit*60";
					$sameIp = false;
					while( $rsip = DB::fetch($ipq) )
					{
						$rsip['ip'] == $user['ip'] && $sameIp = true;
					}
					if ( $sameIp )
					{
						$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['selltime'])/60 );
						Showmsg("�������ƣ�ͬһIP�û�����ͬһ��Ʊ���� $timedelay ���ӣ�");
					}
				}
				if ( !is_numeric($sellPrice) || $sellPrice < 1 ) showmessage('����ȷ���������۸�');
				if ( !is_numeric($sellNum) || $sellNum < 1 ) showmessage('����ȷ������������');
				$sellNum = (int)$sellNum;
				$leftNum = $rss['stocknum_ava'] - $sellNum;
				if ( $leftNum < 0 )
				{
					showmessage('��û���㹻�Ĺ�Ʊ����');
				}
				else
				{
					if ( $user['id'] == $rs['issuer_id'] && ( $leftNum < $rs['issuenum']/2 ) && ( $_G['timestamp'] - $rs['issuetime'] - 2592000 <= 0 ) )
					{
						showmessage('���Ǹù�˾��Ʊ�����ˣ�һ����֮�ڲ������۹�Ʊ');
					}
					if ( $rss['stocknum_ava'] < 10 )
					{
						if ( $leftNum > 0 )
							showmessage('���Ĺ�Ʊ�������� 10 �ɣ�����ȫ������');
					}
					else
					{
						if ( is_numeric($db_tradenummin) && $db_tradenummin > 0 && $sellNum < $db_tradenummin )
							showmessage("�����й涨��ÿ�����ٽ�����Ϊ $db_tradenummin �ɣ�");
					}
					$dealData = array(
						'uid'		=> $user['id'],
						'username'	=> $user['username'],
						'sid'		=> $stock_id,
						'direction'	=> '2',
						'quant_deal'=> $sellNum,
						'price_deal'=> $sellPrice,
						'time_deal'	=> $_G['timestamp'],
						'ok'		=> '0'
					);
					$newdid = DB::insert('kfsm_deal', $dealData, true);
					$query = DB::query("SELECT * FROM ".DB::table('kfsm_deal')." WHERE sid='$stock_id' AND direction='1' AND ( ok='0' OR ok='2' ) AND uid<>'$user[id]' AND hide='0' ORDER BY price_deal DESC");
					DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava-$sellNum, stocknum_war=stocknum_war+$sellNum WHERE cid='{$rss['cid']}'");
					while ( $dbrs = DB::fetch($query) )
					{
						$quant = $dbrs['quant_deal'] - $dbrs['quant_tran'];
						if ( $dbrs['price_deal'] >= $sellPrice && $quant > 0 && $sellNum > 0 )
						{
							if ( $quant >= $sellNum )
							{
								$worth		= $dbrs['price_deal'] * $sellNum;
								$stampduty	= $worth * $db_dutyrate / 100;
								$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
								// �����ʽ𡢽�����������
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$worth}-{$stampduty}, todaysell=todaysell+{$sellNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
								// ������Ʊ��������
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_war=stocknum_war-$sellNum, selltime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$rss['cid']}'");
								// �򷽹�Ʊ��������
								$rsc = DB::fetch_first("SELECT cid, stocknum_ava, averageprice FROM ".DB::table('kfsm_customer')." WHERE uid='{$dbrs[uid]}' AND sid='$stock_id'");
								if ( !$rsc )
								{
									$this->changeoptb($stock_id, $user['id'], $user['username'], $sellNum);
									$priceCost = round( ($worth+$stampduty)/$sellNum, 2 );
									$psData = array(
										'uid'			=> $dbrs['uid'],
										'username'		=> $dbrs['username'],
										'sid'			=> $stock_id,
										'buyprice'		=> $dbrs['price_deal'],
										'averageprice'	=> $priceCost,
										'stocknum_ava'	=> $sellNum,
										'buytime'		=> $_G['timestamp']
									);
									DB::insert('kfsm_customer', $psData);
								}
								else
								{
									$haveNum = $rsc['stocknum_ava'];
									$this->changeoptb($stock_id, $user['id'], $user['username'], intval($sellNum+$haveNum));
									$priceCost = round( ( $worth + $rsc['averageprice']*$haveNum + $stampduty ) / ( $sellNum + $haveNum ), 2 );
									DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$sellNum}, buyprice='{$dbrs['price_deal']}', averageprice='$priceCost', buytime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
								}
								// �򷽽����������
								DB::query("UPDATE ".DB::table('kfsm_user')." SET todaybuy=todaybuy+{$sellNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dbrs[uid]'");
								$kfsclass->calculatefund($user['id'], $stock_id);
								$kfsclass->calculatefund($dbrs['uid'], $stock_id);
								// �򷽲��ֳɽ����п�����ȫ�ɽ�
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$sellNum}, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='2' WHERE did='$dbrs[did]'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dbrs[uid]}', '{$dbrs[sid]}', '{$rs[stockname]}', '1', '$sellNum', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='{$dbrs['did']}'");
								if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
									DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='{$dbrs['did']}'");
								// ������ȫ�ɽ�
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran='{$dbrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='$newdid'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user['id']}', '{$dbrs[sid]}', '{$rs[stockname]}', '2', '$sellNum', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// ���¹�Ʊ���ɽ��ۡ�
								$this->computeNewPrice( $stock_id,  $dbrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
								// ���¹�Ʊ���ɽ�����
								DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$sellNum} WHERE sid='$stock_id'");
								// ���¹�����Ϣ
								DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaysell=todaysell+{$sellNum}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
								showmessage('��Ʊ�����ɹ���', "$baseScript&mod=member&act=trustsmng");
							}
							else
							{
								$worth		=  $dbrs['price_deal'] * $quant;
								$stampduty	= $worth * $db_dutyrate / 100;
								$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
								// �����ʽ𡢽�����������
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$worth}-{$stampduty}, todaysell=todaysell+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user['id']}'");
								// ������Ʊ��������
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_war=stocknum_war-$quant, selltime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$rss['cid']}'");
								// �򷽹�Ʊ��������
								$rsc = DB::fetch_first("SELECT cid, stocknum_ava, averageprice FROM ".DB::table('kfsm_customer')." WHERE uid='$dbrs[uid]' AND sid='$stock_id'");
								if ( !$rsc )
								{
									$this->changeoptb($stock_id, $user['id'], $user['username'], $quant);
									$priceCost = round( ($worth+$stampduty)/$quant, 2 );
									$bsData = array(
										'uid'			=> $dbrs['uid'],
										'username'		=> $dbrs['username'],
										'sid'			=> $stock_id,
										'buyprice'		=> $dbrs['price_deal'],
										'averageprice'	=> $priceCost,
										'stocknum_ava'	=> $quant,
										'buytime'		=> $_G['timestamp']
									);
									DB::insert('kfsm_customer', $bsData);
								}
								else
								{
									$haveNum = $rsc['stocknum_ava'];
									$this->changeoptb($stock_id, $user['id'], $user['username'], intval($quant+$haveNum));
									$priceCost = round( ( $worth + $rsc['averageprice']*$haveNum + $stampduty ) / ( $quant + $haveNum ), 2 );
									DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$quant}, buyprice='{$dbrs['price_deal']}', averageprice='$priceCost', buytime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
								}
								// �򷽽����������
								DB::query("UPDATE ".DB::table('kfsm_user')." SET todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dbrs[uid]'");
								$kfsclass->calculatefund($user['id'], $stock_id);
								$kfsclass->calculatefund($dbrs['uid'], $stock_id);
								// ����ȫ�ɽ�
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='1' WHERE did='$dbrs[did]'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dbrs[uid]}', '{$dbrs[sid]}', '{$rs[stockname]}', '1', '{$quant}', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// �������ֳɽ����п�����ȫ�ɽ�
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$quant}, price_tran='{$dbrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='2' WHERE did='$newdid'");
								$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='$newdid'");
								if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
									DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='$newdid'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user['id']}', '{$dbrs[sid]}', '{$rs[stockname]}', '2', '{$quant}', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// ���¹�Ʊ���ɽ��ۡ�
								$this->computeNewPrice( $stock_id, $dbrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
								// ���¹�Ʊ���ɽ�����
								DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$quant} WHERE sid='$stock_id'");
								// ���¹�����Ϣ
								DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaysell=todaysell+{$quant}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
								$sellNum -= $quant;
								continue;
							}
						}
					}
					showmessage('ί��������Ʊ�ѳɹ��ҵ���', "$baseScript&mod=member&act=trustsmng");
				}
			}
		}
	}
	private function computeNewPrice( $stock_id, $tradePrice, $openPrice, $issueTime )
	{
		global $db_wavemax;
		$waved_a = round( ( $tradePrice - $openPrice ) / $openPrice * 100, 2 );
		$db_wavemax = is_numeric($db_wavemax) && $db_wavemax > 0 && $db_wavemax < 100 ? $db_wavemax : 10;
		if ( $waved_a > $db_wavemax )
		{
			$priceMax	= round( $openPrice * ( 1 + $db_wavemax / 100 ), 2 );
			$price		= $priceMax;
			$waved_a	= $db_wavemax;
		}
		else if ( $waved_a < -$db_wavemax )
		{
			$priceMin	= round( $openPrice * ( 1 - $db_wavemax / 100 ), 2 );
			$price		= $priceMin;
			$waved_a	= -$db_wavemax;
		}
		else
		{
			$price		= $tradePrice;
		}
		$waved_t = round( ( $tradePrice - $issuePrice ) / $issuePrice * 100, 2 );
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET currprice={$price}, todaywave={$waved_a}, totalwave={$waved_t} WHERE sid='$stock_id'");
		DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET ain_t=ain_t+{$waved_a}");
		$this->updateTSP($stock_id,$price);
	}
	private function changeoptb( $stock_id, $userid, $username, $totalnum )
	{
		$changeoptb = false;
		$rsm = DB::fetch_first("SELECT holder_id FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		if ( $rsm['holder_id'] == 0 )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'");
			$changeoptb = true;
		}
		else if ( $rsm['holder_id'] <> $userid )
		{
			$rs = DB::fetch_first("SELECT stocknum_ava, cid FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND cid<>'$userid' ORDER BY stocknum_ava DESC");
			if ( $rs )
			{
				if ( $totalnum > $rs['stocknum_ava'] )
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'");
					$changeoptb = true;
				}
			}
		}
		return $changeoptb;
	}
	private function changeopts( $stock_id, $userid, $username, $remnum )
	{
		$changeopts = '';
		$rsm = DB::fetch_first("SELECT holder_id FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		$rs = DB::fetch_first("SELECT c.stocknum_ava, c.username, c.cid FROM ".DB::table('kfsm_customer')." c INNER JOIN ".DB::table('kfsm_user')." u ON c.cid=u.uid WHERE c.sid='$stock_id' AND c.cid<>'$userid' ORDER BY c.stocknum_ava DESC");
		if ( $rs )
		{
			if ( $remnum > $rs['stocknum_ava'] )
			{
				if ( $rsm['holder_id'] == $userid )
				{
					$changeopts = '';
				}
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'"); 
					$changeopts = $username;
				}
			}
			else if ( $remnum < $rs['stocknum_ava'] )
			{
				if ( $rsm['holder_id'] == $rs['cid'] )
				{
					$changeopts = '';
				}
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$rs[cid]', holder_name='$rs[username]' WHERE sid='$stock_id'");
					$changeopts = $rs['username'];
				}
			}
		}
		else if ( $remnum <= 0 )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_name='-', holder_id='0' WHERE sid='$stock_id'");
		}
		return $changeopts;
	}
	private function updateTSP( $stock_id, $price )
	{
		global $_G, $db_klcolor;
		if ( $stock_id && $price )
		{
			$klcolor = $db_klcolor;
			$pricedata = DB::query("SELECT pricedata FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
			$pricedata = substr($pricedata,strpos($pricedata,'|')+1).'|'.round($price,2);
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET uptime='{$_G[timestamp]}', pricedata='$pricedata' WHERE sid='$stock_id'");
		}
	}
	private function showMyDeals( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qd = DB::query("SELECT d.*, s.stockname FROM ".DB::table('kfsm_deal')." d LEFT JOIN ".DB::table('kfsm_stock')." s ON d.sid=s.sid WHERE d.uid='{$user['id']}' ORDER BY d.did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">����</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">�쳣</span>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal']	= dgmdate($rsd['time_deal'],'Y-m-d H:i:s');
			else
				$rsd['time_deal']	= '-';
			if ( $rsd['ok'] == 0 )
			{
				$rsd['ok'] = 'δ�ɽ�';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>����</em></button></form>";
			}
			else if ( $rsd['ok'] == 1 )
			{
				$rsd['ok'] = '<span style="color:#008000">�ɽ�</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else if ( $rsd['ok'] == 2 )
			{
				$rsd['ok'] = '<span style="color:#FFA500">���ֳɽ�</span>';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>����</em></button></form>";
			}
			else if ( $rsd['ok'] == 3 )
			{
				$rsd['ok'] = '<span style="color:#0000FF">�û�����</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else if ( $rsd['ok'] == 4 )
			{
				$rsd['ok'] = '<span style="color:#A52A2A">ϵͳ����</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			else
			{
				$rsd['ok'] = '<span style="color:#FF0000">�쳣</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>����</em></button>';
			}
			$ddb[] = $rsd;
		}
		include template('stock:member_trustsmng');
	}
	private function showMyTrans( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qt = DB::query("SELECT t.*, s.stockname FROM ".DB::table('kfsm_transaction')." t LEFT JOIN ".DB::table('kfsm_stock')." s ON t.sid=s.sid WHERE t.uid='{$user['id']}' ORDER BY t.tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">����</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">����</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">�쳣</span>';
			if ( $rst['ttime'] )
				$rst['ttime']	= dgmdate($rst['ttime'],'Y-m-d H:i:s');
			else
				$rst['ttime']	= '-';
			$tdb[] = $rst;
		}
		include template('stock:member_trustsmng');
	}
	private function cancelDeal( $user, $deal_id )
	{
		global $baseScript, $db_dutyrate, $db_dutymin, $kfsclass;
		$qd = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_deal')." WHERE did='$deal_id' AND uid='{$user['id']}'");
		if ( $qd )
		{
			$quantLeft = $qd['quant_deal'] - $qd['quant_tran'];
			if ( $quantLeft > 0 && $qd['hide'] == 0 )
			{
				if ( $qd['ok'] == 0 && $qd['quant_deal'] == $quantLeft )
				{
					$worth	= $qd['price_deal'] * $qd['quant_deal'];
				}
				else if ( $qd['ok'] == 2 )
				{
					$worth	= $qd['price_deal'] * $quantLeft;
				}
				else
				{
					showmessage('��ί�е�����״̬�쳣���޷�������');
				}
				$stampduty	= $worth * $db_dutyrate / 100;
				$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
				if ( $qd['direction'] == 1 )
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$worth}+{$stampduty}, fund_war=fund_war-{$worth}-{$stampduty} WHERE uid='{$user['id']}'");
					DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='3' WHERE did='{$qd['did']}'");
					$kfsclass->calculatefund($user['id']);
					showmessage('ί�������Ʊ�����ɹ���', "$baseScript&mod=member&act=trustsmng");
				}
				else if ( $qd['direction'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$quantLeft}, stocknum_war=stocknum_war-{$quantLeft} WHERE uid='{$user['id']}' AND sid='{$qd['sid']}'");
					DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='3' WHERE did='{$qd['did']}'");
					$kfsclass->calculatefund($user['id']);
					showmessage('ί��������Ʊ�����ɹ���', "$baseScript&mod=member&act=trustsmng");
				}
				else
				{
					showmessage('��ί�е����������쳣���޷�������');
				}
			}
			else
			{
				showmessage('��ί�е���ȫ���ɽ������ѹ��ڣ��޷�������');
			}
		}
		else
		{
			showmessage('��Ч��ί�е���');
		}
	}
}
?>
