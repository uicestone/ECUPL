<?php
/////////////////////////////////////
//        Examore����è���        //
//��ǰ�汾��1.3.110711             //
//ʹ�÷�Χ��Discuz! X2 GBK         //
//�ٷ���վ��www.examore.com        //
//��Ȩ����(c)2009-2011, Examore.com//
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
		showtableheader("��Examore����è���ʹ��Э�顷");
		echo '<tr><td>��Ȩ����(c)2009-2010,Examore.com<br/>��������Ȩ��.<br/><br/>Examore����è�����Examore.com��������,ȫ�����ļ�������Examore.com<br/>�ٷ���վΪhttp://www.examore.com�ٷ�������̳Ϊhttp://www.examore.net<br/>����ȨЭ��������Examore�κΰ汾��Examore.comӵ�жԱ���ȨЭ������ս���Ȩ���޸�Ȩ��<br/><br/>Examore����è���<br/>1��Examore��ѭ���ʹ��ڳ�����Դ���Э�飬�������Ҫ����Examoreϵͳ�Ĳ��ֳ��򹹼���������ϵͳ�������ȡ�����ǵ�ͬ�⡣�������ǽ�׷�����Σ��޸ĺ�Ĵ��룬δ��������ɣ��Ͻ��������������������������ӯ��ҵ��<br/><br/>2�������û����ɲ鿴Examore��ȫ��Դ����,Ҳ���Ը����Լ�����Ҫ��������޸ģ���������Σ���������;��Ρ��Ƿ񾭹��޸Ļ��������޸ĳ̶���Σ�ֻҪ��ʹ��Examore���κ�����򲿷ֳ����㷨�������뱣��Դ������Examore.com�İ�Ȩ˵����<br/><br/>3��δ����ҵ��Ȩ�����ý������������ҵ��;(��ҵ��վ����ӯ��ΪĿ�ľ�Ӫ����վ)���������ǽ�����׷����Ȩ����<br/>�й�Examore��Ȩ�����ķ���Χ������֧�ֵȣ���ο�http://www.examore.com<br/>����Υ��������������κ�Ŀ�ĸ��ƻ���Examore����֯����ˣ����ǽ�����׷�������Ρ�<br/><br/>��������:<br/>1�����ñ������������վ���κ���Ϣ�����Լ����µ��κΰ�Ȩ���׺ͷ������鼰������ٷ����е��κ����Ρ�<br/>2���𻵰��������ʹ��(���޷���ʹ��)������һ�㻯,���⻯,żȻ�ԵĻ��Ȼ�Ե���(���������������ݵĶ�ʧ,�Լ����������ά�����ݵĲ���ȷ�޸�,����������Э�������г���ı�����),�ٷ����е��κ����Ρ�</td></tr>';
		echo '<tr><td align="center"><a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&agree=1">ͬ��</a></td></tr>';
		showtablefooter();
	}else{
		if (!is_numeric($examore)){
			$examparam['examopen'] = $examparam['examopen']=="1"?"����":"�ر�";
			$examparam['examlevel'] = $examparam['examlevel']=="1"?"ע���Ա":"�κ���";
			$examkey = $examinfo[0];
			$exampass = $examinfo[1];
			$examsubject = $subject[$examinfo[2]];
			showtableheader("ϵͳѡ��");
			echo '<tr><td width="100%" style="text-align:center;"><input type="button" value="ϵͳ�ſ�" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=1\'" />&nbsp;<input type="button" value="��������" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2\'" />&nbsp;<input type="button" value="��������" onclick="location=\''.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=3\'" /></td></tr>';
			showtablefooter();
			switch ($em_ac){
				case 1:
					showtableheader("ϵͳ�ſ�");
					echo '<tr><td width="50%">������ȣ�<script type="text/javascript" src="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=status&ka='.$examkey.'"></script></td><td width="50%">��Ŀ��⣺'.$examsubject.'</td></tr>';
					echo '<tr><td>ʶ����룺'.$examkey.'</td><td>�������ţ�'.$examparam['examopen'].'</td></tr>';
					echo '<tr><td>ͨѶ��Կ��'.$exampass.'</td><td>����Ȩ�ޣ�'.$examparam['examlevel'].'</td></tr>';
					echo '<tr><td>��ǰ�汾��'.$examore_version.'</td><td>���Ѵ�����'.$examinfo[5].'��</td></tr>';
					echo '<tr><td colspan="2">�����̳У�'.$examinfo[0].'||'.$examinfo[1].'||'.$examinfo[2].'||'.$examinfo[3].'||'.$examinfo[4].'</td></tr>';
					echo '<tr><td colspan="2">���°汾��<script type="text/javascript" src="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=version"></script></td></tr>';
					showtablefooter();
					break;
				case 2:	
					showtableheader("��������");
					echo '<tr><td align="center">ID</td><td align="center">�û���</td><td align="center">������������</td><td align="center">������ս����</td><td align="center">�����������</td></tr>';
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
					echo '<form id="modscore" name="modscore" action="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=4" method="post"><tr class="tr3"><td colspan="5" align="center"><input type="hidden" id="pnum" name="pnum" value="'.$groupsnum.'" /><input type="hidden" id="pid" name="pid" value="'.$pid.'" /><input type="hidden" id="punit" name="punit" value="" /><input type="hidden" id="pscore" name="pscore" value="" /><input type="button" class="bta" value="ȫ������" onclick="resetScore()" /> <input type="button" class="bta" value="��������" onclick="checkScore()" /></td></tr></form>';
					echo '<script language="javascript">function checkScore(){var pnum = '.$groupsnum.';var punit = "";var pscore = "";for (var i=0; i<pnum; i++){punit += (punit==""?"":"|")+document.getElementById("ft1"+i).value+"|"+document.getElementById("ft2"+i).value+"|"+document.getElementById("ft3"+i).value;pscore += (pscore==""?"":"|")+document.getElementById("fe1"+i).value+"|"+document.getElementById("fe2"+i).value+"|"+document.getElementById("fe3"+i).value;}document.getElementById("punit").value = punit;document.getElementById("pscore").value = pscore;document.getElementById("modscore").submit();}function resetScore(){var pnum = '.$groupsnum.';var punit = "";var pscore = "";for (var i=0; i<pnum; i++){document.getElementById("ft1"+i).selectedIndex = 0;document.getElementById("ft2"+i).selectedIndex = 0;document.getElementById("ft3"+i).selectedIndex = 0;document.getElementById("fe1"+i).value = 0;document.getElementById("fe2"+i).value = 0;document.getElementById("fe3"+i).value = 0;}}</script>';
					showtablefooter();
					break;
				case 3:					
					$myexam = $emclass->create_sig('myexam', '0', $examore_version, $examinfo[0], $examinfo[1], TIMESTAMP);
					showtableheader("��������");
					echo '<tr><td style="text-align:center;"><input type="button" value="���뿼��è��������ϵͳ" onclick="doExam()" /></td></tr>';
					showtablefooter();
					echo '<script language="javascript">function doExam(){examOpen("http://www.examore.net/hack.php?H_name='.$examv.'&em_sid=0&em_eid=0&em_cs='.$examore_charset.'&'.$myexam.'","���Զ�examore");return false;}function examOpen(oUrl,oName){if(typeof(window.showModalDialog)!="undefined"){window.showModalDialog(oUrl, oName, "dialogWidth="+window.screen.availWidth+"px;dialogHeight="+window.screen.availHeight+"px;scroll=1;center=1;help=0;resizable=0;status=0;");}else{window.open(oUrl, oName, "menubar=0,toolbar=0,directories=0,location=0,status=0,scrollbars=1,width="+(window.screen.availWidth-10)+",height="+(window.screen.availHeight-30)+",top=0,left=0,resizable=0");}window.location.replace(window.location.href);}</script>';
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
						cpmsg('�����������ñ���ɹ���','action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2','succeed');
					}else{
						cpmsg('�����ˣ�','action=plugins&operation=config&do='.$pluginid.'&identifier=examore&pmod=admin&em_ac=2','error');
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
			showtableheader("��������");
			echo '<tr><td align="center" class="bold">��������д����ı�<span id="msg" name="msg" class="error">'.$msg.'</span></td></tr>';
			echo '<script language="javascript">';
			echo 'String.prototype.lTrim = function(){return this.replace(/^\s*/,"");};';
			echo 'String.prototype.rTrim = function(){return this.replace(/\s*$/,"");};';
			echo 'String.prototype.Trim = function(){return this.lTrim().rTrim();};';
			echo 'function checkApi(){var msg = document.getElementById("msg");var sn = document.getElementById("sn");var su = document.getElementById("su");var iu = document.getElementById("iu");var imgok = document.getElementById("imgok");var pp = document.getElementById("pp");';
			echo 'if (sn.value.length > 50 || sn.value.Trim() == ""){msg.innerHTML = "<br/><u>��վ����</u>����Ϊ�ջ򳬹�50����";sn.select();return false;}';
			echo 'if (su.value.length > 50 || su.value.Trim() == ""){msg.innerHTML = "<br/><u>��վ����</u>����Ϊ�ջ򳬹�50����";su.select();return false;}';
			echo 'if (iu.value.length > 100 || iu.value.Trim() == ""){msg.innerHTML = "<br/><u>��վͼƬ��ַ</u>����Ϊ�ջ򳬹�100����";iu.select();return false;}';
			echo 'if (imgok.value == ""){msg.innerHTML = "<br/><u>��վͼƬ�ߴ�</u>Ϊ760��100����";iu.select();return false;}';
			echo 'if (pp.value.Trim()=="" || isNaN(pp.value) || parseInt(pp.value)<1 || parseInt(pp.value)>50){msg.innerHTML = "<br/><u>ÿҳ��ʾ����</u>Ϊ1~50����";pp.select();return false;}';
			echo 'msg.innerHTML = "";if (confirm("������Ŀ�ύ������޷��޸�\\nȷ���������")){return true;}else{return false;}}';
			echo 'function changeIu(){var iu = document.getElementById("iu");var imgtr = document.getElementById("imgtr");var iuimg = document.getElementById("iuimg");var iusize = document.getElementById("iusize");var imgok = document.getElementById("imgok");var img = new Image();';
			echo 'img.onload = function(){iusize.innerHTML = this.width +"��"+ this.height;if (this.width>760 || this.height>100){imgok.value = "";}else{imgok.value = "1";}};';
			echo 'img.src = iu.value;iuimg.src = iu.value;';
			echo 'if (iu.value == ""){imgtr.style.visibility = "hidden";imgtr.style.display = "none";}else{imgtr.style.visibility = "visible";imgtr.style.display = "block";}}';
			echo '</script>';
			echo '<tr><td align="center">';
			echo '<table cellspacing="0" cellpadding="0" width="100%"><form action="http://www.examore.net/hack.php?H_name=plugin-'.$examore_charset.'&action=addnew" method="post" onsubmit="return checkApi()"><input type="hidden" id="apiurl" name="apiurl" value="'.$apiurl.'" /><input type="hidden" id="apipass" name="apipass" value="'.$exampass.'" />';
			echo '<tr><td width="30%" align="right" height="40"><b>��վ���ƣ�</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="sn" id="sn" value="" maxlength="50" style="width:400px;" /><br/>���ó���50����</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>��վ������</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="su" id="su" value="" maxlength="50" style="width:400px;" /><br/>��ʽ��www.examore.com</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>��վͼƬ��</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="iu" id="iu" value="" maxlength="100" style="width:400px;" onchange="changeIu()" /><input type="hidden" id="imgok" name="imgok" value="" /><br/>��ʽ��http://www.examore.com/images/logo.gif<br/>�ߴ磺760��100����</td></tr>';
			echo '<tr id="imgtr" name="imgtr" style="visibility:hidden; display:none;"><td colspan="2" align="center" height="40"><img src="" id="iuimg" name="iuimg" /><br/><span id="iusize" name="iusize"></span></td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>ѧ�����ã�</b></td><td width="70%" align="left" height="40"><select type="text" class="input" name="ks" id="ks"><option value="1">����Ա</option><option value="6">����</option><option value="9">����һ��</option><option value="13">����</option></select><br/>��Ŀѡ���󽫲��������޸�</td></tr>';
			echo '<tr><td width="30%" align="right" height="40"><b>ÿҳ������</b></td><td width="70%" align="left" height="40"><input type="text" class="input" name="pp" id="pp" value="" maxlength="2" style="width:20px;" /><br/>����ÿҳ��ʾ������������1~50֮��</td></tr>';
			echo '<tr><td colspan="2" align="center" height="40"><input type="submit" class="bta" value="�������߿��Է���" /></td></tr></form></table></td></tr>';
			showtablefooter();
		}
	}
}else{
	if (!empty($ea) && strpos($ea,"||")==40 && strpos($ea,"||",42)==74){
		if (empty($pass)){
			if (!empty($examore) && is_numeric($examore)){
				DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$ea}||{$examparam['examopen']}||{$examparam['examlevel']}||{$examparam['examcredit']}||{$examparam['examfee']}||0||0' WHERE i_d = 1");
				cpmsg('��������ɹ�����ȴ���ˣ�','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'succeed');
			}else{
				cpmsg('�����ˣ�','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
			}
		}else{
			if (substr($examore,42,32) == $pass){
				DB::query("UPDATE ".DB::table('examore')." SET e_a = '{$ea}||{$examparam['examopen']}||{$examparam['examlevel']}||{$examparam['examcredit']}||{$examparam['examfee']}||0||0' WHERE i_d = 1");
				cpmsg('��������ɹ�����ȴ���ˣ�','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'succeed');
			}else{
				cpmsg('�����ˣ�','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
			}
		}
	}else{
		cpmsg('�����ˣ�','admincp.php?action=plugins&operation=config&identifier=examore&pmod=admin&do='.$pluginid,'error');
	}
}
?>