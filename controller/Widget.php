<?php
/**
 * 挂件抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.controller
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.4
 * @version v1.5.4
 * $Id: Widget.php 274 2018-04-23 08:48:51Z charles_li $
 */
abstract class Widget extends AbstractController
{
    private  $_className;
    private  $_actionName;
    private $_param = array();
    private $_path;     //视图路径
    /**
     * 挂件中用到的组件数组
     * @var array
     */
    protected $components = array();			//调用的组件数组
    /**
     * 组件对象
     * @var Component
     */
    private $_oComponent;
    private $_useComponent = FALSE;		//是否启用组件
    /**
     * 解析输出模版 eg: array('<{__LEFT__}>'=>'global/left');视图标签<{__LEFT__}>，解析载入views/global/left.phtml
     * @var array
     */
    private $_parse = array();
    public function __construct() {
        $this->_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['view_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['view_sub']['widget_dir'];
        $this->request =  self::instance('Request');
        $this->response =  self::instance('Response');
        //parent::__construct();
        if($this->useSession) $this->useSession();
        $controllername = get_class($this);
        if(!isset(self::$_global['components'][$controllername])) self::$_global['components'][$controllername] = array();
 		$this->components = array_merge(self::$_global['components'][$controllername],$this->components);
        if(count($this->components) > 0) {
            $this->_useComponent = TRUE;
            $this->_oComponent = self::instance('ComponentFactory');
            $this->_oComponent->init($this,$this->components,$controllername);
            $this->_oComponent->before($this);
        }
    }
    /**
     * 初始化
     * @param string $widget 挂件名
     * @param string $action 挂件方法
     * @return void
     */
    public function Init($widget,$action) {
        $this->_className = $widget;
        $this->_actionName = $action;
    }
    /**
     * 设置解析载入的模版
     * @param string $key 模版中替换的key值，注意别重复
     * @param string $value 载入模版的目录
     */
    public function setParse($key,$value) {
        $this->_parse[$key] = $value;
    }
    /**
	 * 传递控制器值到视图对象
	 * @param object/array/string $name 传递对象属性/数组/键名
	 * @param string $value	当name为string时为键值，name为其他参数类型时设置无效
	 */
    public function assign($name,$value) {
        if(is_object($name)) {
            foreach($name as $k=>$v) {
                $this->_param[$k]=$v;
            }
        }else{
            if(is_array($name)) {
                $this->_param = array_merge($this->_param,$name);
            }else $this->_param[$name] = $value;
        }
    }
    /**
     * 载入模版内容到解析数组
     * @return array
     */
    private function _renderParse() {
        $_parse = array();
        foreach($this->_parse as $k=>$v) {
            $_parse[$k] = $this->_render($v);
        }
        return $_parse;
    }
    /**
     * 渲染输出
     */
    private function _render($path) {
        $_tpl = File::getFile($this->_path.DIRECTORY_SEPARATOR.$path.'.'.self::$_config['template']['ext']);
        return $_tpl;
    }
    /**
     * 输出模版
     * @param string $path 目录 如：tbsms/user
     */
    protected  function display($path='') {
        if($this->_useComponent) {
            $this->_oComponent->after($this);
        }
        ob_start();
		ob_implicit_flush(0);
		if($path == '') {
			$path = $this->_className.DIRECTORY_SEPARATOR.$this->_actionName;
		}
		extract($this->_param,EXTR_OVERWRITE);
		$_tpl = File::getFile($this->_path.DIRECTORY_SEPARATOR.$path.'.'.self::$_config['template']['ext']);
		$_tpl = strtr($_tpl,$this->_renderParse());
		$_path = $this->_getTplPath($path, $this->_path.DIRECTORY_SEPARATOR.$path.'.'.self::$_config['template']['ext'], $_tpl);
		include $_path;
    }
    
    /**
     * 获得模版解析后的路径
     * @param string $filename
     * @param string $inFile
     * @param string $str
     * @return string
     */
    private function _getTplPath($filename,$inFile,$str,$layoutFile='') {
        $_cachePath = self::$_buildpath['data_dir'].DIRECTORY_SEPARATOR.self::$_buildpath['data_sub']['template_dir'];
        $_newfile = md5($filename).TF_EXT;
        $_outFile = APP_PATH.DIRECTORY_SEPARATOR.$_cachePath.DIRECTORY_SEPARATOR.$_newfile;
        if(is_file($_outFile)) {
            if(filemtime($_outFile)<filemtime($inFile) || filemtime($_outFile)<filemtime($layoutFile)) {
                unlink($_outFile);
                File::setFile($_outFile, $str);
            }
            File::setFile($_outFile, $str);
        }else{
            File::setFile($_outFile, $str);
        }
        return $_outFile;
    }
}

?>