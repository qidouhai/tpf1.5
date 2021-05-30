<?php
/**
 * @copyright    Copyright 2013 TYNT.CN
 * @author    <charles_li@msn.com>
 * @package    tpf
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.7
 * $Id: Loader.php 250 2017-09-15 03:25:19Z charles_li $
 */
class Loader extends Tbs{
	private static $_import = array();		//载入文件数组
	private static $_hasController = false;    //是否加载了主控制器
	/**
	 * 框架类
	 * @var array
	 */
	private static $_framework = array(
        'ArrayList'  => 'core/ArrayList',
        'Date'      => 'core/Date',
	    'Debug'    => 'core/Debug',
	    'TString'   => 'core/TString',
	    'Unit'     => 'core/Unit',
	    'fileLog'  => 'log/fileLog',
	    'File'     => 'core/File',
	    'Validator'    => 'core/Validator',
		'Cache'=>'cache/Cache',
		'Tcrypt'=>'crypt/Tcrypt',
		'Http'=>'http/Http',
		'Plugin'=>'plugins/Plugin',
        'Model'=> 'model/Model',
		'AbstractSession'=>'session/AbstractSession',
		'Session'=>'session/Session',
	    'View' => 'view/View',
	    'Helper'   => 'view/Helper',
		'ComponentFactory'=>'controller/ComponentFactory',
	    'Component'    => 'controller/Component',
	    'Widget'   => 'controller/Widget',
	    'Scaffold' =>  'scaffold/Scaffold',
	    'HandleModelScaffold'  => 'scaffold/HandleModelScaffold',
	    'JsonView' =>  'view/JsonView',
	    'JsonpView'    => 'view/JsonpView',
	    'TextView' =>  'view/TextView',
	    'HtmlView'  => 'view/HtmlView',
	    'Compile'  => 'core/Compile',
	    'Filter'   =>  'controller/Filter',
	    'db'       =>  'db/db',
	    'ParseMysqldb' => 'db/ParseMysqldb',
	    'Abstractdb'   => 'db/Abstractdb',
	    'Idb'      =>  'db/Idb'
	);
	
	/**
	 * 设置语言
	 */
	public static function importLang() {
	    $_lang_charset = strtolower(self::$_config['charset']);
	    if($_lang_charset!='gb2312' && $_lang_charset!='gbk' && $_lang_charset!='gb18030') {
	        $_lang_charset = 'utf8';
	    }
	    self::$_lang = self::_doImport('lang'.DIRECTORY_SEPARATOR.$_lang_charset.DIRECTORY_SEPARATOR.self::$_config['language']);
	}
	/**
	 * 获取框架语言包数据
	 * @return array;
	 */
	public static function getLang() {
	    return self::$_lang;
	}
	public static function autoLoad($classname) {
	    if(isset(self::$_framework[$classname])) {
			self::_doImport(self::$_framework[$classname]);
		}elseif(substr($classname,-10)==='Controller') {	//载入用户控制器
		    try {
			    self::_loadCM('controller_dir', $classname,2);  
		    }catch (LoaderException $e) {
		        return ;
		    }
		}elseif(substr($classname,-9)==='Component') {
			try {
				self::_loadCM('controller_dir', $classname,1,self::$_buildpath['controller_sub']['component_dir']);
			}catch(LoaderException $e) {
				return ;
			}
		}elseif(substr($classname,-6) == 'Filter') {
		    try {
		        self::_loadCM('controller_dir', $classname,1,self::$_buildpath['controller_sub']['filter_dir']);
		    }catch(LoaderException $e) {
		        return ;
		    }
		}elseif(substr($classname,-6) == 'Widget') {
		    try {
		        self::_loadCM('controller_dir', $classname,1,self::$_buildpath['controller_sub']['widget_dir']);
		    }catch(LoaderException $e) {
		        return ;
		    }
		}elseif(substr($classname,-5) == 'Model'){
			try {
				self::_loadCM('model_dir', $classname,1);
			}catch(LoaderException $e) {
				return ;
			}
		}elseif(substr($classname,-6) == 'Helper') {
			self::_loadHelper($classname);
		}else{
		    try {
			    self::_loadVendors($classname);
		    }catch(LoaderException $e) {
		        return ;
		    }
		}
		return ;
	}
	private static function _loadVendors($classname) {
	    $_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['vendors_dir'].DIRECTORY_SEPARATOR.$classname.TF_EXT;
	    return self::_importFile($_path,$_path);
	}
	/**
	 * 载入控制器、组件、模块、模型
	 * @param string $classname
	 */
	private static function _loadCM($pathname,$classname,$logLevel,$childpath='') {
	    try {
    		$_cm_dir = self::$_buildpath[$pathname];
    		if($childpath!='') $_cm_dir .= DIRECTORY_SEPARATOR.$childpath;
    		$_module = Router::getModule();
    		if($_module!='' && $pathname == 'controller_dir' && $childpath=='') {
    			$_cm_dir .= DIRECTORY_SEPARATOR.self::$_buildpath['controller_sub']['module_dir'].DIRECTORY_SEPARATOR.$_module;
    		}
    		if(!isset(self::$_import[$_cm_dir.DIRECTORY_SEPARATOR.$classname])) {
    		    return self::_importFile(APP_PATH.DIRECTORY_SEPARATOR.$_cm_dir.DIRECTORY_SEPARATOR.$classname.TF_EXT, $_cm_dir.DIRECTORY_SEPARATOR.$classname);
    		}
	    }catch (LoaderException $e) {
	        self::_loadVendors($classname);
	    }
	}
	/**
	 * 载入助手类
	 * @param string $classname
	 */
	private static function _loadHelper($classname) {
		try {
			$_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['view_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['view_sub']['helper_dir'].DIRECTORY_SEPARATOR.$classname.TF_EXT;
			return self::_importFile($_path, $_path);
		}catch(LoaderException $e) {
			try {
				$_path = TF_PATH.'view'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$classname.TF_EXT;
				return self::_importFile($_path,$_path);
			}catch(LoaderException $e) {
				return FALSE;
			}
		}
	}
	/**
	 * 导入包含文件
	 * @param string $filename
	 * @param $string $ext 扩展名
	 * @example Loader::import('lib/News.phtml');
	 */
	final public static function import($filename) {
		return self::_doImport($filename, FALSE);
	}
	/**
	 * 载入404错误模版,结束程序
	 */
	public static function load404() {
		if(self::$_config['errorpage']!='') {
			try{
				$_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['view_dir'].DIRECTORY_SEPARATOR.self::$_config['errorpage'].'.'.self::$_config['template']['ext'];
				self::_importFile($_path,'errorpage');
			}catch(LoaderException $e) {
			    $_param['language'] = self::$_lang;
			    $_param['charset'] = self::$_config['charset'];
				Loader::loadTpl('404',$_param);
			}
		}else{
		     $_param['language'] = self::$_lang;
		     $_param['charset'] = self::$_config['charset'];
			 Loader::loadTpl ('404',$_param);
		}
		die;
	}
	/**
	 * 载入系统模版文件
	 * @param string $filename
	 * @param array $param 传入模版中的参数
	 */
	public static function loadTpl($filename,$param=array()) {
		$_path = TF_PATH.'view'.DIRECTORY_SEPARATOR.'tynt'.DIRECTORY_SEPARATOR.$filename.'.tpl';
		return self::_importFile($_path,'tpl.'.$filename,$param);
	}
	/**
	 * 载入应用程序配置文件
	 * @param string $filename 配置文件名
	 */
	public static function loadConf($filename) {
		if(!isset(self::$_import['app'.DIRECTORY_SEPARATOR.self::$_config['configdir'].DIRECTORY_SEPARATOR.$filename])) {
			$_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_config['configdir'].DIRECTORY_SEPARATOR.$filename.TF_EXT;
			    if(!is_file($_path)) {
                    Error::printSysError('Configuration file not found "'.$_path.'"');
			    }else{
    				self::$_import['app'.DIRECTORY_SEPARATOR.self::$_config['configdir'].DIRECTORY_SEPARATOR.$filename] = TRUE;
    				return require $_path;
			    }
		}else return ;
	}
	/**
	 * 载入包含文件
	 * @param string $filename
	 * @throws LoaderException
	 * @return object
	 */
	private static function _importFile($filename,$key,$param=array()) {
	    if(!is_file($filename)) {
	        throw new LoaderException(Loader::getErrMsg('FILE_ISNOT_EXIST',array($filename)),2);
	    }
	    self::$_import[$key] = true;
	    return require $filename;
	}
	public static function loadWidget($widget,$action,$param=array()) {
	    $_widget = $widget.'Widget';
	    $oWidget = new $_widget;
	    try{
	       self::_checkWidget($_widget,$action);
	       $oWidget->Init($widget, $action);
	       call_user_func_array(array($oWidget,$action), $param);
	    }catch(LoaderException $e) {
	        $e->getInfo();
	    }
	}
	
	private static function _checkWidget($widget,$action) {
	    if(!is_subclass_of($widget, 'Widget')) {
	        throw new LoaderException(Loader::getErrMsg("WIDGET_ISNOT_INTERFACE",array($widget)),2);
	    }
	    if(!method_exists($widget, $action)) {
	        throw new LoaderException(Loader::getErrMsg("WIDGET_ISNOT_ACTION",array($widget,$action)),4);
	    }
	}
	/**
	 * 加载数据引擎
	 * @param string $classname
	 */
	public static function loadDB($classname) {
		self::_doImport('db/drivers/'.$classname);
	}
	/**
	 * 加载文件
	 * @param string $name 文件名包含包名 eg:com.tynt.web.user=webapp/com/tynt/web/user.php, com.tynt.web.* 设置载入这个目录
	 * @param string $ext 扩展名 默认扩展名.php
	 * @param boolean $isTF 是否框架类
	
	 */
	private static function _doImport($name,$isTF=TRUE) {
	    if(!$isTF) $_loadname = 'app/'.$name;
	    else $_loadname = $name;
		if(!isset(self::$_import[$_loadname])) {
			if($isTF) {
				$_path = TF_PATH.$name.TF_EXT;
				self::$_import[$_loadname] = true;
				return require $_path;
			}else {
			    if(substr($name,-1,1)==='*') {
			        $_dir = strtr($name, array('.'=>DIRECTORY_SEPARATOR,'*'=>''));
			        set_include_path(
            		implode(PATH_SEPARATOR, array(
            		APP_PATH.DIRECTORY_SEPARATOR.$_dir,
            		get_include_path())
            		));
			        self::$_import[$_loadname] = TRUE;
			    }else{
                    $_offset = strrpos($name, '.');
                    if($_offset == FALSE) {
                        $_suffix = TF_EXT;
                    }else {
                        $_suffix = substr($name,$_offset);
                        $name = substr($name,0,$_offset);
                    }
    				$_path = APP_PATH.DIRECTORY_SEPARATOR.strtr($name, '.' , DIRECTORY_SEPARATOR).$_suffix;
    				try{
    					return self::_importFile($_path,$_loadname);
    				}catch(LoaderException $e){
    					$e->getInfo();
    				}
			    }
			}
			
		}
	}
	/**
	 * 获得包含页数组
	 * @return array
	 */
	public static function getInclude()
	{
		return self::$_import;
	}
	/**
	 * 获取错误提示
	 * @param string $langKey 语言包键名
	 * @param array $param 替换参数数组
	 * @return string
	 */
	public static function getErrMsg($langKey,$param=array()) {
		$_langstr = self::$_lang[$langKey];
		if(isset($param) && is_array($param) && count($param) != 0){
			foreach ($param as $k => $v){
				$rk = $k+1;
				$_langstr = str_replace('\\'.$rk, $v, $_langstr);
			}
		}
		return $_langstr;
	}
}
class LoaderException extends TException {
	
}
?>