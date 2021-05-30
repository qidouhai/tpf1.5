<?php
/**
 * 框架配置类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: Config.php 228 2017-08-24 02:36:28Z charles_li $
 */
class Config extends Tbs
{
	const TPL_EXT = '.tpl';
	private static $_configfileValue = array('environment','basic','router','db','cookie','cache','session');
	private static $_configfileKey = array('env','config','router','database','cookie','cache','session');
    private static $_sessiondir = '';
	/**
	 * 设置配置信息
	 * @param string $key
	 * @param string $value
	 */
	public static function setConfig($key,$value) {
		self::$_config[$key] = $value;
	}
	/**
	 * 设置应用程序目录配置
	 * @param array $pathArr
	 */
	public static function setPath($pathArr) {
	    self::$_buildpath = $pathArr;
	}
	/**
	 * 设置配置信息
	 * @param array $arr
	 */
	public static function setConfigArr($arr,$key='') {
        if($key == 'router' || $key == 'cache' || $key == 'cookie' || $key == 'session' || $key == 'template') {
                self::$_config[$key] = array_merge(self::$_config[$key],$arr);
        }elseif($key == 'db'){
                self::$_config[$key] = $arr;
        }else{
            self::$_config = array_merge(self::$_config,$arr);
        }
	}
	/**
	 * 获得配置信息
	 * @param string $key
	 * @return Ambigous <multitype:string number , string>
	 */
	public static function getConfig($key) {
		if(!isset($key)) throw new InvalidArgumentException('Please set key');
		else return self::_parseValue($key,false);
	}
	/**
	 * 获取配置文件模板
	 * @return string
	 */
	private static function _getConfTpl($type) {
		$_confStr = File::getFile(TF_PATH.'conf'.DIRECTORY_SEPARATOR.self::$_configfileKey[$type].self::TPL_EXT);
		return $_confStr;
	}
	/**
	 * 生成基础配置文件字符串
	 * @param array $confKey
	 * @param string
	 */
	private static function _getConfStr($type=0) {
		switch($type) {
		    case 0:
		        $_param = array('environment','log.level','log.filesize','log.type','log.format','log.host','log.port','log.username','log.password');
		        break;
			case 1:
				$_param = array('charset','debugtrace','timezone','passwdseek','usecurl','httpconnectout','debugtype','template.auto','template.ext','scaffold.password');
				break;
			case 2:
				$_param = array('router.controller_key','router.module_key','router.action_key',
				'router.entry','router.suffix','router.entryenable','router.enable','router.mapper');
				break;
			case 3:
			    $_param = array();
			    break;
			case 4:
				$_param = array('cookie.perfix','cookie.expire','cookie.domain','cookie.path');
				break;
			case 5:
				$_param = array('cache.storage','cache.timeout');
				break;
			case 6:
				$_param = array('session.name','session.storage','session.lifetime','session.cookiepath','session.cookiedomain','session.probability','session.path');
				break;
			default:
				$_param = array();
				break;
		}
		$_key = array_map('Config::_parseKey', $_param);
		$_value = array_map('Config::_parseValue', $_param);
		if($type == 3) {
			$_newArr = array();
		}else $_newArr = array_combine($_key,$_value);
		return strtr(self::_getConfTpl($type),$_newArr);
	}
	/**
	 * 生成配置文件,返回session存储目录名
	 * @param string $app_path 应用目录
	 * @return string
	 */
	public static function buildConf($app_path) {
		foreach(self::$_configfileKey as $k=>$v) {
			$_confPath = self::$_configfileValue[$k];
			if(isset(self::$_config['configfile'][$v])) {
				$_confPath = self::$_config['configfile'][$v];
			}
			$_confStr = self::_getConfStr($k);
			File::setFile($app_path.DIRECTORY_SEPARATOR.self::$_config['configdir'].DIRECTORY_SEPARATOR.$_confPath.TF_EXT, $_confStr);
		}
		return self::$_sessiondir;
	}
	/**
	 * 解析键数组
	 * @param string $v
	 * @return string
	 */
	private static function _parseKey($str) {
		return '${'.$str.'}';
	}
	private static function _parseValue($str,$isDeploy=true) {
		$_conf = self::$_config;
		$_strArr = explode('.',$str);
		$_strNum = sizeof($_strArr);
		if($isDeploy) {
    		if($str == 'session.path') {
    		    $_numlen = rand(3, 7);
    		    self::$_sessiondir = 'session_'.TString::getRandStr($_numlen,15);
    		    return self::$_sessiondir ;
    		}
    		if($str == 'scaffold.password') {
    		    $_numlen = rand(6, 12);
    		    return TString::getRandStr($_numlen,15);
    		}
    		if($str == 'passwdseek') {
    		    $_numlen = rand(5, 8);
    		    return TString::getRandStr($_numlen,15);
    		}
		}
		switch ($_strNum) {
			case 1:
				return $_conf[$_strArr[0]];
				break;
			case 2:
				return $_conf[$_strArr[0]][$_strArr[1]];
				break;
			case 3:
				return $_conf[$_strArr[0]][$_strArr[1]][$_strArr[2]];
				break;
			case 4:
				return $_conf[$_strArr[0]][$_strArr[1]][$_strArr[2]][$_strArr[3]];
				break;
			case 5:
				return $_conf[$_strArr[0]][$_strArr[1]][$_strArr[2]][$_strArr[3]][$_strArr[4]];
				break;
			case 6:
				return $_conf[$_strArr[0]][$_strArr[1]][$_strArr[2]][$_strArr[3]][$_strArr[4]][$_strArr[5]];
				break;
			default:
				return $_conf[$_strArr[0]][$_strArr[1]];
				break;		
		}
	}
	
	/**
	 * 获得所有配置
	 * @return Ambigous <multitype:string number , string>
	 */
	public static function getAll() {
		return self::$_config;
	}
}
class ConfigException extends TException {
}
?>