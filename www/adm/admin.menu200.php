<?php
$menu['menu200'] = array (
    array('200000', '회원관리', G5_ADMIN_URL.'/member_list.php', 'member'),
    array('200100', '회원관리', G5_ADMIN_URL.'/member_list.php', 'mb_list'),
    array('200300', '회원메일발송', G5_ADMIN_URL.'/mail_list.php', 'mb_mail'),
    array('200400', '회원 알림톡/푸시 발송', G5_ADMIN_URL.'/alimtalk_list.php', 'mb_alimtalk'),
    array('200800', '접속자집계', G5_ADMIN_URL.'/visit_list.php', 'mb_visit', 1),
    array('200810', '접속자검색', G5_ADMIN_URL.'/visit_search.php', 'mb_search', 1),
    array('200820', '접속자로그삭제', G5_ADMIN_URL.'/visit_delete.php', 'mb_delete', 1),
    array('200200', '포인트관리', G5_ADMIN_URL.'/point_list.php', 'mb_point'),
    array('200900', '투표관리', G5_ADMIN_URL.'/poll_list.php', 'mb_poll'),
    array('200830', '사용자 통계분석', G5_ADMIN_URL.'/user_statistics.php', 'mb_statistics'),
    array('200950', '추천상품관리', G5_ADMIN_URL.'/shop_admin/product_recommended.php', 'product_recommended'),
    array('200840', '서비스 로그관리', G5_ADMIN_URL.'/service_log_management.php', 'sv_management'),
	array('200110', '회원탈퇴 관리', G5_ADMIN_URL.'/member_leave.php', 'mb_leave'),
);
?>
