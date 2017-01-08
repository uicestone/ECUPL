<?php



/*

Discuz X2 modified By Marshal,2011-07-08

本插件支持X2版伪静态切换，并支持不开启伪静态

www.modchina.com Marshal版权所有



require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$discuz->init();

*/

if(!defined('IN_DISCUZ')) {

	exit('Access Denied');

}



$article = 1000; //生成多少文章 

$thread = 20000; //生成多少广场帖子 

$blogs = 1000; //生成多少家园日志 

$exceptUrl = array ('mail.ecupl.org');//要过滤的关键字数组

//DiscuzX2 sitemap by marshal(www.modchina.com)
	

function Marshal($rewrite,$row){

	global $_G,$discuz;

	$rewriterule = $_G['setting']['rewriterule'];

	$status = $_G['setting']['rewritestatus'];

	if(in_array($rewrite,$status)){

	 $mod=$rewriterule[$rewrite];

	 $mod=preg_replace("/{page}/","1",$mod);

	 $mod=preg_replace("/{prevpage}/","1",$mod);	 

	 $mod=str_replace("{id}","".$row."",$mod);

	 $mod=str_replace("{fid}","".$row."",$mod);	

	 $mod=str_replace("{tid}","".$row."",$mod);

	 $mod=str_replace("{blogid}","".$row."",$mod);

	 }

	 elseif($rewrite =="portal_article"){$mod="portal.php?mod=view&aid=".$row."";}

	 elseif($rewrite =="forum_forumdisplay"){$mod="forum.php?mod=forumdisplay&fid=".$row."";}

	 elseif($rewrite =="forum_viewthread"){$mod="forum.php?mod=viewthread&tid=".$row."";}

	 elseif($rewrite =="home_blog"){$mod="home.php?mod=space&do=blog&id=".$row."";}

	 return $mod;

}

//





$s=array('baidu','google');

class sitemap_xml

{

	var $xml=array('');

	var $site;

	var $index=0;

	var $sitemap_file='sitemap.xml';

	var $size;

	var $sizet=0;


	function sitemap_xml($sitemap_file='sitemap.xml',$site='baidu')

	{

		$this->site=$site;

		$this->sitemap_file=$sitemap_file;

		$this->size=$site=='baidu'?1024*10240:1024*10240;

	}

	

	function add_url($loc,$lastmod,$changefreq,$priority){

		static $size=0;

		$size=$size==0?$this->size:$size;

		$str="<url><loc>".str_replace('&','&amp;',$loc)."</loc><lastmod>$lastmod</lastmod><changefreq>$changefreq</changefreq><priority>$priority</priority></url>";

		$this->xml[$this->index].=$str;

		if($size<102400){

			$this->index++;

			$size=$this->size;

			$this->xml[$this->index]='';

		}

		$size-=strlen($str);

	}

	function write(){

		for($i=0;$i<=$this->index;$i++){

			@file_put_contents($this->sitemap_file.'_'.$i.'.xml','<?xml version="1.0" encoding="UTF-8"?> <urlset '.($this->site=='google'?' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"':'').'> '.$this->xml[$i].'</urlset>');

		}

		$this->write_index();

	}

	function write_index(){

		global $_G;

		$size=$this->size;

		$text='<?xml version="1.0" encoding="UTF-8"?>';

		$text.=($this->site=='google')?'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">':'<sitemapindex>';

		for($i=0;$i<=$this->index && $size>102400;$i++){

			$str='<sitemap><loc>'.$_G['siteurl'].$this->sitemap_file.'_'.$i.'.xml</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';

			$text.=$str;

			$size-=strlen($str);

		}

		$text.='</sitemapindex>';

		return file_put_contents($this->sitemap_file,$text);

	}

	

}

function exception($url,$exceptUrl){
	$return = false;
	for ($i=0;$i<count($exceptUrl);$i++){
		if (strpos($url,$exceptUrl[$i])!==false){
			$return= true;break;
		}
	}
	return $return;
}//增加网址过滤，by Uice Stone

foreach($s as $key)

	$ct[]=new sitemap_xml('sitemap_'.$key.'.xml',$key);





$query=DB::query('SELECT id,parentid,url FROM '.DB::table('common_nav').' WHERE available=1 ');

while($row=DB::fetch($query)){

	if($row['url'] != "#" && !empty($row['url'])  && !exception($row['url'],$exceptUrl)){

	foreach($ct as $key=>$item){

		$url=$row['url'];

		$preg='|^http://|';  //DiscuzX2 sitemap by marshal(www.modchina.com)

			if(!preg_match($preg,$url)) {  

				$url=$_G['siteurl'].$url;

			}

		$ct[$key]->add_url($url,date('Y-m-d'),'always','1.0');

		}

	}

}



//1.门户文章DiscuzX2 sitemap by marshal(www.modchina.com)

$query =DB::query('SELECT aid,dateline FROM '.DB::TABLE('portal_article_content').'  LIMIT 0 , '.$article.' ');

while($row = DB::fetch($query)) {

	$row['url']=Marshal("portal_article",$row['aid']);

	if(exception($row['url'],$exceptUrl)){
		continue;
	}

	foreach($ct as $key => $item) {

		$ct[$key]->add_url($_G['siteurl'].$row['url'],date('Y-m-d',$row['dateline']),'daily','0.8');

	}

}

//2.广场板块DiscuzX2 sitemap by marshal(www.modchina.com)

$query=DB::query("SELECT fid,type,status FROM ".DB::table('forum_forum')." WHERE status='1' And type='forum' order by fid ");

while($row=DB::fetch($query)){

	$row['url']=Marshal("forum_forumdisplay",$row['fid']);
	
	if(exception($row['url'],$exceptUrl)){
		continue;
	}
	foreach($ct as $key=>$item){

		$ct[$key]->add_url($_G['siteurl'].$row['url'],date('Y-m-d'),'always','0.9');

	}

}

//3.广场帖子DiscuzX2 sitemap by marshal(www.modchina.com)

$query=DB::query('SELECT tid,lastpost FROM '.DB::table('forum_thread').' order by tid desc limit 0 , '.$thread.' ');

while($row=DB::fetch($query)){

	$row['url']=Marshal("forum_viewthread",$row['tid']);
	
	if(exception($row['url'],$exceptUrl)){
		continue;
	}
	
	foreach($ct as $key=>$item){

		$ct[$key]->add_url($_G['siteurl'].$row['url'],date('Y-m-d',$row['lastpost']),'daily','0.7');

	}

}

//4.家园日志DiscuzX2 sitemap by marshal(www.modchina.com)

/*$query=DB::query('SELECT blogid,uid,dateline FROM '.DB::table('home_blog').' order by blogid desc limit 0, '.$blogs.' ');

while($row=DB::fetch($query)){

	$row['url']=Marshal("home_blog",$row['blogid']);

	$row['url']=str_replace("{uid}","".$row['uid']."",$row['url']);
	
	if(exception($row['url'],$exceptUrl)){
		continue;
	}
	
	foreach($ct as $key=>$item){

		$ct[$key]->add_url($_G['siteurl'].$row['url'],date('Y-m-d',$row['dateline']),'daily','0.6');

	}

}*/

foreach($ct as $item){

	$item->write();

}



?>