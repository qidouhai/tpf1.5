<?php
class fileLog extends Tbs implements ILog {
    const 
		ALETR 	= 1,
		ERROR 	= 2,
		WARN 	= 4,
		NOTICE 	= 8,
		INFO	= 16,
		DEBUG   = 32;	//日志级别从高到底，设置 level = 7 则表示 ALETR，ERROR，WARN
	private static $_log = array();
	private static $_appender;			//日志存放目录
	private static $_level = 0;
	private static $_format = '%d %t %p %m';	//%d 时间 、%t错误类型、%p客户端ip、%m错误信息
	private static $_levelName = array(1=>'aletr',2=>'error',4=>'warn',8=>'notice',16=>'info',32=>'debug');
	/**
	 * 设置记录日志等级
	 * @param int $level
	 */
	public static function setLevel($level) {
	    self::$_level = $level;
	    self::$_format = self::$_config['log']['format'];
	}
	public function __construct() {
	    self::$_appender = APP_PATH.'/'.self::$_buildpath['data_dir'].'/'.self::$_buildpath['data_sub']['log_dir'];
	}
	/**
	 * 写入错误日志
	 * @param string $msg
	 */
	public static function aletr($msg) {
		self::_record($msg, self::ALETR);
	}
	
	public static function error($msg) {
		self::_record($msg, self::ERROR);
	}
	
	public static function warn($msg) {
		self::_record($msg, self::WARN);
	}
	
	public static function notice($msg) {
		self::_record($msg, self::NOTICE);
	}
	/**
	 * 写入应用日志信息
	 * @param string $msg
	 */
	public static function info($msg) {
		self::_record($msg, self::INFO);
	}
	
	public static function debug($msg) {
		self::_record($msg, self::DEBUG);
	}
	
	/**
	 * 记录日志信息
	 * @param string $msg
	 * @param int $level
	 */
	private static function _record($msg,$level) {
		$_ip = '[client '.Request::getIP().' request uri:'.$_SERVER['REQUEST_URI'].']';
		$_date = date('[c]');
		$_type = '['.self::$_levelName[$level].']';
		$_str = self::$_format;
		if(self::$_level & $level) {
			$_str = str_replace('%d',$_date,$_str);
			$_str = str_replace('%t', $_type, $_str);
			$_str = str_replace('%p',$_ip,$_str);
			$_str = str_replace('%m',$msg,$_str);
			self::$_log[] = $_str;
		}
	}
	/**
	 * 输出日志数组
	 * @return multitype:
	 */
	public static function getLog() {
		return self::$_log;
	}
	/**
	 * 写入日志文件
	 */
	public static function write() {
	    if(isset(self::$_log) && is_array(self::$_log) && count(self::$_log) != 0 && self::$_config['log']['level']>0){
    		$_path = self::$_appender.'/'.date('Y-m-d');
    		if(!is_dir($_path)) mkdir($_path);
    		$_destination = $_path.'/'.date('Y_m_d').'.php';
    		if(is_file($_destination) && filesize($_destination) > self::$_config['log']['filesize']) {
    			rename($_destination, substr($_destination,0,-4).'_'.date('H_i').'.php');
    		}
    		if(!file_exists($_destination)) {
    		    error_log('<?php'." "."!defined('TF_IN') && exit('Access Denied');?>\r\n",3,$_destination);
    		}
    		error_log(iconv(Config::getConfig('charset'),'utf-8',implode("\r\n",self::$_log))."\r\n\r\n",3,$_destination);
    		self::$_log = array();
	    }
	}
}

?>