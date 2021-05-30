<?php
/**
 * 模型抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.model
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.12
 * $Id: Model.php 272 2018-01-29 13:29:01Z charles_li $
 */
abstract class Model extends Tbs {
	/**
	 * 模型调用默认数据库配置项名
	 * @var string
	 */
	protected $connection = 'default';     //数据连接配置项名
	/**
	 * 表前缀
	 * @var string
	 */
	protected $tablePrefix = '';
	/**
	 * 表主键
	 * @var string
	 */
	protected $pk = 'id';
	/**
	 * 默认表名
	 * @var string
	 */
	protected $table = '';
	/**
	 * 每页显示多少行
	 * @var int
	 */
	protected $pageRowCount = 20;
	/**
	 * 当前数据连接
	 * @var db
	 */
	private static $_db;
	/**
	 * 是否启用事务
	 * @var boolean
	 */
	private $_useTransaction = FALSE;
	/**
	 * 是否使用缓存
	 * @var boolean
	 */
	protected $useCache = FALSE;
	/**
	 * cache对象
	 * @var Cache
	 */
	protected $cache = null;
	/**
	 * 是否有异常被模型层捕获
	 * @var boolean
	 */
	private  $_hasException = FALSE;
	/**
	 * 当前模型名
	 * @var string
	 */
	private  $_modelname = '';
	/**
	 * 组成的SQL表达式
	 * @var array
	 */
	private $_sqlExpression = array();
	/**
	 * 临时绑定的dsn连接名
	 * @var string
	 */
	private $_tmpDsnConnection = '';   //是否是临时dsn绑定数据连接配置
	/**
	 * 临时绑定的连接资源名
	 * @var string
	 */
	private $_tmpConnection = '';      //是否临时配置文件数据
	/**
	 * 临时绑定写库
	 * @var string
	 */
	private $_tmpBindWrite = 0;
	/**
	 * 当前查询使用的表格
	 * @var string
	 */
	private $_currentTable = '';         //当前使用的table
    /**
     * 模型初始化
     * @var boolean
     */
	private static $_init = 0;     //是否已经初始化
	/**
	 * 载入的db配置信息数组
	 * @var array
	 */
	private static $_dbconfig = array(); //已经载入过的db配置
	public function __construct() {
	    if(self::$_init == 0) {
    		self::$_init = 1;
    		self::$_db = self::instance('db');    //db实例
	    }
	    self::$_global['model'][] = $this->_modelname = $this->toString();
        $this->_bindConfig($this->connection);
        if($this->tablePrefix=='') $this->tablePrefix = self::$_config['db'][self::$_config['environment']][$this->connection]['perfix'];
        if($this->useCache) {
           $this->cache = new Cache();
           $this->cache->init($this->_modelname);
        }
	}
	/**
	 * 获得该模型的表前缀
	 * @return string
	 */
	public function getPerfix() {
	    return $this->tablePrefix;
	}
	/**
	 * 绑定配置文件，返回当前连接名的配置数组
	 * @param $connection 连接名
	 * @return array
	 * @throws ConfigException
	 */
	private function _bindConfig($connection) {
        if(!isset(self::$_dbconfig[$connection])) {
            if(!array_key_exists($connection, self::$_config['db'][self::$_config['environment']])) {
                throw new ConfigException(Loader::getErrMsg('DBCONFIG_ISNOT_EXIST',array($connection)),1);
            }
            //在数据配置列表中搜索读写库配置是否存在
            if(!array_key_exists(self::$_config['db'][self::$_config['environment']][$connection]['dbread'], self::$_config['db']['config']) || !array_key_exists(self::$_config['db'][self::$_config['environment']][$connection]['dbwrite'], self::$_config['db']['config'])) {
                throw new ConfigException(Loader::getErrMsg('DBCONFIG_WRCONFIG_ERROR',array($connection)),1);
            }
            self::$_dbconfig[$connection] = self::$_config['db'][self::$_config['environment']][$connection] ;
        }
        return self::$_dbconfig[$connection];
	}
	/**
	 * 获取主键名
	 * @return string
	 */
	public final function getPK() {
		return $this->pk;
	}
	/**
	 * 执行数据查询前的连接配置
	 * @param boolean $isWrite 是否是写库 
	 */
	private function _beforeQuery($rw='dbread') {
	    $_connection = '';
	    if($this->_useTransaction) $rw='dbwrite';
	    $this->_setCurrentTable();
		if($this->_tmpDsnConnection!='') {
		    $_connection = $this->_tmpDsnConnection;
		    $_config = self::$_dbconfig[$_connection];
		    $this->_tmpDsnConnection = '';
		}elseif($this->_tmpConnection!='') {
		    $_connection = $this->_tmpConnection;
		    $this->_bindConfig($_connection);
		    if($this->_tmpBindWrite) {
		        $rw = 'dbwrite';
		    }
		    $this->_tmpBindWrite = 0;
		    $_config = array_merge(self::$_dbconfig[$_connection],self::$_config['db']['config'][self::$_config['db'][self::$_config['environment']][$_connection][$rw]]);
		    $this->_tmpConnection = '';
		}else {
		    $_connection = $this->connection;
		    $_config = array_merge(self::$_dbconfig[$_connection],self::$_config['db']['config'][self::$_config['db'][self::$_config['environment']][$_connection][$rw]]);
		}
		$_table = $this->_currentTable;
		$this->_currentTable = '';
		self::$_db->init($_config);
		return $_table;
	}
	/**
	 * 切换临时数据连接时执行查询，返回查询结果对象，此为SQL非安全
	 * @param string $sql
	 * @return Idb
	 */
	protected final function query($sql) {
	    try {
    		$this->_beforeQuery('dbwrite');
    		return self::$_db->query($sql);
	    }catch(DBException $e) {
	        $this->_hasException = TRUE;
	        $e->getInfo();
	    }
	}
	/**
	 * 临时切换数据连接时执行数据操作，查询sql是非安全的，注意被注入风险
	 * @param string $sql
	 * @return number
	 */
	protected final function exec($sql) {
	    try{
    		$this->_beforeQuery('dbwrite');
    		return self::$_db->exec($sql);
	    }catch(DBException $e) {
	        $this->_hasException = TRUE;
	        $e->getInfo();
	    }
	}
	/**
	 * 绑定一个数据配置到模型
	 * @param string $dbconfig	数据配置项名
	 */
	protected final function bindDB($connection) {
		$this->_tmpConnection = $connection;
	}
	/**
	 * 绑定一个DSN数据配置到模型
	 * @param string $dsn	设置使用DSN连接,eg:"mysql://root:123456@localhost:3306/databasename?charset=utf8"
	 */
	protected final function bindDSN($dsn) {
	    $_dsn = md5($dsn);
	    if(!isset(self::$_dbconfig[$_dsn])) {
	       self::$_dbconfig[$_dsn] = db::dsn2Config($dsn);
	    }
	    $this->_tmpDsnConnection = $_dsn;
	}
	/**
	 * 更新数据，$where为非安全的，注意注入
	 * @param array $array  eg:'username'=>'lizy'
	 * @param string $where 查询条件 eg:'id=1'
	 */
	public final function update($array,$where) {
	    $_opt = array();
		$_opt['table'] = $this->_beforeQuery('dbwrite');
	    if(!empty($where)) {
	        $_opt['where'][] = array(array($where),'AND');
	    }
	    return $this->_execute('save',$_opt,$array);
	}
	/**
	 * 是否有异常被捕获
	 * @return boolean
	 */
	public final function hasException() {
	    return $this->_hasException;
	}
	/**
	 * 清除之前的异常
	 */
	public final function clearException() {
	    $this->_hasException = false;
	}
	/**
	 * 开启事务
	 */
	public final function begin() {
	    $this->_useTransaction = true;
		$this->_beforeQuery('dbwrite');
		self::$_db->begin();
	}
	/**
	 * 事务回滚
	 */
	public final function rollback() {
	    $this->_useTransaction = false;
		$this->_beforeQuery('dbwrite');
		self::$_db->rollback();
	}
	/**
	 * 提交事务
	 */
	public final function commit() {
	    $this->_useTransaction = false;
		$this->_beforeQuery('dbwrite');
		self::$_db->commit();
	}
	/**
	 * 插入数据，返回ID号
	 * @param array $array eg:'username'=>'lizy'
	 * @return int
	 */
	public final function insert($array) {
	    $_opt = array();
		$_opt['table']  = $this->_beforeQuery('dbwrite');
		$this->_execute('add',$_opt,$array);
        return self::$_db->getLastInsertId();
	}
	/**
	 * 根据id获取单条数据
	 * @param int $id
	 * @return multitype:
	 */
	public function getOne($id) {
	    $_opt = array();
	    $_opt['table']  = $this->_beforeQuery();
		$_opt['where'][] = array(array($this->pk,'=',$id));
		return $this->_execute('first',$_opt);
	}
	/**
	 * 根据筛选条件删除信息，$where非安全，注意SQL注入
	 * @param string $where
	 * @return int
	 */
	public final function delete($where) {
	    $_opt = array();
	    $_opt['table']  = $this->_beforeQuery('dbwrite');
	    if(!empty($where)) {
	        $_opt['where'][] = array(array($where),'AND');
	    }
	    return $this->_execute('del',$_opt);
	}
	/**
	 * 根据查询条件获得返回行数,where字符串非安全的
	 * @param string $where
	 * @return int
	 */
	public final function getCount($where='') {
	    $_opt = array();
	    $_opt['table']  = $this->_beforeQuery();
	    if(!empty($where)) {
			$_opt['where'][] = array(array($where),'AND');
		}
		$_opt['fields'] = 'COUNT(*) as NUM';
		$_list = $this->_execute('first',$_opt);
		return $_list['NUM'];
	}

	/**
	 * 根据查询条件获取数据列表,$where非安全
	 * @param string $where
	 * @param string $field
	 * @param string $orderby
	 * @return array:
	 */
	public function getData($where,$field='*',$orderby='') {
	    $_opt = array();
	    $_opt['table']  = $this->_beforeQuery();
	    if(!empty($where)) {
	        $_opt['where'][] = array(array($where),'AND');
	    }
	    if($orderby=='') $_opt['order'] = $this->pk.' DESC';
		else $_opt['order'] = $orderby;
		return $this->_execute('select',$_opt);
	}
	/**
	 * 返回数据列表
	 * @param string $field 查询字段，默认查询所有
	 * @param string $where 查询条件
	 * @param int $pagesize 每页显示数量
	 * @param int $page 当前页
	 * @param string $orderby 排序 eg:username desc
	 * @return array
	 */
	public function getList($field='*',$where='',$pagesize='',$page='',$orderby='') {
	    $_opt = array();
	    $_opt['table']  = $this->_beforeQuery();
	    $_opt['fields'] = $field;
	    if(!empty($where)) {
	        $_opt['where'][] = array(array($where),'AND');
	    }
	    if($page=='') $page = 1;
	    if(empty($pagesize)) $pagesize = $this->pageRowCount;
	    $_opt['limit'] = array(($page-1) * $pagesize,$pagesize);
	    if($orderby=='') $_opt['order'] = $this->pk.' DESC';
	    else $_opt['order'] = $orderby;
	    return $this->_execute('select',$_opt);
	}
	/**
	 * 连接特定配置名称的数据源
	 * @param string $connection 连接名
	 * @return Model
	 */
	public final function connect($connection) {
	    $_obj = $this;
	    $_obj->bindDB($connection);
	    return $_obj;
	}
	/**
	 * 临时连接写库
	 * @param string $connection
	 * @return Model
	 */
	public final function writeConnect($connection='default') {
	    $this->_tmpBindWrite = 1;
	    return $this->connect($connection);
	}
	/**
	 * 指定数据表,以模型$tablePrefix为表前缀
	 * @param string $tablename
	 * @return Model
	 */
	public final function table($tablename) {
	    $this->_currentTable = $this->tablePrefix.$tablename;
	    return $this;
	}
	/**
	 * 指定无表前缀表
	 * @param string $tablename
	 * @return Model
	 */
	public final function from($tablename) {
	    $this->_currentTable = $tablename;
	    return $this;
	}
	/**
	 * 执行数据库操作
	 * @param string $function 操作方法
	 * @param array $opt 组成sql表达式模板
	 * @param array $data 数据
	 */
	private function _execute($function,$opt=null,$data=null) {
	    try{
            if($opt==null) {
    	        $this->_sqlExpression['table'] = $this->_beforeQuery();
    	        $_opt = $this->_sqlExpression;
    	        $this->_sqlExpression = array();
            }else{
                $_opt = $opt;
            }
            //echo 'gggg';
            return self::$_db->$function($_opt,$data);
	    }catch(DBException $e) {
            $this->_hasException = TRUE;
            $e->getInfo();
        }
	}
	/**
	 * 更新数据,返回更新的条数
	 * @param array $data 字段与字段对应数据的数组 eg: array('username'=>'lizy');
	 * @return int
	 */
	public final function save($data) {
	    return $this->_execute('save',null,$data);
	}
	/**
	 * 插入数据,返回插入数据的ID号
	 * @param array $data 字段与字段对应数据的数组 eg: array('username'=>'lizy');
	 * @return int
	 */
	public final function add($data) {
	    $this->_execute('add',null,$data);
	    return self::$_db->getLastInsertId();
	}
	/**
	 * 获取查询结果集数组
	 * @return array
	 */
	public final function select() {
	    return $this->_execute('select');
	}
	/**
	 * 获取第一条数据
	 * @return array
	 */
	public final function first() {
	    return $this->_execute('first');
	}
	/**
	 * 获取查询结果集的条数
	 * @return int
	 */
	public final function count() {
	    return $this->_functionColumn('*', 'COUNT');
	}
	/**
	 * 获取字段最大值
	 * @param string $column 字段名
	 */
	public final function max($column) {
	    return $this->_functionColumn($column, 'MAX');
	}
	/**
	 * 获取字段最小值
	 * @param string $column 字段名
	 */
	public final function min($column) {
	    return $this->_functionColumn($column, 'MIN');
	}
	/**
	 * 获取字段平均值
	 * @param string $column 字段名
	 */
	public final function avg($column) {
	    return $this->_functionColumn($column, 'AVG');
	}
	/**
	 * 字段求和
	 * @param string $column 字段名
	 */
	public final function sum($column) {
	    return $this->_functionColumn($column, 'SUM');
	}
	/**
	 * 函数求值
	 * @param string $column 字段名
	 * @param string $function 函数名
	 * @return int
	 */
	private function _functionColumn($column,$function) {
	    $this->_sqlExpression['fields'] = $function.'('.$column.') AS NUM';
	    $_list = $this->_execute('first');
	    return $_list['NUM'];
	}
	/**
	 * 返回查询sql语句
	 * @return string
	 */
	public final function sql() {
	    return $this->_execute('sql');
	}
	/**
	 * 连接查询
	 * @param string $table 连接的表名
	 * @param string $column1 前表的连接字段
	 * @param unknown $column2 后表的连接字段
	 * @param string $type 类型 inner/left/right/outer
	 * @return Model
	 */
	public final function join($table,$column1,$column2,$type='inner') {
	    $this->_sqlExpression['join'][] = array($table,$column1,$column2,$type);
	    return $this;
	}
	/**
	 * 联盟查询
	 * @param string $sql
	 * @return Model
	 */
	public final function union($sql) {
	    $this->_sqlExpression['union'] = $sql;
	    return $this;
	}
	/**
	 * 使用column字段群组
	 * @param string $colunm 字段名
	 * @return Model
	 */
	public final function group($column) {
	    $this->_sqlExpression['group'] = $column;
	    return $this;
	}
	/**
	 * 对组记录进行筛选
	 * @param string $column 字段可以是count(id)这样的函数
	 * @param string $operator 操作符
	 * @param string $value
	 * @return Model
	 */
	public final function having($column,$operator,$value) {
	    $this->_sqlExpression['having'] = array($column,$operator,$value);
	    return $this;
	}
	/**
	 * 删除选定的数据，返回执行到的数据条数
	 * @return int
	 */
	public final function del() {
	    return $this->_execute('del');
	}
	/**
	 * 自增方法
	 * @param string $fields 自增字段名
	 * @param int $step 自增数量，默认为1
	 * @param array $data 更新同时更新其他字段，eg:array('username'=>'lizy')
	 * @return int
	 */
	public final function increment($field,$step=1,$data=array()) {
	    $step = $field.'+'.$step;
	    return $this->_idcrement($field, $step, $data);
	}
	/**
	 * 自减方法
	 * @param string $fields 自增字段名
	 * @param int $step 自增数量，默认为1
	 * @param array $data 更新同时更新其他字段，eg:array('username'=>'lizy')
	 * @return int
	 */
	public final function decrement($field,$step=1,$data=array()) {
	    $step = $field.'-'.$step;
	    return $this->_idcrement($field, $step, $data);
	}
	/**
	 * 字段自增或自减
	 * @param string $field
	 * @param string $step
	 * @param array $data
	 */
	private function _idcrement($field,$step,$data) {
	    try{
    	    $this->_sqlExpression['table'] = $this->_beforeQuery('dbwrite');
    	    $_opt = $this->_sqlExpression;
    	    $this->_sqlExpression = array();
    	    return self::$_db->increment($field, $step, $data, $_opt);
	    }catch(DBException $e) {
	        $this->_hasException = TRUE;
	        $e->getInfo();
	    }
	}
	/**
	 * 设置返回字段
	 * @param string $columns eg: username / username as user
	 * @return Model
	 */
    public final function fields($columns) {
        $this->_sqlExpression['fields'] = $columns;
        return $this;
    }
    /**
     * 查询结果返回行数
     * @param int $offset 偏移量
     * @param int $rows 返回条数
     * @return Model
     */
    public final function limit($offset=1,$rows=0) {
        if($rows==0) $this->_sqlExpression['limit'] = array($offset);
        else $this->_sqlExpression['limit'] = array($offset,$rows);
        return $this;
    }
    /**
     * 排序
     * @param array|string $field 多个字段排序的话使用数组 eg:array('username'=>'desc','password'=>'asc');
     * @param string $sort
     * @return Model
     */
    public final function order($column,$sort='DESC') {
        $_orderby = '';
        if(is_array($column)) {
            foreach($column as $k=>$v) {
                if($_orderby == '') {
                    $_orderby = $k.' '.$v;
                }else{
                    $_orderby .= ','.$k.' '.$v;
                }
            }
        }else{
            $_orderby = $column.' '.$sort;
        }
        $this->_sqlExpression['order'] = $_orderby;
        return $this;
    }
    /**
     * 按与条件查询,eg: where('name','like','%c%'),  where(array('name','like','%c%'),array('id','=',1),'or')
     * @param string/array $column
     * @param string/array $operator
     * @param string/array $value
     * @return Model
     */
    public final function where($column,$operator=null,$value=null) {
        $args = func_get_args();
        $this->_sqlExpression['where'][] = array($args,'AND');
        return $this;
    }
    /**
     * 按或条件查询
     * @param string/array $column
     * @param string/array $operator
     * @param string/array $value
     * @return Model
     */
    public final function whereor($column,$operator,$value) {
        $args = func_get_args();
        $this->_sqlExpression['where'][] = array($args,'OR');
        return $this;
    }
    /**
     * 设置查询是锁定行或表（Innodb当查询条件为主键或者索引字段时行级锁）
     * @return Model
     */
    public final function lock() {
        $this->_sqlExpression['lock'] = true;
        return $this;
    }
	/**
	 * 设置当前使用表
	 * @return void
	 * @throws ModelException
	 */
	private function _setCurrentTable() {
	    if(empty($this->_currentTable)) {
	        if($this->table=='') {
	            throw new ModelException(Loader::getErrMsg('MODEL_NOT_DEFINED_TABLENAME',array($this->_modelname)),1);
	        }else{
	            $this->_currentTable = $this->tablePrefix.$this->table;
	        }
	    }
	}
	
}
class ModelException extends TException {
	
}
?>