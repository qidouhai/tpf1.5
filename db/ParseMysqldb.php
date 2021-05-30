<?php
/**
 * 解析mysql
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.db
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.4
 * $Id$
 */
abstract class ParseMysqldb extends Abstractdb
{
    private $_tableFields = array();    //运行时表结构信息
    /**
     * 资源组
     *
     * @var array
     */
    protected $_resource = array();
    
    protected static $_result = array(); // 查询资源
    protected $_dbname = ''; // 当前数据库名
    /**
     * 解析数据表
     * @param string $str
     * @return string
     */
    protected function _parseTable($str,$alias=0) {
        preg_match('/^(\w+)\s*(\w*)/i', $str,$arr);
        if($alias==0) {
            return '`'.$arr[1].'`';
        }else{
            return '`'.$arr[1].'` '.$arr[2];
        }
    }
    
    protected function _formatVal($type,$value) {
        switch ($type) {
            case 'float':
                return (float) $value;
                break;
            case 'int':
                return (int) $value;
                break;
            case 'double':
                return (double) $value;
                break;
            default:
                return (float) $value;
                break;
        }
    }
    
    /**
     * 解析字段名
     * @param string $column
     * @return array
     */
    protected function _parseColumn($column) {
        preg_match('/^(\w+\.)*(\w+)/i', $column,$arr);
        return array($arr[1],$arr[2]);
    }
    /**
     * 获取表字段信息
     *
     * @see Abstractdb::getFields()
     */
    public function getFields($tablename, $resourceid)
    {
        $tablename = $this->_parseTable($tablename);
        try {
            $_qe = $this->query('SHOW FIELDS FROM ' . $tablename, $resourceid);
            $_result = $_qe->fetchAll();
            $_arr = array();
            foreach ($_result as $k => $v) {
                $_arr[$v['Field']] = array(
                    'name' => $v['Field'],
                    'typestr' => $v['Type'],
                    'type' => $this->_getType($v['Type']),
                    'null' => $v['Null']
                );
                if (strtolower($v['Key']) == 'pri')
                    $_arr[$v['Field']]['primary'] = 1;
                    if (strtolower($v['Extra']) == 'auto_increment')
                        $_arr[$v['Field']]['auto'] = 1;
            }
            return $_arr;
        }catch (DBException $e) {
            $e->getInfo();
        }
    }
    /**
     * 根据mysql类型获取php强制转换类型
     *
     * @param string $type
     * @return string
     */
    private function _getType($type)
    {
        $col = str_replace(')', '', $type);
        if (strpos($col, '(') !== false) {
            list ($col, $vals) = explode('(', $col);
        }
        if ($col == 'bigint') {
            return 'int';
        }
        if (strpos($col, 'int') !== false) {
            return 'int';
        }
        if (strpos($col, 'float') !== false) {
            return 'float';
        }
        if (strpos($col, 'numeric') !== false || strpos($col, 'real') !== false || strpos($col, 'double') !== false || strpos($col, 'decimal') !== false) {
            return 'double';
        }
        return 'string';
    }
    /**
     * where操作符组合成where子句
     * @param string $column 字段名
     * @param string $operator 操作符
     * @param string $type 数据字段类型
     * @param string $value 值
     * @return string
     */
    protected function _setWhereOperator($column,$operator,$type,$value,$wherenum) {
        $whereStr = '';
        $operator = trim(strtoupper($operator));
        if($operator == 'REGEXP' || $operator=='NOT REGEXP' || $operator == 'LIKE' || $operator == 'NOT LIKE') {
            $whereStr = $column.' '.$operator.' '.db::addQuotes($value);
        }elseif($operator == 'IN' || $operator == 'NOT IN') {
            switch ($type) {
                case 'string':
                    $whereStr =  $column.' '.$operator.' ('.implode(',', array_map('db::addQuotes', $value)).')';
                    break;
                case 'float':
                    $whereStr =  $column.' '.$operator.' ('.implode(',', array_map('floatval', $value)).')';
                    break;
                case 'int':
                    $whereStr = $column.' '.$operator.' ('.implode(',', array_map('intval', $value)).')';
                    break;
                case 'double':
                    $whereStr = $column.' '.$operator.' ('.implode(',', array_map('doubleval', $value)).')';
                    break;
                default:
                    $whereStr = $column.' '.$operator.' ('.implode(',', array_map('db::addQuotes', $value)).')';
                    break;
            }
        }else{
            switch($type) {
                case 'string':
                    $whereStr = $column .' '.$operator.' '.db::addQuotes($value);
                    break;
                case 'double':
                    $whereStr = $column .' '.$operator.' '.(double) $value;
                    break;
                case 'int':
                    $whereStr = $column .' '.$operator.' '.(int) $value;
                    break;
                case 'float':
                    $whereStr = $column .' '.$operator.' '.(float) $value;
                    break;
                default:
                    $whereStr = $column .' '.$operator.' '.db::addQuotes($value);
                    break;
            }
        }
        if($wherenum>1) {
            $whereStr = '('.$whereStr.')';
        }
        return $whereStr;
    }
    
    /**
     * 解析sql语句
     * @param array $opt
     * @param string $sqlstr
     * @param array $tableFields
     * @return string
     */
    protected function _parseSql($opt, $sqlstr,$tableFields)
    {
        if(isset($opt['join'])) {
            foreach($opt['join'] as $v) {
                $_type = strtoupper($v[3]);
                $sqlstr .= ' '.$_type.' JOIN '.$this->_parseTable($v[0],1).' ON '.$v[1].' = '.$v[2];
            }
        }
        $_wherestr = '';
        if (isset($opt['where'])) {
            $_wherenum = count($opt['where']);
            foreach ($opt['where'] as $args) {
                if (! isset($args[0][1])) { // 传入where字符串
                    if (empty($_wherestr)) {
                        if($_wherenum>1) {
                            $_wherestr = '('.$args[0][0].')';
                        }else{
                            $_wherestr = $args[0][0];
                        }
                    } else {
                        $_wherestr .= ' ' . $args[1] . ' (' . $args[0][0] . ')';
                    }
                } elseif (is_array($args[0][0])) {
                    $_argsNum = count($args[0]);
                    $_logicNum = $_argsNum - 1;
                    $_subLogic = $args[0][$_argsNum - 1];
                    $_subwhere = '';
                    for ($i = 0; $i < $_logicNum; $i ++) {
                        $_column = $this->_parseColumn($args[0][$i][0]);
                        if ($_subwhere == '') {
                            $_subwhere = $this->_setWhereOperator($_column[0].'`'.$_column[1].'`', $args[0][$i][1], $tableFields[$_column[1]]['type'], $args[0][$i][2], $_wherenum);
                        } else {
                            $_subwhere .= ' ' . $_subLogic . ' ' . $this->_setWhereOperator($_column[0].'`'.$_column[1].'`', $args[0][$i][1], $tableFields[$_column[1]]['type'], $args[0][$i][2], $_wherenum);
                        }
                    }
                    if (empty($_wherestr)) {
                        if($_wherenum>1) {
                            $_wherestr = '(' . $_subwhere . ')';
                        }else{
                            $_wherestr = $_subwhere;
                        }
                    } else {
                        $_wherestr .= ' ' . $args[1] . ' (' . $_subwhere . ')';
                    }
                } else {
                    $_column = $this->_parseColumn($args[0][0]);
                    if (empty($_wherestr)) {
                        $_wherestr = $this->_setWhereOperator($_column[0].'`'.$_column[1].'`', $args[0][1], $tableFields[$_column[1]]['type'], $args[0][2],$_wherenum);
                    } else {
                        $_wherestr .= ' ' . $args[1] .' '. $this->_setWhereOperator($_column[0].'`'.$_column[1].'`', $args[0][1], $tableFields[$_column[1]]['type'], $args[0][2],$_wherenum);
                    }
                }
            }
            if(!empty($_wherestr))
                $sqlstr .= ' WHERE '.$_wherestr;
        }
        if(isset($opt['union'])) {
            $sqlstr .= ' UNION '.$opt['union'];
        }
        if(isset($opt['group'])) {
            $sqlstr .= ' GROUP BY '.$opt['group'];
        }
        if(isset($opt['having'])) {
            $sqlstr .= ' HAVING '.$opt['having'][0].' '.$opt['having'][1].' '.$opt['having'][2];
        }
        if (isset($opt['order']))
            $sqlstr .= ' ORDER BY ' . $opt['order'];
        if (isset($opt['limit'])) {
            if(isset($opt['limit'][1])) {
                $sqlstr .= ' LIMIT ' . $opt['limit'][0] .','.$opt['limit'][1];
            }else{
                $sqlstr .= ' LIMIT '.$opt['limit'][0];
            }
        }
        if(isset($opt['lock']) && $opt['lock']) {
            $sqlstr .= ' FOR UPDATE';
        }
        return $sqlstr;
    }
    /**
     * 从数据库中获取字段类型数组
     * @param string $connection 连接资源名
     * @param boolean $isWrite 是否写入缓存文件
     * @return array
     */
    private function _getDBField($resourceid,$tablename) {
        $_tableKey = $resourceid.'_'.$this->_dbname.'_'.$tablename;
        if(!isset($this->_tableFields[$_tableKey])) {
            if(self::$_config['environment']=='prd') {
                $_modelfile = APP_PATH.'/'.self::$_buildpath['data_dir'].'/'.self::$_buildpath['data_sub']['model_dir'].'/~M'.md5($_tableKey.self::$_config['passwdseek']).TF_EXT;
                if(is_file($_modelfile)) {
                    $_data  = file_get_contents($_modelfile);
                    $_data = substr($_data,51);
                    $_data = TFunserialize($_data);
                    $this->_tableFields[$_tableKey] = $_data;
                }else{
                    $_fields = $this->getFields($tablename, $resourceid);
                    $_data = "<?php !defined('TF_IN') && exit('Access Denied');?>";
                    $_data .= TFserialize($_fields);
                    File::setFile($_modelfile, $_data);
                    $this->_tableFields[$_tableKey] = $_fields;
                }
            }else{
                $this->_tableFields[$_tableKey] = $this->getFields($tablename, $resourceid);
            }
        }
        return $this->_tableFields[$_tableKey];
    }
    
    /**
     * 获取查询结果集
     *
     * @param array $opt
     * @param string $resourceid 资源id
     * @param boolean $type 0查询列表，1查询第一条
     * @return array
     */
    public function select($opt, $resourceid, $type = 0)
    {
        $sqlstr = $this->_getSelectSQL($opt, $resourceid);
        $_rs = $this->query($sqlstr, $resourceid);
        if ($type == 1) {
            return $_rs->fetch();
        } else
            return $_rs->fetchAll();
    }
    /**
     * 返回select的sql语句
     * @param array $opt
     * @param string $resourceid
     * @return string
     */
    public function sql($opt,$resourceid) {
        return $this->_getSelectSQL($opt, $resourceid);
    }
    /**
     * 返回查询sql语句
     * @param array $opt
     * @param string $resourceid
     * @return string
     */
    private function _getSelectSQL($opt,$resourceid) {
        $_tableKey = $resourceid.'_'.$this->_dbname.'_'.TFserialize($opt);
        
        $_tableFields = $this->_getDBField($resourceid, $opt['table']); //获取表字段信息
        $sqlstr = 'SELECT ';
        if (! isset($opt['fields'])) {
            $sqlstr .= '*';
        } else
            $sqlstr .= $opt['fields'];
            $sqlstr .= ' FROM ' . $this->_parseTable($opt['table'],1);
            $sqlstr = $this->_parseSql($opt, $sqlstr,$_tableFields);
            return $sqlstr;
    }
    /**
     * 更新数据,返回更新条数
     *
     * @param array $data
     *            待更新的数据
     * @param array $opt
     *            查询结构
     * @param string $resourceid
     * @return int
     */
    public function update($data, $opt, $resourceid)
    {
        $_tableFields = $this->_getDBField($resourceid, $opt['table']); //获取表字段信息
        $_updateStr = '';
        foreach ($data as $k => $v) {
            if(!isset($_tableFields[$k])) {
                throw new DBException(Loader::getErrMsg('DBCONFIG_FIELD_ISNOT_EXIST',array($opt['table'],$k)),2);
            }
            if ($_tableFields[$k]['type'] == 'string') {
                $_updateStr .= ',`' . $k . "`='" . addslashes_deep($v) . "'";
            } else {
                if ($v !== '')
                    $_updateStr .= ',`' . $k . '`=' . $this->_formatVal($_tableFields[$k]['type'],$v);
            }
        }
        $_updateStr = trim($_updateStr, ',');
        $sqlstr = 'UPDATE ' . $opt['table'] . ' SET ' . $_updateStr;
        $sqlstr = $this->_parseSql($opt, $sqlstr,$_tableFields);
        return $this->exec($sqlstr, $resourceid);
    }
    public function increment($field,$step,$data,$opt,$resourceid) {
        $_tableFields = $this->_getDBField($resourceid, $opt['table']); //获取表字段信息
        $_updateStr = $field.'='.$step;
        foreach ($data as $k => $v) {
            if ($_tableFields[$k]['type'] == 'string') {
                $_updateStr .= ',' . $k . "='" . addslashes_deep($v) . "'";
            } else {
                if ($v !== '')
                    $_updateStr .= ',' . $k . '=' . $this->_formatVal($_tableFields[$k]['type'], $v);
            }
        }
        $sqlstr = 'UPDATE ' . $opt['table'] . ' SET ' . $_updateStr;
        $sqlstr = $this->_parseSql($opt, $sqlstr,$_tableFields);
        return $this->exec($sqlstr, $resourceid);
    }
    /**
     * 插入数据返回插入后的ID
     * @param array $data 待插入的数据
     * @param array $opt 查询结构
     * @param string $resourceid 资源id
     * @return int
     */
    public function insert($data, $opt, $resourceid)
    {
        return $this->_irSql($data, $opt, $resourceid, 'INSERT');
    }
    /**
     * 根据主键替换数据
     * @see Abstractdb::replace()
     */
    public function replace($data, $opt, $resourceid) {
        return $this->_irSql($data, $opt, $resourceid, 'REPLACE');
    }
    private function _irSql($data, $opt, $resourceid,$sql) {
        $_tableFields = $this->_getDBField($resourceid, $opt['table']); //获取表字段信息
        $_insertKey = '';
        $_insertValue = '';
        foreach ($data as $k => $v) {
            if ($_tableFields[$k]['type'] == 'string') {
                $_insertKey .= ',`' . $k.'`';
                $_insertValue .= ",'" . addslashes_deep($v) . "'";
            } else {
                if ($v !== '') {
                    $_insertKey .= ',`' . $k.'`';
                    $_insertValue .= ',' . $this->_formatVal($_tableFields[$k]['type'],$v);
                }
            }
        }
        $_insertKey = trim($_insertKey, ',');
        $_insertValue = trim($_insertValue, ',');
        return $this->exec($sql." INTO ".$opt['table']."(".$_insertKey.") VALUES(".$_insertValue.")",$resourceid);
    }
    /**
     * 删除记录
     *
     * @param array $opt
     * @param string $resourceid
     */
    public function delete($opt, $resourceid)
    {
        $_tableFields = $this->_getDBField($resourceid, $opt['table']); //获取表字段信息
        $sqlstr = 'DELETE FROM ' . $opt['table'];
        $sqlstr = $this->_parseSql($opt, $sqlstr, $_tableFields);
        return $this->exec($sqlstr,$resourceid);
    }
}

