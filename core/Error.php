<?php
/**
 * 错误信息操作类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.1
 * $Id: Error.php 210 2017-02-23 01:48:57Z charles_li $
 */
class Error extends Tbs{
	public static function runErr($level, $errstr,$errfile, $errline) {
		$_msg = '';
		$_isStop = FALSE;
		switch ($level) {
			case E_ERROR:
			case E_USER_ERROR:
				$_msg = "TF ERROR {$errstr} on line {$errline} in file {$errfile}";
				Log::error($_msg);
				self::_printErr($_msg);
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$_msg = "TF WARNING {$errstr} on line {$errline} in file {$errfile}";
				Log::warn($_msg);
				break;
			case E_NOTICE:
			case E_STRICT:
			case E_USER_NOTICE:
				$_msg = "TF NOTICE {$errstr} on line {$errline} in file {$errfile}";
				Log::notice($_msg);
				break;
			default:
				$_msg = "Unknown error type: {$errstr} on line {$errline} in file {$errfile}";
				Log::error($_msg);
				break;
		}
	}
	private static function _printErr($msg,$isTrace=TRUE) {
		$_lastErr = error_get_last();
		$_trace = debug_backtrace();
		$_errParam = array();
		$_errParam['debug'] = FALSE;
		$_errParam['app'] = '"'.APP_PATH.'"';
		$_errParam['exception'] = '';
		$_errParam['message'] = $msg;
		$_errParam['language'] = self::$_lang;
		$_errParam['charset'] = Config::getConfig('charset');
		if(defined('TF_DEBUG') && TF_DEBUG && $isTrace) {
			$_errParam['debug'] = TRUE;
			$_errParam['file'] = $_trace[0]['file'];
			$_errParam['line'] = $_trace[0]['line'];
			foreach($_trace as $k=>$v) {
				$_errParam['trace'] .= '#'.$k.' '.$v['class'] . $v['type'] . $v['function'] . ' called at [' .$v['file'].':'.$v['line'].']<br/>';
			}
		}
		if(self::$_config['environment']=='prd') {
		    Loader::load404();
		}else{
		    Loader::loadTpl('error',$_errParam);
		}
	}
	final public static function printSysError($msg) {
	    $_errParam = array();
	    $_errParam['app'] = '"'.APP_PATH.'"';
	    $_errParam['message'] = $msg;
	    $_errParam['charset'] = Config::getConfig('charset');
	    Loader::loadTpl('syserr',$_errParam);
	    die;
	}
	/**
	 * 打印错误信息，显示错误页面
	 * @param string $msg
	 */
	public static function printErr($msg) {
		self::_printErr($msg,FALSE);
	}
	
}

?>