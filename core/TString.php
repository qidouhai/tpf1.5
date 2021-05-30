<?php
/**
 * 字符串操作类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: TString.php 247 2017-09-07 05:52:18Z charles_li $
 */
class TString
{
/**
	 * 获得UUID
	 * @return string
	 */
	public static function getUUID() {
		$_uuid = '';
		list($dec, $sec) = explode(' ', microtime());
		$dec = dechex($dec * 1000000);
		$sec = dechex($sec);
		$_uuid .= self::_getUID($dec, 5);
		$_uuid .= self::_createGUID(3) . '-' . self::_createGUID(4) . '-' . self::_createGUID(4) . '-' . self::_createGUID(4) . '-';
		$_uuid .= self::_getUID($sec, 8) . self::_createGUID(4);
		return $_uuid;
	}
	private static function _createGUID($len) {
		$return = "";
		for($i=0; $i<$len; $i++)
		{
		$return .= dechex(mt_rand(0,15));
		}
		return $return;
	}
	private static function _getUID($str, $len) {
		$strlen = strlen($str);
		if($strlen < $len){
			$str = str_pad($str,$len,"0");
		}else{
			$str = substr($str, 0, $len);
		}
		return $str;
	}
	/**
	 * 获得随机字符串，字母均小写
	 * @param int $length 生成的字符串长度
	 * @param enum $type 类型 11:小写字母，12:大写字母，14：数字，15:小写字母+数字,16：大写字母+数字，13:所有字母，17:所有字母+数字，21-27:过滤容易错的字符，默认27
	 * @return string
	 */
	public static function getRandStr($length,$type=15) {
		if($length>20) throw new TStringException(Loader::getErrMsg('STRING_LEN_NOMORETHAN_20'),4);
		$_stype[0][1] = 'abcdefghijklmnopqrstuvwxyz';
		$_stype[0][2] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$_stype[0][4] = '0123456789';
		$_stype[1][1] = 'abdefghmnqrt';
		$_stype[1][2] = 'ABCDEFGHJMNQRT';
		$_stype[1][4] = '23456789';
		if($type > 20) {
			$_type = $type - 20;
			$_num = 1;
		}else{
			$_type = $type - 10;
			$_num = 0;
		}
		switch($_type) {
			case 1:
				$str= $_stype[$_num][1];
				break;
			case 2:
				$str= $_stype[$_num][2];
				break;
			case 3:
				$str= $_stype[$_num][1].$_stype[$_num][2];
				break;
			case 4:
				$str= str_repeat($_stype[$_num][4],5);
				break;
			case 5:
				$str= $_stype[$_num][1].$_stype[$_num][4];
				break;
			case 6:
				$str= $_stype[$_num][2].$_stype[$_num][4];
				break;
			case 7:
				$str= $_stype[$_num][1].$_stype[$_num][2].$_stype[$_num][4];
				break;
			default:
				$str= $_stype[0][1].$_stype[0][4];
				break;
		}
		$_newstr = substr(str_shuffle($str),0,$length);
		return $_newstr;
	}
	 /**
	 * 中文字符串截取
	 * @param string $str 字符串
	 * @param int $start 开始位置
	 * @param int $length 截取长度
	 * @param string $charset 字符集 utf-8,UTF8,GB2312,GBK,big5
	 * @param string $suffix 后缀
	 * @return string
	 */
	public static function subcnstr($str, $start=0, $length, $charset="utf-8", $suffix='...')
	{
		$_charset = strtolower($charset);
		switch($_charset)
		{
			case 'utf-8':$_char_len=3;break;
			case 'utf8':$_char_len=3;break;
			default:$_char_len=2;
		}
	    if(strlen($str)<=($length*$_char_len))
		{	
			return $str;
		}
		if(function_exists("mb_substr"))
	    {   
		 	$slice= mb_substr($str, $start, $length, $_charset);
		}
	    else if(function_exists('iconv_substr'))
	    {
	        $slice=iconv_substr($str,$start,$length,$_charset);
	    }
		else
	    { 
		   	$_re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$_re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$_re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$_re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($_re[$_charset], $str, $_match);
			$slice = join("",array_slice($_match[0], $start, $length));
		}
		return $slice.$suffix;
	}
	/**
	 * 返回子字符串出现的位置
	 * @param string $str
	 * @param string $substr
	 * @param int $from
	 * @return string
	 */
	public static function indexOf($str,$substr,$from=0) {
		return strpos($str, $substr ,$from);
	}

}
class TStringException extends TException
{
}