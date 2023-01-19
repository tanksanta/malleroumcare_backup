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
    /* // 파일명 : /www/adm/shop_admin/popup.payment_OnlineBilling_ExcelUpload.php */
    /* // 파일 설명 :   온라인 결제(관리자화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  대금 청구서를 위해 업로드된 엑셀 파일을 분석하여 각 항목별 DB 저정 하는 페이지 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

$sub_menu = '500150';
include_once("./_common.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// 22.12.28 : 서원 - 파일명이 있는지 여부
if( !$_FILES['excelfile']['tmp_name'] ) { alert_close('파일을 읽을 수 없습니다.'); }


// 22.12.28 : 서원 - 확장자 엑셀파일 체크
$file_ext = pathinfo(iconv("UTF-8", "EUC-KR", $_FILES['excelfile']['name']));
if( $file_ext['extension'] != "xlsx" ) { alert_close('엑셀 파일만 업로드 가능 합니다.\n확장자 xlsx만 가능 합니다.'); }
else if( $_FILES['excelfile']['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ) { alert_close('엑셀 파일만 업로드 가능 합니다.\n업로드 파일의 형식을 확인하여주시기 바랍니다.'); }


// 23.01.19 : 서원 - 동일 파일명 중복 업로드 방지.
$sql = (" SELECT COUNT(*) as cnt
          FROM payment_billing_list 
          WHERE billing_uploadfile_nm = '" . $_FILES['excelfile']['name'] . "'
      ");
$_sql = sql_fetch($sql);
if( $_sql['cnt'] >= 1 ) { alert_close('[중복업로드체크]\n업로드가 되었던 파일 입니다.\n파일명과 내용을 확인하시기 바랍니다.\n\n파일명: '.$_FILES['excelfile']['name']); }


// 22.12.28 : 서원 - 엑셀 파일 읽기.
$file = iconv("UTF-8", "EUC-KR", $_FILES['excelfile']['tmp_name']);
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);


// 22.12.28 : 서원 - 엑셀 파일의 내영이 있는 경우.
if($sheetData) {

  // 데이터의 row 수
  $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');


  // 22.12.28 : 서원 - 엑셀파일 형식 체크 (필드명이 다르거나 없을 경우 모두 리턴.)    
  if( addslashes($sheetData[2]['A']) != "일자-No." ||
      addslashes($sheetData[2]['B']) != "품목명[규격]" ||
      addslashes($sheetData[2]['C']) != "수량" ||
      addslashes($sheetData[2]['D']) != "단가(Vat포함)" ||
      addslashes($sheetData[2]['E']) != "공급가액" ||
      addslashes($sheetData[2]['F']) != "부가세" ||
      addslashes($sheetData[2]['G']) != "판매" ||
      addslashes($sheetData[2]['H']) != "출고처" ||
      addslashes($sheetData[2]['I']) != "거래처코드"
  ) { alert_close('엑셀 파일 내부 데이터 형식이 옳바르지 않습니다.'); }
  else if( $num_rows <= 3 ) { alert_close('엑셀 파일 내부 데이터의 형식이 옳바르지 않거나 부족합니다.'); }


  $_price = [];
  // 22.12.28 : 서원 - 금액 계산을 위한 Loop 처리
  for( $i = 3; $i <= $num_rows; $i++ ) {
    if( !addslashes($sheetData[$i]['A']) || !addslashes($sheetData[$i]['B']) || !addslashes($sheetData[$i]['I']) ) { continue; }

    // 부가세 여부 확인
    if( !addslashes($sheetData[$i]['F']) ) {
      // 부가세 제외대상 금액
      $_price[addslashes($sheetData[$i]['I'])]['supply_free'] += (int)preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['G']));
    } else {
      // 부가세 적용 대상 금액
      $_price[addslashes($sheetData[$i]['I'])]['supply'] += (int)preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['G']));
    }
    // 판매 금액 전체 합산
    $_price[addslashes($sheetData[$i]['I'])]['total'] += (int)preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['G']));
  }


  // 22.12.28 : 서원 - 동일 데이터 입력 여부 확인용 변수
  $_check = $_overlap = 0;
  $_bl_id = $_thezone = "";


  $sql_bl_id = $sql_bl = $sql_bld = []; // 정상 데이터 처리 배열
  $_error_list = []; // 오류 데이터 처리 배열
  $_ecount_title = "";


  // 22.12.28 : 서원 - 엑셀 엑셀데이터 Loop
  for( $i = 0; $i <= $num_rows; $i++ ) {


    // 22.12.28 : 서원 - 거래처코드( 1개의 파일에 여러 사업소 정보가 있을 경우 체크값)
    if( $_thezone != addslashes($sheetData[$i]['I']) ) {
      $_thezone = addslashes($sheetData[$i]['I']);
      $_check = $_overlap = 0;
    }


    // 파일 내용중 1개 업체이상 데이터가 있을 경우 타이틀 추출 (엑셀 연결데이터용)
    if( !$_check ) {      
      if(strpos(addslashes($sheetData[$i]['A']), "회사명 : ") !== false) {
        $_ecount_title = addslashes($sheetData[$i]['A']);
      }
    }


    // 22.12.28 : 서원 - row단위 값이 데이터가 아닌 거나, 필수 데이터가 없을 경우 패스~
    if( !addslashes($sheetData[$i]['A']) || 
        !addslashes($sheetData[$i]['B']) ||
        addslashes($sheetData[$i]['I']) == "거래처코드" ||
        addslashes($sheetData[$i]['A']) == "일자-No." ||
        addslashes($sheetData[$i]['B']) == "품목명[규격]"
    ) { continue; }
    

    // 22.12.28 : 서원 - 동일 엑셀 데이터 중복 여부 입력 확인
    if( !$_check ) {
      
      // 22.12.28 : 서원 - 업로드 년/월 기준 업로드 데이터가 있는지 확인.
      $sql = "";
      $sql = ("  SELECT 
                      COUNT(*) as cnt, mb_bnm
                    FROM 
                      payment_billing_list 
                    WHERE 
                      mb_thezone = '" . addslashes($sheetData[$i]['I']) . "'
                      AND billing_yn = 'Y'
                      AND YEAR(create_dt) = YEAR(CURRENT_DATE()) 
                      AND MONTH(create_dt) = MONTH(CURRENT_DATE())
                      AND pay_confirm_id IS NULL
                      AND pay_confirm_dt IS NULL
      ");      
      $_sql = sql_fetch($sql);

      // 검색 데이터 유/무 확인
      if( $_sql['cnt'] > 0 ) {

        $_error_list[addslashes($sheetData[$i]['I'])] = $_sql['mb_bnm'];
        continue;

      } else {

        $sql = "";
        $sql = (" SELECT 
                    mb_id, mb_name, mb_giup_bname, mb_thezone
                  FROM 
                    g5_member 
                  WHERE 
                    mb_thezone = '" . addslashes($sheetData[$i]['I']) . "'
        ");
        $_sql = sql_fetch($sql);

        // 검색 데이터 유/무 확인
        if( $_sql['mb_id'] && $_sql['mb_name'] && $_sql['mb_giup_bname'] && $_sql['mb_thezone'] ) {
          
          if( !isset($sql_bl[$_sql['mb_thezone']]) ) {
            $sql_bl_id[$_sql['mb_thezone']] = $_bl_id = "Billing_" . $_sql['mb_id'] . "_" . date("ymdHis");
            $sql_bl[addslashes($sheetData[$i]['I'])] = ("  INSERT `payment_billing_list`
                                          SET   `bl_id`                 = '" . $sql_bl_id[$_sql['mb_thezone']] . "',  /* 빌링 아이디 */
                                                `mb_id`                 = '" . $_sql['mb_id'] . "',  /* 사업소 아이디 */
                                                `billing_ecount_title`  = '" . $_ecount_title . "',  /* 사업소 명칭*/                                                
                                                `billing_uploadfile_nm` = '" . $_FILES['excelfile']['name'] . "',  /* 사업소 명칭*/
                                                `mb_bnm`                = '" . $_sql['mb_giup_bname'] . "',  /* 사업소 명칭*/
                                                `mb_thezone`            = '" . $_sql['mb_thezone'] . "',  /* 사업소 코드 */
                                                `price_tax`             = '" . ( ($_price[addslashes($sheetData[$i]['I'])]['supply'])?($_price[addslashes($sheetData[$i]['I'])]['supply']):("0") ) . "',  /* 부가세 대상 금액 */
                                                `price_tax_free`        = '" . ( ($_price[addslashes($sheetData[$i]['I'])]['supply_free'])?($_price[addslashes($sheetData[$i]['I'])]['supply_free']):("0") ) . "',  /* 부가세 제외 금액 */
                                                `price_total`           = '" . ( ($_price[addslashes($sheetData[$i]['I'])]['total'])?($_price[addslashes($sheetData[$i]['I'])]['total']):("0") ) . "',  /* 전체 금액 */
                                                `create_id`             = '" . $member["mb_id"] . "'   /* 빌링 생성 아이디(관리자) */
                                      ");
          }

        } else { $_error_list[addslashes($sheetData[$i]['I'])] = "* 정보없음 (DB에 존재하지 않음)"; $_overlap = 1; }
        
      }

      $_check = 1;
    }

    // 대금 청구가 가능한 사업소일 경우 청구 리스트 저장
    if( $_check && !$_overlap ) { 

      // 청구 리스트 데이터 SQL 배열 저장을 위한 선언문
      if( !is_array($sql_bld[addslashes($sheetData[$i]['I'])]) )
        $sql_bld[addslashes($sheetData[$i]['I'])] = array();

      $sql_bld[addslashes($sheetData[$i]['I'])][] = (" INSERT `payment_billing_list_data`
                      SET	`bl_id`         = '" . $sql_bl_id[$_sql['mb_thezone']] . "',  /* 빌링 아이디 */
                          `mb_thezone`    = '" . addslashes($sheetData[$i]['I']) . "', /* 거래처 코드 */
                          `bld_id`        = '" . addslashes($sheetData[$i]['A']) . "', /* 일자-No */
                          `item_nm`       = '" . addslashes($sheetData[$i]['B']) . "', /* 품목명[규격] */
                          `item_qty`      = '" . ( (addslashes($sheetData[$i]['C']))?(preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['C']))):("0") ) . "', /* 수량 */
                          `price_qty`     = '" . ( (addslashes($sheetData[$i]['D']))?(preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['D']))):("0") ) . "', /* 단가(vat포함) */
                          `price_supply`  = '" . ( (addslashes($sheetData[$i]['E']))?(preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['E']))):("0") ) . "', /* 공급가액 */
                          `price_tax`     = '" . ( (addslashes($sheetData[$i]['F']))?(preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['F']))):("0") ) . "', /* 부가세 */
                          `price_total`   = '" . ( (addslashes($sheetData[$i]['G']))?(preg_replace("/[^-0-9]*/s", "", addslashes($sheetData[$i]['G']))):("0") ) . "', /* 판매 */
                          `item_delivery` = '" . addslashes($sheetData[$i]['H']) . "' /* 출고처 */
                  ");
    
    }
    

    //23.01.19 : 서원 - 업로드 데이터중... 직전월 데이터가 아닌 전전월 또는 당월 데이터가 있을 경우 해당 데이터 업로드 금지.
    //                  해당 기능은 전원 데이터만 업로드 하여 결제하는 방식으로 고정 요청.
    //                   업로드 되는 데이터의 일자에서 전월 이외 텍스트가 있을 경우 업로드 금지.
    $_mon = explode( "/", substr(addslashes($sheetData[$i]['A']),0,8) )[1];
    if( date("m", mktime(0, 0, 0, date("m")-1, 1)) != $_mon ) {
      $_error_list[addslashes($sheetData[$i]['I'])] = date("m", mktime(0, 0, 0, date("m")-1, 1))."월청구만가능<br/>('".substr(addslashes($sheetData[$i]['A']),0,8)."'포함됨)";
      $sql_bl[addslashes($sheetData[$i]['I'])] = $sql_bld[addslashes($sheetData[$i]['I'])] = "";
      $_overlap = 1;
    }


  }
  
  //var_dump($sql_bl);
  //var_dump($sql_bld);
  //var_dump($_error_list);

  
  // 23.01.02 : 서원 - 트랜잭션 시작
  sql_query("START TRANSACTION");

  try {
    
    // 청구 데이터 SQL 실행
    if( is_array($sql_bl) ) {
      foreach($sql_bl as $sql) { sql_query($sql); }
    }

    // 청구 리스트 SQL 실행
    if( is_array($sql_bld) ) {
      foreach($sql_bld as $val) {

        if( is_array($val) ) {
          foreach($val as $sql) { sql_query($sql); }
        }

      }
    }

    // 23.01.02 : 서원 - 트랜잭션 커밋
    sql_query("COMMIT");

  } catch (Exception $e) {
    // 23.01.02 : 서원 - 트랜잭션 롤백
    sql_query("ROLLBACK");
  }

} else {
  alert('파일을 읽을 수 없습니다.',FALSE);
}

?>

<?php if( COUNT($_error_list) > 0 ) { ?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>대금 청구 엑셀파일 업로드</title>
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/payment_reset.css">
  <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/payment_style.css">
  <!-- fontawesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
  <div class="visual">
    <div class="visalWrap">
      <!-- title -->
      <div class="headerTitle">
        <h5>중복 청구 사업소 리스트</h5>
      </div>

        <!-- contents -->
        <div class="contentsWrap">

          <div class="priceListWrap">
            <div class="listTitle">
              <p>* 이미 청구가된 중복청구 사업소가 있습니다.</p>
              <p>* 리스트와 업로드된 엑셀 파일을 확인해주세요.</p>
            </div>

            <table>
              <tr>                
                <th style="width:50%;">사업소코드</th>
                <th>비고</th>
              </tr>
            </table>
            <div style="width:100%; height:228px; overflow:auto">
            <table >
              <?php foreach ($_error_list as $key => $val) { ?>
              <tr>
                <td style="width:50%;"><?=$key;?></td>
                <td><?=$_error_list[$key];?></td>                
              </tr>
              <?php } ?>
            </table>
            </div>
          </div>

        </div>

        <div class="okBtn" onclick="opener.location.reload();window.close();">닫기</div>


    </div>
  </div>

<style>
  .visalWrap {height: 100%;}
  .contentsWrap {height: 376px;}
  .listTitle {padding-bottom: 20px;}
</style>

</body>
</html>
<?php } else if( COUNT($_error_list) == 0 ) { ?>
<script language="javascript">
  alert("온라인 결제용 대금 청구서 업로드가 완료되었습니다.\n리스트에서 업로드된 사업소 청구 금액을 확인하여주시기 바랍니다.");
  opener.location.reload();
  window.close();
</script>
<?php } else { ?>
<?php } ?>