<?php

/**
 * 框架编译类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: Compile.php 230 2017-08-28 13:02:40Z charles_li $
 */
class Compile extends Tbs
{

    /**
     * 载入编辑文件，返回文件内容
     * 
     * @param string $filename
     *            文件名
     * @return string
     */
    public static function getFileContent($filename)
    {
        $_content = file_get_contents(TF_PATH . $filename . TF_EXT);
        $_content = substr(trim($_content), 5);
        if ('?>' == substr($_content, - 2))
            $_content = substr($_content, 0, - 2);
        return $_content;
    }
    /**
     * 编译框架文件到runtime
     */
    public static function setCompile($tpfCoreFile) {
        $_content = "<?php\n";
        $_content .= "!defined('TF_IN') && exit('Access Denied');\n";
        foreach($tpfCoreFile as $v) {
            $_content .= Compile::getFileContent($v);
        }
        $_content .= "class Debug extends Tbs{\n";
        $_content .= "public static function out(){}\n";
        $_content .= "public static function setNoout(){}\n";
        $_content .= 'public static function setParam($name,$value){}'."\n";
        $_content .= 'public static function unitEnd($tag){}'."\n";
        $_content .= 'public static function unitStart($tag){}'."\n";
        $_content .= "}\n";
        $_content = self::_strip_whitespace($_content);
        $_content .= 'Config::setConfigArr('.var_export(self::$_config,true).");\n";
        $_content .= 'Config::setPath('.var_export(self::$_buildpath,true).");\n?>";
        file_put_contents(APP_PATH.DIRECTORY_SEPARATOR.self::$_config['runtime'].DIRECTORY_SEPARATOR.'~runtime'.TF_EXT, $_content);
    }
    private static function _strip_whitespace($content) {
        $stripStr = '';
        $tokens = token_get_all($content);
        $last_space = false;
        for ($i = 0, $j = count($tokens); $i < $j; $i ++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    // 过滤各种PHP注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    // 过滤空格
                    case T_WHITESPACE:
                        if (! $last_space) {
                            $stripStr .= ' ';
                            $last_space = true;
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr .= $tokens[$i][1];
                }
            }
        }
        return $stripStr;
    }
    /**
     * 生产环境生成配置组件
     * @param boolean $prd 是否是生产环境
     */
    public static function loadComponents($prd=false) {
        $_controllerArr = self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir']));
        $_controllerArr = array_merge($_controllerArr,self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['controller_sub']['widget_dir'])));
        foreach(self::$_config['router']['module'] as $modulename) {
            $_controllerArr=array_merge($_controllerArr,self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['controller_sub']['module_dir'].DIRECTORY_SEPARATOR.$modulename)));
        }
        self::$_global['components'] = array();
        foreach($_controllerArr as $_controllername) {
            foreach(self::$_config['components'] as $_component=>$_ccontrollerArr) {
                foreach($_ccontrollerArr as $_controller) {
                    if(preg_match('/'.$_controller.'/', $_controllername)) {
                        self::$_global['components'][$_controllername][] = $_component;
                    }
                }
            }
        }
        if($prd) {
            $_content = "<?php\n";
            $_content .= "!defined('TF_IN') && exit('Access Denied');\n";
            $_content .= 'return '.var_export(self::$_global['components'],true).";\n";
            file_put_contents(APP_PATH.DIRECTORY_SEPARATOR.self::$_config['runtime'].DIRECTORY_SEPARATOR.'~components'.TF_EXT, $_content);
        }
    }
    /**
     * 生产环境生成过滤器
     * @param boolean $prd 是否是生产环境
     */
    public static function loadFilter($prd=false) {
        $_controllerArr = self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir']));
        $_controllerArr = array_merge($_controllerArr,self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['controller_sub']['widget_dir'])));
        foreach(self::$_config['router']['module'] as $modulename) {
            $_controllerArr=array_merge($_controllerArr,self::_getControllerArr(scandir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['controller_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['controller_sub']['module_dir'].DIRECTORY_SEPARATOR.$modulename)));
        }
        $_filterArr = array();
        $_filterkey = array();
        foreach($_controllerArr as $_controllername) {
            foreach(self::$_config['filters'] as $_filter=>$_ccontrollerArr) {
                foreach($_ccontrollerArr as $_controller) {
                    if(preg_match('/'.$_controller.'/', $_controllername) && !in_array($_controllername,$_filterkey)) {
                        $_filterArr[$_controllername] = $_filter;
                        $_filterkey[] = $_controllername;
                    }
                }
            }
        }
        self::$_global['filters'] = $_filterArr;
        if($prd) {
            $_content = "<?php\n";
            $_content .= "!defined('TF_IN') && exit('Access Denied');\n";
            $_content .= 'return '.var_export(self::$_global['filters'],true).";\n";
            file_put_contents(APP_PATH.DIRECTORY_SEPARATOR.self::$_config['runtime'].DIRECTORY_SEPARATOR.'~filters'.TF_EXT, $_content);
        }
    }
    
    /**
     * 目录控制器文件中获取控制器名
     * @param array $controllerfiles
     * @return string[]
     */
    private static function _getControllerArr($controllerfiles) {
        $_arr = array();
        foreach($controllerfiles as $_controllerfile) {
            if(substr($_controllerfile, -4)=='.php') {
                $_arr[] = substr($_controllerfile,0,-4);
            }
        }
        return $_arr;
    }
    /**
     * 生成应用程序
     */
    public static function deploy() {
        $_basicConf = require TF_PATH.'conf/conf.php'; //载入基础配置
        $_tpfCoreFile = array('core/TException','core/Loader','core/Config','core/Error','core/Functions','http/Request','http/Response','core/Router');
        foreach ($_tpfCoreFile as $v) {
            require TF_PATH.$v.TF_EXT;
        }
        spl_autoload_register(array('Loader', 'autoload'));
        Config::setConfigArr($_basicConf);
        $_buildPath = require TF_PATH.'conf/build.php';
        Config::setPath($_buildPath);
        Response::charset(self::$_config['charset']);
        Loader::importLang();  // 载入语言包
        Router::getInstance();
        require TF_PATH.'scaffold/HandleInstall'.TF_EXT;
        $do = getFormInt('do');
        if(HandleInstall::hasInstall()) {
            HandleInstall::setError(4);
        }else{
            switch($do) {
                case 0:
                    $isWrite = File::setFile('testwrite.txt', 'test');
                    if(!$isWrite) {
                        HandleInstall::setError(1);
                    }else{
                        unlink('testwrite.txt');
                        HandleInstall::setStep(1);
                    }
                    break;
                case 1:
                    HandleInstall::setStep(2);
                    HandleInstall::setSN();
                    break;
                case 2:
                    $key = getForm('key');
                    if(HandleInstall::checkKey($key)) {
                        HandleInstall::setStep(3);
                    }else{
                        HandleInstall::setSN();
                        HandleInstall::setError(2);
                    }
                    break;
                case 3:
                    $tracename = getForm('tracename');
                    if($tracename=='') {
                        HandleInstall::setError(5);
                    }else{
                        $nodb = getForm('nodb');
                        if($nodb!=1) {
                            $dbparam = array();
                            $dbparam['type'] = getForm('type');
                            $dbparam['host'] = getForm('host');
                            $dbparam['user'] = getForm('user');
                            $dbparam['pass'] = getForm('pass');
                            $dbparam['port'] = getForm('port');
                            $dbparam['perfix'] = getForm('perfix');
                            $dbparam['dbname'] = getForm('dbname');
                            $dbparam['charset'] = 'utf8';
                            HandleInstall::checkDB($dbparam);
                        }
                        $_relative = self::getRelativePath();
                        HandleInstall::createDirectory($_relative,$tracename);
                        HandleInstall::setError(3);
                        self::_forbidden();
                    }
                    break;
                default:
                    break;
            }
        }
        HandleInstall::show();
    }
    /**
     * 获取应用程序路径与框架的相对路径
     */
    private static function getRelativePath() {
        $_app_path = str_replace('\\', '/', APP_PATH);
        $_app_path = preg_replace('#/$#', '', $_app_path);
        $_tf_path = str_replace('\\','/',TF_PATH);
        $_tf_path = preg_replace('#/$#','',$_tf_path);
        $_app_pathArr = explode('/',$_app_path);
        $_tf_pathArr = explode('/',$_tf_path);
        $_diff1 = array_diff_assoc($_app_pathArr,$_tf_pathArr);
        $_diff2 = array_diff_assoc($_tf_pathArr,$_app_pathArr);
        $_count = count($_diff1);
        $path = '';
        for($i=0;$i<$_count;$i++) {
            $path .= '../';
        }
        $path .= implode('/', $_diff2);
        return $path;
    }
    
    /**
     * 增加目录访问权限安全性
     */
    private static function _forbidden() {
        File::setFile(APP_PATH.DIRECTORY_SEPARATOR.self::$_config['configdir'].DIRECTORY_SEPARATOR.'index.html', 'Access Forbidden');
        File::setFile(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['data_dir'].DIRECTORY_SEPARATOR.'index.html', 'Access Forbidden');
        foreach(self::$_buildpath['data_sub'] as $v) {
            File::setFile(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['data_dir'].DIRECTORY_SEPARATOR.$v.DIRECTORY_SEPARATOR.'index.html', 'Access Forbidden');
        }
    }
}

?>