<?php
/////////////////////////////////////
//        Examore����è���        //
//��ǰ�汾��1.3.110711             //
//ʹ�÷�Χ��Discuz! X2 GBK         //
//�ٷ���վ��www.examore.com        //
//��Ȩ����(c)2009-2011, Examore.com//
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