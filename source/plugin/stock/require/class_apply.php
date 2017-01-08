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
	public function __construct($member, $section)
	{
		if ( empty($section) )
			$this->showApplyList($member);
		else if ( $section == 'apply' )
			$this->showApplyForm($member);
		else if ( $section == 'save' )
			$this->saveData($member);
	}
	private function showApplyList($user)
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $page, $hkimg;
		$apply_list = array();
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_apply'));
		if ( $cnt > 0 )
		{
			$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
			$page = $_G['gp_page'];
			if ( $page < 1 )
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
			$pages = foxpage($page,$numofpage,"$baseScript&mod=member&act=apply&");
			$i = 0;
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_apply')." ORDER BY aid DESC LIMIT $start, $readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$i++;
				$rs['i'] = $i;
				$rs['stockprice'] = number_format($rs['stockprice'],2);
				$rs['applytime'] = dgmdate($rs['applytime']);
				if ( $rs['state'] == 0 )
					$rs['state'] = '�����';
				else if ( $rs['state'] == 1 )
					$rs['state'] = '����׼';
				else if ( $rs['state'] == 2 )
					$rs['state'] = 'δͨ��';
				else if ( $rs['state'] == 3 )
					$rs['state'] = '������';
				else
					$rs['state'] = '�쳣';
				$applys[] = $rs;
			}
		}
		include template('stock:member_apply');
	}
	private function showApplyForm($user)
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_esifopen, $db_esnamemin, $db_esnamemax, $db_esminnum, $db_introducemax;
		if ( $db_esifopen == '1' )
		{
			$min_wealth = $db_esminnum * 10;
			if ( $user['fund_ava'] < $min_wealth )
			{
				$applyAllow = false;
				$msg = '���Ĺ����ʻ������ʽ��� <span class="xi1">'.number_format($min_wealth,2).'</span> Ԫ���������빫˾����';
			}
			else
			{
				$applyAllow = true;
				/* Prepared for JavaScript Begin */
				if ( empty($db_esnamemin) || !is_numeric($db_esnamemin) || $db_esnamemin < 3 )
					$db_esnamemin = 3;
				if ( empty( $db_esnamemax) || !is_numeric( $db_esnamemax) ||  $db_esnamemax < 3 )
					 $db_esnamemax = 3;
				if ( empty($db_introducemax) || !is_numeric($db_introducemax) || $db_introducemax < 10 )
					$db_introducemax = 10;
				if ( empty($db_esminnum) || !is_numeric($db_esminnum) || $db_esminnum < 0 )
					$db_esminum = 0;
				/* Prepared for JavaScript End */
				$rndPrice = mt_rand(2,99);
			}
		}
		else
		{
			$applyAllow = false;
			$msg = '��ʱֹͣ���ܹ�˾���е�����';
		}
		include template('stock:member_apply');
	}
	private function saveData($user)
	{
		global $baseScript, $db_esifopen, $db_esnamemin, $db_esnamemax, $db_esminnum, $db_esminfunds, $db_introducemax, $_G;
		if ( $user['id'] )
		{
			$stname		= $_G['gp_stname'];
			$stprice	= $_G['gp_stprice'];
			$stnum		= $_G['gp_stnum'];
			$minprice	= $_G['gp_minprice'];
			$maxprice	= $_G['gp_maxprice'];
			$comintro	= trim($_G['gp_comintro']);
			if ( empty($stname) || strlen($stname) < $db_esnamemin || strlen($stname) > $db_esnamemax )
				showmessage("��Ʊ���Ƴ��Ȳ���С�� $db_esnamemin �ֽڻ��ߴ��� $db_esnamemax �ֽ�");
			if ( empty($stprice) || !is_numeric($stprice) || $stprice < 2 )
				showmessage('����ȷ�����Ʊ���е��ۣ���Ʊ���е��۲���С�� 2 Ԫ');
			$stnum = $stnum * $db_esminnum;
			if ( empty($stnum) || !is_numeric($stnum) || $stnum < $db_esminnum )
				showmessage("����ȷ�����Ʊ������������Ʊ������������С�� " . intval($db_esminnum) . " ��");
			if ( $user['fund_ava'] < $stprice * $stnum )
				showmessage('��û���㹻���ʽ����ʵ�������Ʊ���е����뷢������');
			if ( empty($comintro) || strlen($comintro) < 10 || strlen($comintro) > $db_introducemax )
				showmessage("��˾��鳤��������� 10 �ֽ��� $db_introducemax �ֽ�֮��");
			$rsName = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE stockname='$stname'");
			$rsName && showmessage('������Ĺ�Ʊ�����Ѿ�����');
			$rsaName = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_apply')." WHERE stockname='$stname' AND state<>2");
			$rsaName && showmessage('������Ĺ�Ʊ�����Ѿ�����');
			$capitalisation = $stprice * $stnum;
			DB::query("INSERT INTO ".DB::table('kfsm_apply')." (userid, username, stockname, stockprice, stocknum, surplusnum, capitalisation, comintro, applytime, state) VALUES('$user[id]', '$user[username]', '$stname', $stprice, '$stnum', '$stnum', '$capitalisation', '$comintro', '$_G[timestamp]', '0')");
			DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava-$capitalisation WHERE uid='$user[id]'");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('�û�����', '$user[username]', '��˾ $stname �������У����� " . number_format($stprice,2) . " Ԫ������ $stnum ��', '$_G[timestamp]', '$_G[clientip]')");
			showmessage('���Ĺ�˾�������������ѳɹ��ύ����ȴ�֤����������', $baseScript);
		}
	}
}
?>
