<?php
include_once('./_common.php');

// 체험프로세스 중단
// : 모든 튜토리얼을 종료 처리한다.

if(!$member['mb_id'])
  json_response(400, '먼저 로그인하세요.');

if($member['mb_type'] !== 'default')
  json_response(400, '사업소 회원이 아닙니다.');

set_tutorial('recipient_add', 1);
set_tutorial('recipient_order', 1);
set_tutorial('document', 1);
set_tutorial('claim', 1);

json_response(200, 'OK');
