<?php
/////////////////////////////////////
//        Examore考试猫插件        //
//当前版本：1.3.110711             //
//使用范围：Discuz! X2 GBK         //
//官方网站：www.examore.com        //
//版权所有(c)2009-2011, Examore.com//
/////////////////////////////////////
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$examore_version = "1.3.110711";
require_once('examore.class.php');

$ac = $_G['gp_ac'];
$ea = $_G['gp_ea'];
$agree = $_G['gp_agree'];
$em_ac = $_G['gp_em_ac'];
$page = $_G['gp_page'];
empty($em_ac) && $em_ac = 1;
empty($page) && $page = 1;

$examparam=array('examopen'=>'1','examlevel'=>'0');
foreach($pluginvars as $i => $var) {
	$examparam[$i] = $var['value'];
}

$examore = DB::result_first("SELECT e_a FROM ".DB::table('examore')." WHERE i_d = 1");
if ($examore==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('1', ".TIMESTAMP.")");
	$first = 1;
}else if (!is_numeric($examore)){
	$examinfo = explode("||",$examore);
	$examinfo[3]=$examparam['examopen'];
	$examinfo[4]=$examparam['examlevel'];
	if (count($examinfo)>7){
		DB::query("ALTER TABLE ".DB::table('examore')." CHANGE `e_a` `e_a` VARCHAR(500) NOT NULL DEFAULT ''"); 
		$examinfo[5] = $examinfo[7];
		$examinfo[6] = $examinfo[8];
	}else{
		$examinfo[5]=="" && $examinfo[5] = "0";
		$examinfo[6]=="" && $examinfo[6] = "0";
	}
	DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$examinfo[0]}||{$examinfo[1]}||{$examinfo[2]}||{$examinfo[3]}||{$examinfo[4]}||{$examinfo[5]}||{$examinfo[6]}' WHERE i_d = 1");
}else if ($examore=="0"){
	$first = 1;
}
if ($first && $agree){
	DB::query("UPDATE ".DB::table('examore')." SET e_a = '".TIMESTAMP."' WHERE i_d = 1");
}
$query = DB::query("SELECT e_a FROM ".DB::table('examore')." WHERE i_d IN (2,3,4)");
while($rt = DB::fetch($query)) {
	$ems[] = $rt;
}
$emid = $ems[0][e_a];
$emunit = $ems[1][e_a];
$emscore = $ems[2][e_a];
if ($emid==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emids = explode('|', $emid);
}
if ($emunit==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emunits = explode('|', $emunit);
}
if ($emscore==""){
	DB::query("INSERT INTO ".DB::table('examore')." (`e_a`, `d_t`) values ('0', ".TIMESTAMP.")");
}else{
	$emscores = explode('|', $emscore);
}

if($ac==""){
	if ($first){
		showtableheader("《Examore考试猫插件使用协议》");
		echo '<tr><td>版权所有(c)2009-2010,Examore.com<br/>保留所有权力.<br/><br/>Examore考试猫插件由Examore.com独立开发,全部核心技术归属Examore.com<br/>官方网站为http://www.examore.com官方考试论坛为http://www.examore.net<br/>本授权协议适用于Examore任何版本，Examore.com拥有对本授权协议的最终解释权和修改权。<br/><br/>Examore考试猫插件<br/>1、Examore遵循国际国内常见开源软件协议，如果您需要采用Examore系统的部分程序构架其他程序系统，请务必取得我们的同意。否则我们将追究责任！修改后的代码，未经书面许可，严禁公开发布，更不得利用其从事盈利业务。<br/><br/>2、所有用户均可查看Examore的全部源代码,也可以根据自己的需要对其进行修改！但无论如何，既无论用途如何、是否经过修改或美化、修改程度如何，只要您使用Examore的任何整体或部分程序算法，都必须保留源代码中Examore.com的版权说明。<br/><br/>3、未经商业授权，不得将本软件用于商业用途(企业网站或以盈利为目的经营性网站)，否则我们将保留追究的权力。<br/>有关Examore授权包含的服务范围，技术支持等，请参看http://www.examore.com<br/>对于违反以上条款，或以任何目的复制或发行Examore的组织或个人，我们将依法追究其责任。<br/><br/>免责声明:<br/>1、利用本软件构建的网站的任何信息内容以及导致的任何版权纠纷和法律争议及后果，官方不承担任何责任。<br/>2、损坏包括程序的使用(或无法再使用)中所有一般化,特殊化,偶然性的或必然性的损坏(包括但不限于数据的丢失,自己或第三方所维护数据的不正确修改,和其他程序协作过程中程序的崩溃等),官方不承担任何责任。</td></tr>';
		echo '<tr><td align="center"><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&agree=1">同意</a></td></tr>';
		showtablefooter();
	}else{
		if (!is_numeric($examore)){
			$examparam['examopen'] = $examparam['examopen']=="1"?"开放":"关闭";
			$examparam['examlevel'] = $examparam['examlevel']=="1"?"注册会员":"任何人";
			$examkey = $examinfo[0];
			$exampass = $examinfo[1];
			$examsubject = $subject[$examinfo[2]];
			showtableheader("系统选项");
			echo '<tr><td width="100%" style="text-align:center;"><input type="button" value="系统概况" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=1\'" />&nbsp;<input type="button" value="积分消费" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2\'" />&nbsp;<input type="button" value="自助管理" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=3\'" /></td></tr>';
			showtablefooter();
			switch ($em_ac){
				case 1:
					showtableheader("系统概况");
					echo '<tr><td width="50%">申请进度：<script type="text/javascript" src="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=status&ka='.$examkey.'"></script></td><td width="50%">科目题库：'.$examsubject.'</td></tr>';
					echo '<tr><td>识别编码：'.$examkey.'</td><td>考场开放：'.$examparam['examopen'].'</td></tr>';
					echo '<tr><td>通讯密钥：'.$exampass.'</td><td>考试权限：'.$examparam['examlevel'].'</td></tr>';
					echo '<tr><td>当前版本：'.$examore_version.'</td><td>消费次数：'.$examinfo[5].'次</td></tr>';
					echo '<tr><td colspan="2">参数继承：'.$examinfo[0].'||'.$examinfo[1].'||'.$examinfo[2].'||'.$examinfo[3].'||'.$examinfo[4].'</td></tr>';
					echo '<tr><td colspan="2">最新版本：<script type="text/javascript" src="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=version"></script></td></tr>';
					showtablefooter();
					break;
				case 2:	
					showtableheader("积分消费");
					echo '<tr><td align="center">ID</td><td align="center">用户组</td><td align="center">正常考试消费</td><td align="center">错题挑战消费</td><td align="center">随机抽题消费</td></tr>';
					$extcredits = $_G['setting']['extcredits'];
					$query = DB::query("SELECT groupid, grouptitle FROM ".DB::table('common_usergroup')." ORDER BY groupid");
					while($rt = DB::fetch($query)) {
						$groups[] = $rt;
					}
					$groupsnum = count($groups);
					foreach ($groups as $key => $value){
						$pid .= ($pid==""?"":"|").($key+1);
						$sels1 = $sels2 = $sels3 = "";
						foreach($extcredits as $keys => $values){
							$sels1 .= '<option value="'.$keys.'"'.($keys==$emunits[$key*3]?" selected":"").'>'.$values[title].'</option>';
							$sels2 .= '<option value="'.$keys.'"'.($keys==$emunits[$key*3+1]?" selected":"").'>'.$values[title].'</option>';
							$sels3 .= '<option value="'.$keys.'"'.($keys==$emunits[$key*3+2]?" selected":"").'>'.$values[title].'</option>';
						}
						echo '<tr><td align="center">'.($key+1).'</td><td align="center">'.$value['grouptitle'].'</td><td align="center"><select id="ft1'.$key.'" name="ft1'.$key.'">'.$sels1.'</select> <input type="text" name="fe1'.$key.'" id="fe1'.$key.'" value="'.$emscores[$key*3].'" style="width:40px; text-align:center;" onmouseover="this.select();" /></td><td align="center"><select id="ft2'.$key.'" name="ft2'.$key.'">'.$sels2.'</select> <input type="text" name="fe2'.$key.'" id="fe2'.$key.'" value="'.$emscores[$key*3+1].'" style="width:40px; text-align:center;" onmouseover="this.select();" /></td><td align="center"><select id="ft3'.$key.'" name="ft3'.$key.'">'.$sels3.'</select> <input type="text" name="fe3'.$key.'" id="fe3'.$key.'" value="'.$emscores[$key*3+2].'" style="width:40px; text-align:center;" onmouseover="this.select();" /></td></tr>';
					}
					echo '<form id="modscore" name="modscore" action="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=4" method="post"><tr class="tr3"><td colspan="5" align="center"><input type="hidden" id="pnum" name="pnum" value="'.$groupsnum.'" /><input type="hidden" id="pid" name="pid" value="'.$pid.'" /><input type="hidden" id="punit" name="punit" value="" /><input type="hidden" id="pscore" name="pscore" value="" /><input type="button" class="bta" value="全部归零" onclick="resetScore()" /> <input type="button" class="bta" value="保存设置" onclick="checkScore()" /></td></tr></form>';
					echo '<script language="javascript">function checkScore(){var pnum = '.$groupsnum.';var punit = "";var pscore = "";for (var i=0; i<pnum; i++){punit += (punit==""?"":"|")+document.getElementById("ft1"+i).value+"|"+document.getElementById("ft2"+i).value+"|"+document.getElementById("ft3"+i).value;pscore += (pscore==""?"":"|")+document.getElementById("fe1"+i).value+"|"+document.getElementById("fe2"+i).value+"|"+document.getElementById("fe3"+i).value;}document.getElementById("punit").value = punit;document.getElementById("pscore").value = pscore;document.getElementById("modscore").submit();}function resetScore(){var pnum = '.$groupsnum.';var punit = "";var pscore = "";for (var i=0; i<pnum; i++){document.getElementById("ft1"+i).selectedIndex = 0;document.getElementById("ft2"+i).selectedIndex = 0;document.getElementById("ft3"+i).selectedIndex = 0;document.getElementById("fe1"+i).value = 0;document.getElementById("fe2"+i).value = 0;document.getElementById("fe3"+i).value = 0;}}</script>';
					showtablefooter();
					break;
				case 3:					
					$myexam = $emclass->create_sig('myexam', '0', $examore_version, $examinfo[0], $examinfo[1], TIMESTAMP);
					showtableheader("自助管理");
					echo '<tr><td style="text-align:center;"><input type="button" value="进入考试猫自助管理系统" onclick="doExam()" /></td></tr>';
					showtablefooter();
					echo '<script language="javascript">function doExam(){examOpen("http://www.examore.net/hack.php?H_name='.$examv.'&em_sid=0&em_eid=0&em_cs='.$examore_charset.'&'.$myexam.'","考试多examore");return false;}function examOpen(oUrl,oName){if(typeof(window.showModalDialog)!="undefined"){window.showModalDialog(oUrl, oName, "dialogWidth="+window.screen.availWidth+"px;dialogHeight="+window.screen.availHeight+"px;scroll=1;center=1;help=0;resizable=0;status=0;");}else{window.open(oUrl, oName, "menubar=0,toolbar=0,directories=0,location=0,status=0,scrollbars=1,width="+(window.screen.availWidth-10)+",height="+(window.screen.availHeight-30)+",top=0,left=0,resizable=0");}window.location.replace(window.location.href);}</script>';
					break;
				case 4:
					$pnum = $_G['gp_pnum'];
					$pid = $_G['gp_pid'];
					$punit = $_G['gp_punit'];
					$pscore = $_G['gp_pscore'];
					$pids = explode('|', $pid);
					$punits = explode('|', $punit);
					$pscores = explode('|', $pscore);
					if (is_numeric($pnum) && $pnum == count($pids) && $pnum == count($punits)/3 && $pnum == count($pscores)/3){
						for ($i=0; $i<count($pscores); $i++){
							!is_numeric($pscores[$i]) && $pscores[$i]<0 && $pscores[$i]=0;
						}
						DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$pid}' WHERE i_d = 2");
						DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$punit}' WHERE i_d = 3");
						DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$pscore}' WHERE i_d = 4");
						cpmsg('积分消费设置保存成功！','action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2','succeed');
					}else{
						cpmsg('出错了！','action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2','error');
					}
					break;
			}
		}else{
			$actime = TIMESTAMP;
			$apiurl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&ac=".$actime;
			if (is_numeric($examore)){
				DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$actime}' WHERE i_d = 1");
			}else{
				$exampass = $examinfo[1];
			}
			showtableheader("服务申请");
			echo '<tr><td align="center" class="bold">请认真填写下面的表单<span id="msg" name="msg" class="error">'.$msg.'</span></td></tr>';
			echo '<script language="javascript">';
			echo 'String.prototype.lTrim = function(){return this.replace(/^\s*/,"");};';
			echo 'String.prototype.rTrim = function(){return this.replace(/\s*$/,"");};';
			echo 'String.prototype.Trim = function(){return this.lTrim().rTrim();};';
			echo 'function checkApi(){var msg = document.getElementById("msg");var sn = document.getElementById("sn");var su = document.getElementById("su");var iu = document.getElementById("iu");var imgok = document.getElementById("imgok");var pp = document.getElementById("pp");';
			echo 'if (sn.value.length > 50 || sn.value.Trim() == ""){msg.innerHTML = "<br/><u>网站名称</u>不得为空或超过50个字";sn.select();return false;}';
			echo 'if (su.value.length > 50 || su.value.Trim() == ""){msg.innerHTML = "<br/><u>网站域名</u>不得为空或超过50个字";su.select();return false;}';
			echo 'if (iu.value.length > 100 || iu.value.Trim() == ""){msg.innerHTML = "<br/><u>网站图片地址</u>不得为空或超过100个字";iu.select();return false;}';
			echo 'if (imgok.value == ""){msg.innerHTML = "<br/><u>网站图片尺寸</u>为760×100以内";iu.select();return false;}';
			echo 'if (pp.value.Trim()=="" || isNaN(pp.value) || parseInt(pp.value)<1 || parseInt(pp.value)>50){msg.innerHTML = "<br/><u>每页显示条数</u>为1~50以内";pp.select();return false;}';
			echo 'msg.innerHTML = "";if (confirm("以下项目提交申请后将无法修改\\n确认填好了吗")){return true;}else{return false;}}';
			echo 'function changeIu(){var iu = document.getElementById("iu");var imgtr = document.getElementById("imgtr");var iuimg = document.getElementById("iuimg");var iusize = document.getElementById("iusize");var imgok = document.getElementById("imgok");var img = new Image();';
			echo 'img.onload = function(){iusize.innerHTML = this.width +"×"+ this.height;if (this.width>760 || this.height>100){imgok.value = "";}else{imgok.value = "1";}};';
			echo 'img.src = iu.value;iuimg.src = iu.value;';
			echo 'if (iu.value == ""){imgtr.style.visibility = "hidden";imgtr.style.display = "none";}else{imgtr.style.visibility = "visible";imgtr.style.display = "block";}}';
			echo '</script>';
			echo '<tr><td align="center">';
			echo '<table cellspacing="0" cellpadding="0" width="100%"><form action="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=addnew" method="post" onsubmit="return checkApi()"><input type="hidden" id="apiurl" name="apiurl" value="'.$apiurl.'" /><input type="hidden" id="apipass" name="apipass" value="'.$exampass.'" />';
			echo '<tr><td width="30%" align="right" height="40"><b>网站名称：</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="sn" id="sn" value="" maxlength="50" style="width:400px;" /><br/>不得超过50个字</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>网站域名：</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="su" id="su" value="" maxlength="50" style="width:400px;" /><br/>格式：www.examore.com</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>网站图片：</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="iu" id="iu" value="" maxlength="100" style="width:400px;" onchange="changeIu()" /><input type="hidden" id="imgok" name="imgok" value="" /><br/>格式：http://www.examore.com/images/logo.gif<br/>尺寸：760×100以内</td></tr>';
			echo '<tr id="imgtr" name="imgtr" style="visibility:hidden; display:none;"><td colspan="2" align="center" height="40"><img src="" id="iuimg" name="iuimg" /><br/><span id="iusize" name="iusize"></span></td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>学科设置：</b></td><td width="70%" align="left" height="40"><select type="text" class="input" name="ks" id="ks"><option value="1">公务员</option><option value="6">外语</option><option value="9">轻松一刻</option><option value="13">驾照</option></select><br/>科目选定后将不能轻易修改</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>每页条数：</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="pp" id="pp" value="" maxlength="2" style="width:20px;" /><br/>设置每页显示的试题条数在1~50之间</td></tr>';
			echo '<tr><td colspan="2" align="center" height="40"><input type="submit" class="bta" value="申请在线考试服务" /></td></tr></form></table></td></tr>';
			showtablefooter();
		}
	}
}else{
	if (!empty($ea) && strpos($ea,"||")==40 && strpos($ea,"||",42)==74){
		if (empty($pass)){
			if (!empty($examore) && is_numeric($examore)){
				DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$ea}||{$examparam['examopen']}||{$examparam['examlevel']}||{$examparam['examcredit']}||{$examparam['examfee']}||0||0' WHERE i_d = 1");
				cpmsg('服务申请成功，请等待审核！','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'succeed');
			}else{
				cpmsg('出错了！','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
			}
		}else{
			if (substr($examore,42,32) == $pass){
				DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$ea}||{$examparam['examopen']}||{$examparam['examlevel']}||{$examparam['examcredit']}||{$examparam['examfee']}||0||0' WHERE i_d = 1");
				cpmsg('服务申请成功，请等待审核！','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'succeed');
			}else{
				cpmsg('出错了！','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
			}
		}
	}else{
		cpmsg('出错了！','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
	}
}
?>