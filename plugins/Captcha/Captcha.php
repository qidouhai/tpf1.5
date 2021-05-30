<?php
/**
 * 验证码扩展类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.plugins.Captcha
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v.1.0.0
 * $Id: Captcha.php 172 2016-05-12 01:15:57Z charles_li $
 */
class Captcha {
	private $_verfiy = 'lizy';
	public function set($sessionName='Verify',$fontNum=4) {
		$this->_verfiy=$this->_getRnd($fontNum);
		Session::add($sessionName, $this->_verfiy);
	}
	/**
	 * 输出验证码
	 */
	public function out() {
		ob_start();
		$Tmax_X = 98;
		$Tmax_Y = 28;
		$sim = imagecreate($Tmax_X,$Tmax_Y);
		$Tbcolor = imagecolorallocate($sim,255,255,255);		
		//填充数据字符
		$Tfcolor = imagecolorallocate($sim,25,45,203);
		for ($i = 0; $i < strlen($this->_verfiy); $i++){
			if($i==0)
				imagettftext($sim, 17, $this->_getXY(-26,26), 3+20*$i+$this->_getXY(3,9), $Tmax_Y/2+$this->_getXY(4,11), $Tfcolor, TF_PATH.'fonts'.DIRECTORY_SEPARATOR.'lzy.ttf', substr($this->_verfiy,$i,1));
			else
				imagettftext($sim, 17, $this->_getXY(-24,24), 21*$i+$this->_getXY(3,9), $Tmax_Y/2+$this->_getXY(5,12), $Tfcolor, TF_PATH.'fonts'.DIRECTORY_SEPARATOR.'lzy.ttf', substr($this->_verfiy,$i,1));
		}
		$im = imagecreate($Tmax_X,$Tmax_Y);
		imagefill($im, 0, 0, imagecolorallocate($im,255,255,255) );
		$y = mt_rand(-9,10)*0.1;
		ob_clean();
		header("Content-type: image/gif");
		imagegif($sim);
		imagedestroy($im);
		imagedestroy($sim);
	}
	
	/**
	 * 获得颜色
	 * @return int
	 */
	private function _getColor(){
		mt_srand((double)microtime() * 1000000);
		return mt_rand(0,255);
	}
	/**
	 * 获得随机坐标点
	 * @param int $x
	 * @param int $y
	 * @return number
	 */
	private function _getXY($x=-4,$y=4){
		mt_srand((double)microtime() * 1000000);
		return mt_rand($x,$y);
	}
	private function _imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		if ($thick == 1) {
			return imageline($image, $x1, $y1, $x2, $y2, $color);
		}
		$t = $thick / 2 - 0.5;
		if ($x1 == $x2 || $y1 == $y2) {
			return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
		}
		$k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
		$a = $t / sqrt(1 + pow($k, 2));
		$points = array(
				round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
				round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
				round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
				round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
		);
		imagefilledpolygon($image, $points, 4, $color);
		return imagepolygon($image, $points, 4, $color);
	}
	
	
	/**
	 * 获取随机数
	 * @param int $length
	 * @return string
	 */
	private function _getRnd($length) {
		$hash = '';
		$chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
		$max = strlen($chars) - 1;
		mt_srand((double)microtime() * 1000000);
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}
}

?>