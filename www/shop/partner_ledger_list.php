<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$g5['title'] = "파트너 거래처원장";
include_once("./_head.php");

# 기간
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if(!$fr_date)
  $fr_date = date('Y-m-01');
if(!$to_date)
  $to_date = date('Y-m-d');

$ledger_result = get_partner_ledger($member['mb_id'], $fr_date, $to_date, $sel_field, $search);
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

$qstr = "fr_date={$fr_date}&to_date={$to_date}&sel_field={$sel_field}&search={$search}";
?>

<section class="wrap">
  <div class="sub_section_tit">거래처원장</div>
  <form method="get">
    <div class="search_box">
       <div class="search_date">
      	
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="sel_field">
        <option value="mb_entNm" <?=get_selected($sel_field, 'mb_entNm')?>>사업소명</option>
        <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>품목명</option>
        <option value="od_id" <?=get_selected($sel_field, 'od_id')?>>주문번호</option>
      </select>
      <div class="input_search">
        <input name="search" value="<?=$_GET["search"]?>" type="text">
        <button type="submit"></button>
      </div>
    </div>
  </form>
  <div class="inner">
    <div class="list_box">
      <div class="subtit">
        검색 기간 내 구매액 : <?=number_format($total_price)?>원 <span>(공급가 : <?=number_format($total_price_p)?>원, VAT : <?=number_format($total_price_s)?>원)</span>

        <div class="r_area">
          <a href="partner_ledger_excel.php?<?=$qstr?>" class="btn_green_box">엑셀다운로드</a>
          <a href="partner_ledger_manage.php" class="btn_gray_box">수금등록</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>일시</th>
              <th>주문번호</th>
              <th>사업소</th>
              <th>품목명</th>
              <th>수량</th>
              <th>공급가액</th>
              <th>부가세</th>
              <th>합계</th>
              <th>수금</th>
              <th>잔액</th>
            </tr>
          </thead>
          <tbody>
            <?php if($page == 1 && $carried_balance && !($sel_field && $search) && !$price) { ?>
            <tr>
              <td class="text_r">-</td>
              <td class="text_c" colspan="6">이월잔액</td>
              <td class="text_r">-</td>
              <td class="text_r">-</td>
              <td class="text_r"><?=number_format($carried_balance)?></td>
            </tr>
            <?php } ?>
            <?php
            for($i = $from_record; $i < ($from_record + $page_rows); $i++) {
              if(!isset($ledgers[$i])) break;
              $row = $ledgers[$i];
              
              if(!$row['od_id']) {
                // 입금 or 출금
            ?>
            <tr>
              <td class="text_r"><?=date('y-m-d', strtotime($row['od_time']))?></td>
              <td class="text_c" colspan="6"><?=$row['it_name']?><?=$row['ct_option'] && $row['ct_option'] != $row['it_name'] ? "({$row['ct_option']})" : ''?></td>
              <td class="text_r"><?=number_format($row['price_d'])?></td>
              <td class="text_r"><?=number_format($row['deposit'])?></td>
              <td class="text_r"><?=number_format($row['balance'])?></td>
            </tr>
            <?php
              } else {
            ?>
            <tr>
              <td class="text_c"><?=date('y-m-d', strtotime($row['od_time']))?></td>
              <td class="text_c"><?=$row['od_id']?></td>
              <td class="text_c"><?=$row['mb_entNm']?></td>
              <td><?=$row['it_name']?><?=$row['ct_option'] && $row['ct_option'] != $row['it_name'] ? "({$row['ct_option']})" : ''?></td>
              <td class="text_c"><?=$row['ct_qty']?></td>
              <td class="text_r"><?=number_format(@round(($row['sales'] ?: 0) / 1.1))?></td>
              <td class="text_r"><?=number_format(@round(($row['sales'] ?: 0) / 1.1 / 10))?></td>
              <td class="text_r"><?=number_format($row['sales'])?></td>
              <td class="text_r"><?=number_format($row['deposit'])?></td>
              <td class="text_r"><?=number_format($row['balance'])?></td>
            </tr>
            <?php
              }
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">  
          <?php echo apms_paging(5, $page, $total_page, '?'.$qstr.'&page='); ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
  // 기간 - 이번달 버튼
  $('#select_date_thismonth').click(function(e) {
    e.preventDefault();

    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 이번달 1일
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 저번달 버튼
  $('#select_date_lastmonth').click(function(e) {
    e.preventDefault();

    var today = new Date();
    today.setDate(0); // 지난달 마지막일
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 지난달 1일
    $('#fr_date').val(formatDate(today));
  });
});
</script>

<?php
include_once('./_tail.php');
?>
