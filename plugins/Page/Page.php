<?php
/**
 * 分页扩展类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.plugin.Page
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.0
 * $Id: Page.php 203 2016-07-04 05:44:05Z charles_li $
 */
class Page {
	private $_totalPage;			//总页数
	private $_stateBeginEnd;		//首页末页状态（0：不显示；1：用图标显示；2：用文字显示）
	private $_statePreviousNext;	//上页下页状态（1：用图标显示；2：用文字显示）
	private $_stateCenter;			//中间数字状态（0：不显示；1：显示）
	private $_stateForm;			//控件显示状态（0：不显示：1：下拉式显示；2：提交式显示）
	private $_pageNum;				//每页显示条数
	private $_pageSum;				//总页数
	private $_infoSum;				//当前页信息数
	private $_noncePage;			//当前页面
	private $_urlParam;				//连接数组
	private $_url;					//连接地址
	private $_lastPage;				//最后一页页号
	private $_limit;				//上限
	private $_nopagenumUrl;			//无每页显示数量的连接地址
	private $_sumUrl;				//信息总数地址显示
	private $_lang;					//语言包
	private $_fileName;				//链接文件名
	private $_noview = 0;			//是否不显示
	
	function __construct() {
		$this->Init(2,2,2,2,20);
	}
	/**
	 * 设置每页显示条数
	 *
	 * @param int $num
	 */
	public function setPagesize($num) {
		$this->Init(2,2,2,2,$num);
	}
	/**
	 * 初始化控件
	 * @param int $sBE 首末页显示方式
	 * @param int $sPN 上下页显示方式
	 * @param int $sC 中间数字显示方式
	 * @param int $sF 控件显示方式
	 * @param int $pN 每页显示条数
	 */
	public function Init($sBE=2,$sPN=2,$sC=1,$sF=2,$pN=20){
		if($sPN!=1 && $sPN!=2) $sPN = 2;
		if($sBE!=0){
			$this->_stateBeginEnd=$sBE;
			$this->_statePreviousNext=$sBE;
		}else{
			$this->_stateBeginEnd=0;
			$this->_statePreviousNext=$sPN;
		}
		$this->_stateCenter=$sC;
		$this->_stateForm=$sF;
		$this->_pageNum=$pN;
	}
	/**
	 * 显示属性值数组
	 *
	 * @return array
	 */
	public function showAttribute() {
		$attribute = array( 
			'sumpage' 			=> $this->_totalPage,
			'stateBeginEnd' 	=> $this->_stateBeginEnd,
			'statePreviousNext' => $this->_statePreviousNext,
			'stateCenter'		=> $this->_stateCentert,
			'stateForm'			=> $this->_stateForm,
			'pageNum'			=> $this->_pageNum,
			'pageSum'			=> $this->_pageSum,
			'infoSum'			=> $this->_infoSum,
			'noncePage'			=> $this->_noncePage,
			'urlParam'			=> $this->_urlParam,
			'url'				=> $this->_url,
			'lastPage'			=> $this->_lastPage,
			'limit'				=> $this->_limit,
			'nopagenumUrl'		=> $this->_nopagenumUrl,
			'sumUrl'			=> $this->_sumUrl		
		);
		return $attribute;		
	}
	/**
	 * 返回页数条
	 * @return string
	 */
	public function show(){
		if(Validator::StringIsNull($this->_noncePage) || Validator::StringIsNull($this->_pageSum)){
			return ;
		}else{
			if($this->_statePreviousNext==1) {
				$pagelist = "<div class=\"sabrosus\">";
			}else{
				$pagelist="<span id=\"pagelist\"><ul>\n";
			}
			$pagelist .= $this->_setBeginEnd();
			$pagelist .= $this->_setPreviousNext(false);
			$pagelist .= $this->_setCenterPage();
			$pagelist .= $this->_setPreviousNext();
			$pagelist .= $this->_setBeginEnd(false);
			$pagelist .= $this->_setFormPage();
			
			if($this->_statePreviousNext==1) {
				$pagelist .= "</div>";
			}else{
				$pagelist .= '</ul></span>';
			}
			echo $pagelist;
		}
	}
	/**
	 * 处理控件跳转
	 * @return string
	 */
	private function _setFormPage(){
		if($this->_stateForm==0){
			return ;
		}elseif($this->_stateForm==1){
			$url=$this->_fileName.$this->_url."&page=";
			$strform="<li><select name=\"page\" onchange=\"javascript:location.href='".$url."'+this.options[selectedIndex].value\">";
			for($i=1;$i<=$this->_pageSum;$i++){
				$strform.="<option value=\"".$i."\" ";
				if($i==$this->_noncePage){
					$strform.="selected";
				}
				$strform.=" >".$i." / ".$this->_pageSum."</option>";
			}
			$strform.="</select></li>";
			return $strform;
		}elseif($this->_stateForm==2){
			$url=$this->_fileName.$this->_url."&page=";
			return "<li><input class=\"textcontrol\" type=\"text\" name=\"page\" id=\"page\" value=\"".$this->_noncePage."\" size=\"4\" /><input class=\"buttonControl\" type=\"button\" onclick=\"javascript:location.href='".$url."'+document.getElementById('page').value\" name=\"goBT\" value=\"Go\" /></li>\n";
		}
	}
	/**
	 * 处理中间数字页面
	 * @return string
	 */
	private function _setCenterPage(){
		if($this->_stateCenter==0){
			return ;
		}elseif($this->_stateCenter==2){
			if(intval($this->_noncePage)<6){
				$page1=1;
			}else{
				$page1=$this->_noncePage-5;
			}
			if($this->_noncePage+5>$this->_pageSum){
				$page2=$this->_pageSum;
			}else{
				$page2=$this->_noncePage+5;
			}
			$cPage="";
			for($i=$page1;$i<=$page2;$i++){
				$cPage.="<li>";
				$url=$this->_fileName.$this->_url."&page=".$i;
				if($this->_noview) {
					$cPage.="<a href=\"javascript:void(0);\" class=\"";
				}else
					$cPage.="<a onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\" class=\"";
				if($i==$this->_noncePage){
					$cPage.="choice";
				}else $cPage.="general";
				$cPage.="\">".$i."</a></li>\n";
			}
			return $cPage;
		}elseif($this->_stateCenter==1){
			if(intval($this->_noncePage)<6){
				$page1=1;
			}else{
				$page1=$this->_noncePage-5;
			}
			if($this->_noncePage+5>$this->_pageSum){
				$page2=$this->_pageSum;
			}else{
				$page2=$this->_noncePage+5;
			}
			$cPage="";
			for($i=$page1;$i<=$page2;$i++){
				$cPage.="";
				$url=$this->_fileName.$this->_url."&page=".$i;
				if($i==$this->_noncePage){
					if($this->_noview) {
						$cPage.="<span class=\"current\"><a href=\"javascript:void(0);\">".$i."</a></span>";
					}else
						$cPage.="<span class=\"current\"><a onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">".$i."</a></span>";
				}else {
					if($this->_noview) {
						$cPage.="<a href=\"javascript:void(0);\">".$i."</a>";
					}else
					$cPage.="<a onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">".$i."</a>";
				}
			}
			return $cPage;
		}
	}
	/**
	 * 处理上下页
	 * @param boolean $isNext
	 * @return string
	 */
	private function _setPreviousNext($isNext=true){
		if($this->_statePreviousNext==1){
			if(!$isNext && $this->_noncePage==1){
				return "<a href=\"javascript:void(0);\">&lt;</a>";
			}elseif(!$isNext && $this->_noncePage!=1){
				$url=$this->_fileName.$this->_url."&page=".($this->_noncePage-1);
				if($this->_noview) {
					return "<a title=\"".$this->_lang['Previous']."\" href=\"javascript:void(0)\");\">&lt;</a>";
				}
				return "<a title=\"".$this->_lang['Previous']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">&lt;</a>";
			}elseif($isNext && $this->_noncePage==$this->_pageSum){
				return "<a href=\"javascript:void(0);\">&gt;</a>";
			}elseif($isNext && $this->_noncePage!=$this->_pageSum){
				$url=$this->_fileName.$this->_url."&page=".($this->_noncePage+1);
				if($this->_noview){
					return "<a title=\"".$this->_lang['Next']."\" href=\"javascript:void(0);\">&gt;</a>";
				}
				return "<a title=\"".$this->_lang['Next']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">&gt;</a>";
			}
		}elseif($this->_statePreviousNext==2){
			if(!$isNext && $this->_noncePage==1){
				return "<li style=\"font-size:12px;\" class=\"disablepage\">".$this->_lang['Previous']."</li>\n";
			}elseif(!$isNext && $this->_noncePage!=1){
				$url=$this->_fileName.$this->_url."&page=".($this->_noncePage-1);
				if($this->_noview) {
					return "<li><a title=\"".$this->_lang['Previous']."\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['Previous']."</a></li>\n";
				}
				return "<li><a title=\"".$this->_lang['Previous']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['Previous']."</a></li>\n";
			}elseif($isNext && $this->_noncePage==$this->_pageSum){
				return "<li style=\"font-size:12px;\" class=\"disablepage\">".$this->_lang['Next']."</li>\n";
			}elseif($isNext && $this->_noncePage!=$this->_pageSum){
				$url=$this->_fileName.$this->_url."&page=".($this->_noncePage+1);
				if($this->_noview) {
					return "<li><a title=\"".$this->_lang['Next']."\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['Next']."</a></li>\n";
				}
				return "<li><a title=\"".$this->_lang['Next']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['Next']."</a></li>\n";
			}
		}
	}
	/**
	 * 设置首页末页
	 * @param boolean $isBegin 是否是首页
	 * @return string
	 */
	private function _setBeginEnd($isBegin=true){
		if($this->_stateBeginEnd!=0){
			if($this->_stateBeginEnd==1){
				if($isBegin && $this->_noncePage==1){
					return "<a href=\"javascript:void(0);\">&lt;&lt;</a>\n";
				}elseif($isBegin && $this->_noncePage!=1){
					$url=$this->_fileName.$this->_url."&page=1";
					if($this->_noview) {
						return "<a title=\"".$this->_lang['First']."\" href=\"javascript:void(0);\">&lt;&lt;</a>";
					}
					return "<a title=\"".$this->_lang['First']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">&lt;&lt;</a>";
				}elseif(!$isBegin && $this->_noncePage==$this->_pageSum){
					return "<a href=\"javascript:void(0);\">&gt;&gt;</a>\n";
				}elseif(!$isBegin && $this->_noncePage!=$this->_pageSum){
					$url=$this->_fileName.$this->_url."&page=".$this->_pageSum;
					if($this->_noview) {
						return "<a title=\"".$this->_lang['End']."\" href=\"javascript:void(0);\">&gt;&gt;</a>";
					}
						return "<a title=\"".$this->_lang['End']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\">&gt;&gt;</a>";
				}
			}elseif($this->_stateBeginEnd==2){
				if($isBegin && $this->_noncePage==1){
					return "<li style=\"font-size:12px;\" class=\"disablepage\">".$this->_lang['First']."</li>\n";
				}elseif($isBegin && $this->_noncePage!=1){
					$url=$this->_fileName.$this->_url."&page=1";
					if($this->_noview) {
						return "<li><a title=\"".$this->_lang['First']."\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['First']."</a></li>\n";
					}
					return "<li><a title=\"".$this->_lang['First']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['First']."</a></li>\n";
				}elseif(!$isBegin && $this->_noncePage==$this->_pageSum){
					return "<li style=\"font-size:12px;\" class=\"disablepage\">".$this->_lang['End']."</li>\n";
				}elseif(!$isBegin && $this->_noncePage!=$this->_pageSum){
					$url=$this->_fileName.$this->_url."&page=".$this->_pageSum;
					if($this->_noview) {
						return "<li><a title=\"".$this->_lang['End']."\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['End']."</a></li>\n";
					}
					return "<li><a title=\"".$this->_lang['End']."\" onClick=\"javascript:location.href='".$url."';return false;\" href=\"javascript:void(0);\" style=\"font-size:12px;\" class=\"pagePN\">".$this->_lang['End']."</a></li>\n";
				}
			}
		}else{
			return ;
		}
	}
	/**
	 * 设置连接条件
	 * @param string $itemName 名称
	 * @param string $itemValue 值
	 * @param boolean $isUrlCode 是否使用URL字符串
	 */
	public function addItem($itemName,$itemValue,$isUrlCode=false){
		if(strtolower($itemName)==strtolower("Sum") || strtolower($itemName)==strtolower("pagenum") || strtolower($itemName)==strtolower("page")){
			$this->outErrMsg('Value of startDate Parameter is not Correct');
			die();
		}
		$Arr=array($itemName=>array($itemValue,$isUrlCode));
		if(Validator::ArrayIsNull($this->_urlParam)){
			$this->_urlParam=$Arr;
		}else{
			$this->_urlParam=array_merge($this->_urlParam,$Arr);
		}
		$this->_nopageArr=$this->_urlParam;
	}
	/**
	 * 当条件为真时显示错误信息
	 *
	 * @param boolean $condition	条件
	 * @param string $msg	输出信息
	 * @return boolean
	 */
	private function outErrMsg($msg) {
		echo '<span style="font-size:12px">',$msg,'<span>';
		return ;
	}
	/**
	 * 设置初始数据
	 * @param int $sum 信息数据总数
	 * @param int $page 当前页
	 */
	public function setData($sum,$page,$filename=''){
		$this->_sumUrl="Sum=".$sum;
		if($page<1){
			$this->_noncePage=1;
		}else $this->_noncePage=$page;
		if($filename!='') {
		$this->_fileName = $filename;
		}else{
			$this->_fileName = '?';
		}
		$this->_setUrl();
		$this->_setPageSum($sum);
	}
	/**
	 * 返回上限数
	 * @return int
	 */
	public function getLimit(){
		return $this->_limit;
	}
	/**
	 * 设置总页数与极限值
	 * @param int $sum
	 */
	private function _setPageSum($sum){
		if(is_int($sum/$this->_pageNum)==1){
			$this->_pageSum=floor($sum/$this->_pageNum);
			$this->_infoSum=$this->_pageNum;
		}else {
			$this->_pageSum=floor($sum/$this->_pageNum)+1;
			if($this->_noncePage==$this->_pageSum){
				$this->_infoSum=$sum%$this->_pageNum;
			}else $this->_infoSum=$this->_pageNum;
		}
		if($this->_noncePage>$this->_pageSum) $this->_noncePage=$this->_pageSum;
		$this->_limit=($this->_noncePage-1)*$this->_pageNum;
	}
	/**
	 * 返回总页数
	 * @return int
	 */
	public function getPageSum(){
		return $this->_pageSum;
	}
	/**
	 * 获得当前页信息数量
	 * @return unknown
	 */
	public function getInfoNum(){
		return $this->_infoSum;
	}
	public function noViewUrl($num1,$num2) {
		$this->_noview = 1;
	}
	/**
	 * 设置连接地址
	 */
	private function _setUrl(){
		$url='';
		$url1='';
		if(!Validator::ArrayIsNull($this->_urlParam)){
			foreach ($this->_urlParam as $k => $v){
				if($v[1]){
					$str=urlencode($v[0]);
				}else{
					$str=$v[0];
				}
				if(Validator::StringIsNull($url)){	
					$url=$k."=".$str;
				}else{
					$url.="&";
					$url.=$k."=".$str;
				}
			}

			$url1=$url;
			$url.="&";
			$url.=$this->_sumUrl;
		}else $url=$this->_sumUrl;
		$this->_url=$url."&pagenum=".$this->_pageNum;
		$this->_nopagenumUrl=$url1;
		
	}
	/**
	 * 返回页面地址
	 * @return string
	 */
	public function getUrl(){
		if(Validator::StringIsNull($this->_url)){
			return ;
		}else return $this->_fileName.$this->_url."&page=".$this->_noncePage;
	}
	/**
	 * 返回不带页与信息总数的页面地址
	 * @return string
	 */
	public function getNopagenumUrl(){
		if(Validator::StringIsNull($this->_nopagenumUrl)){
			return $this->_fileName."pagenum=";
		}else return $this->_fileName.$this->_nopagenumUrl."&pagenum=";
	}
	/**
	 * 析构函数
	 */
	function __destruct(){
		unset($this->_stateBeginEnd);
		unset($this->_statePreviousNext);
		unset($this->_stateCenter);
		unset($this->_stateForm);
		unset($this->_pageNum);
		unset($this->_pageSum);
	}
}
?>