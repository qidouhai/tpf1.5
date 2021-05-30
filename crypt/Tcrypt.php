<?php
/**
 * TPF加密解密类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.crypt
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.1
 * @version v1.3
 * $Id: Tcrypt.php 109 2014-07-14 18:42:22Z licaohai $
 */
class Tcrypt {
	/**
	 * 可逆加密解密函数
	 * @param string $str 加密/解密字符串
	 * @param string $seed 密码种子
	 * @param boolean $type 加密/解密类型 1加密0解密
	 */
	private static function _EnDePwd($str,$type=1,$seed){
		$strLen = strlen($str);
		$skeylen = 3;
		$key = substr(md5(microtime()), -$skeylen);
		$enchr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',0,1,2,3,4,5,6,7,8,9);
		$codesite = fmod($strLen,62);
		$code = $enchr[$codesite];
		if($type == 0){
			$realString = substr($str,0,$strLen-1);
			$realString = substr($str,0,1).substr($str,2,1).substr($realString,-$strLen+6);
			$str = base64_decode($realString);
			$strLen = strlen($str);
		}
		$return = '';
		for($i = 0;$i < $strLen;$i++){
			$j = intval(fmod($i,32));
			$s = ord(substr($str,$i,1)) ^ ord(substr($seed,$j,1));
			$return .= chr($s);
		}
		if($type == 0){
			return $return;
		}elseif($type == 1){
			$return = str_replace('=','',base64_encode($return));
			$newLen = strlen($return);
			$string = substr($return,0,1).substr($key,2,1).substr($return,1,1).substr($key,0,2).substr($return,-$newLen+2).$code;
			return $string;
		}
	}
	/**
	 * 解密方法
	 * @param string $str 被加密的字符串
	 * @param string $seed 加密种子，不设置则使用基础配置文件的加密种子,解密时种子与加密时保持一致
	 * @param int $type 解密类型 0:系统自带解密
	 */
	public static function decrypt($str,$seed='',$type=0) {
		$seed = md5($seed ? $seed : Config::getConfig('passwdseek'));
		$_backStr = '';
		switch($type) {
			case 0:
				$_backStr = self::_EnDePwd($str,0,$seed);
				break;
			default:
				$_backStr = self::_EnDePwd($str,0,$seed);
				break;
		}
		return $_backStr;
	}
	/**
	 * 加密方法
	 * @param string $str 被加密的字符串
	 * @param string $seed 加密种子，不设置则使用基础配置文件的加密种子
	 * @param int $type 加密类型，0：系统自带不可逆加密，1：系统自带可逆加密
	 */
	public static function encrypt($str,$seed='',$type=0) {
		$_backStr = '';
		$seed = md5($seed ? $seed : Config::getConfig('passwdseek'));
		switch($type) {
			case 0:
				$_backStr = self::_toPwd($str,$seed);
				break;
			case 1:
				$_backStr = self::_EnDePwd($str,1,$seed);
				break;
			default:
				$_backStr = self::_toPwd($str,$seed);
				break;
		}
		return $_backStr;
	}
	/**
	 * 不可逆加密密码
	 * @param string $str 需加密的字符串
	 * @param string $seed 加密种子
	 */
	private static function _toPwd($str,$seed){
		$strLen = strlen($str);
		$skeylen = 3;
		$key = substr(md5(microtime()), -$skeylen);
		$enchr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$codesite = rand(0,25);
		$code = $enchr[$codesite];
		$mdStr = md5($str);
		$result = '';
		for($i = 0;$i<=32;$i++){
			$j = intval(fmod($i,32));
			$s = ord(substr($mdStr,$i,1)) ^ ord(substr($seed,$j,1));
			$result .= chr($s);
		}
		$result = md5(base64_encode($result));
		$newStr = substr($result,0,$codesite).substr($key,0,2).substr($result,$codesite,1).substr($key,-1).substr($result,-32+$codesite+1);
		$newStr = substr($newStr,0,15).$code.substr($newStr,-35+15);
		return $newStr;
	}
	/**
	 * 检测两密码是否相同
	 * @param string $pass1	密码1
	 * @param stirng $pass2 密码2
	 * @return boolean
	 */
	public static function equals($pass1,$pass2){
		$enchr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$enchr = array_flip($enchr);
		$str1 = substr($pass1,0,15).substr($pass1,-35+15);
		$str2 = substr($pass2,0,15).substr($pass2,-35+15);
		$code1 = substr($pass1,15,1);
		$code2 = substr($pass2,15,1);
		$codesite1 = $enchr[$code1];
		$codesite2 = $enchr[$code2];
		$newStr1 = substr($str1,0,$codesite1).substr($str1,$codesite1+2,1).substr($str1,-32+$codesite1+1);
		$newStr2 = substr($str2,0,$codesite2).substr($str2,$codesite2+2,1).substr($str2,-32+$codesite2+1);
		if($newStr1 != $newStr2) return false;
		else return true;
	}
}

?>