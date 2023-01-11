<?php
    /* // */
    /* // */
    /* // */
    /* // */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* //  *  */
    /* //  *  */
    /* //  * (주)티에이치케이컴퍼 & 이로움 - [ THKcompany & E-Roum ] */
    /* //  *  */
    /* //  * Program Name : EROUMCARE Platform! = OnlineBilling Ver:0.1 */
    /* //  * Homepage : https://eroumcare.com , Tel : 02-830-1301 , Fax : 02-830-1308 , Technical contact : dev@thkc.co.kr */
    /* //  * Copyright (c) 2022 THKC Co,Ltd.  All rights reserved. */
    /* //  *  */
    /* //  *  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// //// ////  */
    /* // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
    /* // */
    /* // */
    /* // */
    /* // */

    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */
    /* // 파일명 : /www/adm/shop_admin/popup.payment_OnlineBilling_Detail.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  사업소별 청구건에 대한 세부 적인 내용을 확인하는 페이지 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once("./_common.php");

// 23.01.09 : 서원 - 결제관련 코드값 치환
function txt_pay_ENUM( $val ) {

    $_result = "";
  
    switch($val) {    
  
      case('phone'): $_result = "휴대폰"; break;
      case('card'): $_result = "카드"; break;
      case('bank'): $_result = "계좌이체"; break;
      case('vbank'): $_result = "가상계좌"; break;
      case('easy'): $_result = "간편"; break;
      case('easy_rebill'): $_result = "간편자동"; break;
      case('card_rebill'): $_result = "카드자동"; break;
      case('kakaopay'): $_result = "카카오페이"; break;
      case('naverpay'): $_result = "네이버페이"; break;
      case('payco'): $_result = "페이코"; break;
      case('toss'): $_result = "토스"; break;
      case('easy_card'): $_result = "간편카드"; break;
      case('easy_card_rebill'): $_result = "간편카드자동"; break;
      case('auth'): $_result = "본인인증"; break;
      case('digital_card'): $_result = "디지털카드"; break;
      case('digital_bank'): $_result = "디지털계좌이체"; break;
      case('digital_card_rebill'): $_result = "디지털카드자동"; break;
  
      default : $_result = "-"; break;
    }
  
    return $_result;
}

$_sql = ("  SELECT 
                bl.*, 
                par.method_symbol, par.status_locale, card_company, card_quota
            FROM 
                payment_billing_list bl
            LEFT OUTER JOIN
                payment_api_request par ON par.id = (
                        SELECT MAX(id)
                        FROM payment_api_request par2
                        WHERE par2.bl_id = bl.bl_id
                        ORDER BY par2.create_dt
                        LIMIT 1
                    )
            WHERE bl.bl_id = '" . $_GET["bl_id"] . "'
            ORDER BY pay_confirm_dt
            LIMIT 1

");
$result_bl = sql_fetch($_sql);


if( $result_bl['card_quota']=="00" ) {
    $result_bl['card_quota']="일시불";
} else if( (int)$result_bl['card_quota'] > 0 ) {
    $result_bl['card_quota'] = "할부(".$result_bl['card_quota']."개월)";
} 


$widths  = [20, 40, 15, 25, 20, 15, 20, 35];
    
$headers = [
    '일자-No.',
    '품목명[규격]',
    '수량',
    '단가(Vat포함)',
    '공급가액',
    '부가세',
    '판매',
    '출고처'
];

$bl_id = $_GET["bl_id"];
// 내용(본문 리스트)
$_sql = ("  SELECT 
                bld_id, 
                item_nm, 
                item_qty,
                price_qty,
                price_supply,
                price_tax,
                price_total,
                item_delivery
            FROM 
                payment_billing_list_data
            WHERE 
                bl_id = '" . $bl_id . "'
            ORDER BY bld_id
");

$result = sql_query($_sql);
$data = [];

while( $row = sql_fetch_array($result) ) {
    $data[] = $row;
}

include_once(G5_LIB_PATH."/PHPExcel.php");
$excel = new PHPExcel();
foreach($widths as $i => $w) {
    $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
}
$sheet = $excel->getActiveSheet();

$data = array_merge(array($headers), $data);
$sheet->fromArray($data,NULL,'A1');

$last_col = count($headers);
$last_char = column_char($last_col-1);
$last_row = count($data);

// 테두리 처리
$styleArray = array(
    'font' => array(
        'size' => 10,
        'name' => 'Malgun Gothic'
    ),
    'borders' => array(
        'allborders' => array(
        'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    )
);

$sheet
->getStyle('A1:'.$last_char.$last_row)
->applyFromArray($styleArray);

// 헤더 배경
$header_bgcolor = 'FFD3D3D3';
$sheet
->getStyle( "A1:${last_char}1" )
->getFill()
->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
->getStartColor()
->setARGB($header_bgcolor);

// 헤더 폰트 굵기
$sheet
->getStyle( "A1:${last_char}1" )
->getFont()
->setBold(true);

// 열 높이
for($i = 0; $i <= $last_row; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(30);
}

function column_char($i) { return chr( 65 + $i ); }
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CardPayment</title>
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_reset.css">
    <link rel="stylesheet" href="<?=G5_CSS_URL;?>/payment_style.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="<?=G5_JS_URL;?>/jquery.fileDownload.js"></script>

    <style>
        /* 기본 */
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); outline: none; position: relative; }
        html, body { width: 100%; font-family: "Noto Sans KR", sans-serif; }
        body { padding: 50px 5px}
        a { text-decoration: none; color: inherit; }
        ul, li { list-style: none; }
        button { border: 0; font-family: "Noto Sans KR", sans-serif; cursor: pointer; }
        input { font-family: "Noto Sans KR", sans-serif; }

        /* 고정 상단 */
        #popupHeaderTopWrap { position: fixed; width: 100%; left: 0; top: 0; z-index: 10; background-color: #3d3781; padding: 0 20px; }
        #popupHeaderTopWrap:after { display: block; content: ''; clear: both; }
        #popupHeaderTopWrap > div { height: 100%; line-height: 40px; }
        #popupHeaderTopWrap > .title { float: left; font-weight: bold; color: #FFF; font-size: 20px; }
        #popupHeaderTopWrap > .close { float: right; }
        #popupHeaderTopWrap > .close > a { color: #FFF; font-size: 30px; top: -4px; }

        .tbl_head01  {
            padding-bottom:15px;
        }
        .tbl_head01 th {
            border-color: #0214ff;
            background: #c6cafb;
            color: #000;
            width:100px;
        }

        .tbl_head01 tr, .tbl_head01 td {
            border-color: #0214ff;
        }
    </style>

    <script type="application/javascript">
        $(function() {
            $('.close').click(function() { 
                parent.$('#BillingDetail_popup').hide();
            });
        });

        
        function Payment_Set_Billing_cancelled(id){
            if (!confirm("사업소에 청구된 내용을 취소 하시겠습니까?")) { return; }

            $.ajax({
                url: '/adm/shop_admin/ajax.payment_OnlineBilling_SetUpdate.php',
                type: 'POST',
                data: {"mode_set":'cancelled', "bl_id":id},
                dataType: 'json',
                success: function(data) {},
                error: function(e) {}

            });

            location.reload();
        }

        function ExcelDownload(id) {
            
            $('#loading_excel').show();

            excel_downloader = $.fileDownload("/shop/popup.payment_OnlineBilling_PDF.php", {
                httpMethod: "GET", data: { "bl_id":id }
            })
            .always(function() { $('#loading_excel').hide(); });
        }

        function cancelExcelDownload() {
            $('#loading_excel').hide();
        }
    </script>


    </head>
<body>


<div id="popupHeaderTopWrap">
  <div class="title">청구 내역 상세보기</div>
  <div class="close"> <a href="#"> × </a> </div>
</div>

<div class="tbl_wrap tbl_head01">
    <table>
    <tr>
        <th scope="col">사업소 이름 : </th><td colspan="3"><?=$result_bl['mb_bnm'];?></td>
        <th scope="col">사업소 코드 : </th><td><?=$result_bl['mb_thezone'];?></td>
        <th scope="col">사업소 아이디 : </th><td><?=$result_bl['mb_id'];?></td>
    </tr>
    <tr>
        <th scope="col" style="width:10%;">과세 금액 : </th><td style=""><?=number_format($result_bl['price_tax']);?></td>
        <th scope="col" style="width:10%;">면세 금액 : </th><td style="width:10%;"><?=number_format($result_bl['price_tax_free']);?></td>
        <th scope="col" style="width:10%;">청구 금액 : </th><td style="width:14%;"><?=number_format($result_bl['price_total']);?></td>
        <th scope="col" style="width:10%;">청구 품목 : </th><td style="width:14%;"><?=number_format($result->num_rows);?>건</td>
    </tr>

    <tr>
        <th scope="col">청구 아이디 : </th><td><?=$result_bl['bl_id'];?></td>
        <th scope="col">청구 상태 : </th><td><?=($result_bl['billing_yn']=="Y")?"청구완료":"청구취소";?></td>
        <th scope="col">청구 등록일 : </th><td><?=$result_bl['create_dt'];?></td>
        <th scope="col">청구 등록자 : </th><td><?=$result_bl['create_id'];?></td>
    </tr><tr>
        <th scope="col">결제 상태 : </th><td><?=$result_bl['status_locale'];?></td>
        <th scope="col">결제 방법 : </th><td><?=txt_pay_ENUM($result_bl['method_symbol']);?></td>
        <th scope="col">결제 종류 : </th><td><?=$result_bl['card_company'];?></td>
        <th scope="col">할부 구분 : </th><td><?=$result_bl['card_quota'];?></td>
    </tr><tr>
        <th scope="col">Err 코드 : </th><td><?=$result_bl['error_code'];?></td>
        <th scope="col">Err 근원지 : </th><td><?=$result_bl['error_event'];?></td>
        <th scope="col">Err 메시지 : </th><td><span style="color:#ff0000;"><?=$result_bl['error_msg'];?></span></td>
        <th scope="col">Err 발생일 : </th><td><span style="color:#ff0000;"><?=$result_bl['error_dt'];?></span></td>
    </tr>
    </table>
</div>

<div class="tbl_wrap">
    <?php if( (!$result_bl['pay_confirm_receipt_id']) && $result_bl['billing_yn']=="Y" ) { ?>
    <input type="button" id="cancelled" onclick="Payment_Set_Billing_cancelled('<?=$result_bl['bl_id'];?>');" value="청구 취소" class="btn btn_02" style="width:100px; height:35px; font-size:12px; cursor:pointer; float:right;">
    <?php } ?>
    <input type="button" onclick="ExcelDownload('<?=$result_bl['bl_id'];?>');" value="사업소 청구 리스트 PDF 다운로드" class="btn btn_03" style="width:250px; height:35px; font-size:14px; cursor:pointer; float:left;">
</div>

<br /><br />

<div style="padding-bottom:15px;">
    <?php
    ?>
    <?php        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'HTML');
        $writer->save('php://output');
    ?>
</div>

<div id="loading_excel" style="display: none;">
<div class="loading_modal">
    <p>PDF파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
</div>
</div>

<style>
body { margin: 0px; }
table { width: 100%; }

/* 로딩 팝업 */
#loading_excel { display: none; width: 100%; height: 100%; position: fixed; left: 0; top: 0; z-index: 9999; background: rgba(0, 0, 0, 0.3); }
#loading_excel .loading_modal { position: absolute; width: 400px; padding: 30px 20px; background: #fff; text-align: center; top: 50%; left: 50%; transform: translate(-50%, -50%); }
#loading_excel .loading_modal p { padding: 0; font-size: 16px; }
#loading_excel .loading_modal img { display: block; margin: 20px auto; }
#loading_excel .loading_modal button { padding: 10px 30px; font-size: 16px; border: 1px solid #ddd; border-radius: 5px; }
</style>
<script type="application/javascript">
$('tbody > tr:last').remove();
</script>

</body>
</html>