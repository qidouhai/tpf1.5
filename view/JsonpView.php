<?php
/**
 * jsonp视图类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.1
 * $Id$
 */
class JsonpView extends Tbs
{
    /**
     * 输出jsonp返回串，jsonp格式不输出缓冲区，故在控制器中echo无法直接输出，使用Debug设置变量
     * @param string $callback 返回函数名
     */
    public function display($param,$callback) {
        if(self::$_config['environment']=='dev') {
            if(isset($_GET[self::$_config['debugtrace']]) && $_GET[self::$_config['debugtrace']]==1) {
                $_param = array();
                $_param['title'] = self::$_lang['JSONP_VIEW_TITLE'];
                $_param['charset'] = self::$_config['charset'];
                $_param['callback'] = $callback;
                $_param['json'] = json_encode($param);
                Loader::loadTpl('jsonp',$_param);
            }else{
                Debug::setNoout();
                header('Content-type:text/plain');
                echo $callback,'(',json_encode($param),')';
            }
        }else{
            header('Content-type:application/x-javascript');
            echo $callback,'(',json_encode($param),')';
        }
        die;
    }
}

