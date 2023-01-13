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
    /* // 파일명 : /www/shop/popup.payment_OnlineBilling_Excel.php */
    /* // 파일 설명 :   온라인 결제(사업소화면) */
    /*                  대금청구 관련된 파일은 "payment_OnlineBilling" 네임을 포함하는 파일명을 사용한다. */
    /*                  청구내역 PDF 파일 다운로드 */
    /* // == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == == */

	include_once("./_common.php");
    
    $options = array(
        'orientation'=>'Landscape',
        'javascript-delay' => 500,
        'page-size' => 'A4',
        'encoding' => 'UTF-8',
        'margin-top'    => '25px',
        'margin-right'  => '10px',
        'margin-bottom' => '10px',
        'margin-left'   => '10px',
        'dpi' => 100,
    );
    
    $args = '';
    foreach($options as $key => $val) {
        if(is_int($key)) {
            $key = $val;
            $val = null;
        }
        
        $args .= ' --'.$key;
        if($val !== null) $args .= ' '.$val;
    }

    $G5_URL = G5_URL;    
    $bl_id = $_GET["bl_id"];


    // 23.01.03 : 서원 - 윈도우 테스트용
    //$_file_path = G5_DATA_PATH."\\cache\\".$_GET["bl_id"].".pdf";
    //exec("C:\_THKC\_Dev\wkhtmltox\bin\wkhtmltopdf.exe{$args} \"{$G5_URL}/shop/popup.payment_OnlineBilling_HTML.php?bl_id={$bl_id}\" \"{$_file_path}\"");

    
    // 23.01.03 : 서원 - 리눅스용
    $_file_path = G5_DATA_PATH."/cache/".$_GET["bl_id"].".pdf";
    exec("wkhtmltopdf{$args} \"{$G5_URL}/shop/popup.payment_OnlineBilling_HTML.php?bl_id={$bl_id}\" \"{$_file_path}\"");


    @readfile($_file_path);
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"정산내역서_".$member['mb_id']."_".date("Ym").".pdf\"");
    header("Content-Disposition: attachment; filename=\"정산내역서_".$member['mb_id']."_".date("Ym").".pdf\"");
    header("Cache-Control: max-age=0");
    header('Set-Cookie: fileDownload=true; path=/');

    @unlink($_file_path);
?>