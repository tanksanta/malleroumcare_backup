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

<style>
  .search_form td,.search_form th { border:1px solid #e3e3e3 !important;}
  .anchor_active { background:#383838 !important;color:#fff; border-color: #383838 !important; }
  .anchor a { padding : 10px 20px; }
  #search-btn { background:#383838;color:#fff; padding:0 20px; height: 33px; margin:0 10px;}
</style>

<ul class="anchor">
    <li><a <?php echo $type=='login'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=login&<?php echo $query_string ?>">로그인</a></li>
    <li><a <?php echo $type=='order'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=order&<?php echo $query_string ?>">주문서</a></li>
    <li><a <?php echo $type=='eform'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=eform&<?php echo $query_string ?>">계약서</a></li>
    <li><a <?php echo $type=='item_msg'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=item_msg&<?php echo $query_string ?>">제안서</a></li>
    <li><a <?php echo $type=='check_itcare'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=check_itcare&<?php echo $query_string ?>">요양정보조회</a></li>

    <li><a <?php echo $type=='first_order'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=first_order&<?php echo $query_string ?>">첫 주문</a></li>
    <li><a <?php echo $type=='first_contract'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=first_contract&<?php echo $query_string ?>">첫 계약</a></li>
    <li><a <?php echo $type=='first_hit'?'class="anchor_active"':""; ?> href="./service_log_management.php?type=first_hit&<?php echo $query_string ?>">첫 조회</a></li>
</ul>
<script>
function fvisit_submit(act)
{
    var f = document.fvisit;
    f.action = act;
    f.submit();
}
</script>
