<?php
/**
 * memcache存储cache
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version 1.5.1
 * $Id: memcacheStorage.php 262 2017-11-09 02:55:35Z charles_li $
 */
class memcacheStorage implements ICacheStorage {
	/**
	 * memcache对象
	 * @var Memecache
	 */
	private $_memcache = null;
	private $_prefix = '~T';
	/**
	 * 初始化cache
	 * @param array $setting
	 * @return void
	 */
	public function init($setting) {
		if(!isset($this->_memcache)) {
			try {
				$this->_check();
				$this->_prefix = $setting['prefix'];
				$this->_memcache = new Memcache;
				try{
    				foreach($setting['memcachehost'] as $k=>$v) {
    					$this->_memcache->addServer($v,$setting['memcacheport'][$k]);
    					$this->_checkStatus($v,$setting['memcacheport'][$k]);
    				}
				}catch (CacheException $e) {
				    $e->getInfo();
				}
			}catch(LoaderException $e) {
				$e->getInfo();
			}
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
		return $this->_memcache->set($this->_getName($key,$classname),$value,0,$timeout);
	}
	/**
	 * 获取一个cache
	 * @param string $classname
	 * @param int $timeout
	 * @param string $key
	 * @return multitype
	*/
	public function get($classname,$timeout,$key) {
		return $this->_memcache->get($this->_getName($key,$classname));
	}
	/**
	 * 删除一个cache
	 * @param string $classname
	 * @param string $key
	 * @return boolean
	*/
	public function delete($classname,$key) {
		return $this->_memcache->delete($this->_getName($key),0);
	}
	/**
	 * 清除所有cache，如果有其他memcache谨慎使用
	 * @return boolean
	 */
	public function gc() {
		$this->_memcache->flush();
	}
	/**
	 * 清除cache
	 * @param int $timeout
	 * @return boolean
	*/
	public function clear($timeout) {
		return TRUE;
	}
	/**
	 * 检测模块是否存在
	 * @throws LoaderException
	 */
	private function _check() {
		if(!extension_loaded('memcache') || !class_exists('Memcache'))
			throw new LoaderException(Loader::getErrMsg('CACHE_ISNOT_MODULE',array('memcache')),2);
	}
	/**
	 * 检测memcache服务器状态
	 * @param string $host
	 * @param int $port
	 * @throws CacheException
	 */
	private function _checkStatus($host,$port) {
	    if(!$this->_memcache->getserverstatus($host,$port)) {
	        throw new CacheException(Loader::getErrMsg('CACHE_CONNECT_FAIL',array($host.':'.$port)),4);
	    }
	}
}

?>