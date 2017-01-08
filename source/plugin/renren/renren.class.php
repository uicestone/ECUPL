<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_renren {

	function sprintfchg($bbname)
	{
		$utf='';
		for ($k=0;$k<strlen($bbname);$k++)
		{
			$utf.=sprintf("%%%02X",ord(substr($bbname,$k,1)));
		}
		return $utf;
	}
	 function chinesesubstr($str,$start,$len) { 
		$strlen=$start+$len; 
		for($i=$start;$i<$len;$i++) { 
			if(ord(substr($str,$i,1))>0xa0) { 
				$tmpstr.=substr($str,$i,2); 
				$i++;
				$len++;
			} 
			else 
				$tmpstr.=substr($str,$i,1); 
		} 
		return $tmpstr; 
	}
	function plugin_renren() {
		global $_G;
		include_once template('renren:rr_module');
	}
	function global_header() {
		global $_G, $metadescription, $navtitle;
		$_G['setting']['seohead'] .= "\n".'<meta property="og:type" content="website" />'.
			"\n".'<meta property="og:site_name" content="'.$_G['setting'][bbname].'" />'.
			"\n".'<meta property="og:description" content="'.(!empty($metadescription)? htmlspecialchars($metadescription):'').'" />';
			if($_G['gp_mod'] != 'viewthread') {
				$_G['setting']['seohead'] .= "\n".'<meta property="og:title" content="'.(!empty($navtitle)? htmlspecialchars($navtitle):'').'" />';
			}
			$_G['setting']['seohead'] .= "\n".'<link href="'.$_G['siteurl'].'source/plugin/renren/template/renren.css" rel="stylesheet" type="text/css" />';
		return null;
	}
	
	function global_footerlink() {
		global $_G;
		if($_G['cache']['plugin']['renren']['api_key']) {
			require_once './source/plugin/renren/renren.function.php';
			require_once './source/plugin/renren/class/connect.class.php';
			$renren = & renren_connect::instance();
			$renren->inits($_G['uid']);
			$isbind = rr_is_bind();
			include template('renren:rr_footer');
			return $return;
		}
	}
	function global_login_extra() {
		global $_G;
		if($_G['cache']['plugin']['renren']['rr_index'])
		{
			return "";
		}
		else
		{
			return tpl_renren_global_login_extra();
		}
	}
	function global_login_text(){
		global $_G;
		if( $_G['uid']){
			return '';
		}
		return  tpl_renren_login_bar();
	}
	//function common() {
	//	global $_G;
	//	
	//	if(!$_G['uid']) {
	//		$_G['setting']['pluginhooks']['global_login_text'] = tpl_renren_login_bar();
	//	}
	//}

	function index_top() {
		global $_G;
		if($_G['cache']['plugin']['renren']['rr_homeid'])
		{
			include template('renren:likeindex');
		}
		return $return;
		
		}
	/**
	 * 登陆后在顶部右上角，未绑定时显示绑定按钮
	 */
	function global_usernav_extra1(){
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		
		//进行绑定查询
		$bind_status = 2;  //2未绑定状态、1绑定状态
		$bind_status_cookiename = 'renren_bind_status'. $_G['uid'];
		if( !isset($_G['cookie'][$bind_status_cookiename]) ){
			//echo 'running db check';  //@todo 用于检测cookies是否起作用
			$rr_uid = DB::result_first('SELECT `rr_uid` FROM '. DB::table('renren_connect'). ' WHERE `dz_uid` = '. $_G['uid'] );
			if( (int)$rr_uid > 0 ){
				$bind_status = 1;
			}
			dsetcookie($bind_status_cookiename, $bind_status, 604800);
		}elseif( $_G['cookie'][$bind_status_cookiename] == 1 ){
			$bind_status = 1;
		}
		
		if(1 == $bind_status){
			return '';
		}else{
			return '<span class="pipe">|</span><a href="'. $_G['siteurl']. '/home.php?mod=spacecp&ac=plugin&id=renren:spacecp" target="_blank"><img style="vertical-align:-5px;" src="source/plugin/renren/template/image/bind_renren.png" /></a>&nbsp;';
		}
	}
}


class plugin_renren_forum extends plugin_renren {
	function index_status_extra() {
		//global $_G;
		//if($_G['cache']['plugin']['renren']['rr_homeid'])
		//{
		//	include template('renren:likeindex');
		//}
		//return $return;
	}

	function viewthread_postfooter_output() {
		global $_G, $postlist;
		if(empty($_G['gp_page']) || $_G['gp_page'] == 1) {
			$i=0;
			foreach($postlist as $key=>$a) {
				if($a['first']) {
					include template('renren:rr_like');
					//include template('renren:rr_share');
					$arr[$i]=$return;
					$i++;
					break;
				} 
			}
			return $arr;
		} else {
			return null;
		}
	}
		 
	function viewthread_sidetop_output() {
		global $_G, $postlist;
		if($_G[tid]) {
			require_once './source/plugin/renren/renren.function.php';
			$i=0;
			foreach($postlist as $key=>$a) {
				$rruid=get_rruid($a['authorid']);
				if($rruid) {
					if($_G['uid']==$a['authorid'])
					{
						$title=lang('plugin/renren', 'renren_bind_profile_self');
						$arr[$i] = '<p><a href="http://www.renren.com/profile.do?id=' .$rruid. '" title='.$title.' target="_blank"><img  src="source/plugin/renren/template/image/rr.png" /></a></p>';
					}
					else
					{
						$title=lang('plugin/renren', 'renren_bind_profile_other');
						$arr[$i] = '<p><a href="plugin.php?id=renren:connect&uid=' .$a['authorid']. '" title='.$title.' target="_blank"><img  src="source/plugin/renren/template/image/rr.png" /></a></p>';
					}
					
					$i++;
				} else {
					if($_G['uid']==$a['authorid'])
					{
						$title=lang('plugin/renren', 'renren_bind_self');
						$arr[$i] = '<p><a  href="'. $_G['siteurl']. '/home.php?mod=spacecp&ac=plugin&id=renren:spacecp" title='.$title.' target="_blank"><img    src="source/plugin/renren/template/image/norr.png" /></a></p>';
					}
					else
					{
						$title=lang('plugin/renren', 'renren_bind_not');
						$arr[$i] = '<p><img  title='.$title.'  src="source/plugin/renren/template/image/norr.png" /></p>';
					}
					
					$i++;
				}
			}
			return $arr;
		}
	}
	/**
	 * 分享到按钮位置
	 * @return string
	 */
	function viewthread_useraction_output(){
		global $_G, $postlist;
		$return = '';
		if(empty($_G['gp_page']) || $_G['gp_page'] == 1) {
			$query = "SELECT * FROM ".DB::table('forum_post')." WHERE tid='$_G[tid]'";
			$query = DB::query($query);
			while($post = DB::fetch($query)) {
				if(($onlyauthoradd && $post['anonymous'] == 0) || !$onlyauthoradd) {
					if($post['first']) {
						$_G['forum_firstpid'] = $post['pid'];

						$pos=strpos($post['message'],'[img]'.$_G['siteurl'].'xwb/images');
						if($pos>0)
							$postmessage=substr($post['message'],0,$pos);
						else
							$postmessage=$post['message'];

						$metadescription = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($postmessage), 160));
						if(preg_match("/<img.*?src=\"(.*?)\"/",$postmessage, $match)) {
							$img=$match[1];
						} elseif(preg_match("/<IMG.*?src=\"(.*?)\"/",$postmessage, $match)) {
							$img=$match[1];
						} elseif(preg_match("/\[img\](.*?)\[\/img\]/i", $postmessage, $matchaids)) {
							$img=$matchaids[1];
						} elseif(preg_match("/\[img=([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/img\]/i", $postmessage, $matchaids)) {
							$img=$matchaids[3];
						} elseif($post['attachment'] != 0) {
							$queryAttache = DB::query("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='$post[pid]'");
							while($attache = DB::fetch($queryAttache)) {
								$queryPic = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attache['tableid'])." WHERE pid='$post[pid]' AND (RIGHT(filename,3)='bmp' OR RIGHT(filename,3)='jpg' OR RIGHT(filename,3)='png' OR RIGHT(filename,4)='jpeg')");
								while($pic = DB::fetch($queryPic)) {
									if($pic['remote']) {
										$attachurl = $_G['setting']['ftp']['attachurl'].'forum';
									} else {
										$attachurl = $_G['siteurl'].$_G['setting']['attachurl'].'forum';
									}
									$img = $attachurl.'/'.$pic['attachment'];
								}
							}
						}
						if(!empty($img)) {
							$_G['setting']['seohead'] .= "\n".'<meta property="og:image" content="'.$img.'" />';
						}
						if(preg_match("/\[flash\](.*?)\[\/flash\]/i", $postmessage, $matchflash)) {
							$flash=$matchflash[1];
							$_G['setting']['seohead'] .= "\n".'<meta property="og:videosrc" content="'.$flash.'" />';
						} elseif(preg_match("/\[media=swf,([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/media\]/i", $postmessage, $matchflash)) {
							$flash=$matchflash[3];
							$_G['setting']['seohead'] .= "\n".'<meta property="og:videosrc" content="'.$flash.'" />';
						}
						if(preg_match("/\[audio\](.*?)\[\/audio\]/i", $postmessage, $matchaudio)) {
							$audio=$matchaudio[1];
							$_G['setting']['seohead'] .= "\n".'<meta property="og:audiosrc" content="'.$audio.'" />';
						} elseif(preg_match("/\[media=mp3,([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/media\]/i", $postmessage, $matchaudio)) {
							$audio=$matchaudio[3];
							$_G['setting']['seohead'] .= "\n".'<meta property="og:audiosrc" content="'.$audio.'" />';
						}
					}
				}
			}
			if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
				$r = array(
					'{tid}' => $_G['tid'],
					'{page}' => 1,
					'{prevpage}' => 1,);
				$url = $_G['siteurl'].str_replace(array_keys($r), $r, $_G['setting']['rewriterule']['forum_viewthread']);
			} else {
				$url = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_G['tid'];
			}
			$_G['setting']['seohead'] .= "\n".'<meta property="og:title" content="'.$_G['forum_thread']['subject'].'" />'.
				"\n".'<meta property="og:url" content="'.$url.'" />';
			if(!$metadescription) {
				$metadescription = strip_tags($_G['forum_thread']['subject']);
			}
             require_once './source/plugin/renren/class/api.class.php';
						$feed = & renren_api::instance();
						$rr_share_title=$_G['forum_thread'][subject];
						$rr_share_content=$metadescription;
						if(strtolower($_G['charset']) != 'utf-8') {
								$rr_share_title = $feed->convertEncoding($rr_share_title,"GBK","UTF-8");
								$utf='';
								for ($k=0;$k<strlen($rr_share_title);$k++)
								{
									$utf.=sprintf("%%%02X",ord(substr($rr_share_title,$k,1)));
								}
								$rr_share_title=$utf;
								$rr_share_content = $feed->convertEncoding($rr_share_content,"GBK","UTF-8");
								$utf='';
								for ($k=0;$k<strlen($rr_share_content);$k++)
								{
									$utf.=sprintf("%%%02X",ord(substr($rr_share_content,$k,1)));
								}
								$rr_share_content=$utf;
						}
			include template('renren:share_button_viewthread');
			return $return;
		} else {
			return null;
		}
	}
	function post_middle_output() {
		
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		//foreach($_G as $key => $value)
		//{
		//	echo 'key:'.$key.';value:'.$value.'--------';
		//}
		if($_G['cache']['plugin']['renren']['rr_feed']&&$_G['cache']['plugin']['renren']['rr_feednew'])
		{
			if($_G['renren']['rr_uid']) {
				require_once './source/plugin/renren/renren.function.php';
				$iffeed = get_rrfeed($_G['uid']);
				$perm = 1;
				if($_G['gp_action'] == 'newthread') {
					require_once './source/plugin/renren/class/api.class.php';
					$api = & renren_api::instance();
					$resperm = $api->has_perm('publish_feed');
					$perm = $resperm->result;
				}
				include template('renren:rr_postmiddle');
				return $return;
			}
			else
			{
				include template('renren:rr_postmiddle_def');
				return $return;
			}
		}
	}
	function viewthread_imicons_output()
    {
       global $_G, $postlist;
		if($_G[tid]) {
			require_once './source/plugin/renren/renren.function.php';
			$i=0;
			foreach($postlist as $key=>$a) {
				if(get_rruid($a['authorid'])) {
					$arr[$i] = '<a href="plugin.php?id=renren:connect&uid=' .$a['authorid']. '" target="_blank"><img  src="source/plugin/renren/template/image/rr.png" /></a>';
					$i++;
				} else {
					$arr[$i] = '<a  href="'. $_G['siteurl']. '/home.php?mod=spacecp&ac=plugin&id=renren:spacecp" target="_blank"><img    src="source/plugin/renren/template/image/norr.png" /></a>';
					$i++;
				}
			}
			return $arr;
		}
    }
	function post_to_renren_aftersubmit() {
		$msgforwardSet = (array)@unserialize($GLOBALS['_G']['setting']['msgforward']);
		$msgforwardSet['refreshtime'] = 3;  //只能是大于0的整数
		$msgforwardSet['quick'] = 0;
		$GLOBALS['_G']['setting']['msgforward'] = serialize($msgforwardSet);
	}
	
	function post_to_renren_aftersubmit_output() {
		
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		if($_G['renren']['rr_uid'] && $_G['cache']['plugin']['renren']['rr_pic']) {
			require_once './source/plugin/renren/class/api.class.php';
			require_once './source/plugin/renren/renren.function.php';
			$feed = & renren_api::instance();
			$bbname = $_G['setting']['bbname'];
			$bbname_link ='<a href="'.$_G['siteurl'].'">'.$_G['setting']['bbname'].'</a>';
			if(strtolower($_G['charset']) != 'utf-8') {
				//$bbname=$feed->convertEncoding($bbname,"GBK","UTF-8");
				//$bbname=$this->sprintfchg($bbname);

				$bbname_link=$feed->convertEncoding($bbname_link,"GBK","UTF-8");
				//$bbname_link=$this->sprintfchg($bbname_link);
			}
			$iffeed = get_rrfeed($_G['uid']);
			//假如是发主题贴
			if( $_G['gp_action'] == 'newthread' && (submitcheck('topicsubmit', 0, $GLOBALS['seccodecheck'], $GLOBALS['secqaacheck']) ) && $_POST['rrfeed']) {
				$tid = isset($GLOBALS['tid']) ? (int)$GLOBALS['tid'] : 0;
				$pid = isset($GLOBALS['pid']) ? (int)$GLOBALS['pid'] : 0;
				if( $tid >= 1 && $pid >= 1 ) {
					$subject = (string)$GLOBALS['subject'];
					if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
						$r = array(
							'{tid}' => $tid,
							'{page}' => 1,
							'{prevpage}' => 1,);
						$subjectlink = $_G['siteurl'].str_replace(array_keys($r), $r, $_G['setting']['rewriterule']['forum_viewthread']);
					} else {
						$subjectlink = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
					}
					$subject_link = '<a href="'.$_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid.'">'.$subject.'</a>';
					if(strtolower($_G['charset']) != 'utf-8') {
						$subjectlink = $feed->convertEncoding($subjectlink,"GBK","UTF-8");
						
						$subject_link = $feed->convertEncoding($subject_link,"GBK","UTF-8");
						
					}
					$message = (string)$GLOBALS['message'];
					if(preg_match("/<img.*?src=\"(.*?)\"/",$message, $match)) {
						$img=$match[1];
					} elseif(preg_match("/<IMG.*?src=\"(.*?)\"/",$message, $match)) {
						$img=$match[1];
					} elseif(preg_match("/\[img\](.*?)\[\/img\]/i", $message, $matchaids)) {
						$img=$matchaids[1];
					} elseif(preg_match("/\[img=([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/img\]/i", $message, $matchaids)) {
						$img=$matchaids[3];
					} elseif(!empty($_G['forum_attachexist'])) {
						if($_G['setting']['ftp']['on']) {
							$attachurl = $_G['setting']['ftp']['attachurl'].'forum';
						} else {
							$attachurl = $_G['siteurl'].$_G['setting']['attachurl'].'forum';
						}
						$queryAttache = DB::query("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='$pid'");
						while($attache = DB::fetch($queryAttache)) {
							$queryPic = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attache['tableid'])." WHERE pid='$pid' AND (RIGHT(filename,3)='bmp' OR RIGHT(filename,3)='jpg' OR RIGHT(filename,3)='png' OR RIGHT(filename,4)='jpeg')");
							while($pic = DB::fetch($queryPic)) {
								$img = $attachurl.'/'.$pic['attachment'];
							}
						}
					}
					
					$name=messagecutstr($subject, 30);
					$description = str_replace(array("\r", "\n"), '',messagecutstr(strip_tags($message), 200));
					$url=$subjectlink;
					$image=$img;
					
					$feed_message=lang('plugin/renren', 'rr_spacecp_post_bef').$bbname.lang('plugin/renren', 'rr_spacecp_post');
					$action_name=$this->chinesesubstr($bbname,0,10);
					if(strlen($bbname)!=strlen($action_name))
					{
						$action_name=lang('plugin/renren', 'rr_discuz_site');
					}
					if(strtolower($_G['charset']) != 'utf-8') {
						$name = $feed->convertEncoding($name,"GBK","UTF-8");
						$description = $feed->convertEncoding($description,"GBK","UTF-8");
						$action_name = $feed->convertEncoding($action_name,"GBK","UTF-8");
						$feed_message = $feed->convertEncoding($feed_message,"GBK","UTF-8");
						
					}
					$action_link=$_G['siteurl'];
					$feed->pushcustomfeed($name,$description,$url,$image,$action_name,$action_link,$feed_message);
				}
				return null;

			//假如是发回复
			} elseif( $_G['gp_action'] == 'reply' && (submitcheck('replysubmit', 0, $GLOBALS['seccodecheck'], $GLOBALS['secqaacheck']) ) && ($_POST['rrfeed'] || $iffeed[1])) {
				$tid = isset($GLOBALS['_G']['gp_tid']) ? (int)$GLOBALS['_G']['gp_tid'] : 0;
				$pid = isset($GLOBALS['pid']) ? (int)$GLOBALS['pid'] : 0;
				if( $tid >= 1 && $pid >= 1 ){
					$subject = getpostsubject($tid);
					if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
						$r = array(
							'{tid}' => $tid,
							'{page}' => 1,
							'{prevpage}' => 1,);
						$subjectlink = $_G['siteurl'].str_replace(array_keys($r), $r, $_G['setting']['rewriterule']['forum_viewthread']);
					} else {
						$subjectlink = $_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid;
					}
					$subject_link = '<a href="'.$_G['siteurl'].'forum.php?mod=viewthread&tid='.$tid.'">'.$subject.'</a>';
					if(strtolower($_G['charset']) != 'utf-8') {
						$subjectlink = $feed->convertEncoding($subjectlink,"GBK","UTF-8");
						$subject_link= $feed->convertEncoding($subject_link,"GBK","UTF-8");
					}
					$message = (string)$GLOBALS['message'];
					
					if(preg_match("/<img.*?src=\"(.*?)\"/",$message, $match)) {
						$img=$match[1];
					} elseif(preg_match("/<IMG.*?src=\"(.*?)\"/",$message, $match)) {
						$img=$match[1];
					} elseif(preg_match("/\[img\](.*?)\[\/img\]/i", $message, $matchaids)) {
						$img=$matchaids[1];
						if($img=='static/image/common/back.gif')
						{
							if(!empty($_G['forum_attachexist'])) {
								if($_G['setting']['ftp']['on']) {
									$attachurl = $_G['setting']['ftp']['attachurl'].'forum';
								} else {
									$attachurl = $_G['siteurl'].$_G['setting']['attachurl'].'forum';
								}


								$queryAttache = DB::query("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='$pid'");
								while($attache = DB::fetch($queryAttache)) {
									$queryPic = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attache['tableid'])." WHERE pid='$pid' AND (RIGHT(filename,3)='bmp' OR RIGHT(filename,3)='jpg' OR RIGHT(filename,3)='png' OR RIGHT(filename,4)='jpeg')");
									while($pic = DB::fetch($queryPic)) {
										$img = $attachurl.'/'.$pic['attachment'];
									}
								}
							}
							else
							{
								$img='';
							}
						}
					} elseif(preg_match("/\[img=([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/img\]/i", $message, $matchaids)) {
						$img=$matchaids[3];
					} elseif(!empty($_G['forum_attachexist'])) {
						if($_G['setting']['ftp']['on']) {
							$attachurl = $_G['setting']['ftp']['attachurl'].'forum';
						} else {
							$attachurl = $_G['siteurl'].$_G['setting']['attachurl'].'forum';
						}


						$queryAttache = DB::query("SELECT * FROM ".DB::table('forum_attachment')." WHERE pid='$pid'");
						while($attache = DB::fetch($queryAttache)) {
							$queryPic = DB::query("SELECT * FROM ".DB::table('forum_attachment_'.$attache['tableid'])." WHERE pid='$pid' AND (RIGHT(filename,3)='bmp' OR RIGHT(filename,3)='jpg' OR RIGHT(filename,3)='png' OR RIGHT(filename,4)='jpeg')");
							while($pic = DB::fetch($queryPic)) {
								$img = $attachurl.'/'.$pic['attachment'];
							}
						}
					}

					$name=messagecutstr(strip_tags($subject), 30);
					$description = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($message), 200));
					$url=$subjectlink;
					$image=$img;
					if($image=='static/image/common/back.gif')
						{
							$image='';
						}
					//$action_name=lang('plugin/renren', 'rr_discuz_site');
					$action_name=$this->chinesesubstr($bbname,0,10);
					if(strlen($bbname)!=strlen($action_name))
					{
						$action_name=lang('plugin/renren', 'rr_discuz_site');
					}
					$action_link=$_G['siteurl'];
					$feed_message=lang('plugin/renren', 'rr_spacecp_reply_bef').$bbname.lang('plugin/renren', 'rr_spacecp_reply');
					if(strtolower($_G['charset']) != 'utf-8') {
						$name = $feed->convertEncoding($name,"GBK","UTF-8");
						$description = $feed->convertEncoding($description,"GBK","UTF-8");
						$action_name = $feed->convertEncoding($action_name,"GBK","UTF-8");
						$feed_message = $feed->convertEncoding($feed_message,"GBK","UTF-8");
					}
					$feed->pushcustomfeed($name,$description,$url,$image,$action_name,$action_link,$feed_message);
				}
				return null;

			//上述都不是，则什么都没有发生
			} else {
				return null;
			}
		}
	}
}

class plugin_renren_member extends plugin_renren {

	function logging_method() {
		return tpl_renren_login_bar();
	}

	function register_logging_method() {
		return tpl_renren_login_bar();
	}

}
/**
 * 群组group钩子，继承论坛forum钩子
 *
 */
class plugin_renren_group extends plugin_renren_forum{
	
}
class plugin_renren_home extends plugin_renren {
	

	 //家园－记录页面：显示“记录同步到人人网”的设置
	function space_doing_bottom(){
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		if($_G['cache']['plugin']['renren']['rr_feed']&&$_G['cache']['plugin']['renren']['rr_statusnew'])
		{
			require_once './source/plugin/renren/class/connect.class.php';
			$renren = & renren_connect::instance();
			$renren->inits($discuz_uid);
			if($_G['renren']['rr_uid']){
				require_once './source/plugin/renren/renren.function.php';
				$iffeed = get_rrfeed($_G['uid']);
				$perm = 0;
					//require_once './source/plugin/renren/class/api.class.php';
					//$api = & renren_api::instance();
					//$resperm = $api->has_perm('status_update');
					//$perm = $resperm->result;
				//echo "<script>alert('$perm');</script>";
				include template('renren:spacecp_newdoing');
				return $return;
			}else{
				include template('renren:spacecp_newdoing_def');
				return $return;
			}
		}
	}
//同步记录到人人网状态按钮位置
	function  space_home_doing_sync_method(){
		
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		if($_G['cache']['plugin']['renren']['rr_feed']&&$_G['cache']['plugin']['renren']['rr_statusnew'])
		{
			
			require_once './source/plugin/renren/class/connect.class.php';
			$renren = & renren_connect::instance();
			$renren->inits($discuz_uid);
			if($_G['renren']['rr_uid']){
				require_once './source/plugin/renren/renren.function.php';
				$iffeed = get_rrfeed($_G['uid']);
				$perm = 0;
					//require_once './source/plugin/renren/class/api.class.php';
					//$api = & renren_api::instance();
					//$resperm = $api->has_perm('status_update');
					//$perm = $resperm->result;
				//echo "<script>alert('$perm');</script>";
				include template('renren:spacecp_newrecord');
				return $return;
			}else{
				include template('renren:spacecp_newrecord_def');
				return $return;
			}
		}
	}
	
	//家园－记录：发表截获：同步记录到人人网状态
	function spacecp_doing_aftersubmit_message($param){
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		if( $_G['renren']['rr_uid']&&getgpc('addsubmit') && isset($param['param'][2]['doid']) && $param['param'][2]['doid'] > 0&&substr($param['param'][0], -8) =='_success'&&$_POST['rrfeed'])
		{
			
			$newdoid = isset($GLOBALS['newdoid']) ? (int)$GLOBALS['newdoid'] : 0;
			$message = !empty($GLOBALS['message']) ? (string)$GLOBALS['message'] : '';
			if($newdoid>0&&$message!='')
			{
				$message = preg_replace('|<img src=\\\\"static/image/smiley/.*?>|', '', $message);
				
				require_once './source/plugin/renren/class/api.class.php';
				require_once './source/plugin/renren/renren.function.php';
				$feed = & renren_api::instance();
				if(strtolower($_G['charset']) != 'utf-8') {
						$message = $feed->convertEncoding($message,"GBK","UTF-8");
				}
				$feed->pushstatus($message);
			}
		}
	}
//分享相册
	function space_album_op_extra_output(){
		global $album, $pic, $_G;
		//foreach($album as $key => $value)
		//{
		//	echo 'key:'.$key.';value:'.$value.'--------';
		//}
		require_once libfile('function/home');
		$return = '';
		$img = '';
		$uid=$album['uid'];
		$albumid=$album['albumid'];
		$subjectlink = $_G['siteurl']. "home.php?mod=space&uid={$uid}&do=album&id={$albumid}";
		$img = $_G['siteurl'].(isset($album['pic']) & isset($album['picflag']) ? pic_cover_get($album['pic'], $album['picflag']) : '');
		include template('renren:rr_share_button_blog');
		return $return;
		

	}
	//分享同步到人人分享
	function spacecp_share_aftersubmit_message($param){
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		if(getgpc('sharesubmit')&&isset($param['param'][2]['sid'])&&$param['param'][2]['sid']>0&&$_G['renren']['rr_uid']&&substr($param['param'][0], -8)=='_success')
		{
			require_once './source/plugin/renren/class/api.class.php';
			require_once './source/plugin/renren/renren.function.php';
			$iffeed = get_rrfeed($_G['uid']);
			if($iffeed[4])
			{
				
				$arr = isset($GLOBALS['arr']) ? (array)$GLOBALS['arr'] : array();
				$body_data=unserialize($arr['body_data']);
				$body_general=$arr['body_general'];
				$link=$body_data['data'];
				$feed = & renren_api::instance();
				//echo "<script>alert('$link');</script>";
				if(strtolower($_G['charset']) != 'utf-8') {
						$body_general = $feed->convertEncoding($body_general,"GBK","UTF-8");
				}
				$feed->pushshare($link,$body_general);
			}
		}
	}
//分享照片按钮
	function space_album_pic_op_extra_output(){
		//return $this->_album_get_share_button('albumpic');
		global $album, $pic, $_G;
		//foreach($pic as $key => $value)
		//{
		//	echo 'key:'.$key.';value:'.$value.'--------';
		//}
		$return = '';
		$img = '';
		$uid=$pic['uid'];
		$picid=$pic['picid'];
		$subjectlink = $_G['siteurl']. "home.php?mod=space&uid={$uid}&do=album&picid={$picid}";
		$img =  $_G['siteurl'].(isset($pic['pic']) ? (string)$pic['pic'] : '');
		include template('renren:rr_share_button_blog');
		return $return;
	}
//分享日志按钮
	function space_blog_op_extra_output(){
		global $_G,$blog;
		//foreach($blog as $key => $value)
		//{
		//	echo 'key:'.$key.';value:'.$value.'--------';
		//}
		$return = '';
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		require_once './source/plugin/renren/class/api.class.php';
		require_once './source/plugin/renren/renren.function.php';
		require_once './source/function/function_post.php';
		$feed = & renren_api::instance();
		$bbname = $_G['setting']['bbname'];
		$bbname_link ='<a href="'.$_G['siteurl'].'">'.$_G['setting']['bbname'].'</a>';
		if(strtolower($_G['charset']) != 'utf-8') {
			$bbname=$feed->convertEncoding($bbname,"GBK","UTF-8");
			$bbname_link=$feed->convertEncoding($bbname_link,"GBK","UTF-8");
		}
		$blogid=$blog['blogid'];
		$bloguid=$blog['uid'];
		if( $blogid >= 0 ) {
			$subject = (string)$blog['subject'];
			//echo "<script>alert('$blogid');</script>";
			$subjectlink = $_G['siteurl']. "home.php?mod=space&uid={$bloguid}&do=blog&id={$blogid}";
			if(strtolower($_G['charset']) != 'utf-8') {
				$subject = $feed->convertEncoding($subject,"GBK","UTF-8");
				$subjectlink = $feed->convertEncoding($subjectlink,"GBK","UTF-8");
			}
			$message = !empty($blog["message"]) ? (string)$blog["message"] : '';
			
			$pos=strpos($message2,'<IMG src="'.$_G['siteurl'].'xwb/images');
			if($pos>0)
				$message=substr($message,0,$pos);
			$message = str_replace('src="data/attachment/album', 'src="'.$_G['siteurl'].'data/attachment/album', $message);
			if(preg_match("/<img.*?src=\"(.*?)\"/",$message, $match)) {
				$img=$match[1];
			} elseif(preg_match("/<IMG.*?src=\"(.*?)\"/",$message, $match)) {
				$img=$match[1];
			} elseif(preg_match("/\[img\](.*?)\[\/img\]/i", $message, $matchaids)) {
				$img=$matchaids[1];
			} elseif(preg_match("/\[img=([a-zA-Z0-9_]+),([a-zA-Z0-9_]+)\](.*?)\[\/img\]/i", $message, $matchaids)) {
				$img=$matchaids[3];
			} 

			$message = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($message), 200));
			
			if(strtolower($_G['charset']) != 'utf-8') {
				$message = $feed->convertEncoding($message,"GBK","UTF-8");
			}
		}
		require_once './source/plugin/renren/class/api.class.php';
		$feed = & renren_api::instance();
		$rr_share_title=$subject;
		$rr_share_content=$message;
		if(strtolower($_G['charset']) != 'utf-8') {
				//$rr_share_title = $feed->convertEncoding($rr_share_title,"GBK","UTF-8");
				$rr_share_title=$this->sprintfchg($rr_share_title);
				//$rr_share_content = $feed->convertEncoding($rr_share_content,"GBK","UTF-8");
				$rr_share_content=$this->sprintfchg($rr_share_content);
		}
		include template('renren:rr_share_button_blog');
		return $return;
	}
	//home人人主页
	function space_profile_baseinfo_top_output() {
		require_once './source/plugin/renren/renren.function.php';
		global $_G;
		include template('renren:rr_home');
		return $return;
	}
	function spacecp_blog_sync_to_renren_aftersubmit(){
		$msgforwardSet = (array)@unserialize($GLOBALS['_G']['setting']['msgforward']);
		$msgforwardSet['refreshtime'] = 3;  //只能是大于0的整数
		$msgforwardSet['quick'] = 0;
		$GLOBALS['_G']['setting']['msgforward'] = serialize($msgforwardSet);
	}
	
//日志同步
	function spacecp_blog_sync_to_renren_aftersubmit_message(){
		
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		if($_G['renren']['rr_uid'] && $_G['cache']['plugin']['renren']['rr_pic']) {
			require_once './source/plugin/renren/class/api.class.php';
			require_once './source/plugin/renren/renren.function.php';
			require_once './source/function/function_post.php';
			$feed = & renren_api::instance();
			$bbname = $_G['setting']['bbname'];
			$bbname_link ='<a href="'.$_G['siteurl'].'">'.$_G['setting']['bbname'].'</a>';
			if(strtolower($_G['charset']) != 'utf-8') {
				$bbname=$feed->convertEncoding($bbname,"GBK","UTF-8");
				$bbname_link=$feed->convertEncoding($bbname_link,"GBK","UTF-8");
			}
			$iffeed = get_rrfeed($_G['uid']);
			//假如是发日志
			if(submitcheck('blogsubmit', 0, $GLOBALS['seccodecheck'], $$GLOBALS['secqaacheck']) && $_POST['rrfeed']) {
					$blogid = isset($GLOBALS['newblog']['blogid']) ? (int)$GLOBALS['newblog']['blogid'] : 0;
					if( $blogid >= 0 ) {
						$subject = (string)$GLOBALS['newblog']['subject'];
						//echo "<script>alert('$blogid');</script>";
						$subjectlink = $_G['siteurl']. "home.php?mod=space&uid={$_G['uid']}&do=blog&id={$blogid}";
						$subject_link = '<a href="'.$subjectlink.'">'.$subject.'</a>';
						if(strtolower($_G['charset']) != 'utf-8') {
							$subject = $feed->convertEncoding($subject,"GBK","UTF-8");
							$subjectlink = $feed->convertEncoding($subjectlink,"GBK","UTF-8");
							$subject_link = $feed->convertEncoding($subject_link,"GBK","UTF-8");
						}
						$queryPic = DB::query("SELECT * FROM ".DB::table('home_blogfield')." WHERE blogid='$blogid' ");
						if($pic = DB::fetch($queryPic)) {
							$message = $pic['message'];
						}
						$message = !empty($message) ? (string)$message : '';
							
						$message = str_replace(array("\r", "\n"), '', $message);
						$message = str_replace('src="data/attachment/album', 'src="'.$_G['siteurl'].'data/attachment/album', $message);
						if(strtolower($_G['charset']) != 'utf-8') {
							$message = $feed->convertEncoding($message,"GBK","UTF-8");
						}
						$name= $subject;
						$description=$message;
						$feed->pushblog($name,$description);
				}
				return null;
			} else {
				return null;
			}
		}
	}
	
	
	/**
	 * 日志发表同步按钮
	 */
	function spacecp_blog_middle_output(){
		
		global $_G;
		//foreach($_G as $key => $value)
		//{
		//	echo 'key:'.$key.';value:'.$value.'--------';
		//}
		if( !$_G['uid']){
			return '';
		}
		if($_G['cache']['plugin']['renren']['rr_feed']&&$_G['cache']['plugin']['renren']['rr_blognew'])
		{

			if($_G['renren']['rr_uid']){
				require_once './source/plugin/renren/renren.function.php';
				$iffeed = get_rrfeed($_G['uid']);
				$perm = 1;
				if($_G['gp_ac'] == 'blog') {
					require_once './source/plugin/renren/class/api.class.php';
					$api = & renren_api::instance();
					$resperm = $api->has_perm('publish_blog');
					$perm = $resperm->result;
					//echo "<script>alert('$perm');</script>";
				}
				include template('renren:spacecp_newblog');
				return $return;
			}else{
				include template('renren:spacecp_newblog_def');
				return $return;
			}
		}
	}

}
/**
 * 门户钩子
 *
 */
class plugin_renren_portal extends plugin_renren{
	
	/**
	 * 发表文章页面钩子：同步按钮显示
	 */
	function portalcp_bottom_output(){
		global $_G;
		if( !$_G['uid']){
			return '';
		}

		if($_G['cache']['plugin']['renren']['rr_feed']&&$_G['cache']['plugin']['renren']['rr_articalnew'])
		{
			
			if($_G['renren']['rr_uid']){
				require_once './source/plugin/renren/renren.function.php';
				$iffeed = get_rrfeed($_G['uid']);
				$perm = 1;
				if($_G['gp_ac'] == 'article') {
					require_once './source/plugin/renren/class/api.class.php';
					$api = & renren_api::instance();
					$resperm = $api->has_perm('publish_blog');
					$perm = $resperm->result;
					//echo "<script>alert('$perm');</script>";
				}
				include template('renren:portal_article');
				return $return;
			}else{
				include template('renren:portal_article_def');
				return $return;
			}
		}
	}
	
	/**
	 * 发表文章截获钩子：同步到人人网
	 */
	function portalcp_article_sync_to_renren_aftersubmit_output(){
		global $_G;
		if( !$_G['uid']){
			return '';
		}
		require_once './source/plugin/renren/class/connect.class.php';
		$renren = & renren_connect::instance();
		$renren->inits($discuz_uid);
		if($_G['renren']['rr_uid'] && $_G['cache']['plugin']['renren']['rr_pic']) {
			require_once './source/plugin/renren/class/api.class.php';
			require_once './source/plugin/renren/renren.function.php';
			require_once './source/function/function_post.php';
			$feed = & renren_api::instance();
			$bbname = $_G['setting']['bbname'];
			$bbname_link ='<a href="'.$_G['siteurl'].'">'.$_G['setting']['bbname'].'</a>';
			if(strtolower($_G['charset']) != 'utf-8') {
				$bbname=$feed->convertEncoding($bbname,"GBK","UTF-8");
				$bbname_link=$feed->convertEncoding($bbname_link,"GBK","UTF-8");
			}
			$iffeed = get_rrfeed($_G['uid']);
			//假如是发文章
			if(getgpc('articlesubmit') && $_POST['rrfeed']) {
					$aid = isset($GLOBALS['aid']) ? (int)$GLOBALS['aid'] : 0;
					
					if( $aid >= 0 ) {
						$subject = isset($_POST['title']) ? (string)$_POST['title'] : '';
						//echo "<script>alert('$blogid');</script>";
						$subjectlink = $_G['siteurl']. "portal.php?mod=view&aid={$aid}";
						$subject_link = '<a href="'.$subjectlink.'">'.$subject.'</a>';
						if(strtolower($_G['charset']) != 'utf-8') {
							$subject = $feed->convertEncoding($subject,"GBK","UTF-8");
							$subjectlink = $feed->convertEncoding($subjectlink,"GBK","UTF-8");
							$subject_link = $feed->convertEncoding($subject_link,"GBK","UTF-8");
						}
						$queryPic = DB::query("SELECT cid,content FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ");
						if($pic = DB::fetch($queryPic)) {
							$message = $pic['content'];
						}
						$message = !empty($message) ? (string)$message : '';
							
						$message = str_replace(array("\r", "\n"), '', $message);
						$message = str_replace('src="data/attachment/portal', 'src="'.$_G['siteurl'].'data/attachment/portal', $message);
						if(strtolower($_G['charset']) != 'utf-8') {
							$message = $feed->convertEncoding($message,"GBK","UTF-8");
						}
						$name= $subject;
						$description=$message;
						$feed->pushblog($name,$description);
				}
				return null;
			} else {
				return null;
			}
		}
	}

}
?>