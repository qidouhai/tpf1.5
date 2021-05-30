<?php
/**
 * httpresponse类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.http
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * $Id: Response.php 134 2015-03-27 02:40:54Z licaohai $
 */
class Response extends Tbs {
	private static $_status = array(
		'100' => 'Continue',
		'101' => 'Switching Protocols',
		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		'306' => '(Unused)',
		'307' => 'Temporary Redirect',
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'407' => 'Proxy Authentication Required',
		'408' => 'Request Timeout',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'502' => 'Bad Gateway',
		'503' => 'Service Unavailable',
		'504' => 'Gateway Timeout',
		'505' => 'HTTP Version Not Supported'
	);
	/**
	 * 设置头信息
	 * @param string $key
	 * @param string $value
	 */
	public static function setHeader($key,$value) {
		header($key.': '.$value);
	}
	/**
	 * 设置cookie
	 * @param string $name
	 * @param multitype $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 * @param boolean $secure
	 * @param boolean $httponly
	 */
	public static function cookie($name, $value = null, $expire = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE) {
		$_cConfig = Config::getConfig('cookie');
		$name = $_cConfig['perfix'].$name;
		if($expire==0) $expire = time() + $_cConfig['expire'];
		if($path=='/') $path = $_cConfig['path'];
		if($domain=='') $domain = $_cConfig['domain'];
		setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
	}
	/**
	 * 输出字符串
	 * @param string $msg
	 */
	public static function write($msg) {
		echo $msg;
	}
	/**
	 * 从定向
	 * @param string $url
	 * @param int $code
	 */
	public static function redirect($url,$code=302) {
		header('Location:'.$url,TRUE,$code);
		exit;
	}
	/**
	 * 返回http状态码
	 * @param int $code
	 */
	public static function status($code) {
		if(isset(self::$_status[$code])) {
			header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.self::$_status[$code]);
			header('Status:'.$code.' '.self::$_status[$code]);
		}
	}
	public static function charset($encoding) {
		header("Content-type: text/html; charset=".$encoding);
	}
}

?>