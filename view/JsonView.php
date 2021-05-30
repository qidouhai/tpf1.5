<?php
/**
 * json视图类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.1
 * $Id$
 */
class JsonView extends Tbs
{
    /**
     * 输出json串,JSON格式不输出缓冲区，故在控制器中echo无法直接输出，使用Debug设置变量
     * @param string $null 用于统一的空形参
     */
    public function display($param,$str) {
        if(self::$_config['environment']=='dev') {
            if(isset($_GET[self::$_config['debugtrace']]) && $_GET[self::$_config['debugtrace']]==1) {
                $_param = array();
                $_param['title'] = self::$_lang['JSON_VIEW_TITLE'];
                $_param['charset'] = self::$_config['charset'];
                $_param['json'] = json_encode($param);
                Loader::loadTpl('json',$_param);
            }else{
                Debug::setNoout();
                header('Content-type:application/json');
                echo json_encode($param);
            }
        }else{
            header('Content-type:application/json');
            echo json_encode($param);
        }
        die;
    }
}

