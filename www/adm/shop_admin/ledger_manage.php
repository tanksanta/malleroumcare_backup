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
  <table class="new_form_table">
    <tbody>
      <tr>
        <th>분류</th>
        <td>
          <input type="radio" id="ct_is_direct_delivery_all" name="ct_is_direct_delivery" value="" checked="checked"><label for="ct_is_direct_delivery_all"> 입금</label>
          <input type="radio" id="ct_is_direct_delivery_1" name="ct_is_direct_delivery" value="1"><label for="ct_is_direct_delivery_1"> 출금</label>
        </td>
      </tr>
      <tr>
        <th>금액</th>
        <td>
          <input type="text" name="price" value="" class="line" style="width:150px">
        </td>
      </tr>
      <tr>
        <th>메모</th>
        <td>
          <input type="text" name="memo" value="" id="memo" class="frm_input" autocomplete="off" style="width:400px;">
        </td>
      </tr>
    </tbody>
  </table>
  <div class="submit">
    <button type="submit"><span>등록</span></button>
  </div>
</div>

<div class="tbl_head01 tbl_wrap">
  <div class="local_ov01" style="border:1px solid #e3e3e3;">
    <h1 style="border:0;padding:5px 0;margin:0;letter-spacing:0;">
      총 미수금: <?=number_format($total_price)?>원 (공급가:<?=number_format((int)($total_price / 1.1))?>원, VAT:<?=number_format($total_price - (int)($total_price / 1.1))?>원)
    </h1>
  </div>
</div>

<script>
$(function() {
  // 목록 버튼
  $('#btn_list').click(function() {
    location.href = "<?=G5_ADMIN_URL?>/shop_admin/ledger_search.php";
  });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
