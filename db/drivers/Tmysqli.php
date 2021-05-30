<?php
/**
 * mysql数据连接类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db.drivers
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.0
 * $Id$
 */
class Tmysqli extends ParseMysqldb 
{
    protected $_mode = MYSQLI_ASSOC;
    /**
     * 初始化数据连接，返回连接资源id
     *
     * @see Idb::init()
     */
    public function __construct()
    {
        if (! extension_loaded('mysqli')) {
            throw new DBException(Loader::getErrMsg('MYSQLI_ISNOT_EXTENSION'), 1);
        }
    }
    /**
     * {@inheritDoc}
     * @see Abstractdb::connect()
     */
    public function connect($config)
    {
        $_serverid = $this->_getServerid($config);
        if (! isset($this->_resource[$_serverid])) {
            if (isset($config['persistent']) && $config['persistent']) {
                $_conn = mysqli_connect('p:'.$config['host'] , $config['user'], $config['pass'],$config['dbname'],$config['port']);
            }else
                $_conn = mysqli_connect($config['host'] , $config['user'], $config['pass'],$config['dbname'],$config['port']);
            if (! $_conn) {
                throw new DBException(Loader::getErrMsg('MYSQL_CONNECT_ERROR', array(
                    $config['user'] . ':' . $config['pass'] . '@' . $config['host'] . ':' . $config['port']
                )), 1);
            } else {
                $this->_resource[$_serverid] = $_conn;
            }
            mysqli_query($_conn,"SET character_set_connection={$config['charset']}, character_set_results={$config['charset']}, character_set_client={$config['charset']}");
        }
        $this->_dbname = $config['dbname'];
        $this->_resourceid = $_serverid;
        return $_serverid;
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::begin()
     */
    public function begin($resourceid)
    {
        $this->_exec('START TRANSACTION', $resourceid, 1);
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::commit()
     */
    public function commit($resourceid)
    {
        $this->_exec('COMMIT', $resourceid, 1);        
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::rollback()
     */
    public function rollback($resourceid)
    {
        $this->_exec('ROLLBACK', $resourceid, 1);        
    }
    /**
     * 设置查询结果模式
     *
     * @param int $mode
     *            1:MYSQLI_ASSOC 2:MYSQLI_NUM 3:MYSQLI_BOTH 4:OBJECT
     */
    public function setMode($mode)
    {
        if ($mode > 0 && $mode < 5) {
            $this->_mode = $mode;
        }
    }
    /**
     * {@inheritDoc}
     * @see Abstractdb::unbufferedQuery()
     */
    public function unbufferedQuery($sql, $resourceid)
    {
        $this->_querySql = $sql;
        if (! isset(self::$_result[$resourceid . $this->_dbname])) {
            self::$_result[$resourceid . $this->_dbname] = new mysqliStatement($this, $this->_mode);
        }
        self::$_result[$resourceid . $this->_dbname]->unbufferExec($sql, $this->_resource[$resourceid]);
        return self::$_result[$resourceid . $this->_dbname];        
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::query()
     */
    public function query($sql, $resourceid)
    {
        return $this->_exec($sql, $resourceid);
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::exec()
     */
    public function exec($sql, $resourceid)
    {
        $_rs = $this->_exec($sql, $resourceid, 1);
        return $_rs->rowCount();        
    }
    /**
     * 设置最后插入的ID号
     *
     * @param int $lastid
     */
    public function setLastInsertId($lastid)
    {
        $this->_insertid = $lastid;
    }
    private function _exec($sql, $resourceid, $type = 0)
    {
        $this->_querySql = $sql;
        if (! isset(self::$_result[$resourceid . $this->_dbname])) {
            self::$_result[$resourceid . $this->_dbname] = new mysqliStatement($this, $this->_mode);
        }
        self::$_result[$resourceid . $this->_dbname]->exec($sql, $this->_resource[$resourceid], $type);
        return self::$_result[$resourceid . $this->_dbname];
    }

    /**
     * {@inheritDoc}
     * @see Abstractdb::close()
     */
    public function close()
    {
        if (is_array($this->_resource) && ! empty($this->_resource)) {
            foreach ($this->_resource as $serverid=>$link_identifier) {
                mysqli_close($link_identifier);
                unset($this->_resource[$serverid]);
            }
        }
        $this->_resource = array();
        $this->_resourceid = '';        
    }

}

class mysqliStatement extends Tbs implements Idb
{

    private $_result = null;

    private $_oMysql;

    private $_queryMode;

    private $_numRows = 0;

    /**
     * 查询类型 0:query查询返回结果集 1：exec执行返回影响行数
     *
     * @var boolean
     */
    private $_queryType = 0;
    /**
     * 数据查询次数
     * @var int
     */
    private static $_query_times = 0;

    public function __construct(Tmysqli $object, $mode)
    {
        $this->_oMysql = $object;
        $this->_queryMode = $mode;
    }

    public function unbufferExec($sql, $resource)
    {
        self::$_query_times++;
        self::$_global['db_query_times'] = self::$_query_times;
        Application::getMicrotime('db_query' . self::$_query_times);
        if ($this->_result)
            $this->free();
            $this->_result = mysqli_query($resource,$sql);
            $this->_oMysql->Debug(self::$_query_times);
            if ($this->_result === FALSE) {
                throw new DBException($sql.' '.mysqli_error($resource),4);
            }
            return $this->_result;
    }

    /**
     * 数据资源查询，query返回查询资源，exec返回影响条数
     *
     * @param string $sql
     * @param resource $resource
     *            连接资源
     * @param boolean $type
     * @throws DBException
     * @return int resource
     */
    public function exec($sql, $resource, $type = 0)
    {

        self::$_query_times++;
        self::$_global['db_query_times'] = self::$_query_times;
        Application::getMicrotime('db_query' . self::$_query_times);
        $this->_queryType = $type;
        if ($this->_result)
            $this->free();
            $this->_result = mysqli_query($resource,$sql);
            $this->_oMysql->Debug(self::$_query_times);
            if ($this->_result === FALSE) {
                Log::error($sql.' '.mysqli_error($resource));
            }
            if ($type == 0) {
                $this->_numRows = mysqli_num_rows($this->_result);
                return $this->_result;
            } else {
                $this->_numRows = mysqli_affected_rows($resource);
                $_lastid = mysqli_insert_id($resource);
                $this->_oMysql->setLastInsertId($_lastid);
                return $this->_result;
            }
    }

    /**
     * 从结果集中取出一行
     *
     * @return array
     */
    public function fetch()
    {
        if ($this->_queryType)
            return;
            if ($this->_queryMode == 4) {
                return mysqli_fetch_object($this->_result);
            } else
                return mysqli_fetch_array($this->_result, $this->_queryMode);
    }

    public function fetchAll()
    {
        if ($this->_queryType)
            return;
            $_result = array();
            switch ($this->_queryMode) {
                case 1:
                    $_func = 'mysqli_fetch_assoc';
                    break;
                case 2:
                    $_func = 'mysqli_fetch_row';
                    break;
                case 3:
                    $_func = 'mysqli_fetch_array';
                    break;
                case 4:
                    $_func = 'mysqli_fetch_object';
                    break;
                default:
                    $_func = 'mysqli_fetch_assoc';
                    break;
            }
            while (@$row = $_func($this->_result)) {
                $_result[] = $row;
            }
            unset($row);
            return $_result;
    }

    /**
     * 返回影响的行数
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->_numRows;
    }

    public function free()
    {
        if(!is_bool($this->_result))
            mysqli_free_result($this->_result);
            $this->_result = null;
    }

    public function __destruct()
    {
        $this->free();
    }
}

