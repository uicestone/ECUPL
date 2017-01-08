<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_space.php 20882 2011-03-07 07:22:17Z lifangming $
 */

$lang = array(
	'hour' => 'Сʱ',
	'before' => 'ǰ',
	'minute' => '����',
	'second' => '��',
	'now' => '����',
	'dot' => '��',
	'poll' => 'ͶƱ',
	'blog' => '��־',
	'friend_group_default' => '����',
	'friend_group_1' => 'ͨ����վ��ʶ',
	'friend_group_2' => 'ͨ�����ʶ',
	'friend_group_3' => 'ͨ��������ʶ',
	'friend_group_4' => '����',
	'friend_group_5' => 'ͬ��',
	'friend_group_6' => 'ͬѧ',
	'friend_group_7' => '����ʶ',
	'friend_group' => '�Զ���',
	'wall' => '����',
	'pic_comment' => 'ͼƬ����',
	'blog_comment' => '��־����',
	'clickblog' => '��־��̬',
	'clickpic' => 'ͼƬ��̬',
	'clickthread' => '�����̬',
	'share_comment' => '��������',
	'share_notice' => '����',
	'doing_comment' => '��¼�ظ�',
	'friend_notice' => '����',
	'poll_comment' => 'ͶƱ����',
	'poll_invite' => 'ͶƱ����',
	'default_albumname' => 'Ĭ�����',
	'credit' => '����',
	'credit_unit' => '��',
	'man' => '��',
	'woman' => 'Ů',
	'gender_0' => '����',
	'gender_1' => '��',
	'gender_2' => 'Ů',
	'year' => '��',
	'month' => '��',
	'day' => '��',
	'unmarried' => '����',
	'married' => '�ǵ���',
	'hidden_username' => '����',
	'gender' => '�Ա�',
	'age' => '��',
	'comment' => '����',
	'reply' => '�ظ�',
	'from' => '����',
	'anonymity' => '����',
	'viewmore' => '�鿴����',
	'constellation_1' => 'ˮƿ��',
	'constellation_2' => '˫����',
	'constellation_3' => '������',
	'constellation_4' => '��ţ��',
	'constellation_5' => '˫����',
	'constellation_6' => '��з��',
	'constellation_7' => 'ʨ����',
	'constellation_8' => '��Ů��',
	'constellation_9' => '�����',
	'constellation_10' => '��Ы��',
	'constellation_11' => '������',
	'constellation_12' => 'Ħ����',
	'zodiac_1' => '��',
	'zodiac_2' => 'ţ',
	'zodiac_3' => '��',
	'zodiac_4' => '��',
	'zodiac_5' => '��',
	'zodiac_6' => '��',
	'zodiac_7' => '��',
	'zodiac_8' => '��',
	'zodiac_9' => '��',
	'zodiac_10' => '��',
	'zodiac_11' => '��',
	'zodiac_12' => '��',

	'credits' => '����',
	'usergroup' => '�û���',
	'friends' => '����',
	'blogs' => '��־',
	'threads' => '����',
	'albums' => '���',
	'sharings' => '����',
	'space_views' => '���� <strong class="xi1">{views}</strong> �����ù�',
	'views' => '�ռ�鿴��',
	'block1' => '�Զ���ģ��1',
	'block2' => '�Զ���ģ��2',
	'block3' => '�Զ���ģ��3',
	'block4' => '�Զ���ģ��4',
	'block5' => '�Զ���ģ��5',
	'blockdata' => array('personalinfo' => '��������', 'profile' => 'ͷ��', 'doing' => '��¼', 'feed' => '��̬',
				'blog' => '��־', 'stickblog' => '�ö���־', 'album' => '���', 'friend' => '����',
				'visitor' => '����ÿ�', 'wall' => '���԰�', 'share' => '����',
				'thread' => '����', 'group'=>$_G[setting][navs][3][navname],'music'=>'���ֺ�',
				'statistic' => 'ͳ����Ϣ','myapp' => 'Ӧ��',
				'block1'=>'����ģ��1', 'block2'=>'����ģ��2', 'block3'=>'����ģ��3',
				'block4'=>'����ģ��4','block5'=>'����ģ��5'),

	'block_title' => '<div class="blocktitle title"><span>{bname}</span>{more}</div>',
	'blog_li' => '<dl class="bbda cl"><dt><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">{subject}</a><span class="xg2 xw0"> {date}</span></dt>',
	'blog_li_img' => '<dd class="atc"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank"><img src="{src}" class="summaryimg" /></a></dd>',
	'blog_li_ext' => '<dd class="xg1"><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}" target="_blank">({viewnum})���Ķ�</a><span class="pipe">|</span><a href="home.php?mod=space&uid={uid}&do=blog&id={blogid}#comment" target="_blank">({replynum})������</a></dd>',
	'album_li' => '<li style="width:70px"><div class="c"><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, ���� {date}"><img src="{src}" alt="{albumname}" width="70" height="70" /></a></div><p><a href="home.php?mod=space&uid={uid}&do=album&id={albumid}" target="_blank" title="{albumname}, ���� {date}">{albumname}</a></p><span>ͼƬ��: {picnum}</span></li>',
	'doing_li' => '<li>{message}</li><br />{date} {from} �ظ�({replynum})',
	'visitor_anonymity' => '<div class="avatar48"><img src="image/magic/hidden.gif" alt="����"></div><p>����</p>',
	'visitor_list' => '<a href="home.php?mod=space&uid={uid}" target="_blank" class="avt"><em class="{class}"></em>{avatar}</a><p><a href="home.php?mod=space&uid={uid}" title="{username}">{username}</a></p>',
	'wall_form' => '<div class="space_wall_post">
						<form action="home.php?mod=spacecp&ac=comment" id="quickcommentform_{uid}" name="quickcommentform_{uid}" method="post" autocomplete="off" onsubmit="ajaxpost(\'quickcommentform_{uid}\', \'return_commentwall_{uid}\');doane(event);">
							'.($_G['uid'] ? '<span id="message_face" onclick="showFace(this.id, \'comment_message\');return false;" class="cur1"><img src="static/image/common/facelist.gif" alt="facelist" class="mbn vm" /></span>
							<br /><textarea name="message" id="comment_message" class="pt" rows="3" cols="60" onkeydown="ctrlEnter(event, \'commentsubmit_btn\');" style="width: 90%;"></textarea>
							<input type="hidden" name="refer" value="home.php?mod=space&uid={uid}" />
							<input type="hidden" name="id" value="{uid}" />
							<input type="hidden" name="idtype" value="uid" />
							<input type="hidden" name="commentsubmit" value="true" />' :
							'<div class="pt hm">����Ҫ��¼��ſ������� <a href="member.php?mod=logging&action=login" onclick="showWindow(\'login\', this.href)" class="xi2">��¼</a> | <a href="member.php?mod='.$_G['setting']['regname'].'" class="xi2">'.$_G['setting']['reglinkname'].'</a></div>').'
							<p class="ptn"><button '.($_G['uid'] ? 'type="submit"' : 'type="button" onclick="showWindow(\'login\', \'member.php?mod=logging&action=login&guestmessage=yes\')"').' name="commentsubmit_btn" value="true" id="commentsubmit_btn" class="pn"><strong>����</strong></button></p>
							<input type="hidden" name="handlekey" value="commentwall_{uid}" />
							<span id="return_commentwall_{uid}"></span>
							<input type="hidden" name="formhash" value="{FORMHASH}" />
						</form>'.
						($_G['uid'] ? '<script type="text/javascript">
							function succeedhandle_commentwall_{uid}(url, msg, values) {
								wall_add(values[\'cid\']);
							}
						</script>' : '').'
					</div>',
	'wall_li' => '<dl class="bbda cl" id="comment_{cid}_li">
				<dd class="m avt">
				{author_avatar}
				</dd>
				<dt>
				{author}
				<span class="y xw0">{op}</span>
				<span class="xg1 xw0">{date}</span>
				<span class="xgl">{moderated}</span>
				</dt>
				<dd id="comment_{cid}">{message}</dd>
				</dl>',
	'wall_more' => '<dl><dt><span class="y xw0"><a href="home.php?mod=space&uid={uid}&do=wall">�鿴ȫ��</a></span><dt></dl>',
	'wall_edit' => '<a href="home.php?mod=spacecp&ac=comment&op=edit&cid={cid}&handlekey=editcommenthk_{cid}" id="c_{cid}_edit" onclick="showWindow(this.id, this.href, \'get\', 0);">�༭</a> ',
	'wall_del' => '<a href="home.php?mod=spacecp&ac=comment&op=delete&cid={cid}&handlekey=delcommenthk_{cid}" id="c_{cid}_delete" onclick="showWindow(this.id, this.href, \'get\', 0);">ɾ��</a> ',
	'wall_reply' => '<a href="home.php?mod=spacecp&ac=comment&op=reply&cid={cid}&handlekey=replycommenthk_{cid}" id="c_{cid}_reply" onclick="showWindow(this.id, this.href, \'get\', 0);">�ظ�</a>',
	'group_li' => '<li><a href="forum.php?mod=group&fid={groupid}" target="_blank"><img src="{icon}" alt="{name}" /></a><p><a href="forum.php?mod=group&fid={groupid}" target="_blank">{name}</a></p></li>',
	'poll_li' => '<div class="c z"><img alt="poll" src="static/image/feed/poll.gif" alt="poll" class="t" /><h4 class="h"><a target="_blank" href="forum.php?mod=viewthread&tid={tid}" target="_blank">{subject}</a></h4><div class="mtn xg1">����ʱ�䣺{dateline}</div></div>',
	'myapp_li_icon' => '<li><img src="{icon}" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/{appid}\'" alt="{appname}" class="vm" /> <a href="userapp.php?mod=app&id={appid}">{appname}</a></li>',
	'myapp_li_logo' => '<li><a href="userapp.php?mod=app&id={appid}"><img src="http://appicon.manyou.com/logos/{appid}" alt="{appname}" /><p><a href="userapp.php?mod=app&id={appid}">{appname}</a></p></li>',
	'music_no_content' => '��û���������ֺе�����',
	'block_profile_diy' => 'װ��ռ�',
	'block_profile_wall' => '�鿴����',
	'block_profile_avatar' => '�༭ͷ��',
	'block_profile_update' => '��������',
	'block_profile_wall_to_me' => '��������',
	'block_profile_friend_add' => '��Ϊ����',
	'block_profile_friend_ignore' => '�������',
	'block_profile_poke' => '����к�',
	'block_profile_sendmessage' => '������Ϣ',
	'block_doing_reply' => '�ظ�',
	'block_doing_no_content' => '���ڻ�û�м�¼',
	'block_doing_no_content_publish' => '��<a href ="home.php?mod=space&uid={uid}&do=doing&view=me&from=space">���¼�¼</a>',
	'block_blog_no_content' => '���ڻ�û����־',
	'block_blog_no_content_publish' => '��<a href ="home.php?mod=spacecp&ac=blog">������־</a>',
	'block_album_no_content' => '���ڻ�û�����',
	'block_album_no_content_publish' => '��<a href ="home.php?mod=spacecp&ac=upload">�ϴ�ͼƬ</a>',
	'block_feed_no_content' => '���ڻ�û�ж�̬',
	'block_thread_no_content' => '���ڻ�û������',
	'block_thread_no_content_publish' => '��<a href ="forum.php?mod=misc&action=nav&special=0&from=home" onclick="showWindow(\'nav\', this.href);return false;">��������</a>',
	'block_friend_no_content' => '���ڻ�û�к���',
	'block_friend_no_content_publish' => '��<a href ="home.php?mod=spacecp&ac=search">���Һ���</a> �� <a href ="home.php?mod=spacecp&ac=invite">�������</a>',
	'block_visitor_no_content' => '���ڻ�û�зÿ�',
	'block_visitor_no_content_publish' => '��<a href ="home.php?mod=space&do=friend&view=online&type=member">ȥ������</a>',
	'block_share_no_content' => '���ڻ�û�з���',
	'block_wall_no_content' => '���ڻ�û������',
	'block_group_no_content' => '���ڻ�û��Ⱥ��',
	'block_group_no_content_publish' => '��<a href ="forum.php?mod=group&action=create">�����Լ���Ⱥ��</a> �� <a href ="group.php?mod=index">����Ⱥ��</a>',
	'block_group_no_content_join' => '��<a href ="group.php?mod=index">����Ⱥ��</a>',
	'block_myapp_no_content' => '���ڻ�û��Ӧ��',
	'block_myapp_no_content_publish' => '��<a href ="userapp.php?mod=manage&my_suffix=/app/list">��Ҫ��Ӧ��</a>',
	'block_view_noperm' => '��Ȩ�鿴',
	'block_view_profileinfo_noperm' => '�������������Ȩ�鿴',
	'click_play' => '�������',
	'click_view' => '����鿴',
	'feed_view_only' => 'ֻ�����ද̬',

	'export_pm' => '��������Ϣ',
	'pm_export_header' => 'Discuz! X ����Ϣ��¼(����Ϣ��¼��֧�����µ���)',
	'pm_export_touser' => '��Ϣ����: {touser}',
	'pm_export_subject' => 'Ⱥ�Ļ���: {subject}',
	'all' => 'ȫ��',
	'manage_post' => '��������',
	'manage_album' => '�������',
	'manage_blog' => '������־',
	'manage_comment' => '��������',
	'manage_doing' => '������¼',
	'manage_feed' => '������̬',
	'manage_group_prune' => 'Ⱥ������',
	'manage_group_threads' => 'Ⱥ������',
	'manage_share' => '��������',
	'manage_pic' => '����ͼƬ',

	'sb_blog' => '{who}����־',
	'sb_album' => '{who}�����',
	'sb_space' => '{who}�Ŀռ�',
	'sb_feed' => '{who}�Ķ�̬',
	'sb_doing' => '{who}�ļ�¼',
	'sb_sharing' => '{who}�ķ���',
	'sb_friend' => '{who}�ĺ���',
	'sb_wall' => '{who}�����԰�',
	'sb_profile' => '{who}�ĸ�������',
	'sb_thread' => '{who}������',
	'doing_you_can' => '����Ը��¼�¼, �ú�����֪��������ʲô...',
	'block_profile_all' => '<p style="text-align: right;"><a href="home.php?mod=space&uid={uid}&do=profile">�鿴ȫ����������</a></p>',
	'block_profile_edit' => '<span class="y xw0"><a href="home.php?mod=spacecp&ac=profile">�༭�ҵ�����</a></span>',

	'viewthread_userinfo_hour' => 'Сʱ',
	'viewthread_userinfo_uid' => 'UID',
	'viewthread_userinfo_posts' => '����',
	'viewthread_userinfo_threads' => '����',
	'viewthread_userinfo_doings' => '��¼',
	'viewthread_userinfo_blogs' => '��־',
	'viewthread_userinfo_albums' => '���',
	'viewthread_userinfo_sharings' => '����',
	'viewthread_userinfo_friends' => '����',
	'viewthread_userinfo_digest' => '����',
	'viewthread_userinfo_credits' => '����',
	'viewthread_userinfo_readperm' => '�Ķ�Ȩ��',
	'viewthread_userinfo_regtime' => 'ע��ʱ��',
	'viewthread_userinfo_lastdate' => '����¼',
	'viewthread_userinfo_oltime' => '����ʱ��',

);

?>