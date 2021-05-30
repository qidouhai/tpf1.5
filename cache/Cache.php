<?php
/**
 * cache类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.cache
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version 1.5.4
 * $Id: Cache.php 269 2018-01-04 02:49:08Z charles_li $
 */
class Cache extends Tbs {
	/**
	 * 缓存对象
	 * @var ICacheStorage
	 */
	private $_cache = null;
	private $_setting = array();
	public function __construct() {
		$this->_setting['path'] = self::$_buildpath['data_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['data_sub']['cache_dir'];
	}
	public function init($classname) {
		$this->_setting['classname'] = $classname;
		$this->disconnect();
	}
	/**
	 * 设置超时时间
	 * @param number $timeout
	 */
	public function setTimeout($timeout=3600) {
	    $this->_setting['timeout'] = $timeout;
	}
	/**
	 * 链接缓存
	 * @param string $storage 缓存存储类型FILE/APC/MEMCACHE/XCACHE
	 * @param int $timeout 缓存时间(单位：秒）
	 */
	public function connect($storage,$timeout) {
		$this->_setting['storage'] = $storage;
		$this->_setting['timeout'] = $timeout;
		$this->_factory();
	}
	/**
	 * 获取cache存储区域
	 * @return void
	 */
	private function _factory() {
		try {
			$this->_cache = $this->_getStorage();
			$this->_cache->init($this->_setting);
		}catch(LoaderException $e) {
			$e->getInfo();
			die;
		}
	}
	/**
	 * 获取cache存储区域
	 * @return ICacheStorage
	 */
	private function _getStorage() {
		$_id = $this->_setting['storage'].'CacheStorage';
		if (!isset(self::$_inst[$_id])){
			$_storage = strtolower($this->_setting['storage']).'Storage';
			if(!file_exists(TF_PATH.'cache'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.$_storage.TF_EXT)) {
				throw new LoaderException(Loader::getErrMsg('CACHE_ISNOT_STORAGE',array($_storage)),2);
			}
			include TF_PATH.'cache'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.$_storage.TF_EXT;
			self::$_inst[$_id] = new $_storage();
		}
		return self::$_inst[$_id];
	}
	/**
	 * 中途链接的使用disconnect断开否则会存储就近connnect中
	 * @return void
	 */
	public function disconnect() {
		$this->_setting = array_merge($this->_setting,self::$_config['cache']);
		$this->_factory();
	}
	/**
	 * 设置一个缓存
	 * @param string $key
	 * @param string $value
	 */
	public function set($key,$value) {
		$this->_cache->set($this->_setting['classname'],$this->_setting['timeout'],$key, $value);
	}
	/**
	 * 删除一个缓存
	 * @param string $key
	 * @return void
	 */
	public function delete($key) {
	    $this->_cache->delete($this->_setting['classname'],$key);
	}
	/**
	 * 获取一个缓存
	 * @param multitype $key
	 */
	public function get($key) {
		return $this->_cache->get($this->_setting['classname'],$this->_setting['timeout'],$key);
	}
	/**
	 * 清除所有缓存
	 * @return void
	 */
	public function gc() {
		$this->_cache->gc();
	}
	public function __destruct() {
		$this->_cache->clear($this->_setting['timeout']);
	}
}
interface ICacheStorage {
	/**
	 * 初始化cache
	 * @param array $setting
	 * @return void
	 */
	public function init($setting);
	/**
	 * 写入一个cache
	 * @param string $key
	 * @param multitype $value
	 */
	public function set($classname,$timeout,$key,$value);
	/**
	 * 获取一个cache
	 * @param string $key
	 * @return multitype
	 */
	public function get($classname,$timeout,$key);
	/**
	 * 删除一个cache
	 * @param string $key
	 * @return boolean
	 */
	public function delete($classname,$key);
	/**
	 * 清除所有cache
	 * @return boolean
	 */
	public function clear($timeout);
}
class CacheException extends TException {}
?>