<?php
$sub_menu = "200100";
include_once("./_common.php");
include_once(G5_LIB_PATH."/PHPExcel.php");

auth_check($auth[$sub_menu], 'r');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_datetime) ) $fr_datetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_datetime) ) $to_datetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_updatedatetime) ) $fr_updatedatetime = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_updatedatetime) ) $to_updatedatetime = '';

$sql_common = " from {$g5['member_table']} ";

//영업담당자 추가
$flag_m=false;
if($sfl=="mb_manager"){
  $sql_cm = "select `mb_id` from `g5_member` where `mb_name`= '".$stx."'";
  $result_cm= sql_fetch($sql_cm);
  $stx2=$stx;
  $stx=$result_cm['mb_id'];
  $flag_m=true;
}

$sql_search = " where (mb_type <> 'manager') ";
if ($stx) {
  $sql_search .= " and ( ";
  switch ($sfl) {
    case 'mb_point' :
      $sql_search .= " ({$sfl} >= '{$stx}') ";
      break;
    case 'mb_level' :
      $sql_search .= " ({$sfl} = '{$stx}') ";
      break;
    case 'mb_tel' :
    case 'mb_hp' :
      $sql_search .= " ({$sfl} like '%{$stx}') ";
      break;
    case 'all' :
      $sql_search .= "
        mb_tel like '%{$stx}%' OR
        mb_hp like '%{$stx}%' OR
        mb_id like '%{$stx}%' OR
        mb_nick like '%{$stx}%' OR
        mb_name like '%{$stx}%' OR
        mb_email like '%{$stx}%' OR
        mb_datetime like '%{$stx}%' OR
        mb_ip like '%{$stx}%' OR
        mb_giup_bnum like '%{$stx}%' OR
        mb_recommend like '%{$stx}%' OR
        mb_1 like '%{$stx}%' OR
        mb_giup_bname like '%{$stx}%' OR
        mb_manager like '%{$stx}%'
      ";
      break;
    default :
      $sql_search .= " ({$sfl} like '{$stx}%') ";
      break;
  }
  $sql_search .= " ) ";
}

if ($button_type) {
  $sql_search .= " and ( ";
  switch ($button_type) {
    case 'temp' :
      $sql_search .= "
        (
          mb_giup_bnum IN
          (
            SELECT mb_giup_bnum FROM g5_member WHERE mb_temp = TRUE
          )
          AND mb_temp = false
        )
      ";
      break;
    case 'partner' :
      $sql_search .= " (mb_type = 'partner') ";
      break;
    case 'default' :
      $sql_search .= " (mb_type = 'default' AND mb_level = 3) ";
      break;
    case 'vip' :
      $sql_search .= " (mb_type = 'default' AND mb_level = 4) ";
      break;
    case 'normal' :
      $sql_search .= " (mb_type = 'normal') ";
      break;
    case 'block' :
      $sql_search .= " (mb_order_approve = 0) ";
      break;
  }
  $sql_search .= " ) ";
  $qstr .= "&amp;button_type=$button_type";
}

if ($fr_datetime && $to_datetime) {
  $sql_search .= " and ( mb_datetime between '$fr_datetime 00:00:00' and '$to_datetime 23:59:59' )";
  $qstr .= "&amp;fr_datetime=$fr_datetime&amp;to_datetime=$to_datetime";
}

if ($fr_updatedatetime && $to_updatedatetime) {
  $sql_search .= " and ( mb_update_date between '$fr_updatedatetime 00:00:00' and '$to_updatedatetime 23:59:59' )";
  $qstr .= "&amp;fr_updatedatetime=$fr_updatedatetime&amp;to_updatedatetime=$to_updatedatetime";
}

if ($_GET['mb_level']) {
  $sql_search .= " and ( ";

  $mb_level = (int)$_GET['mb_level'];

  $sql_search .= " (mb_level like '%{$mb_level}') ";

  $sql_search .= " ) ";
  $qstr .= "&amp;mb_level=$mb_level";
}

if ($is_admin != 'super')
  $sql_search .= " and mb_level <= '{$member['mb_level']}' ";

if (!$sst) {
  $sst = "mb_datetime";
  $sod = "desc";
}

if($sst == "mb_email_certify") {
  $sql_order = " order by {$sst} {$sod} , mb_datetime asc";
} else {
  $sql_order = " order by {$sst} {$sod} ";
}

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 멤버쉽 확인 ------------------------
$is_membership = (function_exists('apms_membership_item')) ? true : false;

// 멥버쉽 회원수
if($is_membership) {
  $sql = " select count(*) as cnt {$sql_common} {$sql_search} and as_date > 0 {$sql_order} ";
  $row = sql_fetch($sql);
  $membership_count = $row['cnt'];
}

// 탈퇴회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$leave_count = $row['cnt'];

// 차단회원수
$sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
$row = sql_fetch($sql);
$intercept_count = $row['cnt'];

// 임시계정 승인요청 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_giup_bnum IN
  (
    SELECT mb_giup_bnum FROM g5_member WHERE mb_temp = TRUE
  )
  AND mb_temp = false
)";
$row = sql_fetch($sql);
$temp_count = $row['cnt'];

// 파트너 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'partner'
)";
$row = sql_fetch($sql);
$partner_count = $row['cnt'];

// 사업소 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'default'
)";
$row = sql_fetch($sql);
$default_count = $row['cnt'];

// VIP 사업소 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'default' AND mb_level = 4
)";
$row = sql_fetch($sql);
$vip_count = $row['cnt'];

// 일반회원 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_type = 'normal'
)";
$row = sql_fetch($sql);
$normal_count = $row['cnt'];

// 주문정지 회원 수
$sql = " select count(*) as cnt {$sql_common} WHERE (
  mb_order_approve = 0
)";
$row = sql_fetch($sql);
$block_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

if($_GET['manager']){
    $sql_common = " from {$g5['member_table']} a left join {$g5['auth_table']} b on (a.mb_id=b.mb_id) ";
    $sql_search = " where b.au_menu ='200100' ";
}
$sql = " select * {$sql_common} {$sql_search} {$sql_order}";
$result = sql_query($sql);

while ($row=sql_fetch_array($result)) { 
    //영업담당자
    $result_manager = sql_fetch("select `mb_name` from `g5_member` where `mb_id` ='".$row['mb_manager']."'");
    
    $rows[] = [ 
        $result_manager['mb_name'],  //영업담당자
        $row['mb_id'],  //아이디
        $row['mb_name'],  //이름필수
        $row['mb_nick'],  //닉네임
        $row['mb_level'],  //회원권한
        $row['mb_giup_btel'],  //전화번호
        $row['mb_hp'],  //휴대폰번호
        $row['mb_fax'],  //팩스번호
        $row['mb_email'],  //이메일(세금계산서 수신용)필수
        $row['mb_giup_bname'],  //기업명
        $row['mb_giup_boss_name'],  //대표자명
        $row['mb_giup_bnum'],  //사업자번호
        $row['mb_giup_buptae'],  //업태
        $row['mb_giup_bupjong'],  //업종
        $row['mb_giup_manager_name'],  //담당자명
        $row['mb_giup_zip1'].$row['mb_giup_zip2'],  //사업소 우편번호
        $row['mb_giup_addr1'],  //사업소 기본주소
        $row['mb_giup_addr2']." ".$row['mb_giup_addr3'],  //사업소 상세주소
        $row['mb_giup_tax_email'],  //세금계산서이메일
        $row['mb_thezone'],  //고객(거래처)코드
        $row['mb_zip1'].$row['mb_zip2'],  //배송지 우편번호
        $row['mb_addr1'],  //배송지 주소
        $row['mb_addr2'],  //배송지 상세주소
        $row['mb_datetime'],  //회원가입일
        $row['mb_today_login']   //최근접속일
    ];
}

$headers = array("영업담당자","아이디","이름(필수)","닉네임","회원 권한","전화번호","휴대폰번호","팩스번호","이메일(필수)","기업명","대표자명","사업자번호","업태","업종","담당자명","사업소 우편번호","사업소 기본주소","사업소 상세주소","세금계산서이메일","고객(거래처)코드","배송지 우편번호","배송지 주소","배송지 상세주소","회원가입일","최근접속일");
$data = array_merge(array($headers), $rows);

$widths  = array(20, 20, 20, 20, 20, 20, 20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20);
$header_bgcolor = 'FFABCDEF';
$excel = new PHPExcel();
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"orderexcel-".date("ymd", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
$prevPage = $_SERVER['HTTP_REFERER'];
function column_char($i) { return chr( 65 + $i ); }
?>
