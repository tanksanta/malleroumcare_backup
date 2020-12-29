<?php

### 에러리포팅설정 ######################################
/*
INI_SET('display_errors', '1');
INI_SET('display_startup_errors', '1');
ini_set('error_reporting', E_ALL & ~E_NOTICE | E_STRICT);
*/
### 에러리포팅설정 ######################################

define('_GNUBOARD_', true);

### lib.common.php
include dirname(__FILE__).'/lib/lib.common.php';

### DB 연결
include dirname(__FILE__)."/lib/class.db.php";
if (!isset($g5)) include dirname(__FILE__)."/../data/dbconfig.php";
$db = new DB();
$db->connect(G5_MYSQL_HOST,G5_MYSQL_USER,G5_MYSQL_PASSWORD);
$db->select(G5_MYSQL_DB);

?>