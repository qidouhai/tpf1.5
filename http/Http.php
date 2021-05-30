<?php
/**
 * HTTP相关操作
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.http
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.1  用Http::get代替之前的Http::doGet
 * $Id: Http.php 223 2017-08-19 15:59:44Z charles_li $
 */

class Http extends Tbs {
	/**
	 * 执行请求
	 * @param string $url 请求地址
	 * @param array $params 参数
	 * @param array $cookies cookie值
	 * @param int $type 类型 0
	 * @return Ambigous <mixed, string>
	 */
    private static function _doPG($url,$params,$cookies,$type) {
        if(function_exists('curl_init') && self::$_config['usecurl']) {
            $ch = curl_init();
            if($type==1) {
                $url .= empty($params)? '' : '?' .http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$_config['httpconnectout']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            if(!Validator::ArrayIsNull($cookies)) {
                curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
                curl_setopt($ch, CURLOPT_COOKIE,  self::_buildCookie($cookies));
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if($type == 0 || $type==2) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            if($type == 2) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params))
                );
            }
            $result = curl_exec($ch);
            curl_close($ch);
        }else {
            if($type==0 || $type==2) {
                if($type==0) {
                    $params = Validator::ArrayIsNull($params) ? '' : http_build_query($params);
                }
                $opts = array(
                    'http'=>array(
                        'method' => 'POST',
                        'header' =>
                        "Accept-Language: zh-cn\r\n" .
                        "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)\r\n" .
                        "Referer: $url\r\n" .
                        "Content-length: ".strlen($params)."\r\n",
                        'content' => $params.
                        "Connection: Close\r\n"
                    ));
                if(!Validator::ArrayIsNull($cookies)) {
                    $opts['http']['header'].= "Cookie: ".self::_buildCookie($cookies)."\r\n";
                }
                if($type == 2) {
                    $opts['http']['header'].= "Content-type:application/json\r\n";
                }else{
                    $opts['http']['header'].= "Content-type:application/x-www-form-urlencoded\r\n";
                }
            }else{
                $opts = array(
                    'http'=>array(
                        'method'=>"GET",
                        'header'=>
                        "Accept-Language: zh-cn\r\n" .
                        "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)\r\n" .
                        "Referer: $url\r\n" .
                        "Connection: Close\r\n" ,
                    )
                );
                if(!Validator::ArrayIsNull($cookies)) {
                    $opts['http']['header'].= "Cookie: ".self::_buildCookie($cookies)."\r\n";
                }
                $url .= empty($params)? '' : '?' .http_build_query($params);
            }
            $context = stream_context_create($opts);
            $result =  file_get_contents($url,false,$context);
        }
        return $result;
    }
	/**
	 * 提交一个json对象
	 * @param string $url 接收post数据的url
	 * @param unknown $strJson  json
	 * @param unknown $cookies  cookie指
	 * @return Ambigous <mixed, string>
	 */
	public static function postJSON($url,$strJson,$cookies=array()) {
		return self::_doPG($url, $strJson, $cookies, 2);
	}
	/**
	 * get提交,1.4后使用 Http:get()
	 * @param string $url 接收get数据的url
	 * @param array $params get参数
	 * @param array $cookies cookies值
	 * @return string
	 */
	public static function get($url,$params=array(),$cookies=array()) {
	    return self::_doPG($url, $params, $cookies, 1);
	}
	/**
	 * get提交,1.4后使用 Http:post()
	 * @param string $url 接收post数据的url
	 * @param array $params post参数
	 * @param array $cookies cookies值
	 * @return string
	 */
	public static function post($url,$params=array(),$cookies=array()) {
	    return self::_doPG($url, $params, $cookies, 0);
	}
	/**
	 * 老版本get接收数据，以后尽可能使用get,语义有问题
	 * @param string $url 接收get数据的url
	 * @param array $params get参数
	 * @param array $cookies cookies值
	 * @return string
	 */
	public static function doGet($url,$params=array(),$cookies=array()) {
	    return self::_doPG($url, $params, $cookies, 1);
	}
	/**
	 * 老版本post接收数据，以后尽可能使用post,语义有问题
	 * @param string $url 接收post数据的url
	 * @param array $params post参数
	 * @param array $cookies cookies值
	 * @return string
	 */
	public static function doPost($url,$params=array(),$cookies=array()) {
	    return self::_doPG($url, $params, $cookies, 0);
	}
	/**
	 * HTTP 请求下载文件
	 * @param string $path 文件所在路径
	 * @param string $filename 文件名
	 * @param string $aliasname 保存的文件名
	 */
	public static function download($path,$filename,$aliasname='') {
	    Debug::setNoout();
	    $file = $path.$filename;
	    $aliasname = ($aliasname == '') ? $filename : $aliasname;
	    if(!file_exists($file)){
	        throw new TException(Loader::getErrMsg('HTTP_NO_FILE',array($file)),4);       //未找到文件
	    }
	    $fp=fopen($file,"r");
	    $file_size=filesize($file);
	    //下载文件需要用到的头
	    header("Content-type: application/octet-stream");
	    header("Accept-Ranges: bytes");
	    header("Accept-Length:".$file_size);
	    header("Content-Disposition: attachment; filename=".$aliasname);
	    $buffer=1024;
	    $file_count=0;
	    //向浏览器返回数据
	    while(!feof($fp) && $file_count<$file_size){
	        $file_con=fread($fp,$buffer);
	        $file_count+=$buffer;
	        echo $file_con;
	    }
	    fclose($fp);
	}
	
	private static function _buildCookie($data) {
		$_cookies = '';
		if(is_array($data)) {
			foreach($data as $k=>$v) {
			    $_cookies .= ';'.$k.'='.$v;
			}
		}
		return trim($_cookies,';');
	}
}

?>