<?php
/**
 * 控制器和组件抽象类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.controller
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.5.3
 * $Id: AbstractController.php 265 2017-11-16 02:37:53Z charles_li $
 */
abstract class AbstractController extends Tbs {
	/**
	 * request对象
	 * @var Request
	 */
	protected $request;
	/**
	 * response对象
	 * @var Response
	 */
	protected $response;
	/**
	 * 路由器实例
	 * @var Router
	 */
	protected $router;
	/**
	 * 控制器或者组件中调用的模型数组
	 * @var array
	 */
	protected $models = array();
	
	/**
	 * 视图对象
	 * @var View
	 */
	private $_view;
	private $_model = array();					//模型对象数组
	/**
	 * 是否全局使用session
	 * @var boolean
	 */
	protected $useSession = FALSE;
	/**
	 * session对象
	 * @var Session
	 */
	protected $session = null;
	protected $sessionCache = 'nocache';	//nocache/private/private_no_cache/public
	/**
	 * 使用session对象
	 */
	protected function useSession() {
		$this->session = self::instance('Session');
		$this->session->startSession($this->sessionCache);
	}
	protected function __construct() {
		$this->request =  self::instance('Request');
		$this->response =  self::instance('Response');
		$this->router =  self::instance('Router');
	}

	/**
	 * 获取模型实例
	 * @param string $modelName
	 * @throws ControllerException
	 * @return Model
	 */
	private function _instanceModel($modelName) {
		$_oModel= self::instance($modelName.'Model');
		if($_oModel instanceof Model) {
			return $_oModel;
		}else {
			unset($_oModel);
			throw new ControllerException(Loader::getErrMsg('MODEL_IMPLEMENT_INTERFACE',array($modelName)),2);
		}
	}
	
	/**
	 * 获取model对象
	 * @param string $name
	 * @return Model
	 */
	public function Model($name='') {
        if($name=='') $name = $this->models[0];
	    return $this->_getModel($name);
	}
	/**
	 * 获取单个模型对象
	 * @param string $name
	 * @throws LoaderException
	 * @return object
	 */
	private function _getModel($name) {
	    if(!isset($this->_model[$name])) {
	        if(!in_array($name, $this->models)) {
	            throw new ControllerException(Loader::getErrMsg('MODEL_ISNOT_FOUND',array($name)),2);
	        }
	        $this->_model[$name] = $this->_instanceModel($name);
	    }
	    return $this->_model[$name];
	}
}
class ControllerException extends TException {
	
}

?>