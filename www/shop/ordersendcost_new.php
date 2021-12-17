<?php
include_once('./_common.php');

$address = $_POST['address'];
$it_ids = $_POST['it_ids'] ?: [];

if(!$address) {
    echo 0;
    die();
}

$sc_price = get_address_sendcost($address, $it_ids);

echo $sc_price;
die();
