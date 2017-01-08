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
$version = '9.5.4';
$sql = <<<EOF
DROP TABLE IF EXISTS pre_kfsm_apply;
CREATE TABLE pre_kfsm_apply (
  aid smallint(6) NOT NULL AUTO_INCREMENT,
  sid smallint(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stockname varchar(20) NOT NULL DEFAULT '',
  userid int(10) NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  stockprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  stocknum int(20) unsigned NOT NULL DEFAULT '0',
  surplusnum int(20) unsigned NOT NULL DEFAULT '0',
  capitalisation decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  comphoto varchar(20) NOT NULL DEFAULT '0.jpg',
  comintro varchar(255) NOT NULL DEFAULT '',
  applytime int(10) unsigned NOT NULL DEFAULT '0',
  issuetime int(10) unsigned NOT NULL DEFAULT '0',
  state tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (aid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_customer;
CREATE TABLE pre_kfsm_customer (
  cid mediumint(8) unsigned zerofill NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(20) NOT NULL DEFAULT '',
  sid smallint(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stocknum_ava int(20) unsigned NOT NULL DEFAULT '0',
  stocknum_war int(20) unsigned NOT NULL DEFAULT '0',
  buyprice decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  averageprice decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  buytime int(10) unsigned NOT NULL DEFAULT '0',
  selltime int(10) unsigned NOT NULL DEFAULT '0',
  ip VARCHAR( 20 ) NOT NULL DEFAULT '',
  KEY cid (cid,uid,sid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_deal;
CREATE TABLE pre_kfsm_deal (
  did smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  sid int(6) unsigned zerofill NOT NULL DEFAULT '000000',
  price_deal decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_deal int(10) unsigned NOT NULL DEFAULT '0',
  time_deal int(10) unsigned NOT NULL DEFAULT '0',
  price_tran decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant_tran int(10) unsigned NOT NULL DEFAULT '0',
  time_tran int(10) unsigned NOT NULL DEFAULT '0',
  direction tinyint(1) NOT NULL DEFAULT '0',
  ok tinyint(1) NOT NULL DEFAULT '0',
  hide tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (did)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_news;
CREATE TABLE pre_kfsm_news (
  nid smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL DEFAULT '',
  content mediumtext,
  color char(6) NOT NULL DEFAULT '',
  author varchar(15) NOT NULL DEFAULT '',
  addtime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (nid)
) ENGINE=MyISAM;

INSERT INTO pre_kfsm_news (subject, content, color, author, addtime) VALUES ('��ӭʹ�� Kilofox StockIns V{$version} for Discuz! X2', '��ӭʹ��ǧ�� StockIns �������ϵͳ��\nStockIns ��һ�������������˼���д�� PHP �������רΪ�������� PHP ��̳�������Ĳ����Ʒ���ð汾Ϊ Discuz! ����档\n��ø�����Ѷ��������ע�ٷ���վ����[url=http://www.kilofox.net]Kilofox.Net[/url]', '', 'Kilofox.Net', '{$_G[timestamp]}');


DROP TABLE IF EXISTS pre_kfsm_sminfo;
CREATE TABLE pre_kfsm_sminfo (
  id smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  todaybuy int(20) unsigned NOT NULL DEFAULT '0',
  todaysell int(20) unsigned NOT NULL DEFAULT '0',
  todaytotal decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  todaydate int(10) NOT NULL DEFAULT '0',
  ain_y decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  ain_t decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  stampduty decimal(14,3) unsigned NOT NULL DEFAULT '0.000',
  KEY id (id)
) ENGINE=MyISAM;

INSERT INTO pre_kfsm_sminfo (todaybuy, todaysell, todaytotal, todaydate, ain_y, ain_t, stampduty) VALUES(0, 0, 0, '{$_G[timestamp]}',1000, 1000, 0.000);


DROP TABLE IF EXISTS pre_kfsm_smlog;
CREATE TABLE pre_kfsm_smlog (
  id int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL DEFAULT '',
  username1 varchar(20) NOT NULL DEFAULT '',
  username2 varchar(20) NOT NULL DEFAULT '',
  field varchar(20) NOT NULL DEFAULT '',
  descrip varchar(80) NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_stock;
CREATE TABLE pre_kfsm_stock (
  sid smallint(6) unsigned zerofill NOT NULL AUTO_INCREMENT,
  stockname varchar(20) NOT NULL DEFAULT '',
  openprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  currprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  lowprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  highprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  todaywave decimal(6,2) NOT NULL DEFAULT '0.00',
  totalwave decimal(6,2) NOT NULL DEFAULT '0.00',
  todaybuynum int(20) unsigned NOT NULL DEFAULT '0',
  todaysellnum int(20) unsigned NOT NULL DEFAULT '0',
  todaytradenum int(20) unsigned NOT NULL DEFAULT '0',
  issueprice decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  issuenum int(20) unsigned NOT NULL DEFAULT '0',
  issuer_id mediumint(8) NOT NULL DEFAULT '0',
  issuer_name varchar(20) NOT NULL DEFAULT '',
  holder_id mediumint(8) NOT NULL DEFAULT '0',
  holder_name varchar(20) NOT NULL DEFAULT '',
  issuetime int(10) unsigned NOT NULL DEFAULT '0',
  comphoto varchar(20) NOT NULL DEFAULT '0.jpg',
  comintro varchar(255) NOT NULL DEFAULT '',
  pricedata varchar(255) NOT NULL DEFAULT '',
  state tinyint(1) NOT NULL DEFAULT '0',
  cid int(6) NOT NULL DEFAULT '0',
  uptime int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (sid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_transaction;
CREATE TABLE pre_kfsm_transaction (
  tid int(10) unsigned NOT NULL AUTO_INCREMENT,
  sid int(6) unsigned zerofill NOT NULL DEFAULT '000000',
  stockname varchar(20) NOT NULL DEFAULT '',
  direction tinyint(1) unsigned NOT NULL DEFAULT '0',
  did int(10) unsigned NOT NULL DEFAULT '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  price decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  quant int(10) unsigned NOT NULL DEFAULT '0',
  amount decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  ttime int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tid)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS pre_kfsm_user;
CREATE TABLE pre_kfsm_user (
  uid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  forumuid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(20) NOT NULL DEFAULT '',
  fund_ava decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  fund_war decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  asset decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  stocksort smallint(6) unsigned NOT NULL DEFAULT '0',
  stocknum int(20) unsigned NOT NULL DEFAULT '0',
  stockcost decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  stockvalue decimal(14,2) unsigned NOT NULL DEFAULT '0.00',
  todaybuy int(10) unsigned NOT NULL DEFAULT '0',
  todaysell int(10) unsigned NOT NULL DEFAULT '0',
  regtime int(10) unsigned NOT NULL DEFAULT '0',
  lasttradetime int(10) unsigned NOT NULL DEFAULT '0',
  locked boolean NOT NULL DEFAULT '0',
  ip varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

EOF;
runquery($sql);
$finish = TRUE;
?>
