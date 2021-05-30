<?php
/**
 * 数据库存储session
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: dbSession.php 209 2016-10-13 02:32:13Z charles_li $
 */
class dbSession extends AbstractSession {
	/**
	 * 数据库实例
	 * @var db
	 */
	private static $db = null;
	/**
	 * dbsession实例
	 * @var dbsession
	 */
	private static $_dbinst = null;
	/**
	 * session是否已保存过
	 * @var boolean
	 */
	private $_isSave = false;
	public function __construct() {
		self::$db = self::instance('db');
		$this->_init();
	}
	/**
	 * 初始化数据
	 */
	protected function _init() {
		if(!self::$_isSet) {
		    $this->_sessconfig = self::$_config['session'];
		    try{
			$this->_sessconfig['dbconfig'] = $this->_bindConfig(self::$_config['session']['dbconnection']);
			self::$_isSet = TRUE;
		    }catch (ConfigException $e) {
		        $e->getInfo();
		    }
		}
		parent::__construct();
	}
	/**
	 * 绑定配置文件，返回当前连接名的配置数组
	 * @param $connection 连接名或dsn
	 * @return array
	 * @throws ConfigException
	 */
	private function _bindConfig($connection) {
	    $_dbconfig = array();
        if(!array_key_exists($connection, self::$_config['db'][self::$_config['environment']])) {
            $_dbconfig = db::dsn2Config(self::$_config['session']['dbconnection']);
        }else{
	        //在数据配置列表中搜索读写库配置是否存在
	        if(!array_key_exists(self::$_config['db'][self::$_config['environment']][$connection]['dbconfig'], self::$_config['db']['config'])) {
	            throw new ConfigException(Loader::getErrMsg('DBCONFIG_WRCONFIG_ERROR',array($connection)),2);
	        }
	        if(in_array(self::$_config['db'][self::$_config['environment']][$connection]['type'],array('mysql','mysqli','pdomysql','pdomysqli','access','sqlserver','sqlite'))) {
	            $_dbconfig = array_merge(self::$_config['db'][self::$_config['environment']][$connection],self::$_config['db']['config'][self::$_config['db'][self::$_config['environment']][$connection]['dbconfig']]);
	            $_dbconfig['persistent'] = 0;
	        }else{
	            throw new ConfigException(Loader::getErrMsg('DBCONFIG_ERROR',array($connection)),2);
	        }
        }
	    return $_dbconfig;
	}
	/**
	 * 读取session值
	 * @param string $sessID
	 * @return string
	 * @see Abstractsession::read()
	 */
	public function read($sessID) {
		self::$db->init($this->_sessconfig['dbconfig']);
		$_opt = array();
		$_opt['table']  = $this->_sessconfig['table'];
		$_opt['fields'] = 'data';
		$_opt['where'][] = array(array(array('expiry','>',time()),array('sessid','=',$sessID),'AND'),'AND');
        $_opt['orderby'] = 'expiry DESC';
		$_opt['limit'] = array(1);
		$_data = self::$db->first($_opt);
		if(isset($_data['data'])) {
		    $this->_isSave = true;
		    return $_data['data'];
		}else return '';
	}
	/**
	 * 写入session
	 * @param string $sessID
	 * @param string $sessData
	 * @return void
	 * @see Abstractsession::write()
	 */
	public function write($sessID, $sessData) {
	    try{
    		self::$db->init($this->_sessconfig['dbconfig']);
    		$_expiry = time() + $this->_sessconfig['lifetime'];
    		$_opt = array();
    		$_opt['table']  = $this->_sessconfig['table'];
    		$_opt['where'][] = array(array('sessid','=',$sessID),'AND');
    		if($this->_isSave) {
    		    $_num = self::$db->save($_opt,array('sessid'=>$sessID,'expiry'=>$_expiry,'data'=>$sessData));
    		}else{
    		    $_num = self::$db->add($_opt,array('sessid'=>$sessID,'expiry'=>$_expiry,'data'=>$sessData));
    		}
    		$this->_isSave = true;
    		if($_num > 0) {
    			return TRUE;
    		}
    		else return FALSE;
	    }catch (Exception $e) {
	        return FALSE;
	    }
	}
	/**
	 * 删除一个session
	 * @param string $sessID
	 * @return boolean
	 * @see Abstractsession::destroy()
	 */
	public function destroy($sessID) {
	    try {
    		self::$db->init($this->_sessconfig['dbconfig']);
    		$_opt = array();
    		$_opt['table']  = $this->_sessconfig['table'];
    		$_opt['where'][] = array(array('sessid','=',$sessID),'AND');
    		$_num = self::$db->del($_opt);
	    }catch (DBException $e) {
	        return FALSE;
	    }
	    if($_num) {
	        return TRUE;
	    }
	    else return FALSE;
	}
	/**
	 * session回收
	 * @see Abstractsession::gc()
	 */
	public function gc($maxLifeTime) {
	    try {
    		self::$db->init($this->_sessconfig['dbconfig']);
    		$_opt = array();
    		$_opt['table']  = $this->_sessconfig['table'];
    		$_opt['where'][] = array(array('expiry','<',time()),'AND');
    		self::$db->del($_opt);
	    }catch (DBException $e) {
            return FALSE;
	    }
		return TRUE;
	}
}

?>