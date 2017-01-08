<?php
/////////////////////////////////////
//        Examore考试猫插件        //
//当前版本：1.3.110711             //
//使用范围：Discuz! X2 GBK         //
//官方网站：www.examore.com        //
//版权所有(c)2009-2011, Examore.com//
/////////////////////////////////////
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS cdb_examore (
   `i_d` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
   `u_i_d` mediumint(8) unsigned NOT NULL DEFAULT '0',
   `e_a` varchar(500) NOT NULL DEFAULT '',
   `d_t` int(10) unsigned NOT NULL DEFAULT '0',
   PRIMARY KEY (`i_d`)
) TYPE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>