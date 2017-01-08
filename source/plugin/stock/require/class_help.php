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
class Help
{
	public function __construct()
	{
		global $baseScript, $kfsclass, $hkimg, $htype, $_G, $db_smname, $db_wavemax, $db_tradenummin, $db_dutyrate, $db_dutymin;
		$htype = $_G['gp_htype'];
		$db_dutymin = number_format($db_dutymin,2);
		include template('stock:help');
	}
}
?>
