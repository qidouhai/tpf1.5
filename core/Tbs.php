<?php
/**
 * tfphp基类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.1
 * $Id: Tbs.php 223 2017-08-19 15:59:44Z charles_li $
 */
!defined('TF_IN') && exit('Access Denied');
abstract class Tbs {
	protected static $_inst,
	$_config = array(),
	$_buildpath = array(),
	$_lang = array(),
	$_global = array();
	/**
	 * 类的字符串显示
	 * 
	 * @return string
	 */
	public function toString() {
		return get_class($this);
	}
	/**
	 * 实例化类，调用方法
	 * @param string $class 类名
	 * @param string $method 方法名允许空
	 * @return object
	 */
	protected static function instance($class) {
		if(!isset(self::$_inst[$class])) {
			self::$_inst[$class] = new $class();
		}
		return self::$_inst[$class];
	}

}
?>