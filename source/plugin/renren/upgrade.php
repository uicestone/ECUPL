<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$fromversion = $_GET[fromversion];
if($fromversion == '2.4') {
$sql=<<<EOF
ALTER TABLE `pre_renren_connect` ADD `per` int(2) NOT NULL;
ALTER TABLE `pre_renren_connect` ADD `feed` text NOT NULL;
EOF;
runquery($sql);
} elseif($fromversion == '2.5') {
$sql=<<<EOF
ALTER TABLE `pre_renren_connect` ADD `feed` text NOT NULL;
EOF;
runquery($sql);
}
$finish = TRUE;
?>