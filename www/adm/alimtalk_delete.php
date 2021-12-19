<?php
$sub_menu = '200400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'd');

$count = count($_POST['chk']);

if(!$count)
    alert('삭제할 알림톡 목록을 1개이상 선택해 주세요.');

for($i=0; $i<$count; $i++) {
    $al_id = $_POST['chk'][$i];

    // 영업팀 입고예정일 알림 삭제 제외
    if ($al_id !== '3') {
        $sql = "
            DELETE FROM
                g5_alimtalk
            WHERE
                al_id = '$al_id'
        ";
        sql_query($sql);

        $sql = "
            DELETE FROM
                g5_alimtalk_member
            WHERE
                al_id = '$al_id'
        ";
        sql_query($sql);
    }
}

goto_url('./alimtalk_list.php');
?>
