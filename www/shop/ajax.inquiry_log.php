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
$query = "SHOW COLUMNS FROM rep_inquiry_log WHERE `Field` = 'err_msg';";//에러 메세지 없을 시 추가
$wzres = sql_fetch( $query );
if(!$wzres['Field']) {
    sql_query("ALTER TABLE `rep_inquiry_log`
	ADD `err_msg` varchar(255) NULL DEFAULT '' COMMENT '에러 메세지' AFTER occur_page", true);
}

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');

$ent_id = $_POST['data']['ent_id'];
$ent_nm = $_POST['data']['ent_nm'];
$pen_id = $_POST['data']['pen_id'];
$pen_nm = $_POST['data']['pen_nm'];
$resultMsg = $_POST['data']['resultMsg'];
$occur_page = $_POST['data']['occur_page'];
$err_msg = $_POST['data']['err_msg'];

$sql = "
    INSERT INTO
        rep_inquiry_log
    SET
        ent_id = '{$ent_id}',
        ent_nm = '{$ent_nm}',
        pen_id = '{$pen_id}',
        pen_nm = '{$pen_nm}',
        resultMsg = '{$resultMsg}',
        occur_page = '{$occur_page}',
		err_msg = '{$err_msg}'
";
$result = sql_query($sql);

json_response(200, 'OK');

?>
