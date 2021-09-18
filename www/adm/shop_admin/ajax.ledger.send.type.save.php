<?php
include_once('./_common.php');

$arr = $_POST['arr'];
foreach ($arr as $dict) {
    $sql = "INSERT INTO g5_send_ledger_history (mb_id , send_type, receiver)
    VALUES ('{$dict['mb_id']}', '{$dict['send_type']}', '{$dict['receiver']}')";
    sql_query($sql);
}

header('Content-Type: application/json');
$json = json_encode("저장이 완료되었습니다.");
echo $json;

?>