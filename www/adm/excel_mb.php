<?php

	include_once("./_common.php");
	include_once(G5_LIB_PATH."/PHPExcel.php");

    $sql ="select * from `g5_member` where (`mb_level` ='3' or `mb_level` ='4') and mb_type != 'manager' order by `mb_datetime` desc";
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

    $headers = array("영업담당자","아이디","이름필수","닉네임","회원 권한","전화번호","휴대폰번호","팩스번호","에미일(세금계산서 수신용)필수)","기업명","대표자명","사업자번호","업태","업종","담당자명","사업소 우편번호","사업소 기본주소","사업소 상세주소","세금계산서이메일","고객(거래처)코드","배송지 우편번호","배송지 주소","배송지 상세주소","회원가입일","최근접속일");
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