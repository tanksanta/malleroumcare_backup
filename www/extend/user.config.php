<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 최고관리자 (특정 레벨 지정)
if ($member['mb_level'] == 10) $is_admin = 'super';
?>
