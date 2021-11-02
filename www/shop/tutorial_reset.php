<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  alert('로그인이 필요합니다.');
}

sql_query("DELETE FROM tutorial WHERE mb_id = '{$member['mb_id']}'");

alert('이제 서비스를 다시 체험하실 수 있습니다.', G5_SHOP_URL . '/mypage.php');