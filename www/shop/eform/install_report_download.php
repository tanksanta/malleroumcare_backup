<?php
include_once('./_common.php');

$od_id = get_search_string($_GET['od_id']);
if(!$od_id)
    alert('유효하지 않은 요청입니다.');

if($member['mb_level'] >= 9) {
    // 관리자
    $sql = "
        SELECT * FROM
            partner_install_report
        WHERE
            od_id = '$od_id'
    ";
} else if($member['mb_type'] == 'partner') {
    // 파트너
    $sql = "
        SELECT * FROM
            partner_install_report
        WHERE
            od_id = '$od_id' and
            mb_id = '{$member['mb_id']}'
    ";
} else if($member['mb_type'] == 'default') {
    // 사업소
    $sql = "
        SELECT * FROM
            g5_shop_order
        WHERE
            od_id = '$od_id' and
            mb_id = '{$member['mb_id']}'
    ";
    $od = sql_fetch($sql);
    if(!$od['od_id'])
        alert('해당 주문이 존재하지 않습니다.');
    
    $sql = "
        SELECT * FROM
            partner_install_report
        WHERE
            od_id = '$od_id'
    ";
} else {
    alert('먼저 로그인하세요.');
}
$report = sql_fetch($sql);

if(!$report)
    alert('유효하지 않은 요청입니다.');

if(!$report['ir_file_url'])
    alert('작성되지 않은 결과보고서입니다.');

header("Content-type: application/pdf");
header("Content-Disposition: attachment; filename=\"{$report['ir_file_name']}\"");

@readfile(G5_PATH.$report['ir_file_url']);
