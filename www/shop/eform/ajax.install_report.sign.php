<?php
include_once("./_common.php");

if($member['mb_type'] !== 'partner')
    json_response(400, '먼저 로그인하세요.');

$od_id = get_search_string($_POST['od_id']);
$state = json_decode(stripslashes($_POST['state']), true);

if(!$od_id || !$state)
    json_response(400, '유효하지 않은 요청입니다.');

$sql = "
    SELECT * FROM
        partner_install_report
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}'
";
$report = sql_fetch($sql);

if(!$report)
    json_response(400, '서명할 결과보고서를 찾을 수 없습니다.');

if($report['ir_sign_url'])
    json_response(400, '이미 작성된 결과보고서입니다.');

$sql = "
    SELECT
        o.*,
        m.mb_name,
        mb_giup_btel
    FROM
        g5_shop_order o
    LEFT JOIN
        g5_member m ON o.mb_id = m.mb_id
    WHERE
        od_id = '$od_id'
";
$od = sql_fetch($sql);

if(!$od)
    json_response(400, '주문이 존재하지 않습니다.');

// 서명 파일 사본 저장할 경로
$signdir = G5_DATA_PATH.'/eform/sign';
if(!is_dir($signdir)) {
    @mkdir($signdir, G5_DIR_PERMISSION, true);
    @chmod($signdir, G5_DIR_PERMISSION);
}

// 서명 이미지 저장
$sign = $state['sign_ir_1'];
if(!$sign)
    json_response(400, '서명을 완료해주세요.');

$encoded_image = explode(",", $sign)[1];
$decoded_image = base64_decode($encoded_image);

$filename = $od_id."_".$member['mb_id']."_".$id."_".date("YmdHisw").".png";
file_put_contents("$signdir/$filename", $decoded_image);

$ir_sign_url = "/data/eform/sign/{$filename}";
$ir_sign_browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$ir_sign_ip = $_SERVER['REMOTE_ADDR'];

$sql = "
    UPDATE
        partner_install_report
    SET
        ir_sign_url = '$ir_sign_url',
        ir_sign_time = NOW(),
        ir_sign_browser = '$ir_sign_browser',
        ir_sign_ip = '$ir_sign_ip'
    WHERE
        od_id = '$od_id' and
        mb_id = '{$member['mb_id']}'
";
$result = sql_query($sql);

if(!$result)
    json_response(500, 'DB 오류가 발생하여 서명에 실패하였습니다.');

json_response(200, 'OK');
