<?php
/**
 * 文件存储cache
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version 1.5.5
 * $Id: fileStorage.php 271 2018-01-16 03:03:58Z charles_li $
 */
class fileStorage implements ICacheStorage {
    /**
     * 缓存路径
     * @var string
     */
	private $_path = '';
	/**
	 * 是否初始化
	 * @var boolean
	 */
	private static $_init = FALSE;
	/**
	 * 缓存文件前缀
	 * @var string
	 */
	private $_prefix = '~T';
	/**
	 * 缓存数据
	 * @var string
	 */
	private $_cachedata = array();
	/**
	 * 初始化cache
	 * @param array $setting
	 * @return void
	 */
	public function init($setting) {
		$this->_path = APP_PATH.DIRECTORY_SEPARATOR.$setting['path'];
		$this->_prefix = $setting['prefix'];
		try {
			self::$_init = $this->_open();
		}catch(LoaderException $e) {
			$e->getInfo();
			die;
		}
	}
	/**
	 * 开启一个目录
	 * @throws LoaderException
	 * @return boolean
	 */
	private function _open() {
		if(is_dir($this->_path) && is_writable($this->_path)) {
			return TRUE;
		}
		else{
			throw new LoaderException(Loader::getErrMsg('CACHE_FILE_OPEN_ERROR'),4);
		}
	}
	/**
	 * 获取缓存文件名
	 * @param string $key
	 * @param string $classname
	 * @return string
	 */
	private function _getName($key,$classname) {
		$_name = $this->_prefix.md5($classname.$key).TF_EXT;
		return $_name;
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
		$_filename = $this->_path.DIRECTORY_SEPARATOR.$this->_getName($key,$classname);
		if(!self::$_init || $value === '') {
			return FALSE;
		}
		$_data = "<?php !defined('TF_IN') && exit('Access Denied');?>";
		$_data .= TFserialize($value);
		$success = File::setFile($_filename, $_data);
		$this->_cachedata[$classname.'@'.$key] = $_data;
		clearstatcache();
		return $success;
	}
	/**
	 * 获取一个cache
	 * @param string $classname
	 * @param int $timeout
	 * @param string $key
	 * @return multitype
	 */
	public function get($classname,$timeout,$key) {
	    if(empty($this->_cachedata[$key])) {
    		$_filename = $this->_path.DIRECTORY_SEPARATOR.$this->_getName($key,$classname);
    		if(self::$_init && is_file($_filename)) {
    			if(File::getFileLastTime($_filename)+$timeout > time()) {
    				$_data = File::getFile($_filename);
    				clearstatcache();
    				$_data = substr($_data,51);
        			$this->_cachedata[$classname.'@'.$key] = TFunserialize($_data);
        			return $this->_cachedata[$classname.'@'.$key];
    			}else{
    				return '';
    			}
    		}
    		return '';
	    }else{
	        return $this->_cachedata[$classname.'@'.$key];
	    }
	}
	/**
	 * 删除一个cache
	 * @param string $classname
	 * @param string $key
	 * @return boolean
	 */
	public function delete($classname,$key) {
		$_filename = $this->_getName($key,$classname);
		if(is_file($this->_path.DIRECTORY_SEPARATOR.$_filename)) {
			if(unlink($this->_path.DIRECTORY_SEPARATOR.$_filename)) {
			    unset($this->_cachedata[$classname.'@'.$key]);
			    return true;
			}
			return false;
		}
		return false;
	}
	/**
	 * 清除所有缓存
	 * @return true;
	 */
	public function gc() {
		if(@$dir = opendir($this->_path)) {
			while(@$filename = readdir($dir)) {
				@unlink($this->_path.DIRECTORY_SEPARATOR.$filename);
			}
		}
		return TRUE;
	}
	/**
	 * 清除所有过期缓存
	 * @param int $timeout
	 * @return boolean
	 */
	public function clear($timeout) {
		if(@$dir = opendir($this->_path)) {
			while(@$filename = readdir($dir)) {
				if(time() > File::getFileLastTime($this->_path.DIRECTORY_SEPARATOR.$filename) + $timeout)
					unlink($this->_path.DIRECTORY_SEPARATOR.$filename);
			}
		}
		closedir($dir);
		return TRUE;
	}
}

?>