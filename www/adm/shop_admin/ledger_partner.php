<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '거래처원장';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$mb_id = get_search_string($_GET['mb_id']);
if(!$mb_id)
    alert('유효하지 않은 요청입니다.');

$ent = get_member($mb_id);
if(!$ent['mb_id'])
    alert('존재하지 않는 사업소입니다.');

# 영업담당자
$manager = get_member($ent['mb_manager']);

# 파트너 서비스
if (!$mb_partner_type)
  $mb_partner_type = [];
$where_partner_type = [];
if (!$mb_partner_type_all && $mb_partner_type) {
  foreach ($mb_partner_type as $partner_type) {
    $qstr .= "mb_partner_type%5B%5D={$partner_type}&amp;";
    $where_partner_type[] = " mb_partner_type like '%$partner_type%' ";
  }
  $where[] = ' ( ' . implode(' or ', $where_partner_type) . ' ) ';
}

if ($where) {
  $sql_search = ' and '.implode(' and ', $where);
}

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');

$ledger_result = get_partner_ledger($mb_id, $fr_date, $to_date, $sel_field, $search, $sql_search);

$total_price = $ledger_result['total_price'];
$total_price_p = @round(($total_price ?: 0) / 1.1);
$total_price_s = @round(($total_price ?: 0) / 1.1 / 10);
$carried_balance = $ledger_result['carried_balance'];
$ledgers = $ledger_result['ledger'];

$total_count = count($ledgers);
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$qstr = "mb_id={$mb_id}&fr_date={$fr_date}&to_date={$to_date}&sel_field={$sel_field}&search={$search}";

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<style>
.td_price { width: 100px; }
</style>

<div class="new_form">
  <div style="padding: 20px 20px;background-color: #fff;border-bottom: 1px solid #e1e2e2;">
    <h2 style="margin:0;padding:0;"><?=$ent['mb_entNm']?><?=$manager ? " ({$manager['mb_name']})" : ''?></h2>
  </div>
  <form method="get">
    <input type="hidden" name="mb_id" value="<?=$mb_id?>">
    <table class="new_form_table">
      <tbody>
        <tr>
          <th>기간</th>
          <td>
            <input type="text" id="fr_date" class="datepicker" name="fr_date" value="<?=$fr_date?>" size="10" maxlength="10"> ~
            <input type="text" id="to_date" class="datepicker" name="to_date" value="<?=$to_date?>" size="10" maxlength="10">
            <input type="button" value="이번달" id="select_date_thismonth" name="select_date" class="select_date newbutton">
            <input type="button" value="저번달" id="select_date_lastmonth" name="select_date" class="select_date newbutton">
          </td>
        </tr>
        <tr>
          <th>서비스</th>
          <td>
            <input type="checkbox" name="mb_partner_type_all" value="1" id="chk_mb_partner_type_all" <?php if(!array_diff(['직배송', '설치', '물품공급'], $mb_partner_type)) echo 'checked'; ?>>
            <label for="chk_mb_partner_type_all">전체</label>
            <input type="checkbox" name="mb_partner_type[]" value="직배송" id="partner_type_1" class="chk_mb_partner_type" <?php if(in_array('직배송', $mb_partner_type)) echo 'checked'; ?>>
            <label for="partner_type_1">직배송</label>
            <input type="checkbox" name="mb_partner_type[]" value="설치" id="partner_type_2" class="chk_mb_partner_type" <?php if(in_array('설치', $mb_partner_type)) echo 'checked'; ?>>
            <label for="partner_type_2">설치</label>
            <input type="checkbox" name="mb_partner_type[]" value="물품공급" id="partner_type_3" class="chk_mb_partner_type" <?php if(in_array('물품공급', $mb_partner_type)) echo 'checked'; ?>>
            <label for="partner_type_3">물품공급</label>
          </td>
        </tr>
        <tr>
          <th>검색어</th>
          <td>
            <select name="sel_field" id="sel_field">
              <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
              <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>품목명</option>
              <option value="mb_entNm" <?=get_selected($sel_field, 'mb_entNm')?>>사업소명</option>
            </select>
            <input type="text" name="search" value="<?=$search?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="submit">
      <button type="submit" id="search-btn"><span>검색</span></button>
    </div>
  </form>
</div>
<div class="tbl_head01 tbl_wrap">
  <div class="local_ov01" style="border:1px solid #e3e3e3;">
    <h1 style="border:0;padding:5px 0;margin:0;letter-spacing:0;">
      <?php $entNm = $ent['mb_entNm'] ?: $ent['mb_giup_bname'] ?: $ent['mb_name']; ?>
      [<?=$entNm?>] 판매액 합계: <?=number_format($total_price)?>원 (공급가:<?=number_format($total_price_p)?>원, VAT:<?=number_format($total_price_s)?>원)
    </h1>
    <div class="right">
      <button id="btn_ledger_excel"><img src="<?=G5_ADMIN_URL?>/shop_admin/img/btn_img_ex.gif">엑셀다운로드</button>
      <button id="btn_ledger_manage">결제관리</button>
    </div>
  </div>
  <table>
    <thead>
      <tr>
        <th>주문일</th>
        <th>주문번호</th>
        <th style="width: 115px;">서비스</th>
        <th>주문자</th>
        <th>영업담당자</th>
        <th>품목명</th>
        <th>수량</th>
        <th>단가(VAT포함)</th>
        <th>공급가액</th>
        <th>부가세</th>
        <th>판매</th>
        <th>결제</th>
        <th>잔액</th>
        <th>배송지</th>
      </tr>
    </thead>
    <tbody>
      <?php if($page == 1 && $carried_balance && !($sel_field && $search)) { ?>
      <tr>
        <td class="td_date"><?=date('y-m-d', strtotime($fr_date))?></td>
        <td class="td_odrnum2"></td>
        <td><?php echo str_replace("|", ", ", $row['mb_partner_type']); ?></td>
        <td class="td_id"></td>
        <td class="td_payby"></td>
        <td>이월잔액</td>
        <td class="td_numsmall"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"></td>
        <td class="td_price"><?=number_format($carried_balance)?></td>
        <td class="td_id"></td>
      </tr>
      <?php } ?>
      <?php
      for($i = $from_record; $i < ($from_record + $page_rows); $i++) {
        if(!isset($ledgers[$i])) break;
        $row = $ledgers[$i];
        if($row['it_name'] == '입금') $row['it_name'] = '결제';
        if($row['it_name'] == '환수') $row['it_name'] = '회수';
      ?>
      <tr>
        <td class="td_date"><?=date('y-m-d', strtotime($row['od_time']))?></td>
        <td class="td_odrnum2">
          <?php if($row['od_id']) { ?>
          <a href="<?=G5_ADMIN_URL?>/shop_admin/samhwa_orderform.php?od_id=<?=$row['od_id']?>"><?=$row['od_id']?></a>
          <?php } ?>
        </td>
        <td><?php echo str_replace("|", ", ", $row['mb_partner_type']); ?></td>
        <td class="td_id"><?=$row['mb_entNm']?></td>
        <td class="td_payby"><?=$manager['mb_name']?></td>
        <td><?=$row['it_name']?><?=$row['ct_option'] && $row['ct_option'] != $row['it_name'] ? "({$row['ct_option']})" : ''?></td>
        <td class="td_numsmall"><?=$row['ct_qty']?></td>
        <td class="td_price"><?=number_format($row['price_d'])?></td>
        <td class="td_price"><?=number_format($row['price_d_p'])?></td>
        <td class="td_price"><?=number_format($row['price_d_s'])?></td>
        <td class="td_price"><?=number_format($row['sales'])?></td>
        <td class="td_price"><?=number_format($row['deposit'])?></td>
        <td class="td_price"><?=number_format($row['balance'])?></td>
        <td class="td_id"><?=$row['od_b_name']?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&page='); ?>
</div>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
  // 엑셀다운로드 버튼
  $('#btn_ledger_excel').click(function() {
    location.href = "<?=G5_ADMIN_URL?>/shop_admin/ledger_excel.php?<?=$qstr?>";
  });
  // 수금관리 버튼
  $('#btn_ledger_manage').click(function() {
    location.href = "<?=G5_ADMIN_URL?>/shop_admin/ledger_manage.php?mb_id=<?=$mb_id?>";
  });

  // 기간 - datepicker
  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99",
    maxDate: "+0d"
  });

  // 기간 - 이번달 버튼
  $('#select_date_thismonth').click(function() {
    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 이번달 1일
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 저번달 버튼
  $('#select_date_lastmonth').click(function() {
    var today = new Date();
    today.setDate(0); // 지난달 마지막일
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 지난달 1일
    $('#fr_date').val(formatDate(today));
  });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
