<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

/**
* 기존에 있던  eform_document 테이블을 재사용하기 위한 작업
* 새로이 필요한 컬럼(applicantCd)이 존재하는지 확인 후, 없으면 새로 추가하는 작업 진행
*/
$sql_check = "
  show columns from eform_document where field in ('applicantCd');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){

  $append_col = "alter table eform_document ".
                "add column applicantCd varchar(255) after penAddrDtl";
  sql_query($append_col);
}

$w = $_POST['w'];
$sealFile_self = $_POST['sealFile_self'] == "true" ? true : false; //직인 직접날인 여부 

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
//신규
$applicantRelation		= $_POST['applicantRelation']? clean_xss_tags($_POST['applicantRelation']) :"";//신청인 관계
if($_POST['ltm_chk'] == "1" && $applicantRelation != "0" && $applicantRelation != ""){//장기용양급여 신청 시, 본인이 아닐 때
	$applicantNm			= $_POST['applicantNm']? clean_xss_tags($_POST['applicantNm']) :""; //신청인명	
	$applicantBirth			= $_POST['applicantBirth']? clean_xss_tags($_POST['applicantBirth']) :"";//신청인 생년월일
	$applicantAddr			= $_POST['applicantAddr']? clean_xss_tags($_POST['applicantAddr']) :"";//신청인 주소
	$applicantTel			= $_POST['applicantTel']? clean_xss_tags($_POST['applicantTel']) :"";//신청인 전화	
}else{//장기용양급여 신청 아니거나 본인일때
	$applicantNm			= ""; //신청인명
	$applicantRelation		= "0";//신청인 관계
	$applicantBirth			= "";//신청인 생년월일
	$applicantAddr			= "";//신청인 주소
	$applicantTel			= "";//신청인 전화
}
$applicantDate			= $_POST['applicantDate']? clean_xss_tags($_POST['applicantDate']) :"";//신청인신청일
$do_date				= $_POST['do_date']? clean_xss_tags($_POST['do_date']) :"";//계약서 계약일
$dc_sign_send_datetime  = $_POST['dc_sign_send_datetime']? clean_xss_tags($_POST['dc_sign_send_datetime']) :"0000-00-00 00:00:00";//서명요청일

//$contract_tel			= $_POST['contract_tel']? clean_xss_tags($_POST['contract_tel']) :"";//대리인 전화
//$contract_addr			= $_POST['contract_addr']? clean_xss_tags($_POST['contract_addr']) :"";//대리인 주소
//신규 끝

$penId = clean_xss_tags($_POST['penId']) ?: '';
$penNm = clean_xss_tags($_POST['penNm']);
$penLtmNum = clean_xss_tags($_POST['penLtmNum']);
$penConNum = clean_xss_tags($_POST['penConNum']);
$penBirth = '';
$penRecGraCd = clean_xss_tags($_POST['penRecGraCd']);
$penRecGraNm = $pen_rec_gra_cd[$penRecGraCd];
$penRecTypeCd = clean_xss_tags($_POST['penRecTypeCd']);
$penRecTypeTxt = clean_xss_tags($_POST['penRecTypeTxt']);
$penTypeCd = clean_xss_tags($_POST['penTypeCd']);
$applicantCd = $_POST['applicantCd']? clean_xss_tags($_POST['applicantCd']) :"";
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


$contract_sign_type = $_POST['contract_sign_type'];
if($contract_sign_type == 1){//대리인 사용
	$contract_sign_name = clean_xss_tags($_POST['contract_sign_name']) ?: '';//대리인명
	$contract_sign_relation = clean_xss_tags($_POST['contract_sign_relation']) ?: '0';//수급자와의 관계 0본인1가족2친족3기타
	$contract_tel			= $_POST['contract_tel']? clean_xss_tags($_POST['contract_tel']) :"";//대리인 전화
	$contract_addr			= $_POST['contract_addr']? clean_xss_tags($_POST['contract_addr']) :"";//대리인 주소
}else{//수급자 본인
	$contract_sign_type = "0";
	$contract_sign_name = '';//대리인명
	$contract_sign_relation = '0';//수급자와의 관계 0본인1가족2친족3기타
	$contract_tel			= "";//대리인 전화
	$contract_addr			= "";//대리인 주소
}


if( !( $penNm && $penLtmNum && $penConNum && $penRecGraCd && $penTypeCd && $penExpiDtm && $penJumin ) )
    /* 서원 : 22.08.26 - 수급자 정보 오류 alert 멘트 구체화 */
    json_response(400, '수급자의 [연락처,주소]등 모든 정보가 입력 되어있는지 확인해주세요.');

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

if($pen_type != '1') {
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
    if($diff > 1) {
        $rental_price += ( $price * ($diff - 1) );
    }

    // 마지막 달 계산
    $rental_price += (int) floor(
        $price * (
            date('j', $end_time)
            /
            ( date('t', $end_time) * 10 )
        )
    ) * 10;

    if($diff > 0) {
        // 첫째 달 계산
        $rental_price += (int) floor(
            $price * (
                ( date('t', $str_time) - date('j', $str_time) + 1 )
                /
                ( date('t', $str_time) * 10 )
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

if (!$sealFile_self) { //직인 직접날인이 아니면
    $seal_file = $member['sealFile'];
    if(!$seal_file) {
        json_response(400, '회원정보에 직인 이미지를 등록해주세요.');
    }
}
/** 0504 "[간편계약서 작성]필수정보(연락처, 주소)가 입력 안 되어있는 수급자로 계약작성 시 오류발생" 작업
 * if($w=='u'||$w=='w'){...} else{...} ==> if($w==''||($_POST['dc_id']==''&&$w=='w')) {...} if($w=='u'||$w=='w') {...}
 * (기존 진행순서) 수급자 정보입력 => 상품 선택(eform_document 테이블에 로우 insert) => return de_id => 계약서 생성 버튼 클릭 => dc_id에 맞는 데이터 찾아서 update
 * 기존에는 계약서 생성/수정 버튼 클릭 시에  dc_id 생성 여부 상관없이 update 만 진행했었음 => 수급자 정보가 완전하지 않은 경우 dc_id가 정상적으로 생성되지 않기 때문에 계약서 생성이 불가하게 됨
 * if문을 분리함으로써 dc_id가 없는 계약서는 전처리 작업 진행 할 수 있게 함 **/
if($w == '' || ($_POST['dc_id'] == '' && $w == 'w')) {
    // 작성

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
            penRecTypeCd = '$penRecTypeCd', # 수령방법
            penRecTypeTxt = '$penRecTypeTxt',
            penTypeCd = '$penTypeCd', # 본인부담금율
            applicantCd = '$applicantCd', # 장기요양입소이용신청서 신청인
            penTypeNm = '$penTypeNm',
            penExpiDtm = '$penExpiDtm', # 수급자 이용기간
            penJumin = '$penJumin',
            penZip = '$penZip',
            penAddr = '$penAddr',
            penAddrDtl = '$penAddrDtl',
			contract_sign_type = '$contract_sign_type',# 대리인
            contract_sign_name = '$contract_sign_name',
            contract_sign_relation = '$contract_sign_relation',
			contract_tel = '$contract_tel',
			contract_addr = '$contract_addr',
			applicantNm = '$applicantNm',# 신청인
			applicantRelation = '$applicantRelation',
			applicantBirth = '$applicantBirth',
			applicantAddr = '$applicantAddr',
			applicantTel = '$applicantTel',
			applicantDate = '$applicantDate',
			do_date = '$do_date',
			dc_sign_send_datetime = '$dc_sign_send_datetime'
    ";
    $result = sql_query($sql);

    if(!$result)
        json_response(500, 'DB 서버 오류로 계약서를 저장하지 못했습니다.');

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
            `dc_ip` = '$ip'
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

if($w == 'u' || $w == 'w') {
    // 수정 or 생성

    $dc_id = $dc_id?:clean_xss_tags($_POST['dc_id']);

    $dc = sql_fetch(" select * from eform_document where dc_id = UNHEX('$dc_id') and dc_status in ('10', '11') ");
    if(!$dc['entId'] || $dc['entId'] != $member['mb_entId'])
        json_response(400, '유효하지 않은 요청입니다.');
    
    // 직인 파일 삭제
    if($dc['dc_signUrl']) {
        @unlink(G5_PATH . $dc['dc_signUrl']);
    }
    
    if($w == 'w' || $dc['dc_status'] == '11')
        $dc_status = '11';
    else
        $dc_status = '10';
	
	$sql = "
        UPDATE
            eform_document
        SET
            dc_status = '$dc_status',
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
            penRecTypeCd = '$penRecTypeCd', # 수령방법
            penRecTypeTxt = '$penRecTypeTxt',
            penTypeCd = '$penTypeCd', # 본인부담금율
            applicantCd = '$applicantCd', # 장기요양입소이용신청서 신청인
            penTypeNm = '$penTypeNm',
            penExpiDtm = '$penExpiDtm', # 수급자 이용기간
            penJumin = '$penJumin',
            penZip = '$penZip',
            penAddr = '$penAddr',
            penAddrDtl = '$penAddrDtl',
            contract_sign_type = '$contract_sign_type',
            contract_sign_name = '$contract_sign_name',
            contract_sign_relation = '$contract_sign_relation',
			contract_tel = '$contract_tel',
			contract_addr = '$contract_addr',
			applicantNm = '$applicantNm',
			applicantRelation = '$applicantRelation',
			applicantBirth = '$applicantBirth',
			applicantAddr = '$applicantAddr',
			applicantTel = '$applicantTel',
			applicantDate = '$applicantDate',
			do_date = '$do_date',
			dc_sign_send_datetime = '$dc_sign_send_datetime'
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
}

$dc_signUrl = '';
if (!$sealFile_self) { //직인 직접날인이 아니면
    // 직인 파일 사본 저장
    $seal_dir = G5_DATA_PATH.'/file/member/stamp';
    $signdir = G5_DATA_PATH.'/eform/sign';
    if(!is_dir($signdir)) {
    @mkdir($signdir, G5_DIR_PERMISSION, true);
    @chmod($signdir, G5_DIR_PERMISSION);
    }
	$filename = $dc_id."_".$member['mb_entId']."_".date("YmdHisw").".png";
	$img_file = $seal_dir.'/'.$seal_file;
	$ext = end(explode('.', $img_file)); 
	if(strtolower($ext) == "gif"){//gif파일일 경우
		$img=imagecreatefromgif($img_file);
		$img_tex = "gif파일";
		imagepng($img,$signdir."/".$filename);
		imagedestroy($img);	
	}elseif(strtolower($ext) == "jpg" || strtolower($ext) == "jpeg"){//jpg파일일 경우
		$img=imagecreatefromjpeg($img_file);
		$img_tex = "jpg파일";
		imagepng($img,$signdir."/".$filename);
		imagedestroy($img);	
	}elseif(strtolower($ext) == "png"){//png파일일 경우
		$seal_data = @file_get_contents($seal_dir.'/'.$seal_file);
		file_put_contents("$signdir/$filename", $seal_data);
	}
    $dc_signUrl = "/data/eform/sign/$filename";
}

// 직인 업데이트
sql_query("
    UPDATE `eform_document` SET
        `dc_signUrl` = '$dc_signUrl'
    WHERE `dc_id` = UNHEX('$dc_id')
");

sql_query("UPDATE `g5_member` SET
    `sealFile_self` = '$sealFile_self'
    WHERE mb_id = '{$member['mb_id']}'
");


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

