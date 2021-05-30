<?php
/**
 * text视图类,输出参数
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.view
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.1
 * $Id$
 */
class TextView extends Tbs
{
    /**
     * 输出一个文本信息
     * @param string $key
     * @return void
     */
    public function display($param,$key) {
        if($key == '') {
            print_r($param);
        }else{
            echo $param[$key];
        }
        die;
    }
}

