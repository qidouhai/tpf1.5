<?php
/**
 * 数据查询结果集接口
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * $Id: Idb.php 158 2015-10-25 13:19:46Z licaohai $
 */
interface Idb {
    
	/**
	 * 获取单条查询结果数据
	 * @return array
	 */
	public function fetch();
	/**
	 * 获取查询结果集数据
	 * @return array
	 */
	public function fetchAll();
	/**
	 * 释放查询结果
	 * @return void
	 */
	public function free();
	/**
	 * 影响的行数
	 * @return int
	 */
	public function rowCount();
}
?>