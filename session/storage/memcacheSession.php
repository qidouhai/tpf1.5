<?php
/**
 * memcache存储session
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.0
 * $Id: memcacheSession.php 197 2016-05-31 08:30:14Z charles_li $
 */
class memcacheSession extends AbstractSession {
	/**
	 * memcache对象
	 * @var Memcache
	 */
	private $_memcache = null;
	public function __construct() {
		try {
			$this->_check();
			$this->_init();
		}catch(LoaderException $e) {
			$e->getInfo();
			die;
		}
	}
	/**
	 * 初始化数据
	 */
	private function _init() {
		if(!self::$_isSet) {
			$this->_sessconfig = self::$_config['session'];
			if(!isset($this->_memcache)) {
				$this->_memcache = new Memcache();
				try {
				    foreach($this->_sessconfig['memcachehost'] as $k=>$v) {
				        $this->_memcache->addServer($v,$this->_sessconfig['memcacheport'][$k]);
				        $this->_checkStatus($v,$this->_sessconfig['memcacheport'][$k]);
				    }
				}catch(SessionException $e) {
				        $e->getInfo();
				}
			}
		}
		parent::__construct();
	}
	/**
	 * 读取session值
	 * @param string $sessID
	 * @return string
	 * @see Abstractsession::read()
	 */
	public function read($sessID) {
		$_sessid = 'sess_'.$sessID;
		$this->_checkExpiry($_sessid);
		return $this->_memcache->get($_sessid);
	}
	/**
	 * 写入session
	 * @param string $sessID
	 * @param string $sessData
	 * @return void
	 * @see Abstractsession::write()
	 */
	public function write($sessID, $sessData) {
		$_sessid = 'sess_'.$sessID;
		$_expiry = time() + $this->_sessconfig['lifetime'];
		if($this->_memcache->get($_sessid.'_expiry')) {
			$this->_memcache->replace($_sessid.'_expiry',$_expiry);
		}else $this->_memcache->set($_sessid.'_expiry',$_expiry);
		if($this->_memcache->get($_sessid)) {
			$this->_memcache->replace($_sessid,$sessData);
		}else {
			$this->_memcache->set($_sessid,$sessData);
		}
		return TRUE;
	}
	/**
	 * 删除一个session
	 * @param string $sessID
	 * @return boolean
	 * @see Abstractsession::destroy()
	 */
	public function destroy($sessID) {
		$_sessid = 'sess_'.$sessID;
		return $this->_memcache->delete($_sessid);
	}
	/**
	 * 检测加载模块
	 * @throws LoaderException
	 */
	private function _check() {
		if(!extension_loaded('memcache') || !class_exists('Memcache'))
			throw new LoaderException(Loader::getErrMsg('SESSION_ISNOT_MODULE',array('memcache')),2);
	}
	/**
	 * 检测是否过期，过期则删除
	 * @param string $sessid
	 * @return void
	 */
	private function _checkExpiry($sessid) {
		$_expiry = $this->_memcache->get($sessid.'_expiry');
		if($_expiry < time()) {
			$this->_memcache->delete($sessid.'_expiry');
			$this->_memcache->delete($sessid);
		}
	}
	/**
	 * 检测服务器状态
	 * @param string $host 服务器ip地址
	 * @param int $port 服务器端口
	 * @throws SessionException
	 */
	private function _checkStatus($host,$port) {
	    if(!$this->_memcache->getserverstatus($host,$port)) {
	         throw new SessionException(Loader::getErrMsg('SESSION_CONNECT_FAIL',array($host.':'.$port)),4);
	    }
	}
}

?>