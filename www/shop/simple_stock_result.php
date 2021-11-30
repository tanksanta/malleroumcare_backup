<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    alert('접근할 수 없습니다.');

$it_id_arr = $_POST['it_id'];
$io_id_arr = $_POST['io_id'];
$it_qty_arr = $_POST['it_qty'];
$it_barcode_arr = $_POST['it_barcode'];

$prods = [];

for($i = 0; $i < count($it_id_arr); $i++) {
    $it_id = clean_xss_tags($it_id_arr[$i]);
    $io_id = clean_xss_tags($io_id_arr[$i]);
    $it_qty = intval(clean_xss_tags($it_qty_arr[$i]) ?: 0);
    $it_barcode = clean_xss_tags($it_barcode_arr[$i]);
    $it_barcode = explode(chr(30), $it_barcode);

    if(!$it_id) continue;

    // 상품정보
    $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    // 옵션값 가져오기
    $prodColor = $prodSize = $prodOption = '';
    $prodOptions = [];

    // 옵션값이 있으면
    if($io_id) {
        $io_subjects = explode(',', $it['it_option_subject']);
        $io_ids = explode(chr(30), $io_id);

        for ($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
            switch ($io_subjects[$io_idx]) {
                case '색상':
                    $prodColor = $io_ids[$io_idx];
                    break;
                case '사이즈':
                    $prodSize = $io_ids[$io_idx];
                    break;
                default:
                    $prodOptions[] = $io_ids[$io_idx];
                    break;
            }
        }
    }

    if ($prodOptions && count($prodOptions)) {
        $prodOption = implode('|', $prodOptions);
    }

    for($x = 0; $x < $it_qty; $x++) {
        $prods[] = [
            'prodId' => $it_id,
            'prodColor' => $prodColor,
            'prodSize' => $prodSize,
            'prodOption' => $prodOption,
            'prodManuDate' => date("Y-m-d"),
            'prodBarNum' => $it_barcode[$x],
            'stateCd' => '01',
        ];
    }
}

$result = api_post_call(EROUMCARE_API_STOCK_INSERT, [
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'prods' => $prods
]);

if ($result['errorYN'] != 'N') {
    alert($result['message'] ?: '시스템 서버에서 오류가 발생하여 재고 등록에 실패했습니다.');
}

?>
<html>
<head>
<meta charset="utf-8">
<title>보유재고 등록</title>
</head>
<body>
<script>
alert('보유재고가 등록되었습니다.');
window.location.href="sales_Inventory.php";
</script>
</body>
</html>
