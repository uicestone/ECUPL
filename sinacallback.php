<?php

define('APPTYPEID', 0);
define('CURSCRIPT', 'SINAT');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();

@session_start();
include_once(DISCUZ_ROOT.'/source/plugin/dsc/config.php');
include_once(DISCUZ_ROOT.'/source/plugin/dsc/weibooauth.php');
include_once(DISCUZ_ROOT.'/data/plugindata/dsc.lang.php');
include_once(DISCUZ_ROOT.'/source/discuz_version.php');

$xver = DISCUZ_VERSION;
$xver = preg_replace('/(X|R|C)/im','',$xver);

loaducenter();
$discuz->cachelist = $cachelist;
$discuz->init();
$lang = $templatelang['dsc'];
$ac = $_G['gp_act'];

if($ac == 'slogin') {
	$o = new WeiboOAuth($akey,$skey);
	$keys = $o->getRequestToken();
	$_SESSION['keys'] = $keys;
	$aurl = $o->getAuthorizeURL($keys['oauth_token'] ,false ,$_G['siteurl'].'sinacallback.php?act=login&refer='.urlencode(dreferer()));
	header("HTTP/1.1 301 Moved Permanently");
	dheader("location: ".str_replace('&amp;', '&', $aurl));
}

//获取微博信息
$o = new WeiboOAuth($akey,$skey, $_SESSION['keys']['oauth_token'],$_SESSION['keys']['oauth_token_secret']);
$last_key = $o->getAccessToken($_REQUEST['oauth_verifier']);
$_SESSION['last_key'] = $last_key;
$c = new WeiboClient($akey,$skey,$_SESSION['last_key']['oauth_token'],$_SESSION['last_key']['oauth_token_secret']);
$sinamember = $c->verify_credentials();
//print_r($sinamember); //debug
foreach($sinamember as $key => $value){
	$sinamember[$key] = diconv($value,'UTF-8');	
}

$username = $sinamember['screen_name'];
$domain = $sinamember['domain'];
$error = $sinamember['error'];

//注册流程
if($ac == 'login'){
	if($_G['uid']) {
		$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
		showmessage('login_succeed', 'forum.php', array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['uid']));
	}
	if($username){
		//判断已经绑定的domain
		
		if($domain){
			require_once libfile('function/misc');
			if($xver > 1){
				require_once libfile('function/member');
			} else {
				require_once libfile('function/login');	
			}
			
		
			$uid = DB::result_first("SELECT uid FROM ".DB::table('sinaconnect')." WHERE domain = '$domain'");
			$uid = $uid ? $uid : 0;
			
			if($uid > 0){
				$member = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid = $uid");
				if(is_array($member) && $member['username']){
					setloginstatus($member, $_G['gp_cookietime'] ? 2592000 : 0);
					DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='$_G[uid]'");
					$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
		
					include_once libfile('function/stat');
					updatestat('login');
					updatecreditbyaction('daylogin', $_G['uid']);
					checkusergroup($_G['uid']);
					$_G['gp_refer'] = $_G['gp_refer'] ? $_G['gp_refer'] : 'forum.php';
					showmessage($lang['login_success'],$_G['gp_refer'],array('username' => $member['username']));	
				}
			}
		}
		
		//判断是否已经存在该用户名
		$result = uc_user_checkname($username);
		if($result == '-3'){
			showmessage($lang['exist_username'],NULL,array('username' => $username));
		} elseif($result == '-2') {
			showmessage($lang['exist_bad'],NULL,array('username' => $username));
		} elseif($result == '1') {
			//注册账号的处理
			$password = md5(mt_rand(7,999999));
			$password = substr($password,5,8);
			$email = $domain.'@sina.com.cn';
			$groupinfo = array();
			if($_G['setting']['regverify']) {
				$groupinfo['groupid'] = 8;
			} else {
				$groupinfo = DB::fetch_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=".intval($_G['setting']['initcredits'])." AND ".intval($_G['setting']['initcredits'])."<creditslower LIMIT 1");
			}
			//ip控制
			if($_G['cache']['ipctrl']['ipregctrl']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
						$ctrlip = $ctrlip.'%';
						$_G['setting']['regctrl'] = 72;
						break;
					} else {
						$ctrlip = $_G['clientip'];
					}
				}
			} else {
				$ctrlip = $_G['clientip'];
			}
			if($_G['setting']['regctrl']) {
				$query = DB::query("SELECT ip FROM ".DB::table('common_regip')." WHERE ip LIKE '$ctrlip' AND count='-1' AND dateline>$_G[timestamp]-'".$_G['setting']['regctrl']."'*3600 LIMIT 1");
				if(DB::num_rows($query)) {
					showmessage('register_ctrl', NULL, array('regctrl' => $_G['setting']['regctrl']));
				}
			}
			//注册到UCenter
			$uid = uc_user_register($username, $password, $email, $questionid, $answer, $_G['clientip']);
			if($uid <= 0) {
				if($uid == -1) {
					showmessage('profile_username_illegal');
				} elseif($uid == -2) {
					showmessage('profile_username_protect');
				} elseif($uid == -3) {
					showmessage('profile_username_duplicate');
				} elseif($uid == -4) {
					showmessage('profile_email_illegal');
				} elseif($uid == -5) {
					showmessage('profile_email_domain_illegal');
				} elseif($uid == -6) {
					showmessage('profile_email_duplicate');
				} else {
					showmessage('undefined_action', NULL);
				}
			}
			//检测uid重复
			if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
				showmessage('profile_uid_duplicate', '', array('uid' => $uid));
			}
			//单IP注册限制
			if($_G['setting']['regfloodctrl']) {
				if($regattempts = DB::result_first("SELECT count FROM ".DB::table('common_regip')." WHERE ip='$_G[clientip]' AND count>'0' AND dateline>'$_G[timestamp]'-86400")) {
					if($regattempts >= $_G['setting']['regfloodctrl']) {
						showmessage('register_flood_ctrl', NULL, array('regfloodctrl' => $_G['setting']['regfloodctrl']));
					} else {
						DB::query("UPDATE ".DB::table('common_regip')." SET count=count+1 WHERE ip='$_G[clientip]' AND count>'0'");
					}
				} else {
					DB::query("INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
						VALUES ('$_G[clientip]', '1', '$_G[timestamp]')");
				}
			}
			//插入数据表
			$dzpassword = md5(random(10));
			$init_arr = explode(',', $_G['setting']['initcredits']);
			$userdata = array(
				'uid' => $uid,
				'username' => $username,
				'password' => $dzpassword,
				'email' => $email,
				'adminid' => 0,
				'groupid' => $groupinfo[groupid],
				'regdate' => TIMESTAMP,
				'credits' => $init_arr[0],
				'timeoffset' => 9999
				);
			DB::insert('common_member', $userdata);
			sendpm($uid, $lang['pm_title'], $lang['pm_content'].$password, 0);
			DB::insert('sinaconnect', array('uid' => $uid,'domain' => $domain,'dateline' => TIMESTAMP));
			$status_data = array(
				'uid' => $uid,
				'regip' => $_G['clientip'],
				'lastip' => $_G['clientip'],
				'lastvisit' => TIMESTAMP,
				'lastactivity' => TIMESTAMP,
				'lastpost' => 0,
				'lastsendmail' => 0,
				);
			DB::insert('common_member_status', $status_data);
			$profile['uid'] = $uid;
			DB::insert('common_member_profile', $profile);
			DB::insert('common_member_field_forum', array('uid' => $uid));
			DB::insert('common_member_field_home', array('uid' => $uid));
			//初始化积分
			$count_data = array(
				'uid' => $uid,
				'extcredits1' => $init_arr[1],
				'extcredits2' => $init_arr[2],
				'extcredits3' => $init_arr[3],
				'extcredits4' => $init_arr[4],
				'extcredits5' => $init_arr[5],
				'extcredits6' => $init_arr[6],
				'extcredits7' => $init_arr[7],
				'extcredits8' => $init_arr[8]
				);
			DB::insert('common_member_count', $count_data);
			manyoulog('user', $uid, 'add');
			//更新最新注册
			$totalmembers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
			$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $username);
			//更新缓存
			save_syscache('userstats', $userstats);
			
			if($_G['setting']['regctrl'] || $_G['setting']['regfloodctrl']) {
				DB::query("DELETE FROM ".DB::table('common_regip')." WHERE dateline<='$_G[timestamp]'-".($_G['setting']['regctrl'] > 72 ? $_G['setting']['regctrl'] : 72)."*3600", 'UNBUFFERED');
				if($_G['setting']['regctrl']) {
					DB::query("INSERT INTO ".DB::table('common_regip')." (ip, count, dateline)
						VALUES ('$_G[clientip]', '-1', '$_G[timestamp]')");
				}
			}
			//审核理由
	//		$regmessage = dhtmlspecialchars($_G['gp_regmessage']);
	//		if($_G['setting']['regverify'] == 2) {
	//			DB::query("REPLACE INTO ".DB::table('common_member_validate')." (uid, submitdate, moddate, admin, submittimes, status, message, remark)
	//				VALUES ('$uid', '$_G[timestamp]', '0', '', '1', '0', '$regmessage', '')");
	//		}
			//更新session
			$_G['uid'] = $uid;
			$_G['username'] = $username;
			$_G['member']['username'] = dstripslashes($_G['username']);
			$_G['member']['password'] = $dzpassword;
			$_G['groupid'] = $groupinfo['groupid'];
			include_once libfile('function/stat');
			updatestat('register');
			
			$_CORE = & discuz_core::instance();
			$_CORE->session->set('uid', $uid);
			$_CORE->session->set('username', $username);
			//创建cookie
			dsetcookie('auth', authcode("{$_G['member']['password']}\t$_G[uid]", 'ENCODE'), 2592000, 1, true);
			//欢迎信息
			if($welcomemsg && !empty($welcomemsgtxt)) {
				$welcomtitle = !empty($_G['setting']['welcomemsgtitle']) ? $_G['setting']['welcomemsgtitle'] : "Welcome to ".$_G['setting']['bbname']."!";
				$welcomtitle = addslashes(replacesitevar($welcomtitle));
				$welcomemsgtxt = addslashes(replacesitevar($welcomemsgtxt));
				if($welcomemsg == 1) {
					sendpm($uid, $welcomtitle, $welcomemsgtxt, 0);
				} elseif($welcomemsg == 2) {
					sendmail("$username <$email>", $welcomtitle, $welcomemsgtxt);
				}
			}
			showmessage($lang['exist_success'],$_G['gp_refer'],array('username' => $username));	
		}
	} else {
		showmessage($lang['oauth_error'],NULL);
	}
} elseif($ac == 'bind') {
	if($username){
		$olduid = DB::result_first("SELECT uid FROM ".DB::table('sinaconnect')." WHERE domain = '$domain'");
		if($olduid && $olduid != $_G['uid']){
			showmessage($lang['domain_bined'],NULL,array('username' => $username));	
		}
		DB::insert('sinaconnect', array('uid' => $_G['uid'],'domain' => $domain,'dateline' => TIMESTAMP),1,1);
		showmessage($lang['domain_bined_success'],$_G['gp_refer'],array('username' => $username));
	} else {
		showmessage($lang['oauth_error'],NULL);
	}	
}
?>
