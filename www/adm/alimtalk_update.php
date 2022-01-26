<?php
$sub_menu = '200400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$w = $_POST['w'];
$al_id = get_search_string($_POST['al_id']);
$al_subject = clean_xss_tags($_POST['al_subject']);
$al_type = clean_xss_tags($_POST['al_type']);
$al_cate = clean_xss_tags($_POST['al_cate']);
$al_itname = clean_xss_tags($_POST['al_itname']);
$al_itdate = clean_xss_tags($_POST['al_itdate']);

$mb_id_arr = $_POST['mb_id'] ?: [];
$deleted_arr = $_POST['deleted'] ?: [];

if($w == 'd') {
    if(!$al_id)
        alert('유효하지 않은 요청입니다.');

    // 삭제
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
} else {
    if(!$al_subject)
        alert('제목을 입력해주세요.');

    if($al_type == '1' && !$mb_id_arr)
        alert('사업소를 선택해주세요.');

    if($w == 'u') {
        if(!$al_id)
            alert('유효하지 않은 요청입니다.');

        // 수정
        $sql = "
            UPDATE
                g5_alimtalk
            SET
                al_type = '$al_type',
                al_cate = '$al_cate',
                al_subject = '$al_subject',
                al_itname = '$al_itname',
                al_itdate = '$al_itdate',
                updated_at = NOW(),
                updated_by = '{$member['mb_id']}'
            WHERE
                al_id = '$al_id'

        ";
        sql_query($sql, true);
    } else {
        // 등록
        $sql = "
            INSERT INTO
                g5_alimtalk
            SET
                al_type = '$al_type',
                al_cate = '$al_cate',
                al_subject = '$al_subject',
                al_itname = '$al_itname',
                al_itdate = '$al_itdate',
                created_at = NOW(),
                created_by = '{$member['mb_id']}',
                updated_at = NOW(),
                updated_by = '{$member['mb_id']}'
        ";

        sql_query($sql, true);
        $al_id = sql_insert_id();
    }

    if($al_type == '1') {
        for($i = 0; $i < count($mb_id_arr); $i++) {
            $mb_id = get_search_string($mb_id_arr[$i]);
            $deleted = $deleted_arr[$i];

            if($deleted === '0') {
                continue;
            } else if ($deleted === '1') {
                // 삭제
                $sql = "
                    DELETE FROM
                        g5_alimtalk_member
                    WHERE
                        al_id = '$al_id' and
                        mb_id = '$mb_id'
                ";
            } else {
                // 등록
                $sql = "
                    INSERT INTO
                        g5_alimtalk_member
                    SET
                        al_id = '$al_id',
                        mb_id = '$mb_id'
                ";
            }

            sql_query($sql);
        }
    }
}

goto_url('alimtalk_list.php')
?>