<?php
/**
 * 组件工厂类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.component
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * $Id: ComponentFactory.php 223 2017-08-19 15:59:44Z charles_li $
 */
class ComponentFactory extends Tbs {
	private $_components = array();		//组件对象
	public function __construct() {}
	/**
	 * 组件初始化
	 * @param object $Controller
	 */
	public function init(& $Controller,$components,$controllername) {
	   foreach($components as $component) {
			$_oComponent= self::instance($component.'Component');
			if(is_subclass_of($_oComponent, 'Component')) {
				$Controller->{$component} = $_oComponent;
				$this->_components[$controllername][$component] = $_oComponent;
			}else {
				unset($_oComponent);
				throw new ComponentException(Loader::getErrMsg('COMPONENT_IMPLEMENT_INTERFACE',array($component)),2);
			}
		}
	}
	/**
	 * 组件在控制器inputfilter之前执行
	 * @param object $Controller
	 */
	public function before(&$Controller) {
		$this->_callback($Controller, '_before');
	}
	/**
	 * 组件在控制器outputfilter之后执行
	 * @param object $Controller
	 */
	public function after(&$Controller) {
		$this->_callback($Controller, '_after');
	}
	/**
	 * 执行组件回调方法
	 * @param object $Controller
	 * @param string $function
	 */
	private function _callback(&$Controller,$function) {
	    $_controllerStr = get_class($Controller);
		$_comkey = array_keys($this->_components[$_controllerStr]);
		foreach ($_comkey as $component) {
			$this->_components[$_controllerStr][$component]->{$function}($Controller);
		}
	}
}
class ComponentException extends TException {
	
}

?>