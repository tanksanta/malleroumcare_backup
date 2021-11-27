<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

$w = $_POST['w'];

if($w == 'd') {
    // 삭제
    $dc_id = clean_xss_tags($_POST['dc_id']);

    $dc = sql_fetch(" select * from eform_document where dc_id = UNHEX('$dc_id') and dc_status = '11' ");
    if(!$dc['entId'] || $dc['entId'] != $member['mb_entId'])
        json_response(400, '유효하지 않은 요청입니다.');
    
    $sql = " DELETE FROM eform_document_item WHERE dc_id = UNHEX('$dc_id') ";
    sql_query($sql);
    $sql = " DELETE FROM eform_document_log WHERE dc_id = UNHEX('$dc_id') ";
    sql_query($sql);
    $sql = " DELETE FROM eform_document WHERE dc_id = UNHEX('$dc_id') ";
    sql_query($sql);

    json_response(200, 'OK');
}

$penId = clean_xss_tags($_POST['penId']) ?: '';
$penNm = clean_xss_tags($_POST['penNm']);
$penLtmNum = clean_xss_tags($_POST['penLtmNum']);
$penConNum = clean_xss_tags($_POST['penConNum']);
$penBirth = '';
$penRecGraCd = clean_xss_tags($_POST['penRecGraCd']);
$penRecGraNm = $pen_rec_gra_cd[$penRecGraCd];
$penTypeCd = clean_xss_tags($_POST['penTypeCd']);
$penTypeNm = $pen_type_cd[$penTypeCd];
$penExpiStDtm = clean_xss_tags($_POST['penExpiStDtm']);
$penExpiEdDtm = clean_xss_tags($_POST['penExpiEdDtm']);
$penExpiDtm = '';
if($penExpiStDtm && $penExpiEdDtm) {
    $penExpiDtm = $penExpiStDtm . ' ~ ' . $penExpiEdDtm;
}
$penJumin = clean_xss_tags($_POST['penJumin']) ?: '';
$penZip = clean_xss_tags($_POST['penZip']) ?: '';
$penAddr = clean_xss_tags($_POST['penAddr']) ?: '';
$penAddrDtl = clean_xss_tags($_POST['penAddrDtl']) ?: '';
$entConAcc01 = clean_xss_tags($_POST['entConAcc01']) ?: '';
$entConAcc02 = clean_xss_tags($_POST['entConAcc02']) ?: '';

if( !( $penNm && $penLtmNum && $penConNum && $penRecGraCd && $penTypeCd && $penExpiDtm && $penJumin ) )
    json_response(400, '수급자 정보를 입력해주세요.');

$penLtmNum = 'L' . $penLtmNum;

/*
if( $penTypeCd == '04' && !$penJumin )
    json_response(400, '기초수급자는 주민번호(앞자리)를 입력해주세요.');
*/
try {
    $penBirth = DateTime::createFromFormat('Ymd', '19'.$penJumin);
    $penBirth = $penBirth->format('Y.m.d');
} catch(Exception $e) {
    json_response(400, '주민등록번호(앞자리)를 정확히 입력해주세요.');
}

$it_id_arr = $_POST['it_id'];
$it_gubun_arr = $_POST['it_gubun'];
$it_qty_arr = $_POST['it_qty'];
$it_date_arr = $_POST['it_date'];
$it_barcode_arr = $_POST['it_barcode'];

if(!($it_id_arr && $it_gubun_arr && $it_qty_arr)) {
    json_response(400, '품목을 선택해주세요.');
}

// 특약사항 저장
if($_POST['save_conacc'] == 1 && $w == 'w') {
    $sql = "
        UPDATE g5_member
        SET mb_entConAcc01 = '$entConAcc01'
        WHERE mb_id = '{$member['mb_id']}'
    ";
    $result = sql_query($sql);
}

if($pen_type == '1') {
    // 기존 수급자 선택한 경우 생성시 수급자 정보 업데이트\
    if($w == 'w') {
        $data = [
            'entId' => $member["mb_entId"],
            'usrId' => $member['mb_id'],
            'delYn' => 'N',
            'penId' => $penId,
            'penNm' => $penNm,
            'penLtmNum' => $penLtmNum,
            'penConNum' => $penConNum,
            'penBirth' => $penBirth,
            'penRecGraCd' => $penRecGraCd,
            'penTypeCd' => $penTypeCd,
            'penExpiStDtm' => $penExpiStDtm,
            'penExpiEdDtm' => $penExpiEdDtm,
            'penJumin' => $penJumin
        ];

        $valid = valid_recipient_input($data, false);
        if(!$valid) {
            // 입력값 검증 통과된 경우에만 업데이트시킴
            $data = normalize_recipient_input($data);
            $res = api_post_call(EROUMCARE_API_RECIPIENT_UPDATE, $data);
        }
    }
} else {
    // 신규 수급자인 경우
    $penId = '';
}

function calc_rental_price($str_date, $end_date, $price) {
    $rental_price = 0;

    $str_time = strtotime($str_date);
    $end_time = strtotime($end_date);

    $year1 = date('Y', $str_time);
    $year2 = date('Y', $end_time);

    $month1 = date('m', $str_time);
    $month2 = date('m', $end_time);

    $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

    // 중간달 계산
    if($diff > 2) {
        $rental_price += ( $price * ($diff - 2) );
    }

    // 첫째 달 계산
    $rental_price += (int) floor(
        $price * (
            ( date('t', $str_time) - date('j', $str_time) + 1 )
            /
            ( date('t', $str_time) * 10 )
        )
    ) * 10;

    if($diff > 0) {
        // 마지막 달 계산
        $rental_price += (int) floor(
            $price * (
                date('j', $end_time)
                /
                ( date('t', $end_time) * 10 )
            )
        ) * 10;
    }

    return $rental_price;
}

function calc_pen_price($penTypeCd, $price) {
    switch($penTypeCd) {
        case '00':
            $rate = 15;
            break;
        case '01':
            $rate = 9;
            break;
        case '02':
        case '03':
            $rate = 6;
            break;
        case '04':
            return 0;
        default:
            $rate = 15;
            break;
    }

    $pen_price = (int) floor(
        $price * $rate / (100 * 10)
    ) * 10;

    return $pen_price;
}

if($w == 'u' || $w == 'w') {
    // 수정 or 생성

    $dc_id = clean_xss_tags($_POST['dc_id']);

    $dc = sql_fetch(" select * from eform_document where dc_id = UNHEX('$dc_id') and dc_status in ('10', '11') ");
    if(!$dc['entId'] || $dc['entId'] != $member['mb_entId'])
        json_response(400, '유효하지 않은 요청입니다.');
    
    if($w == 'w' || $dc['dc_status'] == '11')
        $dc_status = '11';
    else
        $dc_status = '10';

    $sql = "
        UPDATE
            eform_document
        SET
            dc_status = '$dc_status',
            od_id = '0',
            entNm = '{$member["mb_entNm"]}',
            entCrn = '{$member["mb_giup_bnum"]}',
            entNum = '{$member["mb_ent_num"]}',
            entMail = '{$member["mb_email"]}',
            entCeoNm = '{$member["mb_giup_boss_name"]}',
            entConAcc01 = '$entConAcc01',
            entConAcc02 = '$entConAcc02',
            penId = '$penId',
            penNm = '$penNm',
            penBirth = '$penBirth',
            penLtmNum = '$penLtmNum',
            penRecGraCd = '$penRecGraCd', # 장기요양등급
            penRecGraNm = '$penRecGraNm',
            penTypeCd = '$penTypeCd', # 본인부담금율
            penTypeNm = '$penTypeNm',
            penExpiDtm = '$penExpiDtm', # 수급자 이용기간
            penJumin = '$penJumin',
            penZip = '$penZip',
            penAddr = '$penAddr',
            penAddrDtl = '$penAddrDtl'
        WHERE
            dc_id = UNHEX('$dc_id') and
            entId = '{$member["mb_entId"]}'
    ";
    $result = sql_query($sql);

    if(!$result)
        json_response(500, 'DB 서버 오류로 계약서를 저장하지 못했습니다.');
    
    // 먼저 계약서 품목 삭제
    $sql = " DELETE FROM eform_document_item WHERE dc_id = UNHEX('$dc_id') ";
    sql_query($sql);
} else {
    // 작성
    $seal_file = $member['sealFile'];
    if(!$seal_file) {
        json_response(400, '회원정보에 직인 이미지를 등록해주세요.');
    }

    $dc_id = sql_fetch("SELECT REPLACE(UUID(),'-','') as uuid")["uuid"];

    $sql = "
        INSERT INTO
            eform_document
        SET
            dc_id = UNHEX('$dc_id'),
            dc_status = '10',
            od_id = '0',
            entId = '{$member["mb_entId"]}',
            entNm = '{$member["mb_entNm"]}',
            entCrn = '{$member["mb_giup_bnum"]}',
            entNum = '{$member["mb_ent_num"]}',
            entMail = '{$member["mb_email"]}',
            entCeoNm = '{$member["mb_giup_boss_name"]}',
            entConAcc01 = '$entConAcc01',
            entConAcc02 = '$entConAcc02',
            penId = '$penId',
            penNm = '$penNm',
            penConNum = '$penConNum',
            penBirth = '$penBirth',
            penLtmNum = '$penLtmNum',
            penRecGraCd = '$penRecGraCd', # 장기요양등급
            penRecGraNm = '$penRecGraNm',
            penTypeCd = '$penTypeCd', # 본인부담금율
            penTypeNm = '$penTypeNm',
            penExpiDtm = '$penExpiDtm', # 수급자 이용기간
            penJumin = '$penJumin',
            penZip = '$penZip',
            penAddr = '$penAddr',
            penAddrDtl = '$penAddrDtl'
    ";
    $result = sql_query($sql);

    if(!$result)
        json_response(500, 'DB 서버 오류로 계약서를 저장하지 못했습니다.');

    // 직인 파일 사본 저장
    $seal_dir = G5_DATA_PATH.'/file/member/stamp';
    $seal_data = @file_get_contents($seal_dir.'/'.$seal_file);
    $signdir = G5_DATA_PATH.'/eform/sign';
    if(!is_dir($signdir)) {
    @mkdir($signdir, G5_DIR_PERMISSION, true);
    @chmod($signdir, G5_DIR_PERMISSION);
    }
    $filename = $dc_id."_".$member['mb_entId']."_".date("YmdHisw").".png";
    file_put_contents("$signdir/$filename", $seal_data);

    $ip = $_SERVER['REMOTE_ADDR'];
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $timestamp = time();
    $datetime = date('Y-m-d H:i:s', $timestamp);

    // 문서 제목 생성
    $eform = sql_fetch("SELECT * FROM `eform_document` WHERE `dc_id` = UNHEX('$dc_id')");
    $subject = $eform["entNm"]."_".str_replace('-', '', $eform["entCrn"])."_".$eform["penNm"].substr($eform["penLtmNum"], 0, 6)."_".date("Ymd")."_";
    $subject_count_postfix = sql_fetch("SELECT COUNT(`dc_id`) as cnt FROM `eform_document` WHERE `dc_subject` LIKE '{$subject}%'")["cnt"];
    $subject_count_postfix = str_pad($subject_count_postfix + 1, 3, '0', STR_PAD_LEFT); // zerofill
    $subject .= $subject_count_postfix;

    // 계약서 정보 업데이트
    sql_query("
        UPDATE `eform_document` SET
            `dc_subject` = '$subject',
            `dc_datetime` = '$datetime',
            `dc_ip` = '$ip',
            `dc_signUrl` = '/data/eform/sign/$filename'
        WHERE `dc_id` = UNHEX('$dc_id')
    ");

    // 계약서 로그 작성
    $log = '전자계약서를 생성했습니다.';

    sql_query("INSERT INTO `eform_document_log` SET
        `dc_id` = UNHEX('$dc_id'),
        `dl_log` = '$log',
        `dl_ip` = '$ip',
        `dl_browser` = '$browser',
        `dl_datetime` = '$datetime'
    ");
}

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $it_gubun = clean_xss_tags($it_gubun_arr[$i]);
    $it_qty = intval(clean_xss_tags($it_qty_arr[$i]) ?: 0);
    $it_date = clean_xss_tags($it_date_arr[$i]);
    $it_barcode = clean_xss_tags($it_barcode_arr[$i]);
    $it_barcode = explode(chr(30), $it_barcode);

    if(!$it_id) continue;

    $it = sql_fetch("
        select i.*, ( select ca_name from g5_shop_category where ca_id = left(i.ca_id, 4) ) as ca_name
        from g5_shop_item i where it_id = '$it_id'
    ");
    if(!$it['it_id']) continue;

    if($it_gubun === '판매') {
        $gubun = '00';
        $it_price = $it['it_cust_price']; // 급여가
    } else if($it_gubun === '대여') {
        $gubun = '01';
        $str_date = substr($it_date, 0, 10);
        $end_date = substr($it_date, 11, 10);

        if(!$str_date || !$end_date) {
            sql_query(" DELETE FROM eform_document WHERE dc_id = UNHEX('$dc_id') ");
            json_response(400, '대여상품의 계약기간을 입력해주세요.');
        }

        $it_price = calc_rental_price($str_date, $end_date, $it['it_rental_price']);
    } else {
        continue;
    }

    for($x = 0; $x < $it_qty; $x++) {
        $it_price_pen = calc_pen_price($penTypeCd, $it_price);
        $it_price_ent = $it_price - $it_price_pen;

        $sql = "
            INSERT INTO eform_document_item SET
                dc_id = UNHEX('$dc_id'),
                gubun = '$gubun',
                ca_name = '{$it['ca_name']}',
                it_name = '{$it['it_name']}',
                it_code = '{$it['ProdPayCode']}',
                it_barcode = '{$it_barcode[$x]}',
                it_qty = '1',
                it_date = '$it_date',
                it_price = '$it_price',
                it_price_pen = '$it_price_pen',
                it_price_ent = '$it_price_ent'
        ";
        $result = sql_query($sql);
        if(!$result)
            json_response(500, 'DB 오류로 계약서의 품목을 추가하지 못했습니다.');
    }
}

json_response(200, 'OK', $dc_id);
