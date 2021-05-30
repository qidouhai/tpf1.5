<?php
/**
 * 页面助手类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.2
 * @version v1.3.1
 * $Id: Helper.php 159 2016-04-25 06:41:00Z licaohai $
 */
abstract class Helper extends Tbs {
	/**
	 * 控制器
	 * @var Controller
	 */
	protected $controller = null;
	/**
	 * 绑定controller对象到助手类，方便调用
	 * @param object $controller
	 */
	public function bindController($controller) {
		$this->controller = & $controller;
	}
}
class HelperException extends TException {
	
}
?>