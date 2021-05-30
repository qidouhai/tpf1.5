<?php
/**
 * 数据连接工厂类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.2
 * $Id: db.php 235 2017-08-30 07:50:52Z charles_li $
 */
class db extends Tbs {
	/**
	 * 数据连接对象
	 * @var Abstractdb
	 */
	private $_oDB = null;
	private $_engine = array();		//加载的数据引擎
	private $_Resource = array();		//资源配置数组  array('mysql'=>array(资源id配置数组=>array()));
	private $_Resourceid = '';    //当前连接资源ID   
	public function __construct(){}
	/**
	 * 用配置文件初始化数据源
	 * @param array $config
	 */
	public function init($config) {
		$_type = $config['type'];
		if(!isset($this->_engine[$_type])) {
		      $this->_engine[$_type] = $this->_setEngine($_type);
		}
		$this->_oDB = $this->_engine[$_type];
        $this->_Resourceid = $this->_oDB->connect($config);
	}
	/**
	 * 获得当前连接资源ID
	 * @return string
	 */
	private function _getResource() {
		return $this->_Resourceid;
	}
	/**
	 * 获得最后插入的ID
	 * @return int
	 */
	public function getLastInsertId() {
		return $this->_oDB->getLastInsertId();
	}
	/**
	 * 设置查询结果模式
	 * @param int $mode
	 */
	public function setMode($mode=1) {
		$this->_oDB->setMode($mode);
	}
	/**
	 * 加载数据引擎
	 * @param string $type
	 * @throws DBException
	 * @return void
	 */
	private function _setEngine($type) {
	    $type = 'T'.$type;
		if(!file_exists(TF_PATH.'db/drivers/'.$type.TF_EXT)) {
			throw new DBException(Loader::getErrMsg('DB_ISNOT_DRIVERS',array($type)),1);
		}
		Loader::loadDB($type);
		return new $type();
	}
	/**
	 * 获取表字段信息
	 * @param string $tablename 表名
	 * @return array
	 */
	public function getFields($tablename) {
	    return $this->_oDB->getFields($tablename,$this->_getResource());
	}
	/**
	 * 开启事务
	 */
	public function begin() {
		$this->_oDB->begin($this->_getResource());
	}
	/**
	 * 事务回滚
	 */
	public function rollback() {
		$this->_oDB->rollback($this->_getResource());
	}
	/**
	 * 事务提交
	 */
	public function commit() {
		$this->_oDB->commit($this->_getResource());
	}
	/**
	 * 执行查询返回查询资源
	 * @param string $sql sql语句
	 * @return Idb
	 */
	public function query($sql) {
		return $this->_oDB->query($sql,$this->_getResource());
	}
	/**
	 * 执行查询
	 * @param array $opt 执行条件 eg: array('where'=>'','union'=>'','join'=>'','field'=>'','order'=>'','limit'=>'')
	 */
	public function select($opt) {
	    return $this->_oDB->select($opt,$this->_getResource());
	}
	/**
	 * 查询获取第一条数据
	 * @param array $opt
	 * @param null $data
	 */
	public function first($opt,$data=null) {
	    return $this->_oDB->select($opt,$this->_getResource(),1);
	}
	/**
	 * 返回查询sql语句
	 * @param array $opt
	 * @param null $data
	 */
	public function sql($opt,$data=null) {
	    return $this->_oDB->sql($opt, $this->_getResource());
	}
	/**
	 * 两边添加单引号
	 * @param string $str
	 * @return string
	 */
	public static function addQuotes($str) {
	    $str = addslashes_deep($str);
	    return sprintf("'%s'", $str);
	}
	/**
	 * 更新保存数据
	 * @param array $data
	 * @param array $opt
	 */
	public function save($opt,$data) {
	    return $this->_oDB->update($data, $opt, $this->_getResource());
	}
	/**
	 * 字段值增减
	 * @param string $field
	 * @param int $step
	 * @param array $data
	 * @param array $opt
	 */
	public function increment($field,$step,$data,$opt) {
	    return $this->_oDB->increment($field, $step, $data, $opt, $this->_getResource());
	}
	/**
	 * 插入一条数据
	 * @param array $opt
	 * @param array $data
	 */
	public function add($opt,$data) {
	    return $this->_oDB->insert($data, $opt, $this->_getResource());
	}
	/**
	 * 替换数据
	 * @param array $opt
	 * @param arary $data
	 */
	public function replace($opt,$data) {
	    return $this->_oDB->replace($data, $opt, $this->_getResource());
	}
	/**
	 * 删除数据
	 * @param array $opt
	 * @param null $data
	 */
	public function del($opt,$data=null) {
	    return $this->_oDB->delete($opt, $this->_getResource());
	}
	/**
	 * 执行sql返回影响的行数
	 * @param string $sql sql语句
	 * @return int
	 */
	public function exec($sql) {
		return $this->_oDB->exec($sql,$this->_getResource());
	}
	/**
	 * 设置连接信息
	 * @param string $dsn "mysql://root:123456@localhost:3306/databasename";
	 */
	public static function dsn2Config($dsn) {
		$_dsnUrl = parse_url($dsn);
		$_config['type'] = $_dsnUrl['scheme'];
		$_config['host'] = isset($_dsnUrl['host']) ? $_dsnUrl['host'] : '';
		$_config['user'] = isset($_dsnUrl['user']) ? $_dsnUrl['user'] : '';
		$_config['pass'] = isset($_dsnUrl['pass']) ? $_dsnUrl['pass'] : '';
		$_config['port'] = isset($_dsnUrl['port']) ? $_dsnUrl['port'] : '';
		$_config['dbname'] = isset($_dsnUrl['path']) ? substr($_dsnUrl['path'],1) : '';
		if(isset($_dsnUrl['query'])) {
		   preg_match('/charset=([a-z0-9]+)/i', $_dsnUrl['query'],$_charset);
		   $_config['charset'] = isset($_charset[1]) ? $_charset[1] : 'utf8';
		}else{
		    $_config['charset'] = 'utf8';
		}
		$_config['persistent'] = 0;
		return $_config;
	}
	public function __destruct() {
		if(isset($this->_oDB))
		$this->_oDB->close();
	}
	
}
class DBException extends TException {

}
?>