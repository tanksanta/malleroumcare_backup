<?php
include_once('./_common.php');
$data = json_decode(file_get_contents('php://input'), true);

$test = $data['test'];

json_response(200, $test);
