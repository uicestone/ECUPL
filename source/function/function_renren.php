<?php
function mkdirs($path, $mode = 0777) {
	$dirs = explode('/',$path);
	$pos = strrpos($path, ".");
	if ($pos === false) { // note: three equal signs
	// not found, means path ends in a dir not file
	$subamount=0;
	}
	else {
	$subamount=1;
	}
	
	for ($c=0;$c < count($dirs) - $subamount; $c++) {
		$thispath="";
		for ($cc=0; $cc <= $c; $cc++) {
		$thispath.=$dirs[$cc].'/';
		}
		if (!file_exists($thispath)) {
		//print "$thispath<br>";
		mkdir($thispath,$mode);
		}
	}
}


function upload_avatar($uid, $faceTempPath) {
	require './source/plugin/renren/fsockopenHttp.class.php';
	$http = new fsockopenHttp();
	$postdata = _createUCAvatarPostdata($faceTempPath);
	$http->setUrl( _createUCUrl($uid) );
	$http->setData( $postdata );
	$response = $http->request('post');
	$code = (int)$http->getState();
	if($code != 200){
		showmessage('同步头像错误','home.php?mod=spacecp&ac=plugin&id=renren:spacecp',1);
	}

}

function _createUCAvatarPostdata($faceTempPath){
		$postdata = array();
		require './source/plugin/renren/imageEncoder.class.php';
		$imageEncoder = new imageEncoder();
		foreach ( $faceTempPath as $key => $face ){
			$content = file_get_contents($face);
			if(empty($content)){
				break;
			}
			$postkey = 'avatar'. $key;
			$postdata[$postkey] = $imageEncoder->flashdata_encode($content);
		}
		$imageEncoder = null;

		return $postdata;
}
function _createUCUrl($uid){
		//最关键的input！必须使用dz函数authcode，并且必须使用Discuz!和UC之间的通讯密钥！
		$ucinput = authcode( 'uid='. $uid
						. '&agent='. md5($_SERVER['HTTP_USER_AGENT'])
						. '&time='. time() , 
						'ENCODE', UC_KEY );

		//PHP4没有http_build_query，只好.......
		$posturl = UC_API.'/index.php?m=user'
					. '&a=rectavatar'
					. '&inajax=1'
					. '&appid='. UC_APPID
					. '&agent='. urlencode( md5($_SERVER['HTTP_USER_AGENT']) )
					. '&input='. urlencode($ucinput)
		;
		
		return $posturl;
}

function rr_register($rr_uid,$dz_username,$email,$ifavatar,$password) {
	define('NOROBOT', TRUE);
	loaducenter();
	global $_G;
	loadcache('plugin');
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}

	preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	unset($onlineipmatches);
	
	require_once libfile('class/renren_api');
	
	$api = & renren_api::instance();
	$user_info = $api->get_user_info();
	$username=$dz_username;
	if(empty($password)){
	$password = md5(random(20));
	}
	$questionid = '';
	$answer = '';
	if($email==''){
	$email = "$rr_uid@renren.com";}
	$uid = uc_user_register($username, $password, $email, $questionid, $answer, $onlineip);
	
	if($uid <= 0) {
			if($uid == -1) {
				showmessage('profile_username_illegal',dreferer());
			} elseif($uid == -2) {
				showmessage('profile_username_protect',dreferer(),3);
			} elseif($uid == -3) {
					showmessage('profile_username_duplicate',dreferer(),3);
			} elseif($uid == -4) {
				showmessage('profile_email_illegal',dreferer(),3);
			} elseif($uid == -5) {
				showmessage('profile_email_domain_illegal',dreferer(),3);
			} elseif($uid == -6) {
				showmessage('profile_email_duplicate',dreferer(),3);
			} else {
				showmessage('undefined_action', NULL, 'HALTED');
			}
	}
	 
	if(DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
		uc_user_delete($uid);
		showmessage('profile_uid_duplicate');
	}
	global $_G;
	loadcache('plugin');
	$groupid=intval($_G['cache']['plugin']['renren']['rr_groupid']);
	
	$userdata = array(
		'uid' => $uid,
		'username' => $username,
		'password' => $password,
		'email' => $email,
		'adminid' => 0,
		'groupid' => $groupid,
		'regdate' => TIMESTAMP,
		'timeoffset' => 9999
		);
	DB::insert('common_member', $userdata);
	DB::insert('common_setting', array('skey' => 'lastmember', 'svalue' => $username), false, true);
	$totalmembers = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member'));
	$userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $username);
	loadcache('setting', true);
	$_G['setting']['lastmember'] = $username;
	save_syscache('setting', $_G['setting']);

	rr_bind($rr_uid, $uid, 0);
	
	$status_data = array(
		'uid' => $uid,
		'regip' => $onlineip,
		'lastip' => $onlineip,
		'lastvisit' => TIMESTAMP,
		'lastactivity' => TIMESTAMP,
		'lastpost' => 0,
		'lastsendmail' => 0,
		);
	DB::insert('common_member_status', $status_data);
	
	$sex = $user_info->sex;
	if(isset($sex)) {
		$sex = intval($sex) > 0 ? '1':'2';
	} else {
		$sex = '0';
	}
	
	list($birthyear, $birthmonth, $birthday) =	explode('-', $user_info->birthday);
	
	$hometown = $user_info->hometown_location;
	$birthprovince = $hometown->province;
	$city = $hometown->city;
	$company = $user_info->work_history[0]->company_name;
	$graduateschool = $user_info->university_history[0]->name;

	$birthprovince = mb_convert_encoding($birthprovince, "GBK", "UTF8");
	$city = mb_convert_encoding($city, "GBK", "UTF8");
	$company = mb_convert_encoding($company, "GBK", "UTF8");
	$graduateschool = mb_convert_encoding($graduateschool, "GBK", "UTF8");
	$username = $user_info->name;
	$username = mb_convert_encoding($username,"GBK","UTF8");
	$profile = array('uid' => $uid,
					 'realname' => $username,
					 'gender' => $sex,
					 'birthyear'=> $birthyear,
					 'birthmonth'=>$birthmonth,
					 'birthday'=>$birthday,
					 'resideprovince'=>$birthprovince,
					 'residecity'=>$city,
					 'company'=>$company,
					 'graduateschool'=>$graduateschool);
	
	DB::insert('common_member_profile', $profile,false, true);
	DB::insert('common_member_field_forum', array('uid' => $uid));
	DB::insert('common_member_field_home', array('uid' => $uid));
	$init_arr = explode(',', $_G['setting']['initcredits']);
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
	
	if($_G['cache']['plugin']['renren']['rr_ava']==1 && $ifavatar==1){
		$user_info = $api->get_user_info();
		$avatar = array('tinyurl'=>'', 
						'headurl'=>'', 
						'mainurl'=>'',
						);
		foreach(array_keys($avatar) as $k) {
			$avatar[$k] = $user_info->$k;
		}
		$faceTempPath[1]=$avatar['tinyurl'];
		$faceTempPath[2]=$avatar['headurl'];
		$faceTempPath[3]=$avatar['mainurl'];
		upload_avatar($uid, $faceTempPath);
	}
	$welcomtitle = "欢迎注册";
	$welcomemsgtxt = "欢迎通过人人连接登陆本论坛，您的初始密码为".$password."注意妥善保管";
	sendpm($uid, $welcomtitle, $welcomemsgtxt, 0);

	return $uid;
}

function rr_bind($rr_uid, $dz_uid, $tag=0) {
	$data = array('rr_uid' => $rr_uid,
				  'dz_uid' => $dz_uid,
				  'tag'	   => $tag);
	$data = daddslashes($data);
	return DB::insert('renren_connect', $data);
}

function rr_make_bind($dz_uid) {
	global $_G;
	if(empty($dz_uid))
		$dz_uid = $_G['uid'];
	
	$rr_uid = rr_get_cookie('user');
	
	if(!empty($rr_uid))
		rr_bind($rr_uid, $dz_uid, 1);
}

function rr_unbind() {
	global $_G;
	$dz_uid = $_G['uid'];
	$condition = "dz_uid = $dz_uid";
	$query = DB::query("SELECT * FROM ".DB::table('renren_connect')." WHERE dz_uid='$dz_uid'");
	$member = DB::fetch($query);
	if($member['tag']==1){
		return @DB::delete('renren_connect', $condition);
	}
	elseif($member['tag']==0){
		return @DB::update('renren_connect', array('tag'=>2), $condition);
	}
}

function rr_is_bind() {
	global $_G;

	if(empty($_G['renren'])) {
		$dz_uid = $_G['uid'];
		$query  = DB::query("SELECT * FROM ".DB::table('renren_connect')." WHERE dz_uid='$dz_uid'");
		$member = DB::fetch($query);
	} else {
		$rr_uid = rr_get_rr_uid();
		$query  = DB::query("SELECT * FROM ".DB::table('renren_connect')." WHERE rr_uid='$rr_uid'");
		$member = DB::fetch($query);
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE uid='$member[dz_uid]'");
	$query = DB::fetch($query);
	$isbind['tag'] = $member['tag'];
	$isbind['username'] = $query['username'];
	$isbind['uid'] = $query['uid'];
	if(!empty($member) && intval($member['tag'])!=2){
		$isbind['bing']=true;
		return $isbind;
	}
	else{
		$isbind['bing']=false;
		return $isbind;
	}
}

function rbing($dz_uid){
	$condition = "dz_uid = $dz_uid";
	return @DB::update('renren_connect', array('tag'=>0), $condition);
}


function rr_get_bind_dz_uid($rr_uid) {
	return DB::result_first("SELECT dz_uid FROM ".DB::table('renren_connect')." WHERE rr_uid='$rr_uid' ORDER BY tag DESC");
}

function rr_get_bind_rr_uid($dz_uid) {
	return DB::result_first("SELECT dz_uid FROM ".DB::table('renren_connect')." WHERE dz_uid='$dz_uid' ORDER BY tag DESC");
}

function rr_get_cookie($key) {
	global $_G;
	loadcache('plugin');
	$api_key = $_G['cache']['plugin']['renren']['api_key'];
	return getgpc($api_key.'_'.$key,'C');
}

function rr_get_session($key) {
	global $_G;
	return $_G['renren']['session'][$key];
}

function rr_get_dz_uid() {
	global $_G;
	return $_G['renren']['dz_uid'];
}

function rr_get_rr_uid() {
	global $_G;
	return $_G['renren']['rr_uid'];
}

function rr_generate_sig($params, $secret) {
	ksort($params);
	$sig = '';
	foreach($params as $key=>$value) {
		$sig .= "$key=$value";
	}
	
	$sig .= $secret;
	
	return md5($sig);
}
function get_rruid($uid){
	return DB::result_first("SELECT rr_uid FROM ".DB::table('renren_connect')." WHERE dz_uid='$uid' and tag!=2");
	}
function get_dzuid($uid){
	return DB::result_first("SELECT dz_uid FROM ".DB::table('renren_connect')." WHERE rr_uid='$uid' and tag!=2");
	}
function get_per($uid){
	return DB::result_first("SELECT per FROM ".DB::table('renren_connect')." WHERE dz_uid='$uid' and tag!=2");
	}
function set_per($per){
	global $_G;
	$condition = "dz_uid = $_G[uid]";
	return @DB::update('renren_connect', array('per'=>$per), $condition);
	}
function getpostsubject($tid){
	return DB::result_first("SELECT subject FROM ".DB::table('forum_post')." WHERE tid='$tid'");
}

function get_rrfeed($uid){
	$feed_str = DB::result_first("SELECT feed FROM ".DB::table('renren_connect')." WHERE dz_uid='$uid' and tag!=2");
	$feed = explode(",",$feed_str);
	return $feed;
}
function set_rrfeed($feed){
	global $_G;
	$condition = "dz_uid = $_G[uid]";
	return @DB::update('renren_connect', array('feed'=>$feed), $condition);
	}

?>
