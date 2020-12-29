<?php
$sub_menu = '400300';
include_once('./_common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

auth_check($auth[$sub_menu], "w");

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}

if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/Excel/reader.php');

    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('UTF-8');

    $data->read($file);

    error_reporting(E_ALL ^ E_NOTICE);

    $dup_it_id = array();
    $fail_it_id = array();
    $dup_count = 0;
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;

        $j = 1;

        $it_thezone              = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_model                = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_use_custom_order     = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_use_custom_order     = (int)$it_use_custom_order ? (int)$it_use_custom_order : 0;
        $it_stock_qty            = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_stock_qty            = (int)$it_stock_qty ? (int)$it_stock_qty : 0;
        $it_use                  = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_use                  = (int)$it_use ? (int)$it_use : 0;
        $it_use_partner          = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_use_partner          = (int)$it_use_partner ? (int)$it_use_partner : 0;
        $it_price                = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price                = (int)$it_price ? (int)$it_price : 0;
        $it_price_dealer         = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price_dealer         = (int)$it_price_dealer ? (int)$it_price_dealer : 0;
        $it_price_dealer2        = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price_dealer2        = (int)$it_price_dealer2 ? (int)$it_price_dealer2 : 0;
        $it_price_partner        = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price_partner        = (int)$it_price_partner ? (int)$it_price_partner : 0;

        $sql = " UPDATE {$g5['g5_shop_item_table']}
                     SET it_model = '$it_model',
                            it_use_custom_order = '$it_use_custom_order',
                            it_stock_qty = '$it_stock_qty',
                            it_use = '$it_use',
                            it_use_partner = '$it_use_partner',
                            it_price = '$it_price',
                            it_price_dealer = '$it_price_dealer',
                            it_price_dealer2 = '$it_price_dealer2',
                            it_price_partner = '$it_price_partner'
                        WHERE `it_thezone` = '$it_thezone' ";
        sql_query($sql);

        $succ_count++;
    }
}

$g5['title'] = '상품 엑셀일괄수정 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>상품일괄 수정을 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>건수</dt>
        <dd><?php echo number_format($succ_count); ?></dd>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>