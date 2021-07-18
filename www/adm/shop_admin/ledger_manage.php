<?php
$sub_menu = '400460';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g5['title'] = '수금등록';
include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="new_form">
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
          <input type="text" name="price" value="" class="line" style="width:80px">
        </td>
      </tr>
      <tr>
        <th>메모</th>
        <td>
          <input type="text" name="memo" value="" id="memo" class="frm_input" autocomplete="off" style="width:200px;">
        </td>
      </tr>
    </tbody>
  </table>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
