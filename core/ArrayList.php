<?php
/**
 * 数组操作类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * $Id: ArrayList.php 145 2015-09-21 09:24:48Z licaohai $
 */
!defined('TF_IN') && exit('Access Denied');
class ArrayList
{
	/**
	 * 多维数组转换为一维数组
	 * @param array $arr
	 * @return array
	 */
	public static function multi2array($arr,$key='') {
		static $_tmp = array();
		foreach($arr as $k=>$v) {
			if(is_array($v)) {
				self::multi2array($v,$k);
			}else{
				if($key == '') {
					$_tmp[$k] = $v;
				}
				else {
					$_key = $key.'.'.$k;
					$_tmp[$_key] = $v;
				}	
			}
		}
		return $_tmp;
	}
	
	/**
	 * 根据数组字段排序
	 *
	 * @param array $arr 需要排序的数组
	 * @param string $key 需要排序的键值
	 * @param boolean $isDesc 是否倒序
	 * @return array
	 */
	public static function rankArray($arr,$key,$isDesc=false) {
		foreach ($arr as $k=>$v) {
			$my[$k] = $v[$key];
		}
		if($isDesc) $myDesc = SORT_DESC;
		else $myDesc = SORT_ASC;
		array_multisort($my,$myDesc,$arr);
		return $arr;
	}
	/**
	 * 序列化数组，返回字符串
	 * @param array $array  被序列化的数组
	 * @return string
	 */
	public static function serialize($array )
	{
	    return base64_encode(gzcompress(serialize($array)));
	}
	/**
	 * 将字符串反序列化成数组
	 * @param string $string
	 * @return array
	 */
	public static function unserialize($string)
	{
	    return unserialize(gzuncompress(base64_decode($string)));
	}
}
class ArrayListException extends TException
{
	
}