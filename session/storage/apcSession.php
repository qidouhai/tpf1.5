<?php
/**
 * apc存储session
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.0
 * $Id: apcSession.php 197 2016-05-31 08:30:14Z charles_li $
 */
class apcSession extends AbstractSession {
	public function __construct() {
		try {
			$this->_check();
			$this->_init();
		}catch(LoaderException $e) {
			$e->getInfo();
			die;
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
		return (string) apc_fetch('sess_'.$sessID);
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
		return apc_store($_sessid, $sessData, ini_get("session.gc_maxlifetime"));
	}
	/**
	 * 删除一个session
	 * @param string $sessID
	 * @return boolean
	 * @see Abstractsession::destroy()
	 */
	public function destroy($sessID) {
		return apc_delete('sess_'.$sessID);
	}
	/**
	 * 检测加载模块
	 * @throws LoaderException
	 */
	private function _check() {
		if(!extension_loaded('apc')) 
			throw new LoaderException(Loader::getErrMsg('SESSION_ISNOT_MODULE',array('apc')),2);
	}
}

?>