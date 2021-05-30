<?php
/**
 * 文件存储session
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.session.storage
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0.0
 * @version v1.5.0
 * $Id: fileSession.php 197 2016-05-31 08:30:14Z charles_li $
 */
class fileSession extends AbstractSession{
	public function __construct() {
		ini_set('session.save_handler', 'files');
		$_save_path = self::$_config['session']['path'];
		if($_save_path!='') {
		    $_save_path = APP_PATH.DIRECTORY_SEPARATOR.self::$_buildpath['data_dir'].DIRECTORY_SEPARATOR.$_save_path;
		    session_save_path($_save_path);
		}
		ini_set('session.gc_probability',self::$_config['session']['probability']*100);
		ini_set('session.gc_divisor',10000);
	}
}

?>