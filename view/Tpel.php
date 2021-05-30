<?php
/**
 * 模板语言
 * 
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.2
 * $Id: Tpel.php 159 2016-04-25 06:41:00Z licaohai $
 * 
 * 
 * 设置变量 		#set($name = "lizy")
 * 				#set($age = 20)
 * 				#set($user.name = "lizy")
 * 				#set($user.city = ["zj","jx",$tx])
 * 				#set($user.groupname = $group.getname($id))
 * 
 * 输出变量 		${name}，$name 
 * 
 * 输出常量		{NAME}
 * 
 * 注释			单行：##    多行：#*  *#
 * 
 * 循环 			#foreach($value in ["a","b","c"])
 * 					${tplCount}${value}
 * 				#end
 * 				#set($list = ["a","b","c","d"])
 * 				#foreach($element in $list)
 * 					$tplCount
 * 					This is $element
 * 				#end
 * 				输出
 * 				0This is a1This is b2This is c3This is d
 * 				#foreach($v in $user.getList)
 * 					$v
 * 				#end
 * 
 * 判断			#if(condition)
 * 				#elseif(condition)
 * 				#else
 * 				#end
 * 				#if($user = "lizy")
 * 					This is administrator
 * 				#elseif($user = "charles_li")
 * 					This is lizy's father
 * 				#end
 * 
 * 嵌套循环		#foreach($list in $user.getList)
 * 					#foreach($v in $list)
 * 						$tplCount $v
 * 					#end
 * 				#end
 * 
 * 逻辑操作符 	&& || !
 * 				#if($user != "lizy")
 * 				#end
 * 				#if($user = "charles_li" && $isAdmin)
 * 				#end
 * 
 * 定义函数		#function(函数名,参数1,参数2...参数n)
 * 				#end
 * 
 * 调用函数		#$user = 函数名(参数1,参数2)
 * 
 * 停止			#stop
 * 
 * 非解析包含	#include("left.php","left.html")
 * 
 * 解析包含		#parse("left.tpl")  #parse("left.html") 会解析Tpl语法
 * 
 * 转译字符		#set($user = "lizy")
 * 				$user		输出		lizy
 * 				\$user      输出   	$user
 * 				\\$user		输出		\lizy
 * 				\\\$user	输出		\$user
 * 
 * 替换php标签	#php(echo phpinfo())
 * 
 * 获得数组值	$userlist[0]  $userlist[0][username]
 *				
 */
class Tpel {
	private static
		$_buildPath,
		$_inst,
		$_path = 'tpl';
	private $_varArr = array();

	private function __construct() {
		$this->_setPathConf();
	}
	/**
	 * 初始化实例
	 * @return object
	 */
	public static function & getInstance() {
		if(null === self::$_inst) {
			self::$_inst = new self();
		}
		return self::$_inst;
	}
	/**
	 * 设置模板变量
	 * @param string $key 变量名
	 * @param string $value 变量值
	 */
	public function set($key,$value) {
		$this->_varArr[$key] = $value;
	}
	private function _setVariable(& $tpl) {
		//$tpl = preg_replace('/#set\s*\(\s*\$\{([a-zA-Z]\w*)}\s*\=\s*(array|)/', '', $tpl)
		//$tpl = preg_replace('/#set\s*\(\s*\$\{([a-zA-Z]\w*\}\s*\=\s*/', '\$$1=$2', $tpl);
	}
	/**
	 * 解析注释
	 * @param string $tpl
	 */
	private function _getNotes(& $tpl) {
		$tpl = preg_replace('/#\*(.*)\*#/is', '<?php ?>',$tpl);
		$tpl = preg_replace('/##(.*)/i', '<?php ?>', $tpl);
	}
	/**
	 * 解析数组
	 * @param string $tpl
	 */
	private function _getArray(& $tpl) {
		$tpl = preg_replace('/(\=|in)\s*(\{|\[)(.*)(\}|\])/', '$1 [$3]', $tpl);
		$tpl = preg_replace('/\[\s*(.*)\s*\]/', 'array($1)', $tpl);
		//$tpl = preg_replace('/array\((\"?[a-zA-Z]\w*\"?):(\"?[^\:.*]\"?)/', 'array($1=>$2', $tpl);
		//$tpl = preg_replace('/array\(\"?\$\{([a-zA-Z]\w*)\}\"?=>\"?\$\{([.*])\}\"?/', 'array($1=>$2', $tpl);
		
	}
	/**
	 * 解析变量
	 * @param string $tpl
	 */
	private function _getVariable(& $tpl) {
		//$_keyword = array('#if','#set','#parse','#foreach','#function','#stop','#end','#else','#include','#php');
		$tpl = preg_replace('/\$\{?([a-zA-Z]\w*)\}?/','\${$1}',$tpl);
	}
	/**
	 * 替换结束符
	 * @param string $tpl
	 */
	private function _replaceEnd(& $tpl) {
		$tpl = preg_replace('/#end/', '}', $tpl);
	}
	/**
	 * 替换停止
	 * @param string $tpl
	 */
	private function _replaceStop(& $tpl) {
		$tpl = preg_replace('/#stop/','<?php die()?>',$tpl);
	}
	/**
	 * 解析常量
	 * @param string $tpl
	 */
	private function _getConst(& $tpl) {
		$tpl = preg_replace('/[^$]{([A-Z][A-Z0-9]*)}/', '<?php echo $1?>', $tpl);
	}
	/**
	 * 解析模板
	 * @param string $filename	文件地址
	 * @return string
	 */
	private function _parse($filename) {
		$tpl = File::getFile($filename);
		self::_getNotes($tpl);
		self::_getArray($tpl);
		self::_getVariable($tpl);
		self::_getConst($tpl);
		self::_setVariable($tpl);
		self::_replaceEnd($tpl);
		self::_replaceStop($tpl);
		return $tpl;
	}
	private function _checkErr($filename) {
		$_tplArr = file($filename);
		$_chkArr = array(
				'set'=> array(
							'\$[a-zA-Z]\w*','\"\s*.*\s*\"',''
						),
				);
		foreach($_tplArr as $k=>$v) {
			if(strstr($v, '#set')) {
				echo $v;
				if(preg_match('/#set\s*\(\s*\$[a-zA-Z]\w*\s*=\s*(\"\s*.*\s*\"|\s*\$[a-zA-Z]\w*|\[\s*\"\s*.*\s*\"\s*\]|\{\s*.*\s*\}|\$[a-zA-Z]\w*)/', $v)==0) {
					trigger_error(TF::L('Tpl_Param_Err').' line:'.$k,E_USER_ERROR);
				}
			}
		}
	}
	/**
	 * 返回模板路径
	 * @param string $filename	视图文件名
	 * @param string $inFile 模板文件路径
	 * @param string $str	替换后的字符串
	 * @return string
	 */
	private function _getTplPath($filename,$inFile,$str) {
		$_cachePath = self::$_buildPath['cache_template'];
		$_newfile = md5($filename).TF_EXT;
		$_outFile = APP_PATH.DIRECTORY_SEPARATOR.$_cachePath.DIRECTORY_SEPARATOR.$_newfile;
		if(is_file($_outFile)) {
			if(filemtime($_outFile)<filemtime($inFile)) {
				unlink($_outFile);
				File::setFile($_outFile, $str);
			}
			File::setFile($_outFile, $str);
		}else{
			File::setFile($_outFile, $str);
		}
		return $_outFile;
	}
	/**
	 * 输出解析后的模板
	 * @param string $filename	视图文件
	 * @example $tpl->display('admin/left.tpl'); $tpl->display('index.tpl');
	 */
	public function display($filename) {
		extract($this->_varArr,EXTR_OVERWRITE);
		$_inFile = $_filename = $this->_getInFile($filename);
		//$this->_checkErr($_filename);
		$str = $this->_parse($_filename);
		$_outFile = $this->_getTplPath($filename,$_inFile,$str);
		include $_outFile;
	}
	/**
	 * 获得视图文件
	 * @param string $filename
	 * @return string
	 */
	private function _getInFile($filename) {
		$_viewPath = self::$_buildPath['view_dir'];
		$_inFile = APP_PATH.DIRECTORY_SEPARATOR.$_viewPath.DIRECTORY_SEPARATOR.$filename;
		if(!is_file($_inFile)) throw new TplException(TF::L('Tpl_No_viewPath'));
		return $_inFile;
	}
	/**
	 * 设置模板配置信息
	 */
	private function _setPathConf() {
		if(!isset(self::$_buildPath)) self::$_buildPath = TF::loadConf('build');
		if(Validator::StringIsNull(self::$_buildPath['cache_template'])) self::$_buildPath['cache_template'] = self::$_path;
		if(!is_dir(self::$_buildPath['cache_template'])) {
			mkdir(APP_PATH.DIRECTORY_SEPARATOR.self::$_buildPath['cache_template']);
		}
	}
}
class TplException extends TException {

}
?>