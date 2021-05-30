<?php
/**
 * 日志操作类工厂
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.log
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.0.5
 * $Id: Log.php 216 2017-08-04 06:24:51Z charles_li $
 */
class Log extends Tbs {
    /**
     * 日志对象
     * @var ILog
     */
    private static $_logObj;
    private static $_level = 0;
    private static $_times = array(0,0,0,0,0,0);
	/**
	 * 设置记录日志等级
	 * @param int $level
	 */
	public static function setLevel($level) {
	    if(self::$_level==0) {
	        $_logObj = self::$_config['log']['type'].'Log';
	        self::$_level = $level;
	        self::$_logObj = self::instance($_logObj);
	        self::$_logObj->setLevel($level);
	    }
	}
	/**
	 * 写入错误日志
	 * @param string $msg
	 */
	public static function aletr($msg) {
	    self::$_times[0]++;
	    self::$_logObj->aletr($msg);
	}
	
	public static function error($msg) {
	    self::$_times[1]++;
		self::$_logObj->error($msg);
	}
	
	public static function warn($msg) {
	    self::$_times[2]++;
		self::$_logObj->warn($msg);
	}
	
	public static function notice($msg) {
	    self::$_times[3]++;
		self::$_logObj->notice($msg);
	}
	/**
	 * 写入应用日志信息
	 * @param string $msg
	 */
	public static function info($msg) {
	    self::$_times[4]++;
		self::$_logObj->info($msg);
	}
	/**
	 * 写入debug日志
	 * @param string $msg
	 */
	public static function debug($msg) {
	    self::$_times[5]++;
		self::$_logObj->debug($msg);
	}
	/**
	 * 获取日志记录数
	 * @return number[]
	 */
	public static function getTimes() {
	    return self::$_times;
	}
	/**
	 * 输出日志数组
	 * @return multitype:
	 */
	public static function getLog() {
		return self::$_logObj->getLog();
	}
	public static function write() {
	    self::$_logObj->write();
	}
}
interface ILog {
    public static function setLevel($level);
    public static function aletr($msg);
    public static function error($msg);
    public static function warn($msg);
    public static function notice($msg);
    public static function info($msg);
    public static function debug($msg);
    public static function getLog();
    public static function write();
}
?>