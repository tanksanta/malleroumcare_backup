<?php
include_once('./_common.php');

$test = $_POST['test'];

json_response(200, $test);
