<?php
    include_once('./_common.php');

    if(USE_G5_THEME && defined('G5_THEME_PATH')) {
        require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
        return;
    }

    define("_ORDERINQUIRY_", true);
    $orderTable = 'fm_order';

    // 회원인 경우
    if (!$is_member) goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/pastorderinquiry.php'));   

    // 테이블의 전체 레코드수만 얻음
    $sql = "SELECT count(*) as `cnt` FROM `{$orderTable}` AS `fo`
                LEFT JOIN `fm_member` AS `fm` ON `fm`.`member_seq` = `fo`.`member_seq`
                WHERE `fm`.`userid` = '{$member['mb_id']}'";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    // 비회원 주문확인시 비회원의 모든 주문이 다 출력되는 오류 수정
    // 조건에 맞는 주문서가 없다면
    if ($total_count == 0)
    {
        alert('주문이 존재하지 않습니다.', G5_BBS_URL."/mypage.php");
    }

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $list = array();

    $limit = " LIMIT $from_record, $rows ";
    $sql = "SELECT `fo`.*, `foi`.`goods_count` FROM `fm_order` AS `fo` 
                LEFT JOIN (SELECT `order_seq`, COUNT(`order_seq`) AS `goods_count` FROM `fm_order_item` GROUP BY `order_seq`) AS `foi`
                    ON `foi`.`order_seq` = `fo`.`order_seq`
                LEFT JOIN `fm_member` AS `fm`
                    ON `fm`.`member_seq` = `fo`.`member_seq`
                where `fm`.`userid` = '{$member['mb_id']}' 
                $limit ";

    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
        $list[$i] = $row;
        $list[$i]['od_href'] = G5_SHOP_URL.'/pastorderinquiryview.php?seq='.$row['order_seq'];
    }

    $write_pages = G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'];
    $list_page = $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page=';

    // Page ID
    $pid = ($pid) ? $pid : 'inquiry';
    $at = apms_page_thema($pid);
    include_once(G5_LIB_PATH.'/apms.thema.lib.php');

    $skin_row = array();
    $skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
    $skin_name = $skin_row['order_'.MOBILE_.'skin'];
    $order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
    $order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

    // 스킨 체크
    list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url); 

    // 스킨설정
    $wset = array();
    if($skin_row['order_'.MOBILE_.'set']) {
        $wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
    }

    // 데모
    if($is_demo) {
        @include ($demo_setup_file);
    }

    // 설정값 불러오기
    $is_inquiry_sub = false;
    @include_once($order_skin_path.'/config.skin.php');

    $g5['title'] = '과거주문내역조회';

    if($is_inquiry_sub) {
        include_once(G5_PATH.'/head.sub.php');
        if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
    } else {
        include_once('./_head.php');
    }

    $skin_path = $order_skin_path;
    $skin_url = $order_skin_url;

    // 셋업
    $setup_href = '';
    if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
        $setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
    }

    include_once($skin_path.'/pastorderinquiry.skin.php');

    if($is_inquiry_sub) {
        if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
        include_once(G5_PATH.'/tail.sub.php');
    } else {
        include_once('./_tail.php');
    }