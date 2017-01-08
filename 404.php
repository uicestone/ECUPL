<?php
require 'jump.php';
function getUID($string){
	$pat_num	='/(?<=uid-)(.*?)(?=-)/i';
	preg_match($pat_num, $string, $result);
	if(!isset($result[0])){
		$pat_num	='/(?<=uid-)(.*?)(?=.html)/i';
		preg_match($pat_num, $string, $result);
	}
	if(isset($result[0])){
		return $result[0];
	}
}
function getFID($string){
	$pat_num	='/(?<=fid-)(.*?)(?=-)/i';
	preg_match($pat_num, $string, $result);
	if(!isset($result[0])){
		$pat_num	='/(?<=fid-)(.*?)(?=.html)/i';
		preg_match($pat_num, $string, $result);
	}
	if(isset($result[0])){
		return $result[0];
	}
}
function getTID($string){
	$pat_num	='/(?<=tid-)(.*?)(?=-)/i';
	preg_match($pat_num, $string, $result);
	if(!isset($result[0])){
		$pat_num	='/(?<=tid-)(.*?)(?=.html)/i';
		preg_match($pat_num, $string, $result);
	}
	if(isset($result[0])){
		return $result[0];
	}
}
if($uid=getUID($_SERVER['REQUEST_URI'])){
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: http://www.ecupl.org/space-uid-'.$uid.'.html');
	exit();
}
if($fid=getFID($_SERVER['REQUEST_URI'])){
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: http://www.ecupl.org/forum-'.$fid.'-1.html');
	exit();
}
if($tid=getTID($_SERVER['REQUEST_URI'])){
	header("HTTP/1.1 301 Moved Permanently");
	header('Location: http://www.ecupl.org/thread-'.$tid.'-1-1.html');
	exit();
}



header('HTTP/1.0 404 Not Found');
header('Location: http://www.ecupl.org/thread-9216-1-1.html');
exit();
?>