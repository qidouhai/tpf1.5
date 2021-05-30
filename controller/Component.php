<?php
/**
 * 组件抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.component
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.1
 * $Id: Component.php 265 2017-11-16 02:37:53Z charles_li $
 */
abstract class Component extends AbstractController {
	public function __construct() {
		parent::__construct();
		if($this->useSession) $this->useSession();
	}
	public  function _before($Controller){}
	public  function _after($Controller){}
}

?>