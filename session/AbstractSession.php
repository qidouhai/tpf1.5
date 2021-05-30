<?php
/**
 * 存储session抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.0
 * $Id: AbstractSession.php 197 2016-05-31 08:30:14Z charles_li $
 */
abstract class AbstractSession extends Tbs {
	/**
	 * session配置数组
	 * @var array
	 */
	protected $_sessconfig = array();
	/**
	 * 是否已经设置
	 * @var boolean
	 */
	protected static $_isSet = FALSE;
	public function __construct() {
		$this->register();
	}
	/**
	 * 注册session
	 * @return void
	 */
	public function register() {
		session_module_name('user');
		session_set_save_handler(array(&$this,"open"),
		array(&$this,"close"),
		array(&$this,"read"),
		array(&$this,"write"),
		array(&$this,"destroy"),
		array(&$this,"gc"));
	}
	/**
	 * 读取session
	 * @param string $sessID
	 * @return string
	 */
	public function read($sessID) {
		return ;
	}
	/**
	 * 写入session
	 * @param string $sessID
	 * @param string $sessData
	 * @return boolean
	 */
	public function write($sessID,$sessData) {
		return TRUE;
	}
	/**
	 * 打开session
	 * @param string $savePath
	 * @param string $sessName
	 * @return boolean
	 */
	public function open($savePath, $sessName) {
       	return TRUE;
   	}
   	/**
   	 * 关闭session
   	 * @return boolean
   	 */
   	public function close() {
   		return $this->gc($this->_config['lifetime']);
   	}
   	/**
   	 * 回收session
   	 * @param int $maxLifeTime
   	 * @return boolean
   	 */
   	public function gc($maxLifeTime) {
   		return TRUE;
   	}
   	/**
   	 * 销毁session
   	 * @param string $sessID
   	 * @return boolean
   	 */
   	public function destroy($sessID) {
   		return TRUE;
   	}
}
?>