<?php
/**
 * 单元测试类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * $Id: Unit.php 100 2014-07-03 15:33:10Z licaohai $
 */
class Unit extends Tbs{
	private $_class;		//单元测试的类
	
	public function __construct($classname) {
		$this->_class = new ReflectionClass($classname);
	}
	
	/**
	 * 获得属性名数组
	 * @return array
	 */
	public function getProperty() {
		$_properties = $this->_class->getProperties();
		$_names = array();
		foreach($_properties as $property) {
			$_names[] = $property->getName();
		}
		return $_names;
	}
	
	public function assertProperty($name,$value) {
		$_properties = $this->_class->getProperties();
		//$_oClass = $this->_class->newInstance($args);
		foreach($_properties as $property) {
			
		}
	}
}

?>