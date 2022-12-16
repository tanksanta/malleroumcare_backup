<?php
$sub_menu = '400620';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$it_id_arr = $_POST['it_id'];
$it_name_arr = $_POST['it_name'];
$io_id_arr = $_POST['io_id'];
$qty_arr = $_POST['qty'];
$wh_name_arr = $_POST['wh_name'];
$ws_memo_arr = $_POST['ws_memo'];

$insert_sql = "
    insert into warehouse_stock
    (
        it_id,
        io_id,
        io_type,
        it_name,
        ws_option,
        ws_qty,
        mb_id,
        ws_memo,
        wh_name,
        inserted_from,
        ws_created_at,
        ws_updated_at
    )
    values
";

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $it_name = clean_xss_tags($it_name_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $qty = clean_xss_tags($qty_arr[$i]);
    $wh_name = clean_xss_tags($wh_name_arr[$i]);
    $ws_memo = clean_xss_tags($ws_memo_arr[$i]);

    if(!$it_id)
        continue;

    if(!$qty)
        alert('수량을 입력해주세요.');
    
    if(!$wh_name)
        alert('창고를 선택해주세요.');

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    $io_value = '';
    if ($io_id) {
      $it_option_subjects = explode(',', $it['it_option_subject']);
      $io_ids = explode(chr(30), $io_id);
      for($g = 0; $g < count($io_ids); $g++) {
        if ($g > 0) {
          $io_value .= ' / ';
        }
        $io_value .= $it_option_subjects[$g] . ':' . $io_ids[$g];
      }
    }

    $insert_sql .= "
        (
            '$it_id',
            '$io_id',
            '0',
            '$it_name',
            '$io_value',
            '$qty',
            '{$member['mb_id']}',
            '$ws_memo',
            '$wh_name',
            'stock_add',
            NOW(),
            NOW()
        )
    ";
}

$result = sql_query($insert_sql);
if(!$result)
    alert('DB오류로 재고 등록에 실패했습니다.');
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo $title; ?></title>
<script type="text/javascript" src="<?php echo G5_JS_URL ?>/datetime_components/jquery.min.js"></script>
</head>
<script>  
$(function() {
    alert('완료되었습니다.');
    setTimeout(function() {
        try {
            $('#popup_order_add', parent.document).hide();
            $('#hd').css('z-index', 10);
            parent.location.reload();
        } catch(e) { 
            window.close();
        }
    }, 500);
});
</script>
