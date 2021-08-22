<?php
include_once('./_common.php');

if (!$member['mb_id']) {
  alert('로그인이 필요합니다.');
}

set_tutorial();

goto_url(G5_URL);