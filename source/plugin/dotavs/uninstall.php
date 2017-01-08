<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE cdb_dotavs;
DROP TABLE cdb_dotavs_gamers;

EOF;

runquery($sql);

$finish = TRUE;

?>