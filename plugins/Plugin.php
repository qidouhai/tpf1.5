<?php
/**
 * 扩展插件工厂
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.misc
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.3.1
 * $Id: Plugin.php 169 2016-05-11 07:58:56Z charles_li $
 */
class Plugin extends Tbs {
    /**
     * 初始化插件
     * @param string $pluginName
     */
	public static function init($pluginName) {
	    return self::_getPlugin($pluginName);
	}
	/**
	 * 获取扩展插件
	 */
	private static function _getPlugin($pluginName) {
		$_id = $pluginName.'Plugin';
		if (!isset(self::$_inst[$_id])){
			if(!file_exists(TF_PATH.'plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.$pluginName.TF_EXT)) {
				throw new LoaderException(Loader::getErrMsg('PLUGIN_ISNOT_EXIST',array($pluginName)),2);
			}
			include TF_PATH.'plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.$pluginName.TF_EXT;
			self::$_inst[$_id] = new $pluginName();
		}
		return self::$_inst[$_id];
	}
}

?>