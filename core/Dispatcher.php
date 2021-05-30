<?php
/**
 * @copyright    Copyright 2013 TYNT.CN
 * @author    <charles_li@msn.com>
 * @package    tpf
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.4
 * $Id: Dispatcher.php 269 2018-01-04 02:49:08Z charles_li $
 */

class Dispatcher extends Tbs {
    private static $_tpfCoreFile = array('core/TException','core/Loader','core/Config','core/Error','core/Functions','http/Request','http/Response','controller/AbstractController','controller/Controller','core/Router','log/Log');
	private static function _init() {
	    defined('TF_BEGIN_TIME') or define('TF_BEGIN_TIME', microtime(true)); //开始记录app执行的时间点
	    defined('TF_BEGIN_MEMORY') or define('TF_BEGIN_MEMORY',memory_get_usage());
	    $_basicConf = require TF_PATH.'conf/conf.php'; //载入基础配置
	    $_envConf = include APP_PATH.'/'.$_basicConf['configdir'].'/'.$_basicConf['configfile']['env'].TF_EXT; //载入用户环境配置文件
	    $_basicConf['log'] = array_merge($_basicConf['log'],$_envConf['log']);
	    $_basicConf['environment'] = $_envConf['environment'];
	    self::_setEnv($_basicConf);
	    Response::charset(self::$_config['charset']);
	    Loader::importLang();  // 载入语言包
	}

	/**
	 * 载入执行环境
	 */
	private static function _setEnv($basicConf) {
	    switch($basicConf['environment']) {
	    	case 'dev':
	    	    if(version_compare(PHP_VERSION,'5.1','<')) die('The tpf1.5 framework does not support the following version of PHP5');
	    	    define('TF_DEBUG', 1);
	    	    self::_initTPF($basicConf);
	    	    Compile::loadComponents();
	    	    Compile::loadFilter();
	    	    Log::setLevel(63);
	    	    break;
	    	case 'test':
	    	    if(version_compare(PHP_VERSION,'5.1','<')) die('The tpf1.5 framework does not support the following version of PHP5');
	    	    define('TF_DEBUG',0);
	    	    self::_initTPF($basicConf);
	    	    Compile::loadComponents();
	    	    Compile::loadFilter();
	    	    Log::setLevel(63);
	    	    break;
	    	case 'prd':
	    	    define('TF_DEBUG',0);
	    	    if(is_file(APP_PATH.DIRECTORY_SEPARATOR.$basicConf['runtime'].DIRECTORY_SEPARATOR.'~runtime'.TF_EXT)) {  //含有编译文件时直接载入编辑文件
	    	        require APP_PATH.DIRECTORY_SEPARATOR.$basicConf['runtime'].DIRECTORY_SEPARATOR.'~runtime'.TF_EXT;
	    	        spl_autoload_register(array('Loader', 'autoload'));
	    	        self::$_global['components'] = include APP_PATH.DIRECTORY_SEPARATOR.$basicConf['runtime'].DIRECTORY_SEPARATOR.'~components'.TF_EXT;
	    	        self::$_global['filters'] = include APP_PATH.DIRECTORY_SEPARATOR.$basicConf['runtime'].DIRECTORY_SEPARATOR.'~filters'.TF_EXT;
	    	    }else{
	    	        if(isset($_GET[$basicConf['compileparam']]) && $_GET[$basicConf['compileparam']]==1) {
    	    	        self::_initTPF($basicConf);
    	    	        Compile::setCompile(self::$_tpfCoreFile);
    	    	        Compile::loadComponents(true);    //载入组件
    	    	        Compile::loadFilter(true);
	    	        }else{
	    	            die('System not compiled!');
	    	        }
	    	    }
	    	    Log::setLevel(self::$_config['log']['level']);
	    	    break;
	    	default:
	    	    die('environment file error');
	    	    break;
	    }
	    set_error_handler(array('Error','runErr'));
	    date_default_timezone_set(self::$_config['timezone']);
	}
	
	/**
	 * 初始化载入框架类
	 */
	private static function _initTPF($basicConf) {
	    foreach (self::$_tpfCoreFile as $v) {
	        require TF_PATH.$v.TF_EXT;
	    }
	    spl_autoload_register(array('Loader', 'autoload'));
	    Config::setConfigArr($basicConf);  //载入系统基础配置文件到应用程序
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['config']));	//载入用户基础配置
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['cookie']),'cookie');	//载入cookie配置
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['cache']),'cache');
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['database']),'db');
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['router']),'router');
	    Config::setConfigArr(Loader::loadConf(self::$_config['configfile']['session']),'session');
	    Config::setPath(Loader::loadConf(self::$_config['configfile']['path']));    //应用程序配置路径
	}
	/**
	 * 执行应用程序初始化
	 */
	public static function init() {
	    self::_init();
	    self::instance('Request');
	    set_exception_handler('TException::handleException');
	    register_shutdown_function(array('Application','shutdown'));
	}
	/**
	 * 运行
	 */
	public static function run() {	
		Router::getInstance();
		self::_invoke();
	}
	/**
	 * 发起请求
	 * @throws LoaderException
	 */
	private static function _invoke() {
		$_controller = Router::getController();
		if(isset(self::$_global['filters'][$_controller])) {
		  $_filter = self::$_global['filters'][$_controller].'Filter';
		  $_oFilter = self::instance($_filter);
		  if(!is_subclass_of($_oFilter, 'Filter')) {
		      throw new LoaderException(Loader::getErrMsg("FILTER_ISNOT_INTERFACE",array($_filter)),1);
		  }
		  $_oFilter->doFilter();
		  $_oFilter->chainFilter();
		  unset($_oFilter);
		}
		$_oController = self::instance($_controller);
		if(!is_subclass_of($_oController, 'Controller')) {
			throw new LoaderException(Loader::getErrMsg("CONTROLLER_ISNOT_INTERFACE",array($_controller)),1);
		}
		die;
	}
	/**
	 * 执行一次内部路由
	 */
	public static function router() {
	    self::_invoke();
	}
}

?>