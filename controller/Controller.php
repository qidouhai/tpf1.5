<?php
/**
 * 控制器抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.controller
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.5
 * $Id: Controller.php 265 2017-11-16 02:37:53Z charles_li $
 */
abstract class Controller extends AbstractController {
	/**
	 * 是否使用视图布局
	 * @var boolean
	 */
	protected $useLayout = FALSE;
	/**
	 * 控制器中用到的组件数组
	 * @var array
	 */
	protected $components = array();			//调用的组件数组
	/**
	 * 调用助手类
	 * @var array
	 */
	protected $helpers = array();
	protected $defaultLayout = 'default';
	/**
	 * 组件对象
	 * @var Component
	 */
	private $_oComponent;
	private $_useComponent = FALSE;		//是否启用组件
	public function __construct() {
		if($this->useSession) $this->useSession();
		parent::__construct();
 		$this->_view = self::instance('View');
 		$this->_view->bindHelpers($this->helpers,$this);		//绑定助手类
 		$this->_view->useLayout($this->useLayout);		//是否启用布局视图
 		$controllername = get_class($this);
 		if(!isset(self::$_global['components'][$controllername])) self::$_global['components'][$controllername] = array();
 		$this->components = array_merge(self::$_global['components'][$controllername],$this->components);
		if(count($this->components) > 0) {
			$this->_useComponent = TRUE;
			$this->_oComponent = self::instance('ComponentFactory');
			$this->_oComponent->init($this,$this->components,$controllername);
			$this->_oComponent->before($this);
		}
		$this->_inputFilter();
		if($this->useLayout) $this->_view->setLayout($this->defaultLayout);
		if(method_exists($this, '_before_'.$this->router->getAction())) {
			call_user_func(array(& $this,'_before_'.$this->router->getAction()));
		}
		$this->_callAction();
	}
	/**
	 * 呼叫方法
	 * @throws ControllerException
	 */
	private function _callAction() {
	    if(!method_exists($this, $this->router->getAction())) {
	        throw new ControllerException(Loader::getErrMsg('ACTION_ISNOT_EXIST',array($this->toString(),$this->router->getAction())),2);
	    }else{
	        call_user_func(array(& $this,$this->router->getAction()));
	    }
	}
	protected function _inputFilter() {}
	protected function _outputFilter() {}
	
	/**
	 * 判断是否是POST请求
	 * @return boolean
	 */
	protected function isPost() {
		if(Request::getMethod() == 'POST')
			return TRUE;
		else return FALSE;
	}
	/**
	 * 判断是否是GET请求
	 * @return boolean
	 */
	protected function isGet() {
		if(Request::getMethod() == 'GET')
			return TRUE;
		else return FALSE;
	}
	/**
	 * 判断是否是PUT请求
	 * @return boolean
	 */
	protected function isPut() {
		if(Request::getMethod() == 'PUT')
			return TRUE;
		else return FALSE;
	}
	/**
	 * 判断是否是DELETE请求
	 * @return boolean
	 */
	protected function isDelete() {
		if(Request::getMethod() == 'DELETE')
			return TRUE;
		else return FALSE;
	}
	/**
	 * 设置模版所在根目录
	 * @param string $path 目录如：/views/tbs/user
	 */
	protected function path($path) {
		$this->_view->setPath($path);
	}
	/**
	 * 设置视图布局目录
	 * @param string $path 目录如：/views/mylayout 则调用/views/mylayout.phtml
	 */
	protected function layout($path='') {
		$this->_view->setLayout($path);
	}
	/**
	 * 设置解析载入的模版，此处值变化需手工删除data/tpl目录视图缓存文件
	 * @param string $key 模版中替换的key值，注意别重复
	 * @param string $value 载入模版的目录
	 */
	protected function setParse($key,$value) {
		$this->_view->setParse($key, $value);
	}
	/**
	 * 传递控制器值到试图对象
	 * @param object/array/string $name 传递对象属性/数组/键名
	 * @param string $value	当name为string时为键值，name为其他参数类型时设置无效
	 */
	protected function assign($name,$value) {
		$this->_view->assign($name,$value);
	}
	/**
	 * 输出JSON字符串，隐藏调试信息
	 */
	protected function json() {
		$this->_render('Json');
	}
	/**
	 * 输出文本信息，默认隐藏调试信息，使用Debug::setOutput(),可以在dev模式打开调试信息
	 * @param string $key 打印到页面的某个值
	 */
	protected function text($key='') {
	    $this->_render('Text',$key);
	}
	/**
	 * 输出jsonp
	 * @param string $callback 返回JS函数名
	 */
	protected function jsonp($callback) {
		$this->_render('Jsonp',$callback);
	}
	private function _render($function,$str='') {
		if($this->_useComponent) {
			$this->_oComponent->after($this);
		}
		if(method_exists($this, '_after_'.$this->router->getAction())) {
		    call_user_func(array(& $this,'_after_'.$this->router->getAction()));
		}
		$this->_outputFilter();
		$this->_view->render($function,$str);
	}
	/**
	 * 输出模版
	 * @param string $path 默认在当前控制器对应视图目录一般为views/控制器/动作
	 */	
	protected function display($path='') {
		$this->_render('Html',$path);
	}
}

?>