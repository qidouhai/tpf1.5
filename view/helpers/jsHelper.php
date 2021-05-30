<?php
/**
 * JS常用操作类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view.helpers
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.2
 * $Id: jsHelper.php 101 2014-07-04 10:32:38Z licaohai $
 */
class jsHelper extends Helper
{
	public static function alert($msg) {
		echo "<script type=\"text/javascript\">alert('$msg')</script>";
	}
	public static function back($step) {

	}
	public static function reload() {
		
	}
	public static function write($str) {
		
	}
	public static function go($url) {
		
	}
	public static function close() {
		
	}
}
class JSException extends TException
{
	public function __construct() {
		
	}
}