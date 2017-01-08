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
class Apply
{
	public function getNewApplyNum()
	{
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_apply')." WHERE state='0'");
		return $num ? $num : 0;
	}
	public function getApplyList()
	{
		global $baseScript;
		$query = DB::query("SELECT * FROM ".DB::table('kfsm_apply')." ORDER BY applytime DESC");
		$esdb = array();
		$i = 1;
		while ( $rs = DB::fetch($query) )
		{
			$rs['order'] = $i;
			$i++;
			if ( $rs['state'] == 0 )
			{
				$rs['state'] = '�����';
				$rs['operate'] = "<a href=\"?$baseScript&mod=usmng&ops=pass&aid=$rs[aid]\">��׼</a> <a href=\"?$baseScript&mod=usmng&ops=deny&aid=$rs[aid]\">�ܾ�</a>";
			}
			else if ( $rs['state'] == 1 )
			{
				$rs['state'] = '����׼';
				$rs['operate'] = "<strike>��׼</strike> <strike>�ܾ�</strike>";
			}
			else if ( $rs['state'] == 2 )
			{
				$rs['state'] = '�Ѿܾ�';
				$rs['operate'] = "<strike>��׼</strike> <strike>�ܾ�</strike>";
			}
			else if ( $rs['state'] == 3 )
			{
				$rs['state'] = '������';
				$rs['operate'] = "<strike>��׼</strike> <strike>�ܾ�</strike>";
			}
			else
			{
				$rs['state'] = '�쳣';
				$rs['operate'] = "<strike>��׼</strike> <strike>�ܾ�</strike>";
			}
			$rs['price'] = number_format($rs['price'],2);
			$rs['applytime'] = dgmdate($rs['applytime']);
			$esdb[] = $rs;
		}
		return $esdb;
	}
	public function userStockManage($apply_id=0)
	{
		global $baseScript, $_G;
		$ops = $_G['gp_ops'];
		$aprs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_apply')." WHERE aid='$apply_id'");
		if ( !$aprs )
			cpmsg('δ�ҵ�ָ���Ĺ�Ʊ����', '', 'error');
		else
		{
			$baseScript .= '&mod=esset';
			if ( $ops == 'pass' )
			{
				if ( $aprs['state'] == 1 )
					cpmsg('�ù�˾�ѱ���׼���У������ظ��ύ', '', 'error');
				else if ( $aprs['state'] == 2 )
					cpmsg('�ù�˾�ѱ��ܾ����У������ظ��ύ', '', 'error');
				else if ( $aprs['state'] == 0 )
				{
					$photo = rand(0,5).'.jpg';
					$comintro = addslashes($aprs['comintro']);
					$stockData = array(
						'stockname'		=> $aprs['stockname'],
						'openprice'		=> $aprs['stockprice'],
						'currprice'		=> $aprs['stockprice'],
						'lowprice'		=> $aprs['stockprice'],
						'highprice'		=> $aprs['stockprice'],
						'issueprice'	=> $aprs['stockprice'],
						'issuenum'		=> $aprs['stocknum'],
						'issuer_id'		=> $aprs['userid'],
						'issuer_name'	=> $aprs['username'],
						'holder_id'		=> $aprs['userid'],
						'holder_name'	=> $aprs['username'],
						'issuetime'		=> $_G['timestamp'],
						'comphoto'		=> $photo,
						'comintro'		=> $comintro,
						'state'			=> '4'
					);
					$newsid = DB::insert('kfsm_stock', $stockData, true);
					$issuer_stock_num	= intval($aprs['stocknum'] / 2);
					$surplusnum			= $aprs['stocknum'] - $issuer_stock_num;
					// �����˻�ð�����Ʊ����һ���Ʊ����ʽ���к����
					DB::query("INSERT INTO ".DB::table('kfsm_customer')." (uid, username, sid, stocknum_ava, buyprice, averageprice, buytime) VALUES ('$aprs[userid]', '$aprs[username]', '$newsid', $issuer_stock_num, '$aprs[stockprice]', '$aprs[stockprice]', '$_G[timestamp]')");
					loadcache('plugin');
					$db_issuedays = $_G['cache']['plugin']['stock']['issuedays'];
					$db_issuedays = is_numeric($db_issuedays) && $db_issuedays > 0 ? $db_issuedays : 3;
					$db_issuedays = $_G['timestamp'] + $db_issuedays * 86400;
					DB::query("UPDATE ".DB::table('kfsm_apply')." SET sid='$newsid', surplusnum='$surplusnum', issuetime='$db_issuedays', state='1' WHERE aid='{$aprs[aid]}'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('�������', '$aprs[username]', '{$_G[username]}', '$aprs[username] �Ĺ�˾ $aprs[stockname] ����������׼�����ɹ�', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('��˾����������׼�ɹ�', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('�ù�Ʊ״̬�쳣���޷�����', '', 'error');
				}
			}
			else if ( $ops == 'deny' )
			{
				if ( $aprs['state'] == 1 )
					cpmsg('�ù�˾�ѱ���׼���У������ظ��ύ', '', 'error');
				else if ( $aprs['state'] == 2 )
					cpmsg('�ù�˾�ѱ��ܾ����У������ظ��ύ', '', 'error');
				else if ( $aprs['state'] == 0 )
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$aprs[stockprice]}*{$aprs[stocknum]} WHERE uid='$aprs[userid]'");
					DB::query("UPDATE ".DB::table('kfsm_apply')." SET state='2' WHERE aid='$apply_id'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('�������', '$aprs[username]', '{$_G[username]}', '$aprs[username] �Ĺ�˾ $aprs[stockname] �������뱻�ܾ�', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('��˾��������ܾ��ɹ�', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('�ù�Ʊ״̬�쳣���޷�����', '', 'error');
				}
			}
			else if ( $ops == 'del' )
			{
				if ( $aprs['state'] == 0 )
					cpmsg('�ù�˾���ڵȴ���ˣ�����ɾ��', '', 'error');
				else if ( $aprs['state'] == 1 || $aprs['state'] == 2 )
				{
					DB::query("DELETE FROM ".DB::table('kfsm_apply')." WHERE aid='$apply_id'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('�������', '$aprs[username]', '{$_G[username]}', '$aprs[username] �Ĺ�˾ $aprs[stockname] �������뱻ɾ��', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('��˾��������ɾ���ɹ�', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('�ù�Ʊ״̬�쳣���޷�����', '', 'error');
				}
			}
			else
			{
				cpmsg('����ѡ���������', '', 'error');
			}
		}
	}
}
?>
