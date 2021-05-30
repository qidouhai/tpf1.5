<?php
/**
 * HTML视图类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.0
 * $Id$
 */
class HtmlView extends Tbs
{
    private $_param;
    private $_path;
    private $_parse;
    /**
     * 输出模版
     * @param string $path 目录 如：tbsms/user
     */
    public function display($param,$path,$layout,$rootpath,$hparam,$parse,$useLayout) {
        $this->_param = $param;
        $this->_path = $rootpath;
        $this->_parse = $parse;
        if($path == '') {
            $_controller = strtr(Router::getController(),array('Controller'=>''));
            $_action = strtr(Router::getAction(),array('Action'=>''));
            $path = $_controller.'/'.$_action;
            if(!Validator::StringIsNull((Router::getModule()))) {
                $_controllerArr = explode('_',$_controller);
                $path = Router::getModule().'/'.$_controllerArr[1].'/'.$_action;
            }
        }
        $this->_param = array_merge_recursive($this->_param,$hparam);
        extract($this->_param,EXTR_OVERWRITE);
        if($useLayout && $layout!='') {
            $_tplAttr = $this->_getTplAttr($layout.'_'.$path, $this->_path.'/'.$path.'.'.self::$_config['template']['ext'],$this->_path.'/'.self::$_buildpath['view_sub']['layout_dir'].'/'.$layout.'.'.self::$_config['template']['ext']);
            if($_tplAttr['isExist']) {
                $_path = $_tplAttr['outFile'];
            }else{
                $_tpl = File::getFile($this->_path.'/'.self::$_buildpath['view_sub']['layout_dir'].'/'.$layout.'.'.self::$_config['template']['ext']);
                $_tpl = strtr($_tpl,array('<{'.self::$_config['layoutcontext'].'}>'=>$this->_render($path)));
                $_tpl = strtr($_tpl,$this->_renderParse());
                $_path = $this->_writeTpl($_tplAttr['outFile'], $_tpl);
            }
        }else{
            $_tplAttr = $this->_getTplAttr($path, $this->_path.'/'.$path.'.'.self::$_config['template']['ext']);
            if($_tplAttr['isExist']) {
                $_path = $_tplAttr['outFile'];
            }else{
                $_tpl = File::getFile($this->_path.'/'.$path.'.'.self::$_config['template']['ext']);
                $_tpl = strtr($_tpl,$this->_renderParse());
                $_path = $this->_writeTpl($_tplAttr['outFile'], $_tpl);
            }
        }
        include $_path;
        if(self::$_config['environment']=='dev') @unlink($_path);
        die();
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
        $_tpl = File::getFile($this->_path.'/'.$path.'.'.self::$_config['template']['ext']);
        return $_tpl;
    }
    /**
     * 检测模板缓存是否有效
     * @param string $filename 文件名
     * @param string $inFile 视图文件
     * @param string $layoutFile 布局文件
     * @return array
     */
    private function _getTplAttr($filename,$inFile,$layoutFile='') {
        self::$_global['viewpath'] = $inFile;
        $_cachePath = self::$_buildpath['data_dir'].'/'.self::$_buildpath['data_sub']['template_dir'];
        $_newfile = md5($filename).TF_EXT;
        $_outFile = APP_PATH.'/'.$_cachePath.'/'.$_newfile;
        $_isExist = false;
        if(is_file($_outFile)) {
            if(filemtime($_outFile)<filemtime($inFile) || filemtime($_outFile)<filemtime($layoutFile)) {
                unlink($_outFile);
            }else{
                $_isExist = true;
            }
        }
        return array('isExist'=>$_isExist,'outFile'=>$_outFile);
    }
    /**
     * 数据写入模板缓存
     * @param string $outFile
     * @param string $str
     */
    private function _writeTpl($outFile,$str) {
        File::setFile($outFile, $str);
        return $outFile;
    }
}

