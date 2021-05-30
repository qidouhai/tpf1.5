<?php
/**
 * eaccelerator存储session
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.0
 * $Id: eacceleratorSession.php 197 2016-05-31 08:30:14Z charles_li $
 */
class eacceleratorSession extends AbstractSession {
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
		return (string) eaccelerator_get('sess_'.$sessID);
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
		return eaccelerator_put($_sessid, $sessData, ini_get("session.gc_maxlifetime"));
	}
	/**
	 * 删除一个session
	 * @param string $sessID
	 * @return boolean
	 * @see Abstractsession::destroy()
	 */
	public function destroy($sessID) {
		return eaccelerator_rm('sess_'.$sessID);
	}
	public function gc($maxLifeTime) {
		eaccelerator_gc();
		return TRUE;
	}
	/**
	 * 检测加载模块
	 * @throws LoaderException
	 */
	private function _check() {
		if(!extension_loaded('eaccelerator') || !function_exists('eaccelerator_get'))
			throw new LoaderException(Loader::getErrMsg('SESSION_ISNOT_MODULE',array('eaccelerator')),2);
	}
}

?>