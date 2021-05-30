<?php
/**
 * DEBUG调试类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: Debug.php 228 2017-08-24 02:36:28Z charles_li $
 */
class Debug extends Tbs{
	private static $_param = array();	//自定义参数
	private static $_start = array();
	private static $_end = array();
	/**
	 * 设置DEBUG调试单元标签
	 * @var array
	 */
	private static $_tags = array();
	/**
	 * 是否输出调试界面
	 * @var boolean
	 */
	private static $_isOut = TRUE;
	public function __construct() {
		
	}
	/**
	 * 设置自定义调试变量
	 * @param string $name	变量名
	 * @param multitype $value 变量值
	 */
	public static function setParam($name,$value) {
		self::$_param[$name] = $value;
	}
	/**
	 * 记录单元测试内存时间数据查询次数
	 * @param string $tag	标签
	 */
	public static function unitStart($tag) {
		self::$_start['mem'][$tag] = memory_get_usage();
		self::$_start['time'][$tag] = microtime(TRUE);
		if(isset(self::$_global['db_query_times']))
			self::$_start['db_query_times'][$tag] = self::$_global['db_query_times'];
		else self::$_start['db_query_times'][$tag] = 0;
		self::$_tags[] = $tag;
		self::$_end['mem'][$tag] = 0;
		self::$_end['time'][$tag] = 0;
		self::$_end['db_query_times'][$tag] = 0;
	}
	/**
	 * 保存单元测试数据
	 * @param string $tag 标签，与startMemery
	 */
	public static function unitEnd($tag) {
		$_endmemery = 0;
		$_endtime = 0;
		$_enddbquerytimes = 0;
		if(isset(self::$_start['mem'][$tag])) {
			$_endmemery = memory_get_usage() - self::$_start['mem'][$tag];
			$_endtime = microtime(TRUE) - self::$_start['time'][$tag];
			if(isset(self::$_global['db_query_times']))
				$_enddbquerytimes = self::$_global['db_query_times'] - self::$_start['db_query_times'][$tag];
		}
		self::$_end['mem'][$tag] = $_endmemery; 
		self::$_end['time'][$tag] = $_endtime;
		self::$_end['db_query_times'][$tag] = $_enddbquerytimes;
	}
	/**
	 * 设置不输出调试信息
	 */
	public static function setNoout() {
		self::$_isOut = FALSE;
	}

	/**
	 * 输出debug信息
	 */
	public static function out() {
		if(self::$_isOut) {
			self::_out();
		}
		return ;
	}
	/**
	 * 输出调试信息
	 */
	private static function _out() {
	    $_param = array();
	    $_param['app'] = APP_PATH;
	    $_param['language'] = self::$_lang;
	    //环境变量情况
	    $_param['server']['SESSION_ID'] = session_id();
	    $_param['client']['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
	    $_param['server']['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
	    $_param['server']['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'];
	    $_param['server']['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
	    $_param['client']['CLIENT_ADDR'] = Request::getIP();
	    $_param['client']['CLIENT_PORT'] = $_SERVER['REMOTE_PORT'];
	    $_param['method'] = $_SERVER['REQUEST_METHOD'];
	    $_param['script'] = $_SERVER['SCRIPT_NAME'];
	    //路由情况
	    $_param['router']['module'] = Router::getModule();
	    $_param['router']['controller'] = Router::getController();
	    $_param['router']['action'] = Router::getAction();
	    if(isset(self::$_global['viewpath'])) $_param['router']['view'] = self::$_global['viewpath'];
	    else $_param['router']['view'] = '';
	    if(isset(self::$_global['model'])) $_param['router']['model'] = implode(',', self::$_global['model']);
	    else $_param['router']['model'] = '';
	    $_routerParam = Router::getParam();
	    $_param['router']['param'] = '';
	    if($_routerParam) $_param['router']['param'] = $_routerParam;
	    $_param['router']['uri'] = Router::getRouterUri();
	    //数据库执行次数
	    if(isset(self::$_global['db_query_times']))
	        $_param['db_query_times'] = self::$_global['db_query_times'];
	    else $_param['db_query_times'] = 0;
	    $_param['include'] = get_included_files();
	    $_param['log'] = Log::getLog();
	    $_param['logtimes'] = Log::getTimes();
	    $_param['unitshow'] = 0;
	    if(isset(self::$_end) && is_array(self::$_end) && count(self::$_end) != 0) {
	        $_param['unitshow'] = 1;
	        $_param['unit']['name'] = self::$_tags;
	        $_param['unit']['memery'] = self::$_end['mem'];
	        $_param['unit']['time'] = self::$_end['time'];
	        $_param['unit']['db_query_times'] = self::$_end['db_query_times'];
	    }
	    $_param['param'] = self::$_param;	//自定义调试变量
	    $_param['memery'] = 0;
	    $_param['memery'] = TF_END_MEMORY - TF_BEGIN_MEMORY;
	    $_param['time'] = microtime(TRUE) - TF_BEGIN_TIME;
	    Loader::loadTpl('debug', $_param);
	}
}

?>