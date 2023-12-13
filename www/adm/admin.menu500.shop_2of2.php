<?php
if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

$menu['menu500'] = array (
    array('500000', '쇼핑몰현황/기타', G5_ADMIN_URL.'/shop_admin/itemsellrank.php', 'shop_stats'),
    array('500010', '운영관리통계', G5_ADMIN_URL.'/shop_admin/statistics.php', ''),    
    array('500060', '매칭상담 서비스관리', G5_ADMIN_URL.'/shop_admin/eroumon_matchingservice_list.php', ''),
    array('500050', '수급자연결관리', G5_ADMIN_URL.'/shop_admin/recipient_link_list.php', ''),
    array('500110', '매출현황', G5_ADMIN_URL.'/shop_admin/sale1.php', 'sst_order_stats'),
    array('500100', '상품판매순위', G5_ADMIN_URL.'/shop_admin/itemsellrank.php', 'sst_rank'),
    //array('500600', '무통장', G5_ADMIN_URL.'/shop_admin/albank.php', 'albank', 1),
    //array('500700', '외부발주목록', G5_ADMIN_URL.'/shop_admin/outsourcinglist.php', 'outsourcing', 1),
    //array('500120', '주문내역출력', G5_ADMIN_URL.'/shop_admin/orderprint.php', 'sst_print_order', 1),
    //array('500400', '재입고SMS알림', G5_ADMIN_URL.'/shop_admin/itemstocksms.php', 'sst_stock_sms', 1),
    array('500300', '이벤트관리', G5_ADMIN_URL.'/shop_admin/itemevent.php', 'scf_event'),
    array('500310', '이벤트일괄처리', G5_ADMIN_URL.'/shop_admin/itemeventlist.php', 'scf_event_mng'),
    array('500500', '배너관리', G5_ADMIN_URL.'/shop_admin/bannerlist.php', 'scf_banner', 1),
    array('500140', '보관함현황', G5_ADMIN_URL.'/shop_admin/wishlist.php', 'sst_wish'),
    //array('500210', '가격비교사이트', G5_ADMIN_URL.'/shop_admin/price.php', 'sst_compare', 1),
	array('500900', '간편계약서관리', G5_ADMIN_URL.'/shop_admin/eform.php', ''),
	array('500810', '급여제공기록관리', G5_ADMIN_URL.'/shop_admin/eform_rent.php', ''),
    array('500150', '대금청구서관리', G5_ADMIN_URL.'/shop_admin/payment_OnlineBilling.php', 'payment_OnlineBilling')
);
?>
