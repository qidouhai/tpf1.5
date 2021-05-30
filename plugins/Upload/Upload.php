<?php
/**
 * 文件上传类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.plugins.Upload
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v.1.5.2
 * $Id: Upload.php 216 2017-08-04 06:24:51Z charles_li $
 */
class Upload
{
    private $_filesize = 2097152;	//文件大小 2*1024*1024 2M
    private $_path = 'data/upfile';		//文件路径
    private $_ext_arr = array(
    	'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
    	'flash' => array('swf', 'flv'),
    	'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
    	'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );  //允许上传的文件扩展名
    private $_file;		//上传控件数组
    private $_err;		//错误信息
    private $_filename='';     //设置的文件名
    private $_type = 'image';   //检测上传文件的扩展名是否符合，如果此属性为空则表示不检测扩展名
    private $_ext = 'jpg';      //文件扩展名
    /**
     * 上传文件类
     * @param string $path 保存文件的路径如 '../data/upfile'
     * @param string $filename 上传后文件的文件名，不含扩展名
     * @param string $type 检测类型，image：图片/flash：flash文件/media：媒体/file：普通文件/空则不检测扩展名
     * @param 
     */
    public function __construct() {

    }
    /**
     * 设置保存路径
     * @param string $path 保存文件的路径如 '../data/upfile'
     */
    public function setPath($path) {
        $this->_path = $path;
    }
    /**
     * 设置允许上传的扩展名
     * @param string $type
     * @param array $allowext
     */
    public function setAllowExt($type='image',$allowext=array()) {
        $this->_ext_arr[$type] = $allowext;
    }
    /**
     * 设置上传文件名，在没调用exec之前生效
     * @param string $filename 上传后文件的文件名，不含扩展名
     */
    public function setName($filename='') {
        $this->_filename = $filename;
    }
    /**
     * 设置文件类型检测，在没调用exec前生效
     * @param string $type 检测类型，image：图片/flash：flash文件/media：媒体/file：普通文件/空则不检测扩展名
     */
    public function setType($type='image') {
        $this->_type = $type;
    }
    /**
     * 设置允许上传的文件大小，注意这里设置大的话还受php.ini中upload_max_filesize限制
     * @param int $size
     */
    public function setSize($size='2097152') {
        $this->_filesize = (int) $size;
    }
    /**
     * 上传文件
     * @param string $formname 表单上传控件名
     * @param int $backtype 0只返回新文件名，1写入新文件名返回新旧文件名，2写入新旧文件名
     * @return boolean/string/array  $backtype为真时返回数组
     */
    public function exec($formName,$backtype=0) {
        if( !empty($_FILES[$formName]) && is_array($_FILES[$formName]) ){
            $this->_file = $_FILES[$formName];
        } else {
            $this->_err = 'File is not exist';
            return false;
        }
        if (@is_dir($this->_path) === false) {
            $this->_err = 'directory('.$this->_path.') does not exist';
            return false;
        }
        //检查目录写权限
        if (@is_writable($this->_path) === false) {
            $this->_err = 'Upload directory does not have write permissions';
            return false;
        }
        $_ufilename = $_FILES[$formName]['name'];
        $_utmpname = $_FILES[$formName]['tmp_name'];
        $_ufilesize = $_FILES[$formName]['size'];
        $_utmpname = str_replace('\\\\', '\\', $_utmpname);
        //检查是否已上传
        if (@is_uploaded_file($_utmpname) === false) {
            $this->_err = 'Upload failed'.$_utmpname;
            return false;
        }
        //检查文件大小
        if ($_ufilesize > $this->_filesize) {
            $this->_err = 'Upload file size over limit';
            return false;
        }
        
        if(!empty($_FILES[$formName]['error'])) {
            $this->_err = $this->_checkUpfile($_FILES[$formName]['error']);
            return false;
        }
        if($this->_checkContent($_utmpname)){
            $this->_err = 'File contains illegal characters';
            return false;
        }
        $_newfilename = $this->_getName();
        $_ext = $this->_checkExt($_ufilename);
        if($_ext == '') {
            $this->_err = 'Extension is Error';
            return false;
        }
        if($backtype==2) {
            $_filename = $_ufilename;
        }else{
            $_filename = $_newfilename.'.'.$_ext;
        }
        if(move_uploaded_file($_utmpname,$this->_path.'/'.$_filename)){
            @chmod($this->_path, 0777);
            if($backtype==1) {
                return array('filename'=>$_filename,'name'=>$_ufilename);
            }else
            return $_filename;
        }else return false;
    }
    /**
     * 检测内容是否含有程序标示符
     * @param string $file
     * @return boolean
     */
    private function _checkContent($file) {
        $_filecontent = @file_get_contents($file);
        preg_match('/((<\?)|(<\%))/iu', $_filecontent,$_arr);
        if(count($_arr)==0) {
            return false;
        }else{
            return true;
        }
    }
    /**
     * 上传表单错误返回错误信息
     * @param int $error
     * @return string
     */
    private function _checkUpfile($error) {
        switch($error){
        	case '1':
        	    $_errmsg = 'Size over php.ini allowed';
        	    break;
        	case '2':
        	    $_errmsg = 'More than the size of the form';
        	    break;
        	case '3':
        	    $_errmsg = 'Image is only partially Uploaded';
        	    break;
        	case '4':
        	    $_errmsg = 'Please select a file';
        	    break;
        	case '6':
        	    $_errmsg = 'Temporary directory not found';
        	    break;
        	case '7':
        	    $_errmsg = 'Error writing file to disk';
        	    break;
        	case '8':
        	    $_errmsg = 'File upload stopped by extension';
        	    break;
        	case '999':
        	default:
        	    $_errmsg = 'unknown error';
        	    break;
        }
        return $_errmsg;
    }
    /**
     * 获得文件名，不含扩展名
     */
    private function _getName() {
        if($this->_filename!='') {
            return $this->_filename;
        }else{
            return 'TF'.date("YmdHis") . '-' . rand(10000, 99999);
        }
    }
    
    /**
     * 检测扩展名，返回扩展名
     * @return string
     */
    private function _checkExt($filename) {
        $_arr = explode(".", $filename);
        $_ext = array_pop($_arr);
        $_ext = strtolower(trim($_ext));
        if($this->_type!='') {
            if(in_array($_ext,$this->_ext_arr[$this->_type])) {
                return $_ext;
            }else{
                return '';
            }
        }else{
            return $_ext;
        }
    }
    /**
     * 获取错误信息
     * @return string
     */
    public function getErr() {
        return $this->_err;
    }
}

?>