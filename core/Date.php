<?php
/**
 * 日期时间类
 * @copyright	Copyright 2013 TYNT.CN
 * @author	<charles_li@msn.com>
 * @package	tpf.core
 * @link    http://www.tynt.cn/tpf
 * @license http://www.tynt.cn/tpf/license
 * @since v1.0
 * @version v1.3.1
 * $Id: Date.php 145 2015-09-21 09:24:48Z licaohai $
 */
class Date {
	const YEAR = 'Y';
	const MONTH = 'n';
	const DATE = 'Y-m-d';
	const DAY = 'j';
	const DAY_OF_WEEK = 'w';
	/**
	 * 获取当前时间戳
	 * @return int
	 */
	public static function getTime() {
		return time();
	}
	/**
	 * 获得星期数
	 * @param int $timestamp
	 * @return int
	 */
	public static function getWeek($timestamp=null) {
	    return self::_getDate(self::DAY_OF_WEEK,$timestamp)+1;
	}
	/**
	 * 获得当前月的最大天数
	 * @param int $timestamp 时间戳，不填则取当前时间戳
	 * @return int
	 */
	public static function getDayMaxByYearMonth($timestamp=null) {
	    $_timestramp = self::_getTimestamp($timestamp);
	    $_year = date(self::YEAR,$_timestramp);
	    $_month = date(self::MONTH,$_timestramp);
	    return self::_getDayMaxByYearMonth($_year, $_month);
	}
	/**
	 * 根据年月返回当月最大天数
	 * @param int $year
	 * @param int $month
	 * @return int
	 */
	private static function _getDayMaxByYearMonth($year,$month) {
	    $_max_month_day = array(31,28,31,30,31,30,31,31,30,31,30,31);
	    if(self::_isLeapYear($year)) {
	        $_max_month_day[1] = 29;
	    }
	    return $_max_month_day[($month-1) % 12];
	}
	/**
	 * 获取指定格式的日期
	 * @param string $format
	 * @param int $timestamp
	 * @return string
	 */
	private static function _getDate($format,$timestamp) {
	    $_timestramp = self::_getTimestamp($timestamp);
	    return date($format,$_timestramp);
	}
	/**
	 * 获得时间戳，当timestamp为空时返回当前时间戳
	 * @param int $timestamp
	 * @return int
	 */
	private static function _getTimestamp($timestamp) {
	    $_timestramp = $timestamp;
	    if(empty($timestamp)) $_timestramp = time();
	    return $_timestramp;
	}
	/**
	 * 获取年份
	 * @param int $timestamp 时间戳
	 * @return int
	 */
	public static function getYear($timestamp=null) {
		return self::_getDate(self::YEAR,$timestamp);
	}
	/**
	 * 获取月份
	 * @param int $timestamp 时间戳
	 * @return int
	 */
	public static function getMonth($timestamp=null) {
	    return self::_getDate(self::MONTH,$timestamp);
	}
	/**
	 * 获取日
	 * @param int $timestamp 时间戳
	 * @return int
	 */
	public static function getDay($timestamp=null) {
	    return self::_getDate(self::DAY,$timestamp);
	}
	/**
	 * 是否是闰年
	 * @param int $timestamp 时间戳
	 * @return boolean
	 */
	public static function isLeapYear($timestamp=null) {
	    $_timestramp = self::_getTimestamp($timestamp);
	    $_year = date(self::YEAR,$_timestramp);
	    return self::_isLeapYear($_year);
	}
	/**
	 * 日期相加，返回新的日期
	 * @param date $date  被加日期
	 * @param int $num 加上的数量
	 * @param enum $interval 加上数量的单位，d为天，m为月，y为年
	 * @return date
	 */
	public static function DateAdd($date,$num,$interval='d') {
	    $_sdate = strtotime($date);
	    switch($interval) {
	    	case 'd':
	    	    return date(self::DATE,($_sdate+$num*86400));
	    	    break;
	    	case 'm':
	    	    return self::_getMonthAdd($_sdate, $num);
	    	    break;
	    	case 'y':
	    	    $_year = date(self::YEAR,$_sdate)+$num;
	    	    $_month = date(self::MONTH,$_sdate);
                $_maxDay = self::_getDayMaxByYearMonth($_year, $_month);
                $_day = date(self::DAY,$_sdate);
                $_day = $_day>$_maxDay ? $_maxDay : $_day;
                return $_year.'-'.$_month.'-'.$_day;
	    	    break;
	    	default:
	    	        return date(self::DATE,($_sdate+$num*86400));
	    	        break;
	    }
	}
	/**
	 * 根据年份判断是否是闰年
	 * @param int $year 年份
	 * @return boolean
	 */
	private static function _isLeapYear($year) {
	    return ($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0;
	}
	/**
	 * 月相加
	 * @param int $datestamp
	 * @param int $num
	 * @return date
	 */
	private static function _getMonthAdd($datestamp,$num) {
	    $_nextMonth = (int) ((date(self::MONTH,$datestamp) + $num) % 12);
	    $_tmpYear = $_nextMonth ==0 ? ((date(self::MONTH,$datestamp) + $num) / 12 - 1) : (date(self::MONTH,$datestamp) + $num) / 12;
	    $_nextYear = date(self::YEAR,$datestamp) + floor($_tmpYear);
	    
	    $_nextMonth = $_nextMonth ==0 ? 12 : $_nextMonth;
	    $_nextMonthMaxDay = self::_getDayMaxByYearMonth($_nextYear, $_nextMonth);
	    $_nextMonthDay = date(self::DAY,$datestamp) > $_nextMonthMaxDay ? $_nextMonthMaxDay : date(self::DAY,$datestamp);
	    return $_nextYear.'-'.$_nextMonth.'-'.$_nextMonthDay;
	}
	/**
	 * 获得两日期差
	 * @param date $date1
	 * @param date $date2
	 * @param enum $interval 单位，d日差，m月差，y年差
	 * @return int
	 */
	public static function DateDiff($date1,$date2,$interval='d') {
	    $_sdate = strtotime($date1);
	    $_edate = strtotime($date2);
	    switch ($interval) {
	    	case 'd':
	    	    return ($_edate-$_sdate)/86400;
	    	    break;
	    	case 'm':
	    	    return self::_getMonthDiff($_sdate, $_edate);
	    	    break;
	    	case 'y':
	    	    return (date(self::YEAR,$_edate)-date(self::YEAR,$_sdate));
	    	    break;
	    	default:
	    	    return ($_edate-$_sdate)/86400;
	    	    break;
	    }
	}
	
	/**
	 *  月差
	 * @param int $datestamp1 开始时间戳
	 * @param int $datestamp2 结束时间戳
	 * @return int
	 */
	private static  function _getMonthDiff( $datestamp1, $datestamp2){
	    if(date('Y',$datestamp1)==date('Y',$datestamp2)) {
	        return (date(self::MONTH,$datestamp1)-date(self::MONTH,$datestamp2));
	    }else {
	        return (date(self::MONTH,$datestamp2)+(12-date(self::MONTH,$datestamp1))+(date(self::YEAR,$datestamp2)-date(self::YEAR,$datestamp1)-1)*12);
	    }
	}
}
?>