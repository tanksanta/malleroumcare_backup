<?php
include_once('./_common.php');

/*
 * CREATE TABLE rep_inquiry_log (
 * log_id        INT NOT NULL AUTO_INCREMENT,
 * ent_id     VARCHAR(30),
 * ent_nm    VARCHAR(100),
 * pen_id     VARCHAR(30),
 * pen_nm    VARCHAR(100),
 * resultMsg   VARCHAR(300),
 * occur_page    VARCHAR(100),
 * occur_date    datetime default curdate(),
 * PRIMARY KEY(log_id)
 * ) ENGINE=MYISAM CHARSET=utf8;
 */


if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

$ent_id = $_POST['data']['ent_id'];
$ent_nm = $_POST['data']['ent_nm'];
$pen_id = $_POST['data']['pen_id'];
$pen_nm = $_POST['data']['pen_nm'];
$resultMsg = $_POST['data']['resultMsg'];
$occur_page = $_POST['data']['occur_page'];

$sql = "
    INSERT INTO
        rep_inquiry_log
    SET
        ent_id = '{$ent_id}',
        ent_nm = '{$ent_nm}',
        pen_id = '{$pen_id}',
        pen_nm = '{$pen_nm}',
        resultMsg = '{$resultMsg}',
        occur_page = '{$occur_page}'
";
$result = sql_query($sql);

json_response(200, 'OK');

?>