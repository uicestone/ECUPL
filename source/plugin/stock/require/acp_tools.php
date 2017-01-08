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
class Tools
{
	public function toollist()
	{
		$stockdb = array();
		$query = DB::query("SELECT sid,stockname FROM ".DB::table('kfsm_stock'));
		while ( $rs = DB::fetch($query) )
		{
			$stockdb[] = $rs;
		}
		$stampduty = DB::query("SELECT stampduty FROM ".DB::table('kfsm_sminfo'));
		$stampduty = number_format($stampduty,2);
		return array($stockdb,$stampduty);
	}
	public function kfsmreset()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass->kfsm_reset();
		$baseScript .= '&mod=tools';
		cpmsg('�������������ɹ�', $baseScript, 'succeed');
	}
	public function divide($num=1)
	{
		global $baseScript;
		if ( in_array( $num, array(10,100,1000,10000,100000,1000000) ) )
		{
			DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava/{$num} WHERE fund_ava>{$num}");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('�ʽ�ת��', '{$_G[username]}', '���й����ʽ� {$num}��1 ����', '$_G[timestamp]', '$_G[clientip]')");
			$baseScript .= '&mod=tools';
			cpmsg('�ʽ�ת���ɹ�', $baseScript, 'succeed');
		}
		else
		{
			cpmsg('�ʽ�ת��ʧ�ܣ���ѡ����ȷ�ı�����', '', 'error');
		}
	}
	public function forcesell($stock_id=0,$confirm='')
	{
		global $baseScript;
		if ( $confirm == '1' )
		{
			$baseScript .= '&mod=tools';
			if ( $stock_id == '-1' )
			{
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_customer'));
				while ( $rs = DB::fetch($query) )
				{
					$stockvalue = $rs['stocknum_ava'] * $rs['averageprice'];
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$stockvalue}, asset=fund_ava, stocksort='0', stockcost='0.00', stockvalue='0.00', todaysell=todaysell+{$rs['stocknum_ava']} WHERE uid='{$rs['uid']}'");
				}
				DB::query("DELETE FROM ".DB::table('kfsm_customer'));
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='0', holder_name='-'");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('ǿ������', '{$_G[username]}', 'ǿ���������й���ȫ����Ʊ', '$_G[timestamp]', '$_G[clientip]')");
				cpmsg('���й����ȫ����Ʊ�ѱ�ǿ������', $baseScript, 'succeed');
			}
			else
			{
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
				while ( $rs = DB::fetch($query) )
				{
					$stockvalue = $rs['stocknum_ava'] * $rs['averageprice'];
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$stockvalue}, todaysell=todaysell+{$rs['stocknum_ava']} WHERE uid='{$rs['uid']}'");
					DB::query("DELETE FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND cid={$rs['cid']}");
					$kfsclass = new kfsclass;
					$kfsclass->calculatefund($rs['uid'],$stock_id);
				}
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id=0, holder_name='-' WHERE sid='$stock_id'");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('ǿ������', '{$_G[username]}', 'ǿ����������Ϊ $stock_id �Ĺ�Ʊ', '$_G[timestamp]', '$_G[clientip]')");
				cpmsg("����Ϊ $stock_id �Ĺ�Ʊ�ѱ�ǿ������", $baseScript, 'succeed');
			}
		}
		else
		{
			cpmsg('��ѡ����ȷ�Ĺ�Ʊ����', '', 'error');
		}
	}
	public function distribute()
	{
		global $_G, $baseScript;
		$sendto	= $_G['sendto'];
		$perval	= $_G['perval'];
		!$sendto && cpmsg('��ѡ�񷢷Ŷ����Ա��', '', 'error');
		if ( !$perval || !is_numeric($perval) )
			cpmsg('����ȷ��д���Ž��', '', 'error');
		is_array($sendto) && $sendto = implode(",",$sendto);
		$usernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_user')." u LEFT JOIN pw_members m ON u.forumuid=m.uid WHERE m.groupid IN('" . str_replace(",","','",$sendto) . "')");
		if ( $usernum )
		{
			$totalfunds = DB::result_first("SELECT stampduty FROM ".DB::table('kfsm_sminfo'));
			$totalval = $perval * $usernum;
			if ( $totalval > $totalfunds )
			{
				cpmsg("˰���ʽ��㣬�޷����š�����˰���ʽ� {$totalfunds} Ԫ�����Ŷ�������Ϊ {$usernum} �ˡ�", '', 'error');
			}
			else
			{
				$touids = array();
				$query = DB::query("SELECT u.uid FROM ".DB::table('kfsm_user')." u LEFT JOIN pw_members m ON u.forumuid=m.uid WHERE m.groupid IN('" . str_replace(",","','",$sendto) . "')");
				while ( $rs = DB::fetch($query) )
				{
					$touids[] = $rs['uid'];
				}
				DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$perval} WHERE uid IN(" . pwImplode($touids) . ")");
				DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET stampduty=stampduty-$totalval");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('˰�շ���', '{$_G[username]}', '������˰�� $totalval Ԫ��������� $usernum �ˣ�ÿ������ $perval Ԫ', '$_G[timestamp]', '$_G[clientip]')");
				$baseScript .= '&mod=tools';
				cpmsg('˰���ʽ𷢷����', $baseScript, 'succeed');
			}
		}
		else
		{
			cpmsg('��ѡ��Ա��û�й���', '', 'error');
		}
	}
}
?>
