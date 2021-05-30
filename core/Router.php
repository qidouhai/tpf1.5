<?php
/**
 * 路由类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.3.8
 * $Id: Router.php 247 2017-09-07 05:52:18Z charles_li $
 */
final class Router extends Tbs
{
	private static $_uri,
		$_route,
		$_module = '',
		$_controller = 'index',
		$_action = 'index',
		$_param = array(),
		$_controllerKey = 'c',
		$_actionKey = 'do',
		$_moduleKey = 'm',
		$_suffix = 'html',
		$_entry = 'index.php',
		$_defaultmodule = 'www',
		$_actionUri = '',
		$_rewriteUri = '',
		$_submodule = 'module';
	private function __construct() {
		$this->_init();
		self::$_uri = $this->_route(Request::getUri());//获得路由转化后的uri
		self::$_rewriteUri = $this->_formatUrl(self::$_uri);
		$this->_parse(self::$_rewriteUri);
	}
	/**
	 * 初始化配置
	 */
	private function _init() {
		self::$_controller = strtolower(self::$_config['controller']);
		self::$_action = strtolower(self::$_config['action']);
		self::$_controllerKey = strtolower(self::$_config['router']['controller_key']);
		self::$_actionKey = strtolower(self::$_config['router']['action_key']);
		self::$_moduleKey = strtolower(self::$_config['router']['module_key']);
		self::$_suffix = self::$_config['router']['suffix'];
		self::$_entry = strtolower(self::$_config['router']['entry']);
		self::$_defaultmodule = strtolower(self::$_config['router']['defaultmodule']);
		self::$_submodule = strtolower(self::$_config['router']['submodule']);
	}
	/**
	 * 获取rewrite的uri地址
	 */
	public static function getRewriteUri() {
	    return self::$_rewriteUri;
	}
	/**
	 * 获得模型
	 * @return string
	 */
	public static function getModule() {
		return self::$_module;
	}
	/**
	 * 获得控制器
	 * @return string
	 */
	public static function getController() {
		return self::$_controller;
	}
	/**
	 * 获得动作信息
	 * @return string
	 */
	public static function getAction() {
		return self::$_action;
	}
	/**
	 * 获得路由参数
	 * @return array
	 */
	public static function getParam() {
		return self::$_param;
	}
	/**
	 * 获得路由转化后的uri，校验路由映射是否正确
	 * @return String
	 */
	public static function getRouterUri() {
		return self::$_uri;
	}
	/**
	 * 获取请求的路由转化后请求的地址
	 * @return string
	 */
	public static function getActionUri() {
	    return self::$_actionUri;
	}
	/**
	 * 设置配置路由
	 * @param string $uri
	 * @return string
	 */
	private function _route($uri) {
		if(self::$_config['router']['enable']) {
			$_patterns = array();
			$_replace = array();
			foreach(self::$_config['router']['mapper'] as $k=>$v) {
				$_patterns[] = '|'.str_replace('/','\/',$k).'|';
				$_replace[] = $v;
			}
			$uri = preg_replace($_patterns,$_replace,$uri);
		}
		return $uri;
	}
	/**
	 * 解析uri
	 * @param string $uri
	 */
	private function _parse($uri) {
		$_arr = explode('/', $uri);
		$_arrCount = count($_arr);
		if(self::_isSetModule($_arr[0])) {
			if(isset($_arr[0])) self::$_module = $_arr[0];
			if(isset($_arr[1]) && !strpos($_arr[1],'=')){
				self::$_controller = $_arr[1];
				if(isset($_arr[2])) self::$_action = $_arr[2];
				if($_arrCount > 3) {
					self::$_param = array_slice($_arr, 3,$_arrCount-3);
				}
			}	
		}else{
			if(isset($_arr[0]) && !strpos($_arr[0],'=')) {
				self::$_controller = $_arr[0];
				if(isset($_arr[1])) self::$_action = $_arr[1];
				if($_arrCount > 2) {
					self::$_param = array_slice($_arr, 2,$_arrCount-2);
				}
			}else{
			    $_uriArr = explode('?',$uri);
			    self::$_controller = $_uriArr[0];
				parse_str($_uriArr[1],$_GET);
			}
		}
		$this->_doSubdomian();		//处理二级域名
		$this->_setController();
		$this->_setParam();
		$this->_setAction();
	}
	/**
	 * 内部路由
	 * @param string $uri
	 */
	public static function Redirect($uri) {
		$uri = trim($uri,'/');
		if(strpos($uri, '/')===FALSE) {
			return;
		}else{
			$_uriArr = explode('/',$uri);
			if(self::_isSetModule($_uriArr[0])) {
				self::$_module = $_uriArr[0];
				self::$_controller = $_uriArr[0].'_'.$_uriArr[1].'Controller';
				if(count($_uriArr)>2) {
					self::$_action = $_uriArr[2].'Action';
				}else self::$_action = self::$_config['controller'].'Action';
			}else{
			    self::$_module = '';
				self::$_controller = $_uriArr[0].'Controller';
				self::$_action = $_uriArr[1].'Action';
			}
		}
		Dispatcher::router();
	}
	
	/**
	 * 格式化url
	 */
	private function _formatUrl($uri) {
		if(self::$_config['router']['entryenable']) {
			$uri = strtr($uri,array(
					self::$_config['router']['entry']=>'',
			));
		}
		$_uri = trim($uri,'/');
		$_uri = preg_replace('/\/+/', '/', $_uri);
		$_uri = preg_replace('/\?\//','?',$_uri);
		$_current_uri = '';
		if(strpos($_uri,'/')!==false) {
			if(strpos($_uri, '?')!==false) {
				$_uriArr = explode('?',$_uri);
				$_current_uri = $_uriArr[0];
			}else{
				$_current_uri = $_uri;
			}
		}else{
			if(preg_match('/^'.self::$_config['router']['entry'].'/',$_uri)) {
				get404(Loader::getErrMsg('ROUTER_ENTRY_DISABLE'));
			}else {
				if(preg_match('/^[a-z\.]+/', $_uri)) {
					$_current_uri = $_uri;
				}else{
					if(isset($_GET[self::$_moduleKey])) $_current_uri .= '/'.$_GET[self::$_moduleKey];
					if(isset($_GET[self::$_controllerKey])) $_current_uri .= '/'.$_GET[self::$_controllerKey];
					else $_current_uri .= '/'.self::$_controller;
					if(isset($_GET[self::$_actionKey])) $_current_uri .= '/'.$_GET[self::$_actionKey];
					else $_current_uri .= '/'.self::$_action;
					$_current_uri = trim($_current_uri,'/');
				}
			}
		}
		return $_current_uri;
	}
	/**
	 * 检测是否设置了模块
	 * @param string $module
	 * @return boolean
	 */
	private static function _isSetModule($module) {
		if(in_array($module,self::$_config['router']['module'])) {	//是否是设置的模块
			return TRUE;
		}else return FALSE;
	}
	/**
	 * 处理二级域名
	 */
	private function _doSubdomian() {
		if(self::$_config['router']['subdomain']) {
			$_hostArr = explode('.',Request::getServerName());
			if(self::$_defaultmodule != $_hostArr[0]) {
				if(self::$_module != '') {
					$this->_transfer();
				}
				if(self::_isSetModule($_hostArr[0])) {
					self::$_module = $_hostArr[0];
				}else{
					self::$_module = self::$_submodule;
				}
			}
		}
	}
	/**
	 * 设置模块的情况下递进转换
	 */
	private function _transfer() {
		array_unshift(self::$_param, self::$_action);
		self::$_action = self::$_controller;
		self::$_controller = self::$_module;
	}
	/**
	 * 处理controller
	 */
	private function _setController() {
		$_controller = self::$_controller;
		if(self::$_module!='') {
			$_controller = self::$_module.'_'.self::$_controller;
			self::$_actionUri .= self::$_module .'/';
		}
		self::$_actionUri .= self::$_controller .'/';
		$_controller .= 'Controller';
		self::$_controller = $_controller;
	}
	/**
	 * 设置url中的地址到值数组
	 */
	private function _setParam() {
		if(isset(self::$_param)) {
			$_param = array();
			preg_replace('|(\w+)\/([^\/\/]+)|ie', '$_param[\'\\1\']=strip_tags(\'\\2\');', implode('/',self::$_param));
			self::$_param = $_param;
			$_GET = array_merge($_GET,$_param);
		}
	}
	/**
	 * 处理action
	 */
	private function _setAction() {
		$_action = self::$_action;
		if(count(self::$_param)==0)
		  $_action = strtr($_action, self::$_suffix);
		$_action = strtr($_action,array('.'=>''));
		self::$_actionUri .= $_action;
		self::$_action = $_action.'Action';
	}
	/**
	 * 获得路由配置信息
	 * @return array
	 */
	public static function getConfig() {
		return self::$_config['router'];
	}
	/**
	 * 实例化
	 * @return Router
	 */
	public static function getInstance()
	{
		$_id = 'Router';
		if (!isset(self::$_inst[$_id])){
			self::$_inst[$_id] = new self();
		}
		return self::$_inst[$_id];
	}
}