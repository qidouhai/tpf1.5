<?php
/**
 * 文件操作类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.0
 * $Id: File.php 164 2016-05-06 07:36:10Z charles_li $
 */
class File extends Tbs
{
	/**
	 * 复制一个目录
	 *
	 * @param string $from 原目录
	 * @param string $to 更改目录
	 * @return boolean
	 */
	public static function copyDir($from,$to) {
		if(!file_exists($from) || !is_dir($from)) {
			return false;
		}
		if(!file_exists($to) || !is_dir($to)) {
			mkdir($to,0777);
			@chmod($to,0777);
		}
		$handle = opendir($from);
		while(($file=readdir($handle)) !== false) {
			if($file != '.' && $file != '..') {
				if(is_dir($from.DIRECTORY_SEPARATOR.$file)) {
					self::copyDir($from.DIRECTORY_SEPARATOR.$file,$to.DIRECTORY_SEPARATOR.$file);
				}else{
					copy($from.DIRECTORY_SEPARATOR.$file,$to.DIRECTORY_SEPARATOR.$file);
				}
			}
		}
		return true;
	}
	/**
	 * 删除一个目录
	 * @param string $dirpath 目录路径
	 * @return void
	 */
	public static function delDir($dirpath) {
		if(is_dir($dirpath)){
			$handle = opendir($dirpath);
			while(($file = readdir($handle)) !== false) {
				if($file != '.' && $file != '..') {
					if(is_dir($dirpath.DIRECTORY_SEPARATOR.$file)) {
						self::delDir($dirpath.DIRECTORY_SEPARATOR.$file);
					}else{
						unlink($dirpath.DIRECTORY_SEPARATOR.$file);
					}
				}
			}
			closedir($handle);
			rmdir($dirpath);
		}
	}
	/**
	 * 创建一个目录，支持子目录创建
	 * @param string $path 路径
	 * @see linux mkdir -p /user/lizy
	 */
	public static function createDir($path) {
		$path = trim($path,DIRECTORY_SEPARATOR);
		$_pathArr = explode(DIRECTORY_SEPARATOR, $path);
		$_path = '';
		foreach($_pathArr as $v) {
			$_path .= $v.DIRECTORY_SEPARATOR;
			if(is_dir($_path)) {
			}else{
				mkdir($_path);
			}
		}
	}
	/**
	 * 重命名一个目录
	 * @param string $from 原目录
	 * @param string $to 重命名后目录
	 * @return boolean
	 * @example renameDir("/webroot/abc/","/webroot/cba/");
	 */
	public static function renameDir($from,$to) {
		return rename($from, $to);
	}
	/**
	 * 获取文件内容
	 * @param string $filename 文件名及路径
	 * @return string
	 */
	public static function getFile($filename) {
		$content = file_get_contents($filename);
		if(!$content) {
		    throw new FileException(Loader::getErrMsg('FILE_ISNOT_EXISTORNULL',array($filename)),4);
		}
		return $content;
	}
	
	/**
	 * 写入文件
	 * @param string $filename 文件名，包括路径
	 * @param string $writetext 写入的内容
	 * @param string $openmod 写入的模式参照fopen函数
	 * @return boolean
	 */
	public static function setFile($filename, $writetext, $openmod='w') {
		if(@$fp = fopen($filename, $openmod)) {
			flock($fp, 2);
			fwrite($fp, $writetext);
			fclose($fp);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 获得最后修改时间
	 * @param $filepath 文件地址
	 * @return string
	 */
	public static function getFileLastTime($filepath) {
		return filemtime($filepath);
	}
}
class FileException extends TException {
	
}