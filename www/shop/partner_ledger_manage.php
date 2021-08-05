<?php
include_once('./_common.php');

if(!$is_samhwa_partner)
  alert('파트너 회원만 접근가능합니다.');

$g5['title'] = "파트너 수금등록";
include_once("./_head.php");

// 총 미수금
$total_price = get_partner_outstanding_balance($member['mb_id']);

// 입/출금 내역
$sql_common = "
  FROM
    partner_ledger l
  WHERE
    mb_id = '{$member['mb_id']}'
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$ledger_result = sql_query("
  SELECT
    l.*,
    (
      CASE
        WHEN pl_type = 1
        THEN '입금'
        WHEN pl_type = 2
        THEN '환수'
      END
    ) as pl_type_txt
  {$sql_common}
  ORDER BY
    pl_id DESC
  {$sql_limit}
");

$ledger = [];
for($i = 0; $row = sql_fetch_array($ledger_result); $i++) {
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
  $ledger[] = $row;
}
?>

<style>
  .wrap { position: relative; }
  .wrap > .r_btn_area { position: absolute; right: 0; top: 20px; }
</style>

<section class="wrap">
  <div class="sub_section_tit">수금등록</div>
  <div class="r_btn_area">
    <a href="javascript:void(0)" id="btn_partner_ledger" class="btn eroumcare_btn2">등록</a>
    <a href="partner_ledger_list.php" class="btn eroumcare_btn2">취소</a>
  </div>
  <form id="form_partner_ledger" class="form-horizontal">
    <div class="panel panel-default">
      <div class="panel-body">

        <div class="form-group">
          <label class="col-sm-2 control-label">
            <b>분류</b>
          </label>
          <div class="col-sm-3">
            <label class="checkbox-inline">
              <input type="radio" name="pl_type" value="1" style="vertical-align: middle; margin: 0 5px 0 0;" checked="checked">입금
            </label>
            <label class="checkbox-inline">
              <input type="radio" name="pl_type" value="2" style="vertical-align: middle; margin: 0 5px 0 0;">환수
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">
            <b>금액</b>
          </label>
          <div class="col-sm-3">
            <input type="text" id="pl_amount" name="pl_amount" value="" class="form-control input-sm" style="width:150px;">
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">
            <b>메모</b>
          </label>
          <div class="col-sm-3">
            <input type="text" name="pl_memo" value="" class="form-control input-sm">
          </div>
        </div>

      </div>
    </div>
  </form>

  <div class="list_box">
    <div class="subtit">
      총 미수금 : <?=number_format($total_price)?>원
    </div>

    <div class="table_box">
      <table>
        <thead>
          <tr>
            <th>No.</th>
            <th>일시</th>
            <th>분류</th>
            <th>금액</th>
            <th>메모</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!$ledger) { ?>
          <tr>
            <td colspan="5" class="empty_table">자료가 없습니다.</td>
          </tr>
          <?php } ?>
          <?php foreach($ledger as $row) { ?>
          <tr>
            <td class="td_cntsmall"><?=$row['index']?></td>
            <td class="td_datetime"><?=date('Y-m-d H:i', strtotime($row['pl_created_at']))?></td>
            <td class="td_payby"><?=$row['pl_type_txt']?></td>
            <td class="td_numsum td_itopt"><?=number_format($row['pl_amount'])?></td>
            <td><?=get_text($row['pl_memo'])?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
$(function() {
  // 금액 입력
  $('#pl_amount').on('input propertychange paste', function(e) {
    var input = $(this).val();
    
    input = input.replace(/[\D\s\._\-]+/g, "");
    if(input !== '') {
      input = input ? parseInt( input, 10 ) : 0;
      $(this).val(input.toLocaleString('en-US'));
    } else {
      $(this).val('');
    }
  });

  // 수금등록
  $('#btn_partner_ledger').click(function() {
    $('#form_partner_ledger').submit();
  });
  $('#form_partner_ledger').on('submit', function(e) {
    e.preventDefault();

    $.post('ajax.partner_ledger.php', $(this).serialize(), 'json')
    .done(function() {
      alert('등록이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>

<?php
include_once('./_tail.php');
?>
