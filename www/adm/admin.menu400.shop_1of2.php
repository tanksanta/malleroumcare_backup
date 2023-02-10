<?php
if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) return;

$menu['menu400'] = array (
    array('400000', '쇼핑몰관리', G5_ADMIN_URL.'/shop_admin/', 'shop_config'),
    array('400001', '출고담당자', G5_ADMIN_URL.'/shop_admin/samhwa_deliverylist.php', 'scf_order_delivery', 1),
    array('400100', '쇼핑몰설정', G5_ADMIN_URL.'/shop_admin/configform.php', 'scf_config'),
    array('400460', '거래처원장', G5_ADMIN_URL.'/shop_admin/ledger_search.php', 'scf_ledger', 1),
    array('400470', '거래명세서', G5_ADMIN_URL.'/shop_admin/transactionlist.php', 'scf_ledger', 1),
    array('400480', '발주내역', G5_ADMIN_URL.'/shop_admin/purchase_orderlist.php', 'scf_order', 1),
    array('400400', '주문내역', G5_ADMIN_URL.'/shop_admin/samhwa_orderlist.php', 'scf_order', 1),
    array('400402', '출고리스트', G5_ADMIN_URL.'/shop_admin/samhwa_deliverylist.php', 'scf_order_delivery', 1),
    //array('400403', '반품관리', G5_ADMIN_URL.'/shop_admin/samhwa_cancellist.php', 'scf_order_cancel', 1),
    array('400401', '주문완료관리', G5_ADMIN_URL.'/shop_admin/samhwa_orderlist_complete.php', 'scf_order_complete', 1),
    //array('400440', '개인결제관리', G5_ADMIN_URL.'/shop_admin/personalpaylist.php', 'scf_personalpay', 1),
    //array('400450', '파트너결제관리', G5_ADMIN_URL.'/shop_admin/partnerpaylist.php', 'scf_partnerpay', 1),
    array('400200', '분류관리', G5_ADMIN_URL.'/shop_admin/categorylist.php', 'scf_cate'),
    array('400300', '상품관리', G5_ADMIN_URL.'/shop_admin/itemlist.php', 'scf_item'),
    array('400660', '상품문의', G5_ADMIN_URL.'/shop_admin/itemqalist.php', 'scf_item_qna'),
    array('400650', '사용후기', G5_ADMIN_URL.'/shop_admin/itemuselist.php', 'scf_ps'),
    array('400620', '상품재고관리', G5_ADMIN_URL.'/shop_admin/itemstocklist.php', 'scf_item_stock'),
    array('400610', '상품유형관리', G5_ADMIN_URL.'/shop_admin/itemtypelist.php', 'scf_item_type'),
    array('400500', '상품옵션재고관리', G5_ADMIN_URL.'/shop_admin/optionstocklist.php', 'scf_item_option'),
    array('400800', '쿠폰관리', G5_ADMIN_URL.'/shop_admin/couponlist.php', 'scf_coupon'),
    // array('400810', '쿠폰존관리', G5_ADMIN_URL.'/shop_admin/couponzonelist.php', 'scf_coupon_zone'),
    array('400750', '추가배송비관리', G5_ADMIN_URL.'/shop_admin/sendcost_new_list.php', 'scf_sendcost', 1),
    //array('400410', '미완료주문', G5_ADMIN_URL.'/shop_admin/inorderlist.php', 'scf_inorder', 1),
    // array('400420', '과거주문내역', G5_ADMIN_URL.'/shop_admin/past_order_list.php', 'scf_before_order', 1),
    // array('400430', '과거cs주문내역', G5_ADMIN_URL.'/shop_admin/past_csorder_list.php', 'scf_csorder', 1),
    // array('400510', '엑셀 다운로드 받기', G5_ADMIN_URL.'/shop_admin/exporttoexcel.php', 'scf_productexcel', 1),
);
?>