<?php
include_once("./_common.php");
ini_set("display_errors", 0);
if ($is_admin != 'super' && $member["mb_level"] < "9") {
  alert('최고관리자만 접근 가능합니다.');
  exit;
}

include_once(G5_LIB_PATH.'/PHPExcel.php');
function column_char($i) { return chr( 65 + $i ); }

$type = $_POST['type'];
$sel_date = $_POST['sel_date'];
$search = $_POST['search'];
$fr_date = $_POST['fr_date'];
$to_date = $_POST['to_date'];

$service_type = [ 'login' => [['로그ID'=>20,'회원ID'=>20,'사업소코드'=>20,'회원명'=>40,'접속일자'=>25],"로그인 로그"]
              ,'order' => [['주문ID'=>20,'회원ID'=>20,'사업소코드'=>20,'회원명'=>40,'관리자주문여부'=>20,'주문생성일자'=>25],"주문서 로그"]
              ,'eform' => [['계약서ID'=>50,'회원ID'=>20,'사업소코드'=>20,'회원명'=>40,'계약서생성일'=>25,'계약서서명일'=>25],"계약서 로그"]
              ,'item_msg' => [['제안서ID'=>20,'회원ID'=>20,'사업소코드'=>20,'회원명'=>40,'제안서생성일'=>25,'제안서발송일'=>25],"제안서 로그"]
              ,'check_itcare' => [['조회ID'=>20,'회원ID'=>20,'사업소코드'=>20,'회원명'=>40,'조회번호'=>20,'조회일자'=>25],"요양정보조회 로그"] ];
$type_data = $service_type[$type];

if (!$type || $type == 'login') {
    $headers = array('로그ID', '회원ID', '사업소코드', '회원명', '로그인일자');
    $sql_search = "select 
            A.id as '로그ID',
            A.mb_id as '회원ID',
            IFNULL(NULLIF(B.mb_thezone,''), REPLACE(B.mb_giup_bnum,'-','')) as '사업소코드',
            B.mb_name as '회원명',
            A.regdt as '로그인일자' ";

    $sql_common = "from g5_statistics A 
            left join g5_member B on B.mb_id = A.mb_id
            where A.type='LOGIN'
            and A.regdt > '{$fr_date}'
            and A.regdt < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.regdt desc ";
}
else if ($type == 'order') {
    $headers = array('주문서ID', '회원ID', '사업소코드', '회원명', '관리자주문여부', '생성일자');
    $sql_search = "select
            A.od_id '주문서ID',
            A.mb_id as '회원ID',
            IFNULL(NULLIF(B.mb_thezone,''), REPLACE(B.mb_giup_bnum,'-','')) as '사업소코드',
            B.mb_name as '회원명',
            case when A.od_add_admin='1' then 'Y' else 'N' end as '관리자주문여부',
            A.od_time as '생성일자' ";

    $sql_common = "from g5_shop_order A
            left join g5_member B on B.mb_id = A.mb_id
            where od_id>0
            and A.od_time > '{$fr_date}'
            and A.od_time < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.od_time desc ";
}
else if ($type == 'eform') {
    $headers = array('계약서ID', '회원ID', '사업소코드', '회원명', '계약서생성일', '계약서서명일');
    $sql_search = "select
              HEX(A.dc_id) as '계약서ID',
              B.mb_id as '회원ID',
              IFNULL(NULLIF(B.mb_thezone,''), REPLACE(B.mb_giup_bnum,'-','')) as '사업소코드',
              B.mb_name as '회원명',
              A.dc_datetime as '계약서생성일',
              A.dc_sign_datetime as '계약서서명일' ";

    if($sel_date == 'dc_datetime'){
       $sql_common = "from eform_document A
              left join g5_member B on B.mb_entId=A.entId
              where A.dc_datetime > 0
              and B.mb_id is not null
              and A.dc_datetime > '{$fr_date}'
              and A.dc_datetime < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.dc_datetime desc ";
    } else if ($sel_date == 'dc_sign_datetime'){
       $sql_common = "from eform_document A
              left join g5_member B on B.mb_entId=A.entId
              where A.dc_datetime > 0
              and B.mb_id is not null
              and A.dc_sign_datetime > '{$fr_date}'
              and A.dc_sign_datetime < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.dc_sign_datetime desc ";
    }
}
else if ($type == 'item_msg') {
    $headers = array('제안서ID', '회원ID', '사업소코드', '회원명', '제안서생성일', '제안서발송일');
    $sql_search = "select 
            A.ms_id as '제안서ID',
            A.mb_id as '회원ID',
            IFNULL(NULLIF(B.mb_thezone,''), REPLACE(B.mb_giup_bnum,'-','')) as '사업소코드',
            B.mb_name as '회원명',
            A.ms_created_at as '제안서생성일',
            C.ml_sent_at as '제안서발송일' ";

    if($sel_date == 'ms_created_at'){
       $sql_common = "from recipient_item_msg A
              left join g5_member B on A.mb_id=B.mb_id
              left join recipient_item_msg_log C on C.ms_id =A.ms_id 
              where A.ms_created_at > '{$fr_date}'
              and A.ms_created_at < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by A.ms_created_at desc ";
    } else if ($sel_date == 'ml_sent_at'){
       $sql_common = "from recipient_item_msg A
              left join g5_member B on A.mb_id=B.mb_id
              left join recipient_item_msg_log C on C.ms_id =A.ms_id 
              where C.ml_sent_at > '{$fr_date}'
              and C.ml_sent_at < DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
              and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
              order by C.ml_sent_at desc ";
    }
}
else if ($type == 'check_itcare') {
    $headers = array('조회ID', '회원ID', '사업소코드', '회원명', '조회번호', '조회일자');
    $sql_search = "select 
            A.log_id as '조회ID',
            A.ent_id as '회원ID',
            IFNULL(NULLIF(B.mb_thezone,''), REPLACE(B.mb_giup_bnum,'-','')) as '사업소코드',
            B.mb_name as '회원명',
            A.pen_id as '조회번호',
            A.occur_date as '조회일자' ";

    $sql_common = "from rep_inquiry_log A
            left join g5_member B on B.mb_id=A.ent_id
            where A.occur_date > '{$fr_date}'
            and A.occur_date <DATE_ADD('{$to_date}', INTERVAL 1 DAY) 
            and (B.mb_name like '%{$search}%'or B.mb_id like '%{$search}%')
            order by A.occur_date desc ";
}

$sql = "{$sql_search}
        {$sql_common}";

$result = sql_query($sql);

$rows = [];
for($i=1; $row=sql_fetch_array($result); $i++){
  $tmp_row = [];
  foreach($headers as $value) {
    if($value == '계약서서명일' && $row[$value] == "0000-00-00 00:00:00") $row_value = "";
    else $row_value = " ".$row[$value]." "; // 15자리 이상 숫자 깨짐 방지
    array_push($tmp_row, substr($row_value,1));
  }

  $rows[] = $tmp_row;
}

$data = array_merge(array($headers), $rows);

$widths  = array_values($type_data[0]);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

$excel = new PHPExcel();

$excel->getDefaultStyle()
      ->getNumberFormat()
      ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

$excel->setActiveSheetIndex(0)
    ->getStyle( "A:$last_char" )
    ->getAlignment()
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    ->setWrapText(true);

foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->setTitle($type_data[1]);  // Change sheet's title if you want
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

$filename = $type_data[1]."(".date("YmdHis").")";
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename={$filename}.xls");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>
