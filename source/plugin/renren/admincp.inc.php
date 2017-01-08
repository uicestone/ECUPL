<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$Plang = $scriptlang['renren'];
if($_G['gp_op'] == 'delete') {
	DB::delete('renren_connect',array('dz_uid'=>$_G['gp_uid']));
	loaducenter();
	uc_user_delete($_G['gp_uid']);
	require_once libfile('function/delete');
	$numdeleted = deletemember($_G['gp_uid'], 0);
	ajaxshowheader();
	echo $Plang['rr_deleted'];
	ajaxshowfooter();
} else {
	$ppp = 15;
	$page = max(1, intval($_G['gp_page']));
	$i=1;
	$resultempty = FALSE;
	$srchadd = $searchtext = $extra = $srchuid = '';
	if(!empty($_G['gp_srchrrid'])) {
		$srchrrid = intval($_G['gp_srchrrid']);
		$extra = '&srchrrid='.$srchrrid;
		$srchadd = "AND rr_uid like '$srchrrid%'";
		$searchtext = $Plang['rr_search'].' "'.$srchrrid.'" ';
	} elseif(!empty($_G['gp_srchusername'])) {
		$srchrrid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_srchusername]'");
		if($srchrrid) {
			$srchadd = "AND dz_uid='$srchrrid'";
		} else {
			$resultempty = TRUE;
		}
		$extra = '&srchusername='.rawurlencode(stripslashes($_G['gp_srchusername']));
		$searchtext = $Plang['rr_search'].' "'.stripslashes($_G['gp_srchusername']).'" ';
	}

	if($searchtext) {
		$searchtext = '<a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=renren&pmod=admincp">'.$Plang['rr_viewall'].'</a>&nbsp'.$searchtext;
	}


	showtableheader();
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=renren&pmod=admincp', 'renrensubmit');
	showsubmit('renrensubmit', $Plang['rr_search'], $Plang['rr_uid'].': <input name="srchrrid" value="'.htmlspecialchars(stripslashes($_G['gp_srchrrid'])).'" class="txt" />&nbsp;&nbsp;'.$Plang['dz_username'].': <input name="srchusername" value="'.htmlspecialchars(stripslashes($_G['gp_srchusername'])).'" class="txt" />', $searchtext);
	showformfooter();
	if(!$resultempty) {
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('renren_connect')." WHERE 1 $srchadd");
		$query = DB::query("SELECT * FROM ".DB::table('renren_connect')." WHERE 1 $srchadd LIMIT ".(($page - 1) * $ppp).",$ppp");

		echo '<tr><td colspan=5>'.$Plang['rr_uid_del_des'].'</td></tr><tr class="header"><th>'.$Plang['rr_uid'].'</th><th>'.$Plang['dz_username'].'</th><th>'.$Plang['rr_isbind'].'</th><th>'.$Plang['rr_bindtime'].'</th><th>'.$Plang['rr_edit'].'</th><th></th></tr>';
		while($data = DB::fetch($query)) {
			$data['name'] = DB::result_first("SELECT username FROM ".DB::table('common_member')." where uid=$data[dz_uid]");
			if($data['name']!='admin'&&$data['tag']!=1)
			{
				$i++;
				echo '<tr><td><a href="http://www.renren.com/profile.do?id='.$data['rr_uid'].'" target=_blank>'.$data['rr_uid'].'</a></td>'.
					'<td><a href="admin.php?action=members&operation=edit&uid='.$data['dz_uid'].'">'.$data['name'].'</a></td>'.
					'<td>';
				if($data['tag']==0) {
					echo $Plang['rr_reg'];
				} elseif($data['tag']==1) {
					echo $Plang['rr_binded'];
				} elseif($data['tag']==2) {
					echo $Plang['rr_bebind'];
				}
				echo '</td><td>'.$data['bind_time'].'</td>'.
					'<td><a id="p'.$i.'" onclick="ajaxget(this.href, this.id, \'\');return false" href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=renren&pmod=admincp&op=delete&uid='.$data['dz_uid'].'">'.$Plang['rr_del'].'</a></td></tr>';
				}
			}	
	}
}
showtablefooter();
echo multi($count, $ppp, $page, ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=renren&pmod=admincp$extra");
?>