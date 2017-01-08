<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once DISCUZ_ROOT.'./config/config_ucenter.php';
$sql = <<< SQL
DROP TABLE IF EXISTS `pre_renren_connect`;
CREATE TABLE IF NOT EXISTS `pre_renren_connect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dz_uid` int(10) unsigned NOT NULL,
  `rr_uid` int(10) unsigned NOT NULL,
  `tag` int(2) unsigned NOT NULL,
  `bind_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `per` int(10) unsigned NOT NULL,
  `feed` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dz_uid` (`dz_uid`),
  KEY `rr_uid` (`rr_uid`,`tag`)
) ENGINE=MyISAM;
SQL;

if(submitcheck('installsubmit', 1)) {
	if(submitcheck('installsj', 1)) {
		runquery($sql);
	}
	$finish = TRUE;
} else {
	$html = "<form action=\"$BASESCRIPT?action=plugins&operation=$operation&dir=$dir&installtype=$installtype&instype=$instype&formhash=$formhash&installsubmit=yes&installsj=yes\" method=\"post\">".
		'<label></label><input type="submit" class="btn" value="&#20840;&#26032;&#21019;&#24314;&#20154;&#20154;&#36830;&#25509;&#25968;&#25454;&#34920;" /></form>'.
		'<br /><br />'.
		"<form action=\"$BASESCRIPT?action=plugins&operation=$operation&dir=$dir&installtype=$installtype&instype=$instype&formhash=$formhash&installsubmit=yes\" method=\"post\">".
		'<input type="submit" class="btn" value="&#20445;&#30041;&#20197;&#21069;&#30340;&#20154;&#20154;&#36830;&#25509;&#25968;&#25454;&#34920;" /></form>';

	cpmsg($html);
}
?>