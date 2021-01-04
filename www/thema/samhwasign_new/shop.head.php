<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

if ( $is_main ) {
	include_once(THEMA_PATH . '/shop.head.main.php');
}else{
	include_once(THEMA_PATH . '/shop.head.sub.php');
}
?>