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
$sql = <<<EOF
DROP TABLE IF EXISTS pre_kfsm_apply;
DROP TABLE IF EXISTS pre_kfsm_customer;
DROP TABLE IF EXISTS pre_kfsm_deal;
DROP TABLE IF EXISTS pre_kfsm_news;
DROP TABLE IF EXISTS pre_kfsm_sminfo;
DROP TABLE IF EXISTS pre_kfsm_smlog;
DROP TABLE IF EXISTS pre_kfsm_stock;
DROP TABLE IF EXISTS pre_kfsm_transaction;
DROP TABLE IF EXISTS pre_kfsm_user;
EOF;
runquery($sql);
$finish = TRUE;
?>
