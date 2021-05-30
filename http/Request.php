<?php
/**
 * httprequest类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.http
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.4
 * $Id: Request.php 258 2017-10-04 02:17:01Z charles_li $
 */
class Request extends Tbs{
	private static $_PUT = array(),
		$_DELETE = array(),
		$_JSON = array(),
		$_secure,
		$_method,
		$_serverport,
		$_serverhost,
		$_remoteport,
		$_uri;
	public function __construct() {
		self::$_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? TRUE : FALSE;
		if(isset($_REQUEST[self::$_config['submitmethod']]) && in_array(strtoupper(trim($_REQUEST[self::$_config['submitmethod']])),array('POST','FILES','GET','PUT','DELETE','AJAX'))) {
			self::$_method = strtoupper(trim($_REQUEST[self::$_config['submitmethod']]));
		}else self::$_method = strtoupper($_SERVER['REQUEST_METHOD']);
		self::$_remoteport = $_SERVER['REMOTE_PORT'];
		self::$_serverport = $_SERVER['SERVER_PORT'];
		self::$_serverhost = $_SERVER['HTTP_HOST'];
		self::$_uri = (isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['HTTP_X_REWRITE_URL'],'?')) ? strip_tags($_SERVER['HTTP_X_REWRITE_URL']) : strip_tags($_SERVER['REQUEST_URI']);
		if(self::$_config['restful']) $this->_getREST();
	}

	/**
	 * 获取提交方式
	 * @return string
	 */
	public static function getMethod() {
		return self::$_method;
	}
	/**
	 * 获得当前时间戳
	 * @return int
	 */
	public static function time() {
	    return $_SERVER['REQUEST_TIME'];
	}
	/**
	 * 判断是否是ajax请求
	 * @deprecated 如果jquery请求是通过iframe打开网页的，那么HTTP_X_REQUESTED_WITH参数不会被传递，也就是说没有办法判断请求的类型，如果不是jquery等提交增加conf中submitmethod参数提交值为AJAX
	 * @return boolean
	 */
	public static function isAjax() {
		if((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) =='xmlhttprequest') || self::$_method == 'AJAX') {
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * 是否是pjax请求
	 * @return boolean
	 */
	public static function isPjax() {
	    if (array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX']) {
	        return TRUE;
	    }else{
	        return FALSE;
	    }
	}
	/**
	 * 获得uri资源名称
	 * @return string
	 */
	public static function getUri() {
		return self::$_uri;
	}
	/**
	 * 获得url地址
	 * @return string
	 */
	public static function getUrl() {
		$_url = 'http';
		if(self::$_secure) $_url .= 's';
		$_url .= '://'.self::$_serverhost . self::$_uri;
		return $_url;
	}
	/**
	 * 获得请求中的参数部分
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function getQueryString($key=null,$value=null) {
		parse_str($_SERVER['QUERY_STRING'],$_param);
		if($key === null) return $_param;
		return (isset($_param[$key])) ? $_param[$key] : $value;
	}
	
	/**
	 * 获取post参数值
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function post($key=null,$value=null) {
		if($key === null) {
			return $_POST;
		}
		return (isset($_POST[$key])) ? $_POST[$key] : $value; 
	}
	/**
	 * 获取get参数值
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function get($key=null,$value=null) {
		if($key === null) {
			return $_GET;
		}
		return (isset($_GET[$key])) ? $_GET[$key] : $value;
	}
	/**
	 * put方式提交获取的参数值
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function put($key=null,$value=null) {
		if($key === null) return $this->_PUT;
		return (isset($this->_PUT[$key])) ? $this->_PUT[$key] : $value;
	}
	/**
	 * delete方式提交获取的参数值
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function delete($key=null,$value=null) {
		if($key === null) return $this->_DELETE;
		return (isset($this->_DELETE[$key])) ? $this->_DELETE[$key] : $value;
	}
	/**
	 * 获取cookie值
	 * @param string $key
	 * @param multitype $value
	 * @return multitype
	 */
	public static function cookie($key=null,$value=null) {
		if($key === null) return $_COOKIE;
		$_cConfig = Config::getConfig('cookie');
		$key = $_cConfig['perfix'].$key;
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $value;
	}
	
	/**
	 * json字串提交时值获取
	 * @param string $key
	 * @param multitype $value
	 * @return Ambigous <Ambigous, string/array, string>|unknown
	 */
	public static function json($key=null,$value=null) {
		$_param = self::_getJSON();
		if($key === null) return $_param;
		return (isset($_param[$key])) ? $_param[$key] : $value;
	}
	/**
	 * 获取json串提交信息转化成数组
	 * @return Ambigous <string/array, string>
	 */
	private static function _getJSON() {
		$_arr = array();
		$_obj = file_get_contents('php://input');
		$_arr = json_decode($_obj,true);
		$_arr = addslashes_deep($_arr);
		return $_arr;
	}
	/**
	 * 获取header参数
	 * @param string $key 参数名
	 * @return string
	 */
	public static function header($key) {
	    return isset($_SERVER['HTTP_'.$key]) ? $_SERVER['HTTP_'.$key] : '' ;
	}
	/**
	 * 获取put和delete参数
	 * @return array
	 */
	private function _getREST() {
		if(self::$_method == 'PUT' || self::$_method == 'DELETE') {
			parse_str(file_get_contents('php://input'),$_data);
			$this->_PUT = $_data;
			$this->_DELETE = $_data;
		}
	}
	/**
	 * 获取客户端IP地址
	 * @return string
	 */
	public static function getIP() {
	    $ip = getenv ( "REMOTE_ADDR" );
	    $ip0 = getenv ( "HTTP_REMOTE_ADDR" );
	    $ip1 = getenv ( "HTTP_X_REAL_IP" );
	    $ip2 = getenv ( "HTTP_X_FORWARDED_FOR" );
	    ($ip0) ? $ip = $ip0 : null;
	    ($ip1) ? $ip = $ip1 : null;
	    ($ip2) ? $ip = $ip2 : null;
	    return $ip;
	}
	/**
	 * 获得浏览器版本信息
	 * @return string
	 */
	public static function getBrowser() {
	    $_hua = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
	    $_chk = array(
	        '/rv:11.0.*/i'=>'IE11',
	        '/MSIE\s(\d+)\..*/i'=>'IE',
	        '/Chrome\/(\d+)\..*/i'=>'Chrome',
	        '/Firefox\/(\d+).*/i'=>'Firefox',
	        '/Safari\/(\d+).*/i'=>'Safari',
	        '/Opera[\s|\/](\d+)\..*/i'=>'Opera'
	    );
	    foreach($_chk as $k=>$v) {
    	    if(preg_match($k, $_hua,$_regs)) {
    	        return $v.$_regs[1];
    	    }
	    }
	    return 'Other';
	}
	/**
	 * 获得客户端操作系统
	 * @return string
	 */
	public static function getOS() {
		$_ua = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
		$_chk = array(
		    'Windows NT 6.1'=> 'Windows 7',
		    'Windows NT 6.0'=> 'Windows Vista',
		    'Windows NT 5.2'=> 'Windows 2003',
		    'Windows NT 5.1'=> 'Windows XP',
		    'Windows NT 5.0'=> 'Windows 2000',
		    'Windows ME'=> 'Windows ME',
		    'PPC Mac OS X'=> 'OS X PPC',
		    'Intel Mac OS X'=> 'OS X Intel',
		    'Win98'=> 'Windows 98',
		    'Win95'=> 'Windows 95',
		    'WinNT4.0'=> 'Windows NT4.0',
		    'Mac OS X Mach-O'=> 'OS X Mach',
		    'Ubuntu'=> 'Ubuntu',
		    'Debian'=> 'Debian',
		    'AppleWebKit'=> 'WebKit',
		    'Mint/8'=> 'Mint 8',
		    'Minefield'=> 'Minefield Alpha',
		    'gentoo'=> 'Gentoo',
		    'Kubuntu'=> 'Kubuntu',
		    'Slackware/13.0'=> 'Slackware 13',
		    'Fedora'=> 'Fedora',
		    'FreeBSD'=> 'FreeBSD',
		    'SunOS'=> 'SunOS',
		    'OpenBSD'=> 'OpenBSD',
		    'NetBSD'=> 'NetBSD',
		    'DragonFly'=> 'DragonFly',
		    'IRIX'=> 'IRIX',
		    'Windows CE'=> 'Windows CE',
		    'PalmOS'=> 'PalmOS',
		    'Linux'=> 'Linux',
		    'DragonFly'=> 'DragonFly',
		    'Android'=> 'Android',
		    'Mac OS X'=> 'Mac OS X',
		    'iPhone'=> 'iPhone OS',
		    'Symbian OS'=> 'Symbian',
		    'Symbian OS'=> 'Symbian',
		    'SymbianOS'=> 'SymbianOS',
		    'webOS'=> 'webOS',
		    'PalmSource'=> 'PalmSource'
		);
		foreach($_chk as $k=>$v) {
		    if(strpos($_ua,$k)) {
		        return $v;
		    }
		}
		return 'Others';
	}
	/**
	 * 获得服务端机器名端口号（80端口默认不显示）
	 * @retrun string
	 */
	public static function getServerHost() {
		return self::$_serverhost;
	}
	/**
	 * 获得服务端机器名
	 * @retrun string
	 */
	public static function getServerName() {
		return $_SERVER['SERVER_NAME'];
	}
	/**
	 * 获得服务端IP
	 * @return string
	 */
	public static function getServerAddr() {
		return $_SERVER['SERVER_ADDR'];
	}
	/**
	 * 获得服务端端口
	 * @return int
	 */
	public static function getServerPort() {
		return self::$_serverport;
	}
	/**
	 * 获得发出请求的客户端IP
	 */
	public static function getRemoteAddr() {
		return getIP();
	}
	/**
	 * 获得发出请求的客户端端口号
	 */
	public static function getRemotePort() {
		return self::$_remoteport;
	}
}
?>