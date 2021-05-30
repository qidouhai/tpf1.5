<?php
/**
 * xcache存储cache
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version 1.5.1
 * $Id: xcacheStorage.php 262 2017-11-09 02:55:35Z charles_li $
 */
class xcacheStorage implements ICacheStorage{
	private $_prefix = '~T';
	/**
	 * 初始化cache
	 * @param array $setting
	 * @return void
	 */
	public function init($setting) {
		try {
			$this->_check();
			$this->_prefix = $setting['prefix'];
		}catch (LoaderException $e) {
			$e->getInfo();
			die;
		}
	}
	/**
	 * 获取缓存名
	 * @param string $key
	 * @param string $classname
	 * @return string
	 */
	private function _getName($key,$classname) {
		return $this->_prefix.$classname.$key;
	}
	/**
	 * 写入一个cache
	 * @param string $classname
	 * @param int $timeout
	 * @param string $key
	 * @param multitype $value
	 * @return boolean
	*/
	public function set($classname,$timeout,$key,$value) {
		return xcache_set($this->_getName($key,$classname),$value,$timeout);
	}
	/**
	 * 获取一个cache
	 * @param string $classname
	 * @param int $timeout
	 * @param string $key
	 * @return multitype
	*/
	public function get($classname,$timeout,$key) {
		$_key = $this->_getName($key,$classname);
		return xcache_get($_key);
	}
	/**
	 * 删除一个cache
	 * @param string $classname
	 * @param string $key
	 * @return boolean
	*/
	public function delete($classname,$key) {
		$_key = $this->_getName($key,$classname);
		return xcache_unset($_key);
	}
	/**
	 * 清除所有cache
	 * @param int $timeout
	 * @return boolean
	*/
	public function clear($timeout) {
		return TRUE;
	}
	/**
	 * 清除缓存
	 * @return boolean
	 */
	public function gc() {
		return TRUE;
	}
	/**
	 * 检测加载模块
	 * @throws LoaderException
	 */
	private function _check() {
		if(!extension_loaded('xcache'))
			throw new LoaderException(Loader::getErrMsg('CACHE_ISNOT_MODULE',array('xcache')),2);
	}
}

?>