<?php
/**
 * 过滤器基类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v1.5.0
 * $Id$
 */
abstract class Filter extends AbstractController
{
    /**
     * 下一个过滤器的名称，没有则为空
     * @var string
     */
    protected $chainFilter = '';
    /**
     * 已经载入的过的过滤器避免循环载入
     * @var array
     */
    private static $_hasFilter = array();
    public function __construct() {
        parent::__construct();
        self::$_hasFilter[] = get_class($this);
        if($this->useSession) $this->useSession();
        $this->_initModel();
    }
    /**
     * 执行过滤方法
     */
    abstract public function doFilter();
    final public function chainFilter() {
        $_filter = $this->chainFilter.'Filter';
        if($this->chainFilter!='' && !in_array($_filter,self::$_hasFilter)) {
            $_oFilter = self::instance($_filter);
            if(!is_subclass_of($_oFilter, 'Filter')) {
                throw new LoaderException(Loader::getErrMsg("FILTER_ISNOT_INTERFACE",array($_filter)),1);
            }
            $_oFilter->doFilter();
            $_oFilter->chainFilter();
        }
    }
}

