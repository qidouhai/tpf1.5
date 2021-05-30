<?php
/**
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.7
 * $Id: TException.php 265 2017-11-16 02:37:53Z charles_li $
 */
class TException extends Exception
{
	private static $_level;
	private static $_message;
	private static $_param = array();
    // 重定义构造器使 message 变为必须被指定的属性
    /**
     * 异常处理
     * @param string $message
     * @param int $level 同日志FATAL 	= 1,
		ERROR 	= 2,
		WARN 	= 4,
		NOTICE 	= 8,
		INFO	= 16,
		DEBUG   = 32
	 *  @param int $code 错误代码	
     */
    public function __construct($message,$level=0,$code = 0) {
        parent::__construct($message, $code);
        self::$_level = $level;
        self::$_message = $message;
        $this->_handleException($this);
    }
    /**
     * 输出异常信息
     * {@inheritDoc}
     * @see Exception::__toString()
     */
	public function __toString() {
	    self::getInfo();
		return '';
	}
	/**
	 * 整理异常信息
	 * @param Exception $obj
	 */
	private static function _handleException($obj,$trace=array()) {
	    $_errArr = array();
	    $_errArr['exception'] = 'Exception \''.get_class($obj).'\'';
	    $_errInfo = '';
	    if(TF_DEBUG) {
    	    $_trace = $obj->getTrace();
    	    foreach($_trace as $k=>$v) {
    	        if(isset($v['file'])) {
    	            $_errInfo .= '#'.$k.' '.$v['file'].'('.$v['line'].'): ';
    	        }else{
    	            $_errInfo .= '#'.$k.' ';
    	        }
    	        if(isset($v['class'])) {
    	            $_errInfo .= $v['class'];
    	        }
    	        if(isset($v['type'])) {
    	            $_errInfo .= $v['type'];
    	        }
    	        if(isset($v['function'])) {
    	            $_errInfo .= $v['function'];
    	        }
    	        $_errInfo .= self::_setArgs($v['args']);
    	        $_errInfo .= '<br />';
    	    }
    	    $_errArr['trace'] = $_errInfo;
	    }
	    self::$_param['charset'] = Config::getConfig('charset');
	    self::$_param['language'] = Loader::getLang();
	    self::$_param['message'] = $obj->getMessage();
	    self::$_param['file'] = $obj->getFile();
	    self::$_param['line'] = $obj->getLine();
	    self::$_param['app'] = '"'.APP_PATH.'"';
	    self::$_param['debug'] = TRUE;
	    self::$_param['exception'] = $_errArr['exception'];
	    self::$_param['trace'] = $_errInfo;
	}
	/**
	 * trace调用中的参数显示处理
	 * @param array $args
	 */
	private static function _setArgs($args) {
	    $_errInfo = '(';
	    if(count($args)>1) {
	        $_tmp = '';
 	        foreach($args as $k=>$v) {
                if(is_array($v)) $_tmp.=',Array';
                elseif(is_object($v)) $_tmp .=','.get_class($v);
                else $_tmp .= ','.$v;
	        }
	        $_errInfo .= trim($_tmp,',');
	    }else{
	        if(isset($args[0])) {
	            if(is_array($args[0])) $_errInfo .= 'Array';
	            else $_errInfo.=$args[0];
	        }
        }
        $_errInfo .=')';
        return $_errInfo;
	}
	/**
	 * 未捕获异常的终端处理
	 * @param object $e
	 */
	public static function handleException($e) {
	    self::$_message = $e->getMessage();
	    self::$_level = 2;
	    self::_handleException($e);
	    self::getInfo();
	}
	/**
	 * 输出错误信息记录日志
	 */
	public static function getInfo() {
	    if(TF_DEBUG && self::$_level<4) {
			Loader::loadTpl('error',self::$_param);
			die;
		}
		switch (self::$_level) {
			case 1:
			    Log::aletr(self::$_message);
			    Loader::load404();
			    break;
			case 2:
			    Log::error(self::$_message);
			    Loader::load404();
			    break;
			case 4:
			    Log::warn(self::$_message);
			    break;
			case 8:
			    Log::notice(self::$_message);
			    break;
			case 16:
			    Log::info(self::$_message);
			    break;
			default:
			    Log::warn(self::$_message);
			    break;               
		}
	}
}
?>