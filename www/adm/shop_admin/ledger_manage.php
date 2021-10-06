<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '수금등록';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$mb_id = $_GET['mb_id'];
if(!$mb_id)
  alert('유효하지 않은 요청입니다.');

$ent = get_member($mb_id);
if(!$ent['mb_id'])
  alert('존재하지 않는 사업소입니다.');

$manager = get_member($ent['mb_manager']);

// 총 미수금
$total_price = get_outstanding_balance($mb_id);

// 입/출금 내역
$sql_common = "
  FROM
    ledger_content l
  WHERE
    mb_id = '$mb_id'
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
        WHEN lc_type = 1
        THEN '입금'
        WHEN lc_type = 2
        THEN '출금'
      END
    ) as lc_type_txt,
    (
      SELECT mb_name from g5_member WHERE mb_id = l.lc_created_by
    ) as created_by
  {$sql_common}
  ORDER BY
    lc_id DESC
  {$sql_limit}
");

$ledger = [];
for($i = 0; $row = sql_fetch_array($ledger_result); $i++) {
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
  $ledger[] = $row;
}
?>
<div class="local_ov01 local_ov fixed">
  <h1 style="border:0;padding:5px 0;margin:0;">수금등록</h1>
  <div class="right">
    <button id="btn_list">목록</button>
  </div>
</div>
<div class="new_form">
  <div style="padding: 20px 20px;background-color: #fff;border-bottom: 1px solid #e1e2e2;">
    <h2 style="margin:0;padding:0;"><?=$ent['mb_entNm']?><?=$manager ? " ({$manager['mb_name']})" : ''?></h2>
  </div>
  <form id="form_ledger">
    <input type="hidden" name="mb_id" value="<?=$mb_id?>">
    <table class="new_form_table">
      <tbody>
        <tr>
          <th>분류</th>
          <td>
            <input type="radio" id="lc_type_1" name="lc_type" value="1" checked="checked"><label for="lc_type_1"> 입금</label>
            <input type="radio" id="lc_type_2" name="lc_type" value="2"><label for="lc_type_2"> 출금</label>
          </td>
        </tr>
        <tr>
          <th>금액</th>
          <td>
            <input type="text" name="lc_amount" value="" id="lc_amount" class="line" style="width:150px;">
          </td>
        </tr>
        <tr>
          <th>기준일</th>
          <td>
            <input type="text" name="lc_base_date" value="<?=date('Y-m-d')?>" id="lc_base_date" class="line" style="width:150px;">
          </td>
        </tr>
        <tr>
          <th>메모</th>
          <td>
            <input type="text" name="lc_memo" value="" id="lc_memo" class="frm_input" autocomplete="off" style="width:400px;">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="submit">
      <button type="submit"><span>등록</span></button>
    </div>
  </form>
</div>

<div class="tbl_head01 tbl_wrap">
  <div class="local_ov01" style="border:1px solid #e3e3e3;">
    <h1 style="border:0;padding:5px 0;margin:0;letter-spacing:0;">
      총 미수금: <?=number_format($total_price)?>원
    </h1>
  </div>

  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>일시</th>
        <th>분류</th>
        <th>금액</th>
        <th>기준일</th>
        <th>메모</th>
        <th>등록</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!$ledger) { ?>
      <tr>
        <td colspan="6" class="empty_table">자료가 없습니다.</td>
      </tr>
      <?php } ?>
      <?php foreach($ledger as $row) { ?>
      <tr>
        <td class="td_cntsmall"><?=$row['index']?></td>
        <td class="td_datetime"><?=date('Y-m-d H:i', strtotime($row['lc_created_at']))?></td>
        <td class="td_payby"><?=$row['lc_type_txt']?></td>
        <td class="td_numsum td_itopt"><?=number_format($row['lc_amount'])?></td>
        <td class="td_datetime"><?=$row['lc_base_date']?></td>
        <td><?=get_text($row['lc_memo'])?></td>
        <td class="td_payby"><?=$row['created_by']?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?page='); ?>
</div>

<script>
$(function() {
  // 목록 버튼
  $('#btn_list').click(function() {
    location.href = "<?=G5_ADMIN_URL?>/shop_admin/ledger_search.php";
  });

  // 금액 입력
  $('#lc_amount').on('input propertychange paste', function(e) {
    var input = $(this).val();
    
    input = input.replace(/[\D\s\._\-]+/g, "");
    if(input !== '') {
      input = input ? parseInt( input, 10 ) : 0;
      $(this).val(input.toLocaleString('en-US'));
    } else {
      $(this).val('');
    }
  });

  // 입금/출금 폼
  $('#form_ledger').on('submit', function(e) {
    e.preventDefault();

    var params = $(this).serialize();
    $.ajax({
      type: 'POST',
      url: './ajax.ledger.php',
      data: params,
      dataType: 'json'
    })
    .done(function(result) {
      alert('완료되었습니다.');
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
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
