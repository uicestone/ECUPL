<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once './source/plugin/renren/renren.function.php';

class renren_connect {
	var $session = array(  'session_key' => '',
		'user'=>'',
		'ss'=>'',
		'expires'=>''
		);

	var $api_key = '';
	var $secret = '';
	var $sig = '';
	var $rr_uid;
	var $dz_uid;

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new renren_connect();
		}
		return $object;
	}

	function renren_connect() {
		global $_G;
		loadcache('plugin');
		$this->api_key = $_G['cache']['plugin']['renren']['api_key'];
		$this->secret = $_G['cache']['plugin']['renren']['secret'];
	}

	function init($discuz_uid='',$dz_username,$email,$avatar,$password) {
		$this->_init_session();
			$this->rr_uid = $this->session['user'];
			$this->dz_uid = rr_get_bind_dz_uid($this->rr_uid);
			if(empty($this->dz_uid) && empty($discuz_uid))
				$this->dz_uid = rr_register($this->rr_uid,$dz_username,$email,$avatar,$password);

			if(empty($discuz_uid) || $discuz_uid == $this->dz_uid) {
				$GLOBALS['_G']['renren']['session'] = $this->session;
				$GLOBALS['_G']['renren']['rr_uid'] = $this->rr_uid;
				$GLOBALS['_G']['renren']['dz_uid'] = $this->dz_uid;
				$GLOBALS['_G']['renren']['api_key'] = $this->api_key;
				$GLOBALS['_G']['renren']['secret'] = $this->secret;
			}
	}

	function inits($discuz_uid='') {
		$this->_init_session();
			$this->rr_uid = $this->session['user'];
			$this->dz_uid = rr_get_bind_dz_uid($this->rr_uid);
			if(empty($discuz_uid) || $discuz_uid == $this->dz_uid) {
				$GLOBALS['_G']['renren']['session'] = $this->session;
				$GLOBALS['_G']['renren']['rr_uid'] = $this->rr_uid;
				$GLOBALS['_G']['renren']['dz_uid'] = $this->dz_uid;
				$GLOBALS['_G']['renren']['api_key'] = $this->api_key;
				$GLOBALS['_G']['renren']['secret'] = $this->secret;
			}
	}

	function _init_session() {
		foreach(array_keys($this->session) as $key) {
			$this->session[$key] = getcookie($this->api_key.'_'.$key);
		}
		//$this->sig = getgpc($this->api_key, 'C');
	}

	function verify() {
		$time =date();
		if(empty($this->session['expires']) || $time > intval($this->session['expires']) ) {
			return false;
		} else {
			return rr_generate_sig($this->session, $this->secret) == $this->sig;
		}
	}
}
?>