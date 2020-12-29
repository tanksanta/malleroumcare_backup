<?php
include_once(G5_PLUGIN_PATH.'/wznaverpay/lib/nhnapi-simplecryptlib5.1.2.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/lib/class.nhnapiorder.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/lib/status.code.php');
include_once(G5_PLUGIN_PATH.'/wznaverpay/lib/request.php'); // 2019-05-27 추가

$g5['g5_shop_cart_naverpay_table'] = G5_SHOP_TABLE_PREFIX.'cart_naverpay'; // 네이버페이 장바구니 테이블