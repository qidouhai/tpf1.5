<?php
/**
 * 函数库
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.2
 * $Id: Functions.php 276 2018-04-29 02:47:59Z charles_li $
 */
/**
 * 文件加载函数
 * 
 * @param string $filename        	
 * @example import('app.lib.News.phtml');
 */
function import($filename,$ext=TF_EXT) {
	Loader::import ($filename,$ext);
}

/**
 * 获得客户端IP
 * 
 * @return string
 */
function getIP() {
	Request::getIP();
}
/**
 * 魔法函数设置
 *
 * @param string/array $sa        	
 * @return string/array
 */
function addslashes_deep($sa) {
	if (! get_magic_quotes_gpc ()) {
		if (is_array ( $sa )) {
			foreach ( $sa as $k => $v ) {
				$sa [$k] = addslashes_deep ( $v );
			}
		} else {
			$sa = addslashes ( $sa );
		}
	}
	return $sa;
}
/**
 * 重定向
 * @param string $url 跳转的url
 * @param string $msg 提示内容
 * @param int $type 提示框类型(0：页面跳转/1：js跳转/2：路由内部跳转)
 * @param int $wait 等待时间
 * @param boolean $exit 是否退出
 */
function redirect($url,$msg='',$type=0,$wait=0,$exit=1) {
	switch ($type) {
		case 0:
			if (!headers_sent()) {
				if($wait===0) {
					header('location:'.$url);
				}else {
					header("refresh:{$wait};url={$url}");
					echo $msg;
				}
			}else {
				$_str = "<meta http-equiv='Refresh' content='{$wait};URL={$url}'>";
				if($wait!=0)
					$_str .= $msg;
				echo $_str;
			}
			break;
		case 1:
			$_str = "<script type=\"text/javascript\">";
			if($msg!='') $_str .= "alert('".$msg."');";
			$_str .= "location.href='".$url."'";
			$_str .= "</script>"; 
			echo $_str;
			break;
		case 2:
			Router::Redirect($url);
			break;
	}
	if($exit==1) die;
	
}
/**
 * 获取整形表单数据
 * @param string $control	控件名称
 * @param int $flag	取值方式 0post,1get,2files,3request
 * @param boolean $trim 是否过滤前后空格
 * @param boolean $xss 是否启用xss过滤
 * @return int
 */
function getFormInt($control,$flag=0) {
    return (int) getForm($control,$flag);
}
/**
 * 获取浮点型表单数据
 * @param string $control	控件名称
 * @param int $flag	取值方式 0post,1get,2files,3request
 * @param boolean $trim 是否过滤前后空格
 * @param boolean $xss 是否启用xss过滤
 * @return float
 */
function getFormFloat($control,$flag=0) {
    return (float) getForm($control,$flag);
}
/**
 * 获取双精度表单数据
 * @param string $control	控件名称
 * @param int $flag	取值方式 0post,1get,2files,3request
 * @param boolean $trim 是否过滤前后空格
 * @param boolean $xss 是否启用xss过滤
 * @return double
 */
function getFormDouble($control,$flag=0) {
    return (double) getForm($control,$flag);
}
/**
 * 获取表单控件值
 * @param string $control	控件名称
 * @param int $flag	取值方式 0post,1get,2files,3request
 * @param boolean $trim 是否过滤前后空格
 * @param boolean $xss 是否启用xss过滤
 * @return string 控件值
 */
function getForm($control,$flag=0,$trim=true,$xss=true){
	switch ($flag){
		case 0:
			$control=isset($_POST[$control]) ? $_POST[$control] :null;
			break;
		case 1:
			$control=isset($_GET[$control]) ? $_GET[$control] :null;
			break;
		case 2:
			$control=isset($_FILES[$control]) ? $_FILES[$control] :null;
			break;
		case 3:
			$control=isset($_REQUEST[$control]) ? $_REQUEST[$control] :null;
			break;
		default:
			$control=isset($_POST[$control]) ? $_POST[$control] :null;
	}
	if($trim){
		if(is_array($control)) {
			foreach($control as $k => $v) {
				$control[$k] = trim($v);
			}
		}else $control=trim($control);
	}
	if(Config::getConfig('xss') && $xss){
		if(is_array($control)) {
			foreach($control as $k => $v) {
				$control[$k] = xss_clean($v);
			}
		}else $control=xss_clean($control);
	}
	return $control;
}
/**
 * 过滤xss内容，防止跨站攻击
 * @param string $data	待过滤内容
 * @return string 过滤后的内容
 */
function xss_clean($val)
{
	$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search.= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search.= '1234567890!@#$%^&*()';
    $search.= '~`";:?+/={}[]-_|\'\\';

    for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      // &#x0040 @ search for the hex values
      $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // &# @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('alert','javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'confirm', 'eval', 'document');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
        $pattern = '/';
        for ($j = 0; $j < strlen($ra[$i]); $j++) {
          if ($j > 0) {
            $pattern .= '(';
            $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
            $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
            $pattern .= ')?';
          }
          $pattern .= $ra[$i][$j];
        }
        $pattern .= '/i';
        $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
        $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
        if ($val_before == $val) {
          // no replacements were made, so exit the loop
          $found = false;
        }
      }
    }

    return $val;
}
/**
 * 输出404错误信息
 * 
 * @param string $message 错误提示信息        	
 */
function get404($message = '') {
	Response::status (404);
	Log::notice($message);
	Loader::load404();
	die();
}
/**
 * 视图中使用挂件
 * @param string $classname
 * @param string $action
 * @param array $param
 */
function widget($classname,$action,$param=array()) {
    Loader::loadWidget($classname, $action,$param);
}

/**
 * 序列化数组成字符串，解决浏览器传值序列化+号的问题
 * @param array $obj
 * @return string
 */
function TFserialize($obj )
{
    return base64_encode(gzcompress(serialize($obj)));
}
/**
 * 反序列化
 * @param string $txt
 * @return array
 */
function TFunserialize($txt)
{
    return unserialize(gzuncompress(base64_decode($txt)));
}

?>