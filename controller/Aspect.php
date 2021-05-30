<?php

abstract class Aspect extends AbstractController
{
    private $proxyObj = null;
    public function __construct() {
        parent::__construct();
        if($this->useSession) $this->useSession();
        $this->_initModel();
    }
    /**
     * 在切入点之前执行
     */
    abstract public  function _before();
    /**
     * 在切入点之后执行
     */
    abstract public  function _after();
    
    public function __call($name,$param=array()) {
        
    }
    
    public function setProxy($obj) {
        $this->proxyObj = $obj;
    }
    
    public function getProxy() {
        $this->proxyObj;
    }
}

