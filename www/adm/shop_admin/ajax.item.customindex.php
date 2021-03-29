<?php
include_once('./_common.php');

foreach ($customIndexObj as $key => $value) {
    if ($value == '') {
        $sql = "INSERT INTO g5_shop_item_custom_index (it_id , ca_id, custom_index )
        VALUES ('{$key}', '{$ca_id}', NULL) ON DUPLICATE KEY UPDATE
        it_id='{$key}', ca_id='{$ca_id}', custom_index = NULL;";
    } else {
        $sql = "INSERT INTO g5_shop_item_custom_index (it_id , ca_id, custom_index )
        VALUES ('{$key}', '{$ca_id}', {$value}) ON DUPLICATE KEY UPDATE
        it_id='{$key}', ca_id='{$ca_id}', custom_index = {$value};";
    }
    sql_query($sql);
}

header('Content-Type: application/json');
$json = json_encode("우선순위 저장이 완료되었습니다.");
echo $json;

?>