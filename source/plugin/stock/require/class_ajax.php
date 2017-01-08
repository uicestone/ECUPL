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
class Ajax
{
	public function __construct($section)
	{
		method_exists($this,$section) && $this->$section();
	}
	private function esnamecheck()
	{
		global $db_esnamemin, $db_esnamemax, $_G;
		$stname		= $_G['gp_stname'];
		$stnameold	= $_G['p_gstnameold'];
		$msg = '';
		if ( empty($stname) )
		{
			$msg .= "�������Ʊ����";
		}
		else
		{
			$stname = mb_convert_encoding($stname,'gbk','utf-8');
			$stnameold = mb_convert_encoding($stnameold,'gbk','utf-8');
			if ( strlen($stname) < $db_esnamemin )
			{
				$msg .= "��Ʊ���Ƴ��Ȳ���С�� {$db_esnamemin} �ֽ�";
			}
			else if ( strlen($stname) > $db_esnamemax )
			{
				$msg .= "��Ʊ���Ƴ��Ȳ��ܴ��� {$db_esnamemax} �ֽ�";
			}
			else if ( $stname <> $stnameold )
			{
				$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE stockname='$stname'");
				if ( $rs )
				{
					$msg .= '������Ĺ�Ʊ�����Ѿ�����';
				}
				else
				{
					$esrs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_apply')." WHERE stockname='$stname' AND state<>2");
					if ( $esrs )
					{
						$msg .= '������Ĺ�Ʊ�����Ѿ�����';
					}
				}
			}
		}
		if ( !$msg )
		{
			$msg = "��Ʊ����<span class=\"xi1\">{$stname}</span>��ͨ����֤������ʹ��";
		}
		echo $msg;
	}
}
?>
