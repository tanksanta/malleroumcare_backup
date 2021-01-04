<?php
$sub_menu = "200100";
include_once('./_common.php');

set_time_limit(0);
ini_set('memory_limit', '10000M');

// 회원정보 가져오기
$mbs = array();
if (is_array($mb_ids)) {
    foreach ($mb_ids as $mb_id) {
        $sql = "SELECT * FROM g5_member WHERE mb_id = '{$mb_id}'";
        $mb = sql_fetch($sql);
        array_push($mbs, $mb);
    }
}else{
    // 전체 불러오기
    // $sql = "SELECT * FROM g5_member ORDER BY mb_no DESC";
    // $result = sql_query($sql);
    // while ($row = sql_fetch_array($result)) {
    //     array_push($mbs, $row);
    // }

    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_datetime) ) $fr_datetime = '';
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_datetime) ) $to_datetime = '';
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_updatedatetime) ) $fr_updatedatetime = '';
    if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_updatedatetime) ) $to_updatedatetime = '';

    $sql_common = " from {$g5['member_table']} as a LEFT JOIN g5_member_giup_manager as b ON a.mb_id = b.mb_id ";

    $sql_search = " where (1) ";
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
            default :
                $sql_search .= " ({$sfl} like '{$stx}%') ";
                break;
        }
        $sql_search .= " ) ";
    }


    if ($fr_datetime && $to_datetime) {
        $sql_search .= " and ( mb_datetime between '$fr_datetime 00:00:00' and '$to_datetime 23:59:59' )";
    }

    if ($fr_updatedatetime && $to_updatedatetime) {
        $sql_search .= " and ( mb_update_date between '$fr_updatedatetime 00:00:00' and '$to_updatedatetime 23:59:59' )";
    }

    if ($_GET['mb_level']) {
        $sql_search .= " and ( ";

        $mb_level = (int)$_GET['mb_level'];

        $sql_search .= " (mb_level like '%{$mb_level}') ";

        $sql_search .= " ) ";
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

    $sql = " select * {$sql_common} {$sql_search} {$sql_order} ";
    $result = sql_query($sql);

    while ($row = sql_fetch_array($result)) {
        array_push($mbs, $row);
    }

}

// 기업 매니저 정보 가져오기
// for ($i=0; $i<count($mbs); $i++) {
//     $sql = "SELECT * FROM `g5_member_giup_manager` WHERE mb_id = '{$mbs[$i]['mb_id']}'";
//     $result = sql_query($sql);
//     while ($row = sql_fetch_array($result)) {
//         $mbs[$i]['managers'][] = $row;
//     }
// }
$today	= date("YmdHis");

Header("Content-type: application/vnd.ms-excel");
Header("Content-type: charset=utf-8");
header("Content-Disposition: attachment; filename=member_{$today}.xls");
Header("Content-Description: PHP3 Generated Data");
Header("Pragma: no-cache");
Header("Expires: 0");

echo "<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel;charset=utf-8\">";

$str	= "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"1\">";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>회사코드</th>
                <th>거래처코드</th>
                <th>거래처명</th>
                <th>거래처명 약칭</th>
                <th>거래처 구분</th>

                <th>사업자등록번호</th>
                <th>주민등록번호</th>
                <th>대표자명</th>
                <th>업태</th>
                <th>종목</th>

                <th>우편번호</th>
                <th>주소 상세1</th>
                <th>주소 상세2</th>
                <th>휴대폰번호</th>
                <th>팩스</th>

                <th>이메일</th>
                <th>홈페이지 주소</th>
                <th>거래시작일</th>

                <th>수정일</th>
                <th>신규구분</th>

                <th>거래종료일</th>
                <th>사용여부</th>


                <th>지로코드</th>
                <th>예금주명</th>
                <th>예금 계좌번호</th>
                <th>지급예정일</th>
                <th>거래처분류</th>

                <th>프로젝트코드</th>
                <th>종사업장번호</th>
                <th>단위신고거래처</th>
                <th>고객담당그룹코드</th>
                <th>고객담당자부서명</th>

                <th>고객담당자직급</th>
                <th>고객담당자담당업무</th>
                <th>고객담당자담당사원</th>
                <th>고객담당자전화번호</th>
                <th>고객담당자내선번호</th>
                
                <th>고객담당자핸드폰번호</th>
                <th>고객담당자이메일주소</th>
            </tr>";
$str	.= "<tr bgcolor=\"#eeeeee\">
                <th>CO_CD</th>
                <th>TR_CD</th>
                <th>TR_NM</th>
                <th>ATTR_NM</th>
                <th>TR_FG</th>

                <th>REG_NB</th>
                <th>PPL_NB</th>
                <th>CEO_NM</th>
                <th>BUSINESS</th>
                <th>JONGMOK</th>

                <th>ZIP</th>
                <th>DIV_ADDR1</th>
                <th>ADDR2</th>
                <th>TEL</th>
                <th>FAX</th>

                <th>EMAIL</th>
                <th>HOMEPAGE</th>
                <th>INTER_DT</th>
                
                <th></th>
                <th></th>

                <th>DUE_DT</th>
                <th>USE_YN</th>


                <th>JIRO_CD</th>
                <th>DEPOSITOR</th>
                <th>BA_NB</th>
                <th>DOUDATE1_DD</th>
                <th>TRGRP_CD</th>

                <th>PDT_CD</th>
                <th>APPR_NB</th>
                <th>REPTR_CD</th>
                <th>EMPGRP_CD</th>
                <th>TRCHARGE_DEPT</th>

                <th>TRCHARGE_TITLE</th>
                <th>TRCHARGE_JOP</th>
                <th>TRCHARGE_EMP</th>
                <th>TRCHARGE_TEL</th>
                <th>TRCHARGE_EXT</th>
                
                <th>TRCHARGE_HP</th>
                <th>TRCHARGE_EMAIL</th>
            </tr>";

foreach ($mbs as $mb) {
    
    // $mb['open_date'] = str_replace('-', '', $mb['mb_open_date']);
    $mb['open_date'] = $mb['mb_open_date'] != '0000-00-00' ? str_replace('-', '', $mb['mb_open_date']) : '';
    $mb['update_date'] = substr(str_replace('-', '', $mb['mb_update_date']), 0, 8);
    $mb['leave_date'] = str_replace('-', '', $mb['mb_leave_date']);
    $mb['jiro'] = substr($mb['open_date'], 0, 6) != '000000' ? substr($mb['open_date'], 0, 6) : '';
    $mb['mb_giup_bnum'] = str_replace('-', '', $mb['mb_giup_bnum']);

    $tr_nm = $mb['mb_giup_bname'] ? $mb['mb_giup_bname'] : $mb['mb_name'];
    $email = $mb['mb_giup_tax_email'] ? $mb['mb_giup_tax_email'] : $mb['mb_email'];

    $mb_addr1 = $mb['mb_giup_addr1'] ? $mb['mb_giup_addr1'] : $mb['mb_addr1'];
    $mb_addr2 = $mb['mb_giup_addr2'] ? $mb['mb_giup_addr2'] : $mb['mb_addr2'];

    $str	.= "<tr>
                <td align='center'>3000</td>
                <td align='center' style=\"mso-number-format:'\@';\">".$mb['mb_thezone']."</td>
                <td align='center'>".$tr_nm."</td>
                <td align='center'></td>
                <td align='center'>1</td>

                <td align='center'>".$mb['mb_giup_bnum']."</td>
                <td align='center'></td>
                <td align='center'>".$mb['mb_giup_boss_name']."</td>
                <td align='center'>".$mb['mb_giup_buptae']."</td>
                <td align='center'>".$mb['mb_giup_bupjong']."</td>

                <td align='center' style=\"mso-number-format:'\@';\">".$mb['mb_zip1'] . $mb['mb_zip2']."</td>
                <td align='center'>".$mb_addr1."</td>
                <td align='center'>".$mb_addr2."</td>
                <td align='center'>".$mb['mb_hp']."</td>
                <td align='center'>".$mb['mb_fax']."</td>

                <td align='center'>".$email."</td>
                <td align='center'>".$mb['mb_homepage']."</td>
                <td align='center'>".$mb['open_date']."</td>

                <td align='center'>".$mb['update_date']."</td>
                <td align='center'>". ($mb['update_date'] == $mb['open_date'] ? '신규' : '변경' )."</td>

                <td align='center'>".$mb['leave_date']."</td>
                <td align='center'>1</td>

                <td align='center'>".$mb['jiro']."</td>
                <td align='center'></td>
                <td align='center'></td>
                <td align='center'></td>
                <td align='center'></td>

                <td align='center'></td>
                <td align='center' style=\"mso-number-format:'\@';\"></td>
                <td align='center' style=\"mso-number-format:'\@';\"></td>
                <td align='center'>100</td>
                <td align='center'>".$mb['mm_part']."</td>

                <td align='center'>".$mb['mm_rank']."</td>
                <td align='center'>".$mb['mm_work']."</td>
                <td align='center'>".$mb['mm_name']."</td>
                <td align='center' style=\"mso-number-format:'\@';\">".$mb['mm_hp']."</td>
                <td align='center'>".$mb['mm_hp_extension']."</td>

                <td align='center' style=\"mso-number-format:'\@';\">".$mb['mm_tel']."</td>
                <td align='center'>".$mb['mb_email']."</td>
            </tr>";
}
//<td align='center'>".$mb['mm_email']."</td>

$str	.= "</table>";
echo $str;

?>