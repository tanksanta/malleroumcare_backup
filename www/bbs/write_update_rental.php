<?php
    include_once($_SERVER['DOCUMENT_ROOT'] .'/common.php');
    include_once('api.config.php');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type,Accept, Origin');
    header('Content-Type: application/json');
    include_once('./_common.php');
?>
