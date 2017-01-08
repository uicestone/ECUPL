<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: install.php 20657 2009-11-17 08:48:36Z Ted $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `cdb_dotavs` (
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `maxgamers` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `gametime` int(10) unsigned NOT NULL DEFAULT '0',
  `polltime` int(10) unsigned NOT NULL DEFAULT '0',
  `starterid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `redscore` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `bluescore` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

CREATE TABLE IF NOT EXISTS `cdb_dotavs_gamers` (
  `attendtime` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `side` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `rand` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attendtime`)
) ENGINE=MyISAM DEFAULT CHARSET=gbk;

EOF;

runquery($sql);

$finish = TRUE;

?>