<?php
include_once('./_common.php');

if($member['mb_type'] !== 'partner')
    alert('먼저 로그인하세요.');

$od_id = get_search_string($_GET['od_id']);

$sql = "
    SELECT * FROM
        partner_install_report
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}'
";
$report = sql_fetch($sql);

if(!$report)
    alert('유효하지 않은 요청입니다.');

if(!$report['ir_file_url'])
    alert('작성되지 않은 결과보고서입니다.');

$sql = "
    UPDATE
        partner_install_report
    SET
        ir_sign_url = '',
        ir_sign_time = '',
        ir_sign_browser = '',
        ir_sign_ip = '',
        ir_file_url = '',
        ir_file_name = ''
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}'
";
$result = sql_query($sql);
if(!$result)
    json_response(400, 'DB 서버 오류 발생');

json_response(200, 'OK');
