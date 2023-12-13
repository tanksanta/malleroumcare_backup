<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/visit.lib.php');
include_once('./admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if (empty($fr_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = G5_TIME_YMD;
if (empty($to_date) || ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = G5_TIME_YMD;

$qstr = "fr_date=".$fr_date."&amp;to_date=".$to_date;
$query_string = $qstr ? $qstr : '';
?>

<form name="fvisit" id="fvisit" class="local_sch03 local_sch" method="get">
<input type="hidden" name="type" value="<?=$type?>">
<div class="sch_last">
    <strong>기간별검색</strong>
    <input type="text" name="fr_date" value="<?php echo $fr_date ?>" id="fr_date" class="frm_input" size="11" maxlength="10">
    <label for="fr_date" class="sound_only">시작일</label>
    ~
    <input type="text" name="to_date" value="<?php echo $to_date ?>" id="to_date" class="frm_input" size="11" maxlength="10">
    <label for="to_date" class="sound_only">종료일</label>
    <input type="submit" value="검색" class="btn_submit">
    <button id="download_excel">엑셀다운로드</button>
</div>
</form>

<ul class="anchor">
    <li><a href="./user_statistics.php?type=user&<?php echo $query_string ?>">회원등급</a></li>
    <li><a href="./user_statistics.php?type=region&<?php echo $query_string ?>">사업소지역</a></li>
    <li><a href="./user_statistics.php?type=amount&<?php echo $query_string ?>">매출금액</a></li>
    <li><a href="./user_statistics.php?type=proposal_c&<?php echo $query_string ?>">제안서 생성</a></li>
    <li><a href="./user_statistics.php?type=proposal_s&<?php echo $query_string ?>">제안서 발송</a></li>
    <li><a href="./user_statistics.php?type=contract_c&<?php echo $query_string ?>">계약서 생성</a></li>
    <li><a href="./user_statistics.php?type=contract_s&<?php echo $query_string ?>">계약서 서명</a></li>
    <li><a href="./user_statistics.php?type=order_c&<?php echo $query_string ?>">주문서 생성</a></li>
    <li><a href="./user_statistics.php?type=order_user&<?php echo $query_string ?>">주문서 생성(사업소별)</a></li>
    <li><a href="./user_statistics.php?type=login_daily&<?php echo $query_string ?>">방문자집계(일자별)</a></li>
    <li><a href="./user_statistics.php?type=login_user&<?php echo $query_string ?>">방문자집계(사업소별)</a></li>
    <li><a href="./user_statistics.php?type=recipient&<?php echo $query_string ?>">등록한 수급자</a></li>
    <li><a href="./user_statistics.php?type=inquire_data&page=all&<?php echo $query_string ?>">요양정보 조회 집계(전체사업소)</a></li>
    <li><a href="./user_statistics.php?type=matchingservice&<?php echo $query_string ?>">매칭서비스</a></li>
<!--
    <li><a href="./user_statistics.php?type=inquire_data&page=ent&<?php echo $query_string ?>">요양정보 조회 집계(사업소별)</a></li>
    <li><a href="./user_statistics.php?type=inquire_data&page=date&<?php echo $query_string ?>">요양정보 조회 집계(일자별)</a></li>
-->
</ul>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });
});

function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
