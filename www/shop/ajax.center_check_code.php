<?php
include_once('./_common.php');

if($member['mb_type'] !== 'center')
    json_response(400, '방문급여센터회원만 이용할 수 있습니다.');

$cm_code = clean_xss_tags($_POST['cm_code']);

if(!$cm_code)
    json_response(400, '접속코드를 입력해주세요.');

$sql = " select count(*) as cnt from center_member where mb_id = '{$member['mb_id']}' and cm_code = '$cm_code' ";
$result = sql_fetch($sql);

if($result['cnt'] > 0)
    json_response(400, '중복된 접속코드 입니다.');

json_response(200, '사용가능합니다.');
