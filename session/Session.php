<?php
/**
 * session工厂类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.4
 * $Id: Session.php 223 2017-08-19 15:59:44Z charles_li $
 */
class Session extends Tbs {
	/**
	 * session对象
	 * @var Abstractsession
	 */
	private $session = null;
	private static $_isStarted = false;
	private static $_userAgent = '';
	public function __construct() {
		$this->_init();
	}
	/**
	 * 开启session
	 * @param string $cacheStatus //nocache/private/private_no_cache/public
	 */
	public function startSession($cacheStatus) {
		if(!self::$_isStarted) {
		    session_cache_limiter($cacheStatus);
		    $this->start();
		}
	}
	/**
	 * 初始化session设置
	 * @param array $config
	 */
	private function _init() {
		session_name(self::$_config['session']['name']);
		if(self::$_config['session']['cookiedomain']!='') ini_set('session.cookie_domain',self::$_config['session']['cookiedomain']);
		ini_set('session.cookie_path', self::$_config['session']['cookiepath']);
		ini_set('session.use_trans_sid', '0');
		ini_set('session.gc_maxlifetime',self::$_config['session']['lifetime']);
		self::$_userAgent = $_SERVER['HTTP_USER_AGENT'];
	}
	/**
	 * 构建session对象
	 * @param array $config
	 * @return void
	 */
	private function _factory($storage) {
		$this->session = $this->_getStorage($storage);
	}
	/**
	 * 实例化session对象
	 * @return session
	 */
	public static function getInstance() {
		$_id = 'Session';
		if(!isset(self::$_inst[$_id])) {
			self::$_inst[$_id] = new self();
		}
		return self::$_inst[$_id];
	}
	/**
	 * 获取存储方式
	 * @param string $type
	 * @throws SessionException
	 * @Abstractsession
	 */
	private function _getStorage($type) {
		$_id = 'SessionStorage';
		if (!isset(self::$_inst[$_id])){
			$_storage = strtolower($type).'Session';
			if(!file_exists(TF_PATH.'session'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.$_storage.TF_EXT)) {
				throw new SessionException(Loader::getErrMsg('SESSION_ISNOT_STORAGE',array($type)),2);
			}
			include TF_PATH.'session'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.$_storage.TF_EXT;
			self::$_inst[$_id] = new $_storage();
		}
		return self::$_inst[$_id];
	}
	/**
	 * 增加一个session值
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public static function add($key,$value) {
	    if(self::$_config['session']['useragent'] && !isset($_SESSION['TF_USERAGENT'])) {
	        $_SESSION['TF_USERAGENT'] = md5(self::$_userAgent);
	    }
		$_SESSION[$key] = $value;
	}
	/**
	 * 获取一个session值
	 * @param string $key
	 * @return string/array
	 */
	public static function get($key='') {
	    if(self::$_config['session']['useragent'] && (!isset($_SESSION['TF_USERAGENT']) || $_SESSION['TF_USERAGENT']!=md5(self::$_userAgent))) {
	        return ;
	    }
		if($key=='') {
			return $_SESSION;
		}else {
			if(!isset($_SESSION[$key]))
				return ;
			else
			return $_SESSION[$key];
		}
	}
	/**
	 * 销毁所有session值
	 */
	public function destroy() {
		if (!empty($_SESSION)) {
	        $_SESSION = array();
	    }
	    session_unset();
		session_destroy();
		self::$_isStarted =false;
	}
	/**
	 * session提交，处理时间较长的业务建议使用commit后重新使用start打开
	 */
	public function commit() {
		session_commit();
	}
	/**
	 * 开启session
	 */
	public function start() {
	    if(!self::$_isStarted) {
        session_write_close();
		$this->_factory(self::$_config['session']['storage']);	//生产session对象		
		session_start();
		self::$_isStarted = true;
	    }
	}
	/**
	 * 删除一个session值
	 * @param string $key
	 */
	public function del($key) {
		unset($_SESSION[$key]);
	}
	/**
	 * 释放session写入锁
	 */
	public function close() {
		session_write_close();
		self::$_isStarted = false;
	}
	public function __destruct() {
	    $this->close();
	}
}
class SessionException extends TException {
	
}

?>