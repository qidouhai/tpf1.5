<?php
/**
 * 验证类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.1
 * $Id: Validator.php 198 2016-05-31 08:52:15Z charles_li $
 */
class Validator extends Tbs{
	/**
	 * 字符串是否为空
	 * @param string $str
	 * @return boolean
	 */
	public static function StringIsNull($str) {
		if(!isset($str) || $str == '') {
			return TRUE;
		}else return FALSE;
	}
	/**
	 * 验证是否是邮件地址
	 * @param string $str
	 * @return boolean
	 */
	public static function isEmail($str) {
	    if (!$str) return FALSE;
	    return preg_match('/^[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[a-z]+/is', $str);
	}
	/**
	 * 检测目录是否可写
	 * @param string $dirname
	 * @return boolean
	 */
	public static function isWriteDir($dirname) {
		if(!is_dir($dirname)) {
			return FALSE;
		}else{
			file_put_contents($dirname.DIRECTORY_SEPARATOR.'li','1');
			if(!is_writable($dirname.DIRECTORY_SEPARATOR.'li')) {
				return FALSE;
			}else {
				unlink($dirname.DIRECTORY_SEPARATOR.'li');
				return TRUE;
			}
		}
	}
	/**
	 * 手机号码验证
	 * @param string $str
	 * @return boolean
	 */
    public static function isMobile($str) {
    	$_mobhead = self::$_config['mobile'];
    	preg_match('/^[0-9]{11}/i', $str,$m);
    	$_myhead = substr($str,0,3);
    	if(in_array($str,$m) && (in_array($_myhead,$_mobhead['chinamobile']) || in_array($_myhead,$_mobhead['chinaunicom']) || in_array($_myhead,$_mobhead['chinanet'])))
    		return TRUE;
    	else return FALSE;
    }
	/**
	 * 检测字符串是否是网址，包含ftp
	 * @param string $str
	 * @return boolean
	 */
	public static function isUrl($str) {
	    if(!str) return FALSE;
	    return preg_match('/(http|https|ftp|ftps):\/\/([\w-]+\.)+[\w-]+([\w-.\?%&=]*)?/i', $str);
	}
	/**
	 * 
	 * @param string $str
	 * @return boolean
	 */
	public static function isChinese($str) {
	    if(!str) return FALSE;

	   
	}
	/**
	 * 检测数组是否为空，适用于多维
	 * @param array $array
	 * @return boolean
	 */
	public static function ArrayIsNull($array, $isNull = true){
		if(isset($array) && is_array($array) && count($array) != 0 && $isNull){
			foreach($array as $value){
				if(is_array($value)){
					$isNull = Validator::ArrayIsNull($value, $isNull);
				}else {
					if(isset($array) && is_array($array) && count($array) != 0){
						$isNull = false;
					}else{
						$isNull = true;
						break;
					}
				}
			}
		}
		return $isNull;
	}
	/**
	 * 判断多维数组中是否包含某个值，普通数组请使用in_array
	 *
	 * @param array $array
	 * @param string $needle
	 * @param boolean $isExist 请勿设置
	 * @return boolean
	 */
	public static function ArrayIsExist($array, $needle, $isExist = false) {
		if(is_array($array) && !$isExist){
			foreach($array as $value){
				if(is_array($value)){
					$isExist = Validator::ArrayIsExist($value,$needle,$isExist);
				}else {
					if(in_array($needle,$array)){
						$isExist = true;
						break;
					}else $isExist = false;
				}
			}
		}
		return $isExist;
	}
	/**
	 * 检测是否是手机端浏览器
	 * @return boolean
	 */
	public static function fromMobile() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
		$is_mobile = FALSE;
		foreach ($_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = TRUE;
				break;
			}
		}
		return $is_mobile;
	}
}

?>