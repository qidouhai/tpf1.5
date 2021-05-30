<?php
/**
 * 数据连接类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.2
 * $Id: Abstractdb.php 228 2017-08-24 02:36:28Z charles_li $
 */
abstract class Abstractdb extends Tbs
{
	/**
	 * 数据查询语句
	 * @var string
	 */
	protected $_querySql;
	/**
	 * 最后插入的id号
	 * @var int
	 */
	protected $_insertid = 0;
	
	/**
	 * 当前资源id
	 * @var string
	 */
	protected $_resourceid = '';
	/**
	 * 执行结果获得的行数
	 * @var int
	 */
	protected $_numRows = 0;
	/**
	 * 查询结果集输出模式
	 * @var int
	 */
	protected $_mode = 1;
	/**
	 * 记录数据查询日志
	 * @param int $times 第几次查询
	 */
	public function Debug($times) {
		$_last_time = microtime(TRUE) - Application::getMicrotime('db_query'.$times);
		Log::info(Loader::getErrMsg('DB_QUERY_EXECUTE_TIME',array($this->_querySql,$_last_time)));
	}
	/**
	 * 连接数据源
	 * @param array $config
	 */
	public abstract function connect($config);
	/**
	 * 获得资源hash
	 * @param array $config
	 * @return string
	 */
	protected function _getServerid($config) {
		$_str = '';
		$_str .= $config['host'].'|'.$config['port'].'|'.$config['user'].'|'.$config['pass'];
		return md5($_str);
	}
	/**
	 * 获得当前资源ID
	 * @return string
	 */
	public function getResourceId() {
	    return $this->_resourceid;
	}
	
	/**
	 * 返回最新插入到数据库的行的ID
	 * @return int
	 */
	public function getLastInsertId() {
		return $this->_insertid;
	}
	/**
	 * 获取查询结果集
	 * @param array $opt
	 * @param string $resourceid
	 * @param int $type 
	 */
	public abstract function select($opt,$resourceid, $type = 0);
	/**
	 * 返回查询sql语句
	 * @param array $opt
	 * @param string $resourceid
	 */
	public abstract function sql($opt,$resourceid);
	/**
	 * 更新数据
	 * @param array $data  待更新的数据
	 * @param array $opt 查询结构
	 * @param string $resourceid
	 */
	public abstract function update($data,$opt,$resourceid);
	/**
	 * 更新字段自增
	 * @param string $field
	 * @param string $step
	 * @param array $data
	 * @param array $opt
	 * @param int $resourceid
	 */
	public abstract function increment($field,$step,$data,$opt,$resourceid);
	/**
	 * 插入数据
	 * @param array $data 待插入的数据
	 * @param array $opt 查询结构
	 * @param string $resourceid
	 */
	public abstract function insert($data,$opt,$resourceid);
	/**
	 * 根据主键替换数据
	 * @param array $data
	 * @param array $opt
	 * @param string $resourceid
	 */
	public abstract function replace($data,$opt,$resourceid);
	/**
	 * 删除记录
	 * @param array $opt 查询结构
	 * @param string $resourceid
	 */
	public abstract function delete($opt,$resourceid); 
	/**
	 * 开启事务
	 * @param string $resourceid 资源id
	 */
	public abstract function begin($resourceid);
	/**
	 * 提交执行事务
	 * @param string $resourceid 资源id
	 */
	public abstract function commit($resourceid);
	/**
	 * 事务回滚
	 * @param string $resourceid 资源id
	 */
	public abstract function rollback($resourceid);
	/**
	 * 获取表字段信息
	 * @param string $tablename 表名
	 * @param string $resourceid 资源id
	 */
	public abstract function getFields($tablename,$resourceid);
	public abstract function unbufferedQuery($sql,$resourceid);
	/**
	 * 执行数据查询
	 * @param string $sql
	 * @param string $resourceid 资源id
	 * @return IsqlStatement
	 */
	public abstract function query($sql,$resourceid);
	/**
	 * 执行一条sql语句返回影响的行数
	 * @param string $sql
	 * @return int
	 */
	public abstract function exec($sql,$resourceid);
	public function setMode($mode) {
		$this->_mode = $mode;
	}
	/**
	 * 关闭数据库
	 */
	public abstract function close();
}

?>