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
class Subscribe
{
	public function __construct( $member, $section, $stock_id )
	{
		$this->process( $member, $section, $stock_id );
	}
	private function process( $user, $section, $sid )
	{
		if ( empty($section) )
			$this->showBuyForm($user, $sid);
		else if ( $section == 'trade' )
			$this->stockTrade($user, $sid);
	}
	private function stockTrade( $user, $sid )
	{
		global $_G, $db_usertrade;
		if ( $db_usertrade == '1' )
		{
			if ( $_G['gp_sidp'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_sidp'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else
				$this->showBuyForm($user, $sid);
		}
		else
			showmessage('��ʱֹͣ���ף������Ժ�����');
	}
	private function showBuyForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_tradedelay, $db_tradenummin;
		if ( $rs = $this->checkStock($user, $stock_id) )
		{
			$currprice	= $rs['stockprice'] > 0 ? $rs['stockprice'] : 0;
			$buyMin = $db_tradenummin > 0 ? intval($db_tradenummin) : 0;
			$buyMax = intval( $user['fund_ava'] / $currprice );
			$buyMax = $buyMax > $rs['surplusnum'] ? $rs['surplusnum'] : $buyMax;
			$buyMax = $buyMax > intval($rs['stocknum']*0.1) ? intval($rs['stocknum']*0.1) : $buyMax;
			if ( $buyMin > $buyMax )
			{
				$buyMinInit	= $buyMin;
				$buyMin		= $buyMax;
				$buyMax		= $buyMinInit;
			}
			$dutyRate	= 0;
			$dutyMin	= 0;
			if ( $buyMax <= 0 )
				$btn_buy = 'disabled';
			else
				$btn_buy = '';
			include template('stock:member_subscribe');
		}
		else
		{
			showmessage('�ù�Ʊ״̬�쳣����ʱ�޷�����');
		}
	}
	private function checkStock($user, $stock_id=0)
	{
		global $_G, $db_tradedelay, $db_iplimit;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_apply')." WHERE sid='$stock_id'");
		if ( !$rs )
		{
			showmessage('û���ҵ�ָ���Ĺ�Ʊ�����ܸ����й�˾�Ѿ�����');
		}
		else
		{
			$rs['state'] <> 1 && showmessage('�ù�Ʊ״̬�쳣����ʱ�޷�����');
			$buytime = DB::result_first("SELECT MAX(buytime) FROM ".DB::table('kfsm_customer')." WHERE uid='$user[id]' AND sid='$stock_id'");
			if ( $_G['timestamp'] - $buytime < $db_tradedelay * 60 )
			{
				$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$buytime)/60 );
				showmessage("�������ƣ��������ٴ�����ù�Ʊ���� $timedelay ���ӣ�");
			}
			if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
			{
				$ipq = "SELECT buytime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND buytime>{$_G['timestamp']}-$db_iplimit*60";
				$sameIp = false;
				while( $rsip = DB::query($ipq) )
				{
					$rsip['ip'] == $user['ip'] && $sameIp = true;
				}
				if ( $sameIp )
				{
					$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['buytime'])/60 );
					Showmsg("�������ƣ�ͬһIP�û�����ͬһ��Ʊ���� $timedelay ���ӣ�");
				}
			}
		}
		return $rs;
	}
	private function buyStock( $user, $stock_id, $price_buy, $num_buy )
	{
		global $baseScript, $_G, $kfsclass, $db_tradenummin;
		if ( $rs = $this->checkStock($user, $stock_id) )
		{
			if ( !is_numeric($num_buy) || $num_buy < $db_tradenummin )
				showmessage('����ȷ��������������');
			else
			{
				$rs = DB::fetch_first("SELECT userid, stocknum, surplusnum FROM ".DB::table('kfsm_apply')." WHERE sid='$stock_id'");
				if ( $num_buy > $rs['surplusnum'] )
				{
					showmessage('�Բ��𣬹�Ʊ�������㣡');
				}
				else
				{
					if ( $db_tradenummin > 0 && $num_buy < $db_tradenummin )
						showmessage("�����й涨��ÿ�����ٽ�����Ϊ $db_tradenummin �ɣ�");
					$needMoney	= $price_buy * $num_buy;
					if ( $user['fund_ava'] < $needMoney )
						showmessage('�����ʻ���û���㹻���ʽ����������Ʊ');
					else
					{
						$worth		= $price_buy * $num_buy;
						$rsc = DB::fetch_first("SELECT cid, stocknum_ava, stocknum_war, averageprice, buytime FROM ".DB::table('kfsm_customer')." WHERE uid='$user[id]' AND sid='$stock_id'");
						if ( !$rsc )
						{
							DB::query("INSERT INTO ".DB::table('kfsm_customer')." (uid, username, sid, buyprice, averageprice, stocknum_ava, buytime, ip) VALUES ('$user[id]', '$user[username]', '$stock_id', '$price_buy', '$price_buy', '$num_buy', '$_G[timestamp]', '$user[ip]')");
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-{$worth}, stocksort=stocksort+1, todaybuy=todaybuy+{$num_buy}, lasttradetime='$_G[timestamp]' WHERE uid='{$user[id]}'");
						}
						else
						{
							$numLtd = (int)$rs['stocknum']*0.1;
							if ( $num_buy + $rsc['stocknum_ava'] + $rsc['stocknum_war'] > $numLtd )
							{
								$haveNum = $rsc['stocknum_ava'] + $rsc['stocknum_war'];
								showmessage("�����й涨����Ʊ�깺�������ܴ��� $numLtd �ɡ����Ѿ�ӵ�иù�Ʊ $haveNum �ɡ�");
							}
							$avgprice = round( ( $price_buy * $num_buy + $rsc['averageprice'] * $rsc['stocknum_ava'] ) / ( $num_buy + $rsc['stocknum_ava'] ), 2 );
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$num_buy}, buyprice='{$price_buy}', averageprice='{$avgprice}', buytime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$rsc['cid']}'");
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-{$worth}, todaybuy=todaybuy+{$num_buy}, lasttradetime='{$_G['timestamp']}' WHERE uid='{$user[id]}'");
						}
						$kfsclass->calculatefund($user['id'], $stock_id);
						DB::query("UPDATE ".DB::table('kfsm_apply')." SET surplusnum=surplusnum-{$num_buy}, capitalisation=capitalisation+{$worth} WHERE sid='$stock_id'");
						DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$num_buy}, todaytotal=todaytotal+{$worth}");
						showmessage('��Ʊ�깺�ɹ���', "$baseScript&mod=member&act=stocksmng");
					}
				}
			}
		}
		else
		{
			showmessage($baseScript, '����ϵͳ����');
		}
	}
}
?>
