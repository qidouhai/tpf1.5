<?php
/**
 * mysql数据连接类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db.drivers
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id$
 */
class Tpdomysql extends ParseMysqldb
{
    protected $_mode = PDO::FETCH_ASSOC;
    /**
     * 数据查询次数
     * @var int
     */
    private static $_query_times = 0;
    /**
     * 初始化数据连接，返回连接资源id
     *
     * @see Idb::init()
     */
    public function __construct()
    {
        if (! extension_loaded('pdo_mysql')) {
            throw new DBException(Loader::getErrMsg('PDOMYSQL_ISNOT_EXTENSION'), 1);
        }
    }
    /**
	 * 连接数据源
	 * @param array $config
	 */
	public function connect($config){
	    $_serverid = $this->_getServerid($config);
	    if (! isset($this->_resource[$_serverid])) {
	        try {
    	        if (isset($config['persistent']) && $config['persistent']) {
    	            $_conn = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['dbname'],$config['user'],$config['pass'],array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \''.$config['charset'].'\'',PDO::ATTR_PERSISTENT => true));
    	        }else{
    	            $_conn = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['dbname'],$config['user'],$config['pass'],array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES \''.$config['charset'].'\''));
    	        }
    	        $_conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    	        $this->_resource[$_serverid] = $_conn;
    	        $this->_dbname = $config['dbname'];
	        }catch(PDOException $e) {
	            
	            throw new DBException($e->getMessage(),1);
	        }
    	    
	    }  
	    $this->_resourceid = $_serverid;
	    return $_serverid;
	}
	
	/**
	 * 返回最新插入到数据库的行的ID
	 * @return int
	 */
	public function getLastInsertId() {
		return $this->_insertid;
	}
	/**
	 * 开启事务
	 * @param string $resourceid 资源id
	 */
	public function begin($resourceid) {
	    $this->_resource[$resourceid]->beginTransaction();
	}
	/**
	 * 提交执行事务
	 * @param string $resourceid 资源id
	 */
	public function commit($resourceid) {
	    $this->_resource[$resourceid]->commit();
	}
	/**
	 * 事务回滚
	 * @param string $resourceid 资源id
	 */
	public function rollback($resourceid) {
	    $this->_resource[$resourceid]->rollBack();
	}
	/**
	 * 设置查询结果模式
	 *
	 * @param int $mode
	 *            1:MYSQL_ASSOC 2:MYSQL_NUM 3:MYSQL_BOTH 4:OBJECT
	 */
    public function setMode($mode)
    {
        $_modeArr = array('',PDO::FETCH_ASSOC,PDO::FETCH_NUM,PDO::FETCH_BOTH,PDO::FETCH_OBJ);
        if ($mode > 0 && $mode < 5) {
            $this->_mode = $_modeArr[$mode];
        }
    }
	public function unbufferedQuery($sql,$resourceid) {
	    return $this->query($sql, $resourceid,PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
	}
	/**
	 * 执行数据查询
	 * @param string $sql
	 * @param string $resourceid 资源id
	 * @return IsqlStatement
	 */
	public function query($sql,$resourceid,$attribute=PDO::ATTR_DEFAULT_FETCH_MODE) {
	    $this->_querySql = $sql;
	    $this->_resource[$resourceid]->setAttribute($attribute,$this->_mode);
	    try{
	        self::$_query_times++;
	        self::$_global['db_query_times'] = self::$_query_times;
	        Application::getMicrotime('db_query' . self::$_query_times);
	       $_result = $this->_resource[$resourceid]->query($sql);
	       $this->Debug(self::$_query_times);
	       return $_result;
	    }catch(PDOException $e) {
	        throw new DBException($sql.' '.$e->getMessage(),4);
	    }
	}
	/**
	 * 执行一条sql语句返回影响的行数
	 * @param string $sql
	 * @return int
	 */
	public function exec($sql,$resourceid) {
	    $this->_querySql = $sql;
	    try{
    	    self::$_query_times++;
    	    self::$_global['db_query_times'] = self::$_query_times;
    	    Application::getMicrotime('db_query' . self::$_query_times);
    	    $_result = $this->_resource[$resourceid]->exec($sql);
    	    $this->Debug(self::$_query_times);
    	    $this->_insertid = $this->_resource[$resourceid]->lastInsertId();
    	    return $_result;
	    }catch(PDOException $e) {
	        throw new DBException($sql.' '.$e->getMessage(),4);
	    }
	}

	/**
	 * 关闭数据库
	 */
	public function close() {
	    
	}
	
}

