<?php
/**
 * 图片缩略图类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.plugins.Thumb
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.5
 * @version v.1.5.1
 * $Id: Thumb.php 210 2017-02-23 01:48:57Z charles_li $
 */
class Thumb
{
    /**
     * 图片扩展名
     * @var array
     */
    private $_ext = array('image/gif'  => 'gif','image/jpeg' => 'jpg',
        'image/jpeg'=> 'jpeg','image/png'=>'png');
    private $_err;
    
    public function __construct(){}
    /**
     * 切割图片
     * @param string $srcName 原图地址 /data/tmp/TF-xxxx.jpg
     * @param string $targetName
     * @param int $x1 新图开始坐标x
     * @param int $y1 新图开始坐标y
     * @param int $x2 新图结束坐标x
     * @param int $y2 新图开始坐标y
     * @return boolean
     */
    public function cut($srcName,$targetName,$x1,$y1,$x2,$y2) {
        $_snArr = explode('.',$srcName);
        $_ext = array_pop($_snArr); //扩展名
        list($_sWidth,$_sHeight) = getimagesize($srcName);
        switch($_ext){
        	case 'gif'://gif
        	    $imagecreatefromfunc = 'imagecreatefromgif';
        	    $imagefunc = 'imagegif';
        	    break;
        	case 'jpg'://jpg
        	    $imagecreatefromfunc = 'imagecreatefromjpeg';
        	    $imagefunc = 'imagejpeg';
        	    break;
        	case 'jpeg'://jpg
        	    $imagecreatefromfunc = 'imagecreatefromjpeg';
        	    $imagefunc = 'imagejpeg';
        	    break;
        	case 'png'://png
        	    $imagecreatefromfunc = 'imagecreatefrompng';
        	    $imagefunc = 'imagepng';
        	    break;
        	default:
        	    break;
        }
        if(function_exists($imagecreatefromfunc)){
            $src_im=$imagecreatefromfunc($srcName);
        
        } else {
            $this->_err="not exist {$imagecreatefromfunc}";
            return false;
        }
        $_nWidth = $x2-$x1;
        $_nHeight = $y2 - $y1;
        $dst_im = imagecreatetruecolor($_nWidth,$_nHeight);
        
        imagecopyresampled($dst_im, $src_im, $x1, $y1, 0, 0, $_nWidth, $_nHeight, $_sWidth, $_sHeight);
        if(function_exists($imagefunc)){
            if($imagefunc=='imagepng') {
                $result=$imagefunc($dst_im, $targetName, 9);
            }else{
                $result=$imagefunc($dst_im, $targetName, 100);
            }
            clearstatcache();
            return true;
        } else {
            $this->_err="not exist {$imagefunc}";
            return false;
        }
    }
    /**
     * 执行图片缩略
     * @param string $srcName 原图地址 /data/tmp/TF-xxxx.jpg
     * @param string $targetName
     * @param number $width
     * @param number $height
     * @return boolean
     */
    public function exec($srcName,$targetName,$width=0,$height=0) {
        $_snArr = explode('.',$srcName);
        $_ext = array_pop($_snArr); //扩展名
        list($_sWidth,$_sHeight) = getimagesize($srcName);
        if($width==0 && $height==0) {
            copy($srcName, $targetName);
            return true;
        }elseif($width==0 && $height!=0) {
            if($height<$_sHeight) {
                $_nHeight = $height;
                $_nWidth = $height*$_sWidth/$_sHeight;
            }else{
                copy($srcName, $targetName);
                return true;
            }
        }elseif ($width!=0 && $height==0) {
            if($width < $_sWidth) {
                $_nWidth = $width;
                $_nHeight = $width*$_sHeight/$_sWidth;
            }else{
                copy($srcName, $targetName);
                return true;
            }
        }else{
            if($_sHeight/$_sWidth < $height/$width) {
                if($width < $_sWidth) {
                    $_nWidth = $width;
                    $_nHeight = $width*$_sHeight/$_sWidth;
                }else{
                    copy($srcName, $targetName);
                    return true;
                }
            }else{
                if($height < $_sHeight) {
                    $_nHeight = $height;
                    $_nWidth = $height*$_sWidth/$_sHeight;
                }else{
                    copy($srcName, $targetName);
                    return true;
                }
            }
        }
        switch($_ext){
        	case 'gif'://gif
        	    $imagecreatefromfunc = 'imagecreatefromgif';
        	    $imagefunc = 'imagegif';
        	    break;
        	case 'jpg'://jpg
        	    $imagecreatefromfunc = 'imagecreatefromjpeg';
        	    $imagefunc = 'imagejpeg';
        	    break;
        	case 'jpeg'://jpg
        	    $imagecreatefromfunc = 'imagecreatefromjpeg';
        	    $imagefunc = 'imagejpeg';
        	    break;
        	case 'png'://png
        	    $imagecreatefromfunc = 'imagecreatefrompng';
        	    $imagefunc = 'imagepng';
        	    break;
        	default:
        	    break;
        }
        if(function_exists($imagecreatefromfunc)){
            $src_im=$imagecreatefromfunc($srcName);
            
        } else {
            $this->_err="not exist {$imagecreatefromfunc}";
            return false;
        }
        if(function_exists($imagefunc)){
            if($imagefunc=='imagepng') {
                imagesavealpha($src_im, true);
                $dst_im = imagecreatetruecolor($_nWidth,$_nHeight);
                imagealphablending($dst_im, false);
                imagesavealpha($dst_im, true);
                imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $_nWidth, $_nHeight, $_sWidth, $_sHeight);
                $result=$imagefunc($dst_im, $targetName, 9);
            }else{
                $dst_im = imagecreatetruecolor($_nWidth,$_nHeight);
                imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $_nWidth, $_nHeight, $_sWidth, $_sHeight);
                $result=$imagefunc($dst_im, $targetName, 100);
            }
            clearstatcache();
            return true;
        } else {
            $this->_err="not exist {$imagefunc}";
            return false;
        }
    }
    
    /**
     * 获取错误信息
     * @return string
     */
    public function getErr() {
        return $this->_err;
    }
}

?>