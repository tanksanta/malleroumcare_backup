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


// 페이지 상단(속도측정)
//$start_time = array_sum(explode(' ', microtime()));

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/*
*
* 작성자 : 박서원
* 작성일자 : 2023-01-31
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2023-01-31
* 설명 : 관리자가 업로드 한 1개의 파일을 사업소 별로 데이터 분리 작업.
* @param string $_filenm : 업로드 원본파일이름
* @param string $_thezone : 사업소 코드
* @param string $_title : 문서 타이틀 array
* @param string $_data : 문서 ROW 데이터 array
* @param string $_managerID : 파일 업로드 관리자 아이디
* @return boolean 
* 
* 1개의 청구 파일을 사업소별 데이터 분리 하여 콤마(,)를 분리값으로 하는 csv파일 생성.
*
*/
function billing_outputFile_Sort( $_filenm, $_thezone, $_title, $_data, $_managerID ) {

    // 23.01.31 : 서원 - 엑셀 내용 세부 항목 분리 저장용 폴더 존재 확인
    $_dir_path = G5_DATA_PATH . '/billing/' . date("Ym") . "_sort/";
    if (!is_dir($_dir_path)) { mkdir($_dir_path, 0777, true); }

    // 23.01.31 : 서원 - 엑셀 ROW 데이터 기준 $sheetData[$i]['A'] 키값으로 내용 라인별 저장.
    $_file_path = $_dir_path . $_filenm . "_" . $_thezone . "_" . $_managerID . ".csv";
    
    if( !file_exists($_file_path) ) {
      $fp = fopen($_file_path, 'w');
      fwrite($fp,
        
        iconv("UTF-8", "EUC-KR", 
            "'" . str_replace(",", "", addslashes($_title['A']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['B']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['C']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['D']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['E']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['F']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['G']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['H']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['I']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['J']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['K']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['L']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['M']) ) . "', " .
            "'" . str_replace(",", "", addslashes($_title['N']) ) ) . chr(13) . chr(10)
      );

    } else { $fp = fopen($_file_path, 'a+'); }

    fwrite($fp,
      iconv("UTF-8", "EUC-KR",  
        "'" . str_replace(",", "", addslashes($_data['A']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['B']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['C']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['D']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['E']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['F']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['G']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['H']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['I']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['J']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['K']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['L']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['M']) ) . "', " .
        "'" . str_replace(",", "", addslashes($_data['N']) ) ) . "'" . chr(13) . chr(10)
    );


    fclose($fp);

}



/*
*
* 작성자 : 박서원
* 작성일자 : 2023-01-31
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2023-01-31
* 설명 : 엑셀 파일을 읽어 데이터를 array로 리턴 한다.
* @param string $_file : 엑셀 파일 경로
* @return array() 
*
*/
function billing_outputFile_excel_read( $_file )
{
    $excel_sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_file);
    $excel_Data = $excel_sheet->getSheet(0)->toArray(null, true, true, true);
    unset($excel_sheet);

    return $excel_Data;
}


/*
*
* 작성자 : 박서원
* 작성일자 : 2023-01-31
* 마지막 수정자 : 박서원
* 마지막 수정일자 : 2023-01-31
* 설명 : 엑셀 파일에 개별 ROW 데이터를 저장 한다.
* @param string $_filenm : 파일명
* @param string $_data : ROW 데이터
* @param string $_managerID : 관리자 아이디
* @return boolean
*
*/
function billing_outputFile_excel_write( $_filenm, $_data, $_managerID )
{

  include_once(G5_LIB_PATH."/PHPExcel.php");
  $excel = new PHPExcel();
  $sheet = $excel->getActiveSheet();


  // 23.01.31 : 서원 - 엑셀 내용 세부 항목 분리 저장용 폴더 존재 확인
  $_dir_path = G5_DATA_PATH . '/billing_upload/' . date("Y") . '/' . date("m") . '/separation/';
  if (!is_dir($_dir_path)) { mkdir($_dir_path, 0777, true); }

  // 23.01.31 : 서원 - 엑셀 ROW 데이터 기준 $sheetData[$i]['A'] 키값으로 내용 라인별 저장.
  $_filenm = explode('.', $_filenm)[0];
  $_file_path = $_dir_path . $_filenm . "_" . trim(addslashes($_data[0]['A'])) . "_" . $_managerID . ".xlsx";


  $data = [];
  $headers = array( '거래처코드', '거래처명', '품목코드', '품목그룹2명', '품목명[규격]', '단가(vat포함)', '수량', '공급가액', '부가세', '합계', '창고명', '담당자명', '담당자명', '일자' );
  $data = array_merge(array($headers), $data);

  // 23.01.31 : 서원 - 기존 생성된 엑셀 파일 생성 유/무 확인
  /*
  // 엑셀 파일을 오픈 할때 사용되는 라이브러리를 자주 사용하거나 많은 파일을 열고 닫을 경우 메모리 반환이 정상적으로 되지 않아 메모리 오버플 현상으로 멈춤.
  // 엑셀 파일 생성시 ROW단위가 아닌 DATA 묶음으로 한번에 생성 해야 함. (파일에 직접 ROW로 쓰는건 문제가 있음.)
  if( file_exists( $_file_path ) ) { 
    $data = billing_outputFile_excel_read( $_file_path );
  } else {
    $headers = array( '거래처코드', '거래처명', '품목코드', '품목그룹2명', '품목명[규격]', '단가(vat포함)', '수량', '공급가액', '부가세', '합계', '창고명', '담당자명', '담당자명', '일자' );
    $data = array_merge(array($headers), $data);
  }
  */


  $data = array_merge_recursive($data, $_data);
  $sheet->fromArray($data);


	// Excel2007 포맷으로 저장
  $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

  // 서버에 파일을 쓰지 않고 바로 다운로드
  $writer->save( $_file_path );

  unset($excel);
  unset($writer);
  unset($data);
}



// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = 
// 프로세스 시작
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =


// 22.12.28 : 서원 - 파일명이 있는지 여부
if( !$_FILES['excelfile']['tmp_name'] ) { alert_close('파일을 읽을 수 없습니다.'); }
if( !file_exists($_FILES['excelfile']['tmp_name']) ) { alert_close('파일을 읽을 수 없습니다.'); }


// 23.01.31 : 서원 - 업로드 원본 파일 보관용 폴더 존재 확인
$_dir_path = G5_DATA_PATH . '/billing_upload/' . date("Y") . '/' . date("m") . '/original/';
if (!is_dir($_dir_path)) { mkdir($_dir_path, 0777, true); }


// 22.12.28 : 서원 - 확장자 엑셀파일 체크
$file_ext = pathinfo($_FILES['excelfile']['name']);
if( $file_ext['extension'] != "xlsx" ) { alert_close('엑셀 파일만 업로드 가능 합니다.\n확장자 xlsx만 가능 합니다.'); }
else if( $_FILES['excelfile']['type'] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ) { alert_close('엑셀 파일만 업로드 가능 합니다.\n업로드 파일의 형식을 확인하여주시기 바랍니다.'); }


// 파일명 저장
$_fileName = $_FILES['excelfile']['name'];


// 23.01.31 : 서원 - 기존 파일 업로드 되어있는지 확인.
if( !file_exists($_dir_path . $_FILES['excelfile']['name']) ) { 

  // 23.01.31 : 서원 - 업로드 원본 파일 서버 변도 저장. (추후 파일 오류 검증)  
  copy( $_FILES['excelfile']['tmp_name'] , $_dir_path . $_fileName );
} else {

  // 23.01.31 : 서원 - pathinfo에서 파일명 일부가 사라지는 현상이 있어서 해당 파일명에서 직접 잘라 사용.
  $_fileName = explode('.', $_FILES['excelfile']['name'])[0];
  $_fileName .= "_over_" . date("ymdHis"). "." . $file_ext['extension'];

  // 23.01.31 : 서원 - 업로드 원본 파일 서버 변도 저장. (추후 파일 오류 검증)
  copy( $_FILES['excelfile']['tmp_name'] , $_dir_path . $_fileName );
}


// 23.01.19 : 서원 - 동일 파일명 중복 업로드 방지.
$sql = (" SELECT COUNT(*) as cnt
          FROM payment_billing_list 
          WHERE `billing_uploadfile_nm` = '" . $_FILES['excelfile']['name'] . "'
                AND YEAR(create_dt) = YEAR(CURRENT_DATE()) 
                AND MONTH(create_dt) = MONTH(CURRENT_DATE())
      ");
$_sql = sql_fetch($sql);
if( $_sql['cnt'] >= 1 ) { alert_close('[중복업로드체크]\n업로드가 되었던 파일 입니다.\n파일명과 내용을 확인하시기 바랍니다.\n\n파일명: '.$_FILES['excelfile']['name']); }


// 22.12.28 : 서원 - 엑셀 파일 읽기.
$file = iconv("UTF-8", "EUC-KR", $_FILES['excelfile']['tmp_name']);
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
$sheetData = $spreadsheet->getSheet(0)->toArray(null, true, true, true);


// 22.12.28 : 서원 - 엑셀 파일의 내영이 있는 경우.
if($sheetData) {

  
  // 엑셀 데이터의 row 수
  $num_rows = $spreadsheet->getSheet(0)->getHighestDataRow('A');


  // 22.12.28 : 서원 - 엑셀파일 형식 체크 (필드명이 다르거나 없을 경우 모두 리턴.)    
  if( trim( trim(addslashes($sheetData[2]['A'])) ) != "거래처코드" ||
      trim( trim(addslashes($sheetData[2]['B'])) ) != "거래처명" ||

      trim( trim(addslashes($sheetData[2]['E'])) ) != "품목명[규격]" ||
      trim( trim(addslashes($sheetData[2]['F'])) ) != "단가(vat포함)" ||
      trim( trim(addslashes($sheetData[2]['G'])) ) != "수량" || 
      trim( trim(addslashes($sheetData[2]['H'])) ) != "공급가액" || 
      trim( trim(addslashes($sheetData[2]['I'])) ) != "부가세" || 
      trim( trim(addslashes($sheetData[2]['J'])) ) != "합계" || 

      trim( trim(addslashes($sheetData[2]['N'])) ) != "일자"

  ) { 
    alert_close('엑셀 파일 내부 데이터 형식이 옳바르지 않습니다.');
    exit();
  }
  else if( $num_rows <= 4 ) { 
    alert_close('엑셀 파일 내부 데이터의 형식이 옳바르지 않거나 부족합니다.');
    exit();
  }


  // 가격 데이터 처리 배열
  $_price = []; 

  // 대금 청구 타이틀
  $_billing_title = "";

  // 빌링 아이디
  $_billing_id = [];
  
  // 빌링 청구월
  $_billing_month = "";

  // 사업소 코드
  $_thezone = "";


  $_sql_bl = [];  // 정상 데이터 청구 데이터 SQL 쿼리문 저장 배열.
  $_sql_bld = []; // 정상 데이터 청구 세부 항목 SQL 쿼리문 저장 배열.
  $_error_list = []; // 오류 데이터 처리 배열.
  $_excel_dl =[]; // 엑셀 데이터 저장을 위한 사업소별 원본 ROW데이터 분리 저장 배열.


  // 22.12.28 : 서원 - 엑셀 ROW 데이터 Loop 처리
  for( $i = 3; $i <= $num_rows; $i++ ) {
  
    // 청구중인 중복 데이터 체크
    $_overlap_ck = false;

    // 23.02.01 : 서원 - 필수 값인 사업소 코드와 사업소 명칭이 없을 경우 청구 항목 데이터로 추출하지 않는다.
    if( !trim(addslashes($sheetData[$i]['A'])) || !trim(addslashes($sheetData[$i]['B'])) ) { continue; }


    // 23.02.01 : 서원 - 사업소 배열 데이터가 있는 경우 머지 하며, 없을 경우 배열을 생성 한다.
    if( $_excel_dl[trim(addslashes($sheetData[$i]['A']))] ) { 
      $_excel_dl[trim(addslashes($sheetData[$i]['A']))] = array_merge_recursive($_excel_dl[trim(addslashes($sheetData[$i]['A']))], array($sheetData[$i]));
    } else {
      $_excel_dl[trim(addslashes($sheetData[$i]['A']))] = array($sheetData[$i]);
    }


    // 일자, 품목명, 수량, 거래처코드가 없을 경우 continue;
    if( !trim(addslashes($sheetData[$i]['E'])) || !trim(addslashes($sheetData[$i]['F'])) || !trim(addslashes($sheetData[$i]['G'])) || !trim(addslashes($sheetData[$i]['N'])) ) { continue; }
    
        
    // 사업소 코드가 모두 숫자 임으로 해당 필드의 데이터가 숫자가 아닌 경우 continue; 
    if( (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['A']))) <= 0 ) continue;      


    // 부가세 여부 확인
    if( (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['I']))) == 0 ) {
        // 부가세 제외대상 금액
        $_price[trim(addslashes($sheetData[$i]['A']))]['supply_free'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['H'])));
        
        // 판매 금액 전체 합산
        $_price[trim(addslashes($sheetData[$i]['A']))]['total'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['H'])));
    } else {
        // 부가세 적용 대상 금액
        $_price[trim(addslashes($sheetData[$i]['A']))]['supply'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['H'])));
        $_price[trim(addslashes($sheetData[$i]['A']))]['supply'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['I'])));
        
        // 판매 금액 전체 합산
        $_price[trim(addslashes($sheetData[$i]['A']))]['total'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['H'])));
        $_price[trim(addslashes($sheetData[$i]['A']))]['total'] += (int)preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['I'])));
    }


    // 22.12.28 : 서원 - 거래처코드( 1개의 파일에 여러 사업소 정보가 있을 경우 체크값)
    /*
    if( $_thezone_ck ) {
      $_thezone = trim(addslashes($sheetData[$i]['K']));
      $_thezone_ck = false;

      $_billing_id = "billing_" . $_thezone . "_" . date("ymdHis");
    } else {
      if( $_thezone != trim(addslashes($sheetData[$i]['K'])) ) {
        json_response(400, '1곳 이상의 사업소 데이터 존재 합니다.\n\n파일명: ' . $_FILES['files']['name'][$key]);

        $sql_bl = array_diff( $sql_bl, array($_thezone, trim(addslashes($sheetData[$i]['K']))) );
        $sql_bld = array_diff( $sql_bld, array($_thezone, trim(addslashes($sheetData[$i]['K']))) );

        $_overlap_ck = false;
        break;
      }
    }
    */


    //23.01.19 : 서원 - 업로드 데이터중... 직전월 데이터가 아닌 전전월 또는 당월 데이터가 있을 경우 해당 데이터 업로드 금지.
    //                  해당 기능은 전원 데이터만 업로드 하여 결제하는 방식으로 고정 요청.
    //                   업로드 되는 데이터의 일자에서 전월 이외 텍스트가 있을 경우 업로드 금지.
    $_billing_month = explode( "/", substr(trim(addslashes($sheetData[$i]['N'])),0,8) )[1];
    if( date("m", mktime(0, 0, 0, date("m")-1, 1)) != $_billing_month ) {
      $_error_list[trim(addslashes($sheetData[$i]['A']))] = trim(addslashes($sheetData[$i]['B'])) ."<br/>* ". date("m", mktime(0, 0, 0, date("m")-1, 1))."월 청구만 가능('".trim(addslashes($sheetData[$i]['N']))."'포함됨)";
      $_overlap_ck = false;
    }


    // 23.01.31 : 서원 - 사업소 데이터에 문제가 있을 경우 SQL 데이터를 만들지 않음.
    if( $_error_list[trim(addslashes($sheetData[$i]['A']))] ) { 
      $sql_bl[trim(addslashes($sheetData[$i]['A']))] = $sql_bld[trim(addslashes($sheetData[$i]['A']))] = "";
      unset( $sql_bl[ trim(addslashes($sheetData[$i]['A'])) ] );
      unset( $sql_bld[ trim(addslashes($sheetData[$i]['A'])) ] );
      $_overlap_ck = false;
      continue;
    }


    // 22.12.28 : 서원 - 업로드 년/월 기준 업로드 데이터가 있는지 확인.
    if( $_overlap_ck === false ) {


      // 23.02.01 : 서원 - 중복 데이터 조건 검색
      $_sql = sql_fetch(" SELECT COUNT(*) as cnt, mb_bnm
                          FROM payment_billing_list 
                          WHERE 
                          `mb_thezone` = '" . trim(addslashes($sheetData[$i]['A'])) . "'
                          AND `billing_month` = '" . $_billing_month . "'
                          AND `billing_yn` = 'Y'
                          AND YEAR(create_dt) = YEAR(CURRENT_DATE()) 
                          AND MONTH(create_dt) = MONTH(CURRENT_DATE())
                          AND ( `pay_confirm_id` IS NULL OR `pay_confirm_id` = '' )
                          AND ( `pay_confirm_dt` IS NULL OR `pay_confirm_dt` = '' )
      ");      
        

      // 검색 데이터 유/무 확인
      if( $_sql['cnt'] ) {
        
        // 23.02.01 : 서원 - 청구중인 데이터가 있을 경우 입력하지 않음!!!(이외 변경 된 내용이 있는지 확인 체크.)
        $_error_list[trim(addslashes($sheetData[$i]['A']))] = trim(addslashes($sheetData[$i]['B'])) ."<br/>* 기존 청구중 데이터 존재";
        $sql_bl[trim(addslashes($sheetData[$i]['A']))] = $sql_bld[trim(addslashes($sheetData[$i]['A']))] = "";          
        unset( $sql_bl[ trim(addslashes($sheetData[$i]['A'])) ] );
        unset( $sql_bld[ trim(addslashes($sheetData[$i]['A'])) ] );
        $_overlap_ck = false;
        continue;

      } else {
        $_overlap_ck = true;
      }

    }


    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==
    // 기존 입력 데이터가 없거나 이미 청구된 데이터가 결제 완료 되었을 경우 신규 청구서(쿼리문) 생성.
    // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == ==


    // 기존 데이타가 존재하는 경우 처리
    if( $_overlap_ck === true ) {


      // 회원 DB 사업소 검색
      $_sql = sql_fetch(" SELECT mb_id, mb_name, mb_giup_bname, mb_thezone
                          FROM g5_member 
                          WHERE mb_thezone = '" . trim(addslashes($sheetData[$i]['A'])) . "'
      ");


      // 22.12.28 : 서원 - 거래처코드( 1개의 파일에 여러 사업소 정보가 있을 경우 체크값)
      if( $_thezone != trim(addslashes($sheetData[$i]['A'])) ) {
        $_thezone = trim(addslashes($sheetData[$i]['A']));
        
        // 기존 빌링 아이디가 없을 경우 빌링 아이디 생성.
        if( !$_billing_id[ $_sql['mb_thezone'] ] ) {
          $_billing_id[ $_sql['mb_thezone'] ] = "billing_" . $_thezone . "_" . date("ymdHis");
        }
      }

      // 회원 정보가 있는 경우
      if( $_sql['mb_id'] ) {

        // 청구 리스트 데이터 SQL 배열 저장을 위한 선언문
        if( isset($sql_bl[ $_thezone ]) ) {
          $sql_bl[ $_thezone ] = "";
        }
        
        if( !$sql_bl[$_thezone] ) {

          // 사업소 청구 정보 생성
          // 금액의 경우 치환자로 임의 대입하여 전달하며, 최종 SQL 실행 직전 치환자를 금액 배열에서 찾아 치환 한다.
          $sql_bl[$_thezone] = (" INSERT `payment_billing_list`
                                  SET   `bl_id`                 = '" . $_billing_id[$_thezone] . "',
                                        `mb_id`                 = '" . $_sql['mb_id'] . "',
                                        `billing_uploadfile_nm` = '" . $_fileName . "',
                                        `billing_month`         = '" . $_billing_month . "',
                                        `billing_fee`           = '" . json_decode( $default['de_paymenet_billing_OnOff'], TRUE )['fee_card'] . "',
                                        `mb_bnm`                = '" . $_sql['mb_giup_bname'] . "',
                                        `mb_thezone`            = '" . $_sql['mb_thezone'] . "',
                                        `price_tax`             = '##price_tax##',
                                        `price_tax_free`        = '##price_tax_free##',
                                        `price_total`           = '##price_total##',
                                        `create_id`             = '" . $member["mb_id"] . "' 
                                        /* sql end */
                                ");
        }
      } else {
        $_error_list[$_thezone] = trim(addslashes($sheetData[$i]['B'])) . "<br/>* 정보없음 (DB에 존재하지 않음)";
      }


      // 청구 데이터가 있는 사업소의 세부적인 청구 항목을 넣는다.
      if( $sql_bl[ $_thezone ] ) {


        // 청구 리스트 데이터 SQL 배열 저장을 위한 선언문
        if( !$sql_bld[ $_thezone ] )
          $sql_bld[ $_thezone ] = [];
        

        // 데이터 리스트 정보 작성
        $sql_bld[ $_thezone ][]= (" INSERT `payment_billing_list_data`
                                    SET	
                                        `bl_id`             = '" . $_billing_id[$_thezone] . "',
                                        `mb_thezone`        = '" . $_thezone . "',
                                        `item_dt`           = '" . trim(addslashes($sheetData[$i]['N'])) . "',
                                        `item_nm`           = '" . trim(addslashes($sheetData[$i]['E'])) . "',
                                        `item_qty`          = '" . ( (trim(addslashes($sheetData[$i]['G'])))?(preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['G'])))):("0") ) . "',
                                        `price_qty`         = '" . ( (trim(addslashes($sheetData[$i]['F'])))?(preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['F'])))):("0") ) . "',
                                        `price_supply`      = '" . ( (trim(addslashes($sheetData[$i]['H'])))?(preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['H'])))):("0") ) . "',
                                        `price_tax`         = '" . ( (trim(addslashes($sheetData[$i]['I'])))?(preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['I'])))):("0") ) . "',
                                        `price_total`       = '" . ( (trim(addslashes($sheetData[$i]['J'])))?(preg_replace("/[^-0-9]*/s", "", trim(addslashes($sheetData[$i]['J'])))):("0") ) . "';
                                ");

      }

    }

    // for 프로세스 중복 체크 값 리셋
    $_overlap_ck = false;
  }


  // 페이지 하단(속도측정)
  //$end_time = array_sum(explode(' ', microtime()));
  //echo "1-TIME : ". ( $end_time - $start_time ) . "<br /><br />";


  // 23.01.02 : 서원 - 트랜잭션 시작
  sql_query("START TRANSACTION");

  try {

    // 청구 데이터 SQL 실행
    if( is_array($sql_bl) ) {
      foreach($sql_bl as $key => $sql) {

        // 최종 합계금액을 SQL INSERT 직전에 텍스트 치환하여 저장.
        $sql = str_replace("##price_tax##", $_price[$key]["supply"], $sql);
        $sql = str_replace("##price_tax_free##", $_price[$key]["supply_free"], $sql);
        $sql = str_replace("##price_total##", $_price[$key]["total"], $sql);

        // 최종 결제 금액이 마이너스일 경우 청구 진행 불가.
        if( $_price[$key]["total"] <= 0 ) {
          $sql = str_replace("/* sql end */", ", billing_yn='N', error_code='cancel', error_event='system', error_msg='카드결제 불가능한 청구 금액', error_dt=NOW()", $sql);
        }

        //var_dump($sql);
        sql_query($sql);       
      }
    }

    // 페이지 하단(속도측정)
    //$end_time = array_sum(explode(' ', microtime()));
    //echo "2-TIME : ". ( $end_time - $start_time ) . "<br /><br />";

    // 청구 리스트 SQL 실행
    if( is_array($sql_bld) ) {
      foreach($sql_bld as $val) {

        if( is_array($val) ) { 
          foreach($val as $sql) { 
            //var_dump($sql);
            sql_query($sql);
          }
        }
        
      }

      // 페이지 하단(속도측정)
      //$end_time = array_sum(explode(' ', microtime()));
      //echo "3-TIME : ". ( $end_time - $start_time ) . "<br /><br />";
    }

    // 23.01.02 : 서원 - 트랜잭션 커밋
    sql_query("COMMIT");

  } catch (Exception $e) {
    // 23.01.02 : 서원 - 트랜잭션 롤백
    sql_query("ROLLBACK");
  }


  // 페이지 하단(속도측정)
  //$end_time = array_sum(explode(' ', microtime()));
  //echo "4-TIME : ". ( $end_time - $start_time ) . "<br /><br />";


  // 23.02.01 : 서원 - 위 프로세스가 모두 종료 되고, 사업소별 엑셀 파일 생성
  foreach( $_excel_dl as $key => $val ) {
    // 23.01.31 : 서원 - 사업소별 파일 생성
    //billing_outputFile_Sort( $_fileName, trim(addslashes($sheetData[$i]['A'])), $sheetData[2], $sheetData[$i], $member["mb_id"] );
    //var_dump( $val );
    billing_outputFile_excel_write( $_fileName, $val, $member["mb_id"] );
  }


  // 페이지 하단(속도측정)
  //$end_time = array_sum(explode(' ', microtime()));
  //echo "5-TIME : ". ( $end_time - $start_time ) . "<br /><br />";


  //var_dump($sql_bl);
  //var_dump($sql_bld);
  //var_dump($_error_list);
  //var_dump($_excel_dl);


}

if( COUNT($_error_list) > 0 ) { ?>

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
      <div class="headerTitle"><h5>청구서 업로드 실패 사업소 리스트</h5></div>

        <!-- contents -->
        <div class="contentsWrap_result">

          <div class="priceListWrap">

            <li>전체: <?=number_format(COUNT($sql_bl)+COUNT($_error_list));?>사업소 | <span id="success">성공: <?=number_format(COUNT($sql_bl));?>사업소</span> | <span id="fail">실패: <?=number_format(COUNT($_error_list));?>사업소</span></li>

            <div class="listTitle">
              <li>* 청구서 업로드에 실패된 사업소가 있습니다.</li>
              <li>* 리스트와 업로드된 엑셀 파일을 확인해주세요.</li>
            </div>

            <table>
              <tr>                
                <th style="width:32%;">사업소코드</th>
                <th>비고</th>
              </tr>
            </table>
            <div style="width:100%; height:260px; overflow:auto">
            <table >
              <?php foreach ($_error_list as $key => $val) { ?>
              <tr>
                <td style="width:32%;"><?=$key;?></td>
                <td><?=$_error_list[$key];?></td>                
              </tr>
              <?php } ?>
            </table>
            </div>
          </div>

        </div>

        <div class="okBtn" onclick="opener.location.reload(); window.close();">닫기</div>


    </div>
  </div>

<style>
  .visalWrap { height: 100%; }
  .contentsWrap_result { padding: 10px 20px; }
  .listTitle { padding-bottom: 10px; }
  .priceListWrap > li { font-size: 16px; padding-bottom: 10px; }
  .priceListWrap > li #success { font-weight: bold; color: blue; }
  .priceListWrap > li #fail { font-weight: bold; color: red; }
</style>

</body>
</html>
<?php } else if( COUNT($_error_list) == 0 ) { ?>
<script language="javascript">
  alert("온라인 결제용 대금 청구서 업로드가 완료되었습니다.\n리스트에서 업로드된 사업소 청구 금액을 확인하여주시기 바랍니다.");
  opener.location.reload();
  window.close();
</script>
<?php } else { } ?>