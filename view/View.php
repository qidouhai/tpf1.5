<?php
/**
 * 视图类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.4
 * $Id: View.php 223 2017-08-19 15:59:44Z charles_li $
 */
class View extends Tbs{
	/**
	 * 模版地址
	 * @var string
	 */
	private $_path;
	/**
	 * 模版参数
	 * @var array
	 */
	private $_param = array();
	/**
	 * 助手参数
	 * @var array
	 */
	private $_hparam = array();
	/**
	 * 开启布局视图
	 * @var boolean
	 */
	private $_useLayout = FALSE;
	/**
	 * 默认布局名称
	 * @var string
	 */
	private $_layout;
	/**
	 * 解析输出模版 eg: array('<{__LEFT__}>'=>'global/left');视图标签<{__LEFT__}>，解析载入views/global/left.phtml
	 * @var array
	 */
	private $_parse = array();
	/**
	 * 调用视图的控制器
	 * @var Controller
	 */
	private $_controller = null;
	public function __construct() {
		$this->_path = APP_PATH.'/'.self::$_buildpath['view_dir'];
	}
	/**
	 * 传递控制器值到视图对象
	 * @param object/array/string $name 传递对象属性/数组/键名
	 * @param string $value	当name为string时为键值，name为其他参数类型时设置无效
	 */
	public function assign($name,$value='') {
		if(is_object($name)) {
			foreach($name as $k=>$v) {
				$this->_param[$k]=$v;
			}
		}else{
			if(is_array($name)) {
				$this->_param = array_merge($this->_param,$name);
			}else $this->_param[$name] = $value;
		}
	}
	/**
	 * 设置启用布局视图
	 * @param boolean $uselayout 是否启用布局视图
	 */
	public function useLayout($uselayout) {
		$this->_useLayout = $uselayout;
	}
	/**
	 * 设置模版所在根目录
	 * @param string $path 目录如：/views/tbs/user
	 */
	public function setPath($path) {
		$this->_path = APP_PATH.DIRECTORY_SEPARATOR.$path;
	}
	/**
	 * 设置解析载入的模版
	 * @param string $key 模版中替换的key值，注意别重复
	 * @param string $value 载入模版的目录
	 */
	public function setParse($key,$value) {
		$this->_parse[$key] = $value;
	}
	
	
	public function setLayout($path='') {
		$this->_layout = $path;
	}
	/**
	 * 处理显示
	 * @param string $obj 调用显示类名
	 * @param string $str 控制层传入的参数
	 */
	public function render($obj,$str='') {
	    $_oView = self::instance($obj.'View');
	    $_oView->display($this->_param,$str,$this->_layout,$this->_path,$this->_hparam,$this->_parse,$this->_useLayout);
	}
	/**
	 * 载入助手类
	 * @param array $helpers
	 */
	public function bindHelpers($helpers,$controller) {
		$this->_controller = & $controller;
		foreach($helpers as $v) {
			$this->_triggerHelpers($v);
		}
	}
	/**
	 * 设置助手类的情况下引入
	 */
	private function _triggerHelpers($helper) {
		 $_oHelper = $this->_instanceHelper($helper);
		 $_oHelper->bindController($this->_controller);
		 $this->_hparam[$helper] =  $_oHelper;
	}
	/**
	 * 获取模型实例
	 * @param string $modelName
	 * @throws ControllerException
	 * @return Model
	 */
	private function _instanceHelper($helper) {
		$_oHelper= self::instance($helper.'Helper');
		if($_oHelper instanceof Helper) {
			return $_oHelper;
		}else {
			unset($_oHelper);
			throw new ControllerException(Loader::getErrMsg('HELPER_IMPLEMENT_INTERFACE',array($helper.'Helper')));
		}
	}
}

?>