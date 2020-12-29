<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 위젯 대표아이디 설정
$wid = 'CMB';

// 게시판 제목 폰트 설정
$font = 'font-16 en';

// 게시판 제목 하단라인컬러 설정 - red, blue, green, orangered, black, orange, yellow, navy, violet, deepblue, crimson..
$line = 'navy';

// 사이드 위치 설정 - left, right
$side = ($at_set['side']) ? 'left' : 'right';

?>
<style>
.widget-index .at-main,
.widget-index .at-side { padding-bottom:0px; }
.widget-index .div-title-underbar { margin-bottom:15px; }
.widget-index .div-title-underbar span { padding-bottom:4px; }
.widget-index .div-title-underbar span b { font-weight:500; }
.widget-index .widget-img img { display:block; max-width:100%; /* 배너 이미지 */ }
.widget-box { margin-bottom:25px; }
</style>

<div class="at-container widget-index">

</div>
