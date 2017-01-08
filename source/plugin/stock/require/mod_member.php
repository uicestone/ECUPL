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
class Member
{
	private $member = array();
	public function __construct( $forumuid )
	{
		$user = $this->authorise( $forumuid );
		return $user;
	}
	public function processAction( $action )
	{
		global $_G, $kfsclass;
		$actArray = array('fundsmng', 'stocksmng', 'trustsmng', 'showinfo', 'subscribe', 'apply', 'cutamelon');
		if ( empty($action) || !in_array($action, $actArray) )
			showmessage('Messages from Kilofox StockIns: Invalid action');
		switch ( $action )
		{
			case 'fundsmng':
				$this->fundsManage($this->member);
			break;
			case 'stocksmng':
				$kfsclass->calculatefund($this->member['id'],0);
				$this->showMemberInfo($this->member['id']);
			break;
			case 'trustsmng':
				require_once 'class_trust.php';
				new Trust($this->member, $_G['gp_section']);
			break;
			case 'showinfo':
				$this->showMemberInfo($_G['gp_uid']);
			break;
			case 'subscribe':
				require_once 'class_subscribe.php';
				new Subscribe($this->member, $_G['gp_section'], $_G['gp_sid']);
			break;
			case 'apply':
				require_once 'class_apply.php';
				new Apply($this->member, $_G['gp_section']);
			break;
			case 'cutamelon':
				global $sid;
				if ( $_G['gp_section'] == 'docut' )
					$this->shareOutBonus($this->member, $_G['gp_sid']);
				else
					$this->showBonusForm($this->member, $_G['gp_sid']);
			break;
		}
	}
	private function authorise($forumuid=0)
	{
		global $_G;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_user')." WHERE forumuid='$forumuid'");
		if ( !$rs )
		{
			require_once 'class_register.php';
			$register = new register;
			$register->show_reg_form();
		}
		else
		{
			$this->member['id']				= $rs['uid'];
			$this->member['username']		= $rs['username'];
			$this->member['userclass']		= $rs['userclass'];
			$this->member['locked']			= $rs['locked'];
			$this->member['fund_ava']		= number_format($rs['fund_ava'],2,'.','');
			$this->member['asset']			= number_format($rs['asset'],2,'.','');
			$this->member['stocksort']		= $rs['stocksort'];
			$this->member['todaybuy']		= $rs['todaybuy'];
			$this->member['todaysell']		= $rs['todaysell'];
			$this->member['stocknum']		= $rs['stocknum'];
			$this->member['stockcost']		= $rs['stockcost'];
			$this->member['stockvalue']		= $rs['stockvalue'];
			if ( $this->member['stockvalue'] > $this->member['stockcost'] )
				$this->member['profit'] = "<font color=\"#FF0000\">��".number_format($this->member['stockvalue'] - $this->member['stockcost'],2)."</font>";
			else if ( $this->member['stockvalue'] < $this->member['stockcost'] )
				$this->member['profit'] = "<font color=\"#008000\">��".number_format($this->member['stockcost'] - $this->member['stockvalue'],2)."</font>";
			else
				$this->member['profit'] = "��0.00";
			$this->member['stockcost']	= number_format($this->member['stockcost'],2);
			$this->member['stockvalue']	= number_format($this->member['stockvalue'],2);
			if ( $rs['ip'] <> $_G['clientip'] )
			{
				$this->member['ip'] = $_G['clientip'];
				DB::query("UPDATE ".DB::table('kfsm_user')." SET ip='$_G[clientip]' WHERE uid='{$rs[uid]}'");
			}
			else
			{
				$this->member['ip'] = $rs['ip'];
			}
		}
		return $this->member;
    }
	private function showMemberInfo($user_id=0)
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_meloncutting;
		if ( $this->member['id'] == $user_id )
			$showAccount = true;
		else
			$showAccount = false;
		if ( $user_id > 0 )
			$username = DB::result_first("SELECT username FROM ".DB::table('kfsm_user')." WHERE uid='$user_id'");
		else
			$username = '-';
		$usdb = array();
		$query = DB::query("SELECT c.*, s.sid, s.stockname, s.currprice, s.holder_id, s.state FROM ".DB::table('kfsm_customer')." c LEFT JOIN ".DB::table('kfsm_stock')." s ON s.sid=c.sid WHERE c.uid='$user_id' ORDER BY c.stocknum_ava DESC");
		while ( $rs = DB::fetch($query) )
		{
			$averageprice	= $rs['averageprice'];
			$currentprice	= $rs['currprice'];
			$stocknum		= $rs['stocknum_ava'] + $rs['stocknum_war'];
			$stockcost		= $averageprice * $stocknum;
			$profit			= $currentprice - $averageprice;
			$totalprofit	= $profit * $stocknum;
			$rs['stocknum'] = $stocknum;
			$rs['averageprice']	= number_format($averageprice,2);
			$rs['currprice']	= number_format($currentprice,2);
			$rs['stockcost']	= number_format($stockcost,2);
			$rs['stockvalue']	= number_format($currentprice*$stocknum,2);
			$rs['totalprofit']	= number_format($totalprofit,2);
			$rs['profit_p']		= number_format($totalprofit/$stockcost*100,2);
			if ( $profit > 0 )
				$rs['color']	= '#FF0000';
			else if ( $profit < 0 )
				$rs['color']	= '#008000';
			else if ( $profit == 0 )
				$rs['color']	= '';
			if ( $db_meloncutting == '1' && $user_id == $this->member['id'] && $rs['holder_id'] == $this->member['id'] && $rs['state'] == 0 )
				$rs['manage'] = "<a href=\"$baseScript&mod=member&act=cutamelon&sid=$rs[sid]\">�ֺ�</a>";
			else
				$rs['manage'] = '<strike>�ֺ�</strike>';
			$usdb[] = $rs;
		}
		include template('stock:member_showinfo');
	}
	private function fundsManage( $user )
	{
		global $_G, $db_credittype;
		$mtype = $_G['gp_mtype'];
		if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
			$creditid	= $db_credittype;
		else
			$creditid	= $_G['setting']['creditstrans'];
		$credit = $_G['setting']['extcredits'][$creditid];
		$user['moneyType']	= $credit['title'];
		$user['moneyUnit']	= $credit['unit'];
		$user['moneyNum']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
		if ( empty($mtype) )
			$this->showFundsManageForm($user);
		else if ( $mtype == 'd' )
			$this->fundsDeposit($user);
		else if ( $mtype == 'a' )
			$this->fundsAdopt($user);
		else if ( $mtype == 't' )
			$this->fundsTransfer($user);
	}
	private function showFundsManageForm( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_proportion, $db_charge, $db_allowdeposit, $db_allowadopt, $db_allowtransfer, $db_depositmin, $db_adoptmin, $db_transfermin, $db_transfercharge;
		if ( $user['locked'] == 0 )
			$user['state'] = '����';
		else if ( $user['locked'] == 1 )
			$user['state'] = '<span style="color:#FF0000">����</span>';
		else
			$user['state'] = '<span style="color:#0000FF">�쳣</span>';
		$exchange_rate				= $db_proportion > 0 ? $db_proportion : 1;
		$commission_charge			= $db_charge > 0 ? $db_charge : 0;
		$commission_charge_trans	= $db_transfercharge > 0 ? $db_transfercharge : 0;
		include template('stock:member_fundsmng');
	}
	private function fundsDeposit( $user )
	{
		global $baseScript, $_G, $db_charge, $db_allowdeposit, $db_depositmin, $db_proportion, $db_credittype;
		if ( $db_allowdeposit <> '1' )
		{
			showmessage('�Բ��𣬴����ѹر�');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('�Բ��������ʻ��ѱ����ᣬ�޷����');
			}
			else
			{
				$money_in = $_G['gp_moneyi'];
				( !is_numeric($money_in) || $money_in <= 0 ) && showmessage('��������ȷ�Ĵ����');
				if ( $money_in < $db_depositmin )
				{
					showmessage("�Բ�����Ҫ�����{$user[moneyType]}��������С�� $db_depositmin {$user[moneyUnit]}");
				}
				else
				{
					$money_sm = $money_in * $db_proportion;	// ��̳���Ҷһ��ɹ��л���
					$comm_charge = $money_sm * $db_charge/100;	// ��̳����������ʽ𻥶�������ȫ���ӹ����п۳�������Ϊ0
					if ( $money_in > $user['moneyNum'] )
					{
						showmessage("�Բ�������{$user['moneyType']}���㡣<br/>��Ҫ����{$user['moneyType']} $money_in {$user['moneyUnit']}����ֻ��{$user[moneyType]} <font color=\"#FF0000\">{$user['moneyNum']}</font> {$user['moneyUnit']}��");
					}
					else
					{
						if ( $db_proportion > 0 && $db_charge >= 0 )
						{
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$money_sm}-{$comm_charge}, asset=asset+'$money_sm' WHERE uid='{$user[id]}'");
							if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
								$creditid	= $db_credittype;
							else
								$creditid	= $_G['setting']['creditstrans'];
							DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."-{$money_in} WHERE uid='{$_G['uid']}'");
							DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, field, descrip, timestamp, ip) VALUES('�û�����', '{$user[username]}', '�ʽ�����', '�û� {$user[username]} ����̳{$user['moneyTyp']} $money_in {$user['moneyUnit']}��������ʻ����ۺϹ����ʽ� ".number_format($money_sm,2)." Ԫ���۳������� ".number_format($comm_charge,2)." Ԫ', '$_G[timestamp]', '$_G[clientip]')");
							showmessage("���Ѿ�����̳{$user[moneyType]} $money_in {$user[moneyUnit]}�ۺϹ����ʽ� ".number_format($money_sm,2)." Ԫ��������Ĺ����ʻ����۳������� ".number_format($comm_charge,2)." Ԫ", "$baseScript&mod=member&act=fundsmng");
						}
						else
						{
							showmessage('����������̳����������ʽ�һ����������޷���');	// ǿ���ж���̳����������ʽ�һ������������ѣ��Ա���������̨���ô����������̳���һ�����ʽ��������ҡ�
						}
					}
				}
			}
		}
	}
	private function fundsAdopt( $user )
	{
		global $baseScript, $_G, $db_allowadopt, $db_charge, $db_adoptmin, $db_proportion, $db_initialmoney, $db_credittype;
		if ( $db_allowadopt <> '1' )
		{
			showmessage('�Բ���ȡ����ѹر�');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('�Բ��������ʻ��ѱ����ᣬ�޷�ȡ��');
			}
			else
			{
				$money_x = $_G['gp_moneyx'];
				( !is_numeric($money_x) || $money_x <=0 ) && showmessage('��������ȷ��ȡ����');
				if ( ( $user['fund_ava'] - $money_x ) < $db_initialmoney )
				{
					showmessage('�����й涨���ʻ������ʽ��ܵ��� '.number_format($db_initialmoney,2).' Ԫ��<br/>��Ҫȡ�� '.number_format($money_x,2).' Ԫ���ʻ������п����ʽ� <font color="#FF0000">'.number_format($user['fund_ava'],2).'</font> Ԫ��');
				}
				else
				{
					if ( $money_x < $db_adoptmin )
					{
						showmessage('�Բ���ȡ��������� '.number_format($db_adoptmin,2).' Ԫ');
					}
					else
					{
						if ( $money_x > $user['fund_ava'] )
						{
							showmessage('�Բ��������ʻ������ʽ��㡣��Ҫȡ�� '.number_format($money_x,2).' Ԫ���ʻ������п����ʽ� '.number_format($user['fund_ava'],2).' Ԫ��');
						}
						else
						{
							if ( $db_proportion > 0 && $db_charge >= 0 )
							{
								$comm_charge = $money_x * $db_charge/100;
								$money_f = (int)($money_x / $db_proportion);	// ���л��Ҷһ�����̳����
								DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-($money_x+$comm_charge), asset=asset-($money_x+$comm_charge) WHERE uid='{$user[id]}'");
								if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
									$creditid	= $db_credittype;
								else
									$creditid	= $_G['setting']['creditstrans'];
								DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."+{$money_f} WHERE uid='{$_G['uid']}'");
								DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, field, descrip, timestamp, ip) VALUES('�û�����', '{$user[username]}','�ʽ�����','�û� {$user[username]} �ѹ����ʽ� ".number_format($money_x,2)." Ԫ��ȡ����̳�ʻ����۳������� ".number_format($comm_charge,2)." Ԫ���ۺ���̳{$user[moneyType]} $money_f {$user[moneUnit]}', '$_G[timestamp]', '$_G[clientip]')");
								showmessage("���Ѿ��ѹ����ʽ� ".number_format($money_x,2)." Ԫ�ۺ���̳{$user[moneyType]} $money_f {$user[moneyUnit]}�����������̳�ʻ����۳������� ".number_format($comm_charge,2)." Ԫ", "$baseScript&mod=member&act=fundsmng");
							}
							else
							{
								showmessage('����������̳����������ʽ�һ����������޷�ȡ�');
							}
						}
					}
				}
			}
		}
	}
	private function fundsTransfer( $user )
	{
		global $baseScript, $_G, $db_allowtransfer, $db_transfercharge, $db_transfermin, $db_charge, $db_proportion, $db_initialmoney;
		if ( $db_allowtransfer <> '1' )
		{
			showmessage('ת�ʹ����ѹر�');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('�Բ��������ʻ��ѱ����ᣬ�޷�ת��');
			}
			else
			{
				$money_t = $_G['gp_moneyt'];
				if ( !is_numeric($money_t) || $money_t <= 0 )
					showmessage('��������ȷ��ת���ʽ�');
				else
				{
					$comm_charge = $money_t * $db_transfercharge / 100;
					if ( $money_t + $comm_charge > $user['fund_ava'] )
					{
						showmessage('�Բ��������ʻ������ʽ��㡣����ת�� '.number_format($money_t,2).'Ԫ���������� '.number_format($commission_charge,2).' Ԫ���ʻ������п����ʽ� '.number_format($user['fund_ava'],2).' Ԫ��');
					}
				}
				$towho = $_G['gp_towho'];
				if ( !$towho )
					showmessage('�������տ�������');
				else
				{
					if ( $towho == $user['username'] )
						showmessage('�����ܰ��ʽ��͸��Լ�');
					else
					{
						$rs = DB::fetch_first("SELECT uid, username, locked FROM ".DB::table('kfsm_user')." WHERE username='$towho'");
						if ( !$rs )
							showmessage("�տ��� $towho δ�ҵ�");
						else
						{
							if ( $rs['locked'] == 1 )
							{
								showmessage("$towho ���ʻ��ѱ����ᣬ�޷���������ת���ʽ�");
							}
							else
							{
								if ( $user['fund_ava'] - $money_t < $db_initialmoney )
								{
									showmessage('�����й涨���ʻ������ʽ𲻵õ��� '.number_format($db_initialmoney,2).' Ԫ������ת�� '.number_format($money_t,2).' Ԫ���������� '.number_format($comm_charge,2).' Ԫ��');
								}
								else
								{
									if ( $money_t < $db_transfermin )
									{
										showmessage("�Բ���ת�ʽ������� ".number_format($db_transfermin,2)." Ԫ");
									}
									else
									{
										DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-($money_t+$comm_charge), asset=asset-($money_t+$comm_charge) WHERE uid='{$user[id]}'");
										DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$money_t}, asset=asset+{$money_t} WHERE uid='{$rs[uid]}'");
										$subject = "���� $user[username] �ʽ�����ɹ���";
										$content = "���� [url=$baseScript&act=showuser&uid={$user['id']}]{$user['username']}[/url] �� ".number_format($money_t,2)." Ԫ�ʻ��ʽ������ [url=$baseScript&act=showuser&uid={$rs['uid']}]{$rs['username']}[/url] ���� {$rs[username]} ע����ա�";
										DB::query("INSERT INTO ".DB::table('kfsm_news')."(subject, content, color, addtime) VALUES ('$subject', '$content', 'StockIns', '$_G[timestamp]')");
										DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, field, descrip, timestamp, ip) VALUES('�û�����','$rs[username]','$user[username]','�ʽ�����','�û� {$user[username]} �� ".number_format($money_t,2)." Ԫת�ʸ� {$rs[username]} ���۳������� $comm_charge Ԫ', '$_G[timestamp]', '$_G[clientip]')");
										showmessage("���Ѿ��ѹ����ʽ� ".number_format($money_t,2)." Ԫת�� {$rs[username]} ���۳������� ".number_format($comm_charge,2)."Ԫ", "$baseScript&mod=member&act=fundsmng");
									}
								}
							}
						}
					}
				}
			}
		}
	}
	private function showBonusForm( $user, $stock_id )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_otherpp;
		if ( $rs = $this->checkMelonCondition( $user, $stock_id ) )
		{
			$cs = DB::fetch_first("SELECT COUNT(*) AS usernum, SUM(stocknum_ava) AS stocknum FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
			$sn = DB::fetch_first("SELECT stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE uid='$user[id]' AND sid='$stock_id'");
	        require_once 'mod_stock.php';
			$stock = new stock('call');
			$cnt = $cs['usernum'];
			if ( $cnt > 0 )
			{
				$readperpage = $db_otherpp;
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
				$pages = foxpage($cnt,$page,$numofpage,"$baseScript&mod=member&act=cutamelon&sid=$stock_id&");
				$holders = $stock->getStockholdersList($stock_id, $rs['currprice'], $cs['stocknum_ava'], $start, $readperpage);
			}
			include template('stock:member_cutamelon');
		}
	}
	private function shareOutBonus($user, $stock_id)
	{
		global $baseScript, $kfsclass, $_G;
		if ( $rs = $this->checkMelonCondition( $user, $stock_id ) )
		{
			$cutnum1	= $_G['gp_cutnum1'];
			$cutnum2	= $_G['gp_cutnum2'];
			$cutnum3	= $_G['gp_cutnum3'];
			$totalNum	= DB::result_first("SELECT SUM(stocknum_ava) FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
			if ( $_G['gp_cuttype'] == '1' )
			{
				if ( !is_numeric($cutnum1) || $cutnum1 <= 0 )
					showmessage('��������ȷ�ķֺ���');
				else
					$cutnum1 = round($cutnum1,2);
				$feesNeed = $cutnum1 * ceil($totalNum/100);
				if ( $user['fund_ava'] < $feesNeed )
					showmessage('�����ʻ���û���㹻���ʽ������ֺ죬���ʵ����ٷֺ���');
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-{$feesNeed}, asset=asset-{$feesNeed} WHERE uid='$user[id]'");
					$query = DB::query("SELECT uid, stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
					while ( $rc = DB::fetch($query) )
					{
						$addmoney = $cutnum1 * floor($rc['stocknum_ava']/100);
						if ( $addmoney > 0 )
						{
							DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$addmoney}, asset=asset+{$addmoney} WHERE uid='{$rc['uid']}'");
						}
					}
					$subject = "���й�˾ {$rs[stockname]} ��ȫ��ɶ��������";
					$content = "���й�˾ [url=plugin.php?id=stock:index&mod=stock&act=showinfo&sid=$stock_id]{$rs[stockname]}[/url] ��ɶ� [url=plugin.php?id=stock:index&mod=member&act=showinfo&uid=$user[id]]{$user[username]}[/url] ��ȫ����иù�˾��Ʊ�Ĺɶ����������\n�ֺ췽��Ϊÿ 100 �������ֽ� $cutnum1 Ԫ��";
					DB::query("INSERT INTO ".DB::table('kfsm_news')." (subject, content, color, author, addtime) VALUES('$subject', '$content', '008000', 'StockIns', '$_G[timestamp]')");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, field, descrip, timestamp, ip) VALUES('�ֺ����', '{$user[username]}', '��Ʊ�ֺ�', '���й�˾ {$rs[stockname]} ÿ 100 ���ֽ���Ϣ ".number_format($cutnum1,2)." Ԫ', '$_G[timestamp]', '$_G[clientip]')");
					showmessage('��������ɹ���ÿ 100 ���ֽ���Ϣ '.number_format($cutnum1,2).' Ԫ', $baseScript);
				}
			}
			else if ( $_G['gp_cuttype'] == '2' )
			{
				if ( !is_numeric($cutnum2) || $cutnum2 <= 0 )
					showmessage('��������ȷ���͹�����');
				else
					$cutnum2 = (int)$cutnum2;
				$stockPrice = DB::query("SELECT currprice FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
				$feesNeed = $stockPrice * $cutnum2 * ceil($totalNum/100);
				if ( $user['fund_ava'] < $feesNeed )
					showmessage('�����ʻ���û���㹻���ʽ������ֺ죬���ʵ����ٷֺ����');
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-{$feesNeed}, asset=asset-{$feesNeed} WHERE uid='$user[id]'");
					$query = DB::query("SELECT cid, stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
					while ( $rc = DB::fetch($query) )
					{
						$addnum = $cutnum2 * floor($rc['stocknum_ava']/100);
						if ( $addnum > 0 )
						{
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum_ava=stocknum_ava+{$addnum} WHERE cid='{$rc['cid']}'");
						}
					}
					$subject = "���й�˾ {$rs[stockname]} ��ȫ��ɶ��������";
					$content = "���й�˾ [url=plugin.php?id=stock:index&mod=stock&act=showinfo&sid=$stock_id]{$rs['stockname']}[/url] ��ɶ� [url=plugin.php?id=stock:index&mod=member&act=showinfo&uid={$user[id]}]{$user[username]}[/url] ��ȫ����иù�˾��Ʊ�Ĺɶ����������\n�ֺ췽��Ϊÿ 100 �����͹�Ʊ $cutnum2 �ɣ�";
					DB::query("INSERT INTO ".DB::table('kfsm_news')." (subject, content, color, author, addtime) VALUES('$subject', '$content', '008000', 'StockIns', '$_G[timestamp]')");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, field, descrip, timestamp, ip) VALUES('�ֺ����', '{$user[username]}', '��Ʊ�ֺ�', '���й�˾ {$rs[stockname]} ÿ 100 �����͹�Ʊ {$cutnum2} ��', '$_G[timestamp]', '$_G[clientip]')");
					showmessage("��������ɹ���ÿ 100 �����͹�Ʊ $cutnum2 ��", $baseScript);
				}
			}
			else if ( $_G['gp_cuttype'] == '3' )
			{
				// 3.�ֺ�ת���ɱ���ת���ɱ��൱�ڹ���û�õ��ô���ֻ�����ɶ�Ϊ������˾�Ĺɱ��������ģ�����ÿ10��ת��10�ɣ�����˵����ּ���10Ԫ�������г��иùɵĹ���ÿ10��������10�ɣ���ͬʱ���ɼ�Ҳ��ն�ˣ��۸��Ϊ5Ԫ�ˣ��������°������ڵ���ֵ����ת���ɺ�Ĺ���������ɼۣ��Ƕ��پͱ�ɶ��١�
			}
			else
			{
				showmessage('��ѡ��һ�ַֺ췽ʽ');
			}
		}
		else
		{
			showmessage('�ֺ�ϵͳ���ִ���');
		}
	}
	private function checkMelonCondition( $user, $stock_id )
	{
		global $db_meloncutting;
		if ( $db_meloncutting <> '1' )
			showmessage('�ɶ��ֺ칦���Ѿ��ر�');
		else
		{
			$rs = DB::fetch_first("SELECT sid, stockname, currprice, holder_id, state FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
			if ( !$rs )
				showmessage('û���ҵ�ָ�������й�˾�����ܸ����й�˾�Ѿ�����');
			else
			{
				if ( $rs['state'] <> 0 )
					showmessage('�ù�Ʊ״̬�쳣�����ܽ��зֺ����');
				else
				{
					if ( $rs['holder_id'] <> $user['id'] )
						showmessage('�����Ǹù����ɶ������ܽ��зֺ����');
					else
					{
						$usernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_customer')." c LEFT JOIN ".DB::table('kfsm_user')." u ON c.cid=u.uid WHERE u.ip<>'$user[ip]' AND c.sid='$stock_id'");
						if ( $usernum < 1 )
							showmessage('�ù�˾�ɶ�����̫�٣����ֺܷ�');
						else
							return $rs;
					}
				}
			}
		}
	}
}
?>
