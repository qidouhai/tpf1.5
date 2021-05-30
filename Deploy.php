<?php
/**
 * 生成应用程序
 * @copyright    Copyright 2013 TYNT.CN
 * @author    <charles_li@msn.com>
 * @package    tpf
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.3.1
 * $Id: deploy.php 216 2017-08-04 06:24:51Z charles_li $
 */
error_reporting(0);
define('TF_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);	//根路径
define('TF_EXT','.php');					//框架用扩展名
define('TF_IN',1);						        //唯一入口
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
require TF_PATH.'Application'.TF_EXT;
Application::deploy();
?>