<?php
include_once('./_common.php');

if($member['mb_type'] !== 'default')
    json_response(400, '먼저 로그인하세요.');


$_it_id = clean_xss_tags($_POST['it_id']);    
$_qty = clean_xss_tags($_POST['qty']);
$_price = clean_xss_tags($_POST['price']);


/*
    22.12.07
    추후 간편 주문서 개편시 해당 파일을 사용하지 않는 방향으로 개선 방향을 잡아 가야함.
    모든 데이터가 프론트 단에서 처리하고 있는 문제점으로 인하여 현재 페이지를 임시적으로 사용.
    
    현재 주문서에서 수량 변경시 실시간 제고를 확인하고 있지 않음으로
    간편 주문서 개편시 수량 변경 부분에서 재고 체크를 하고,
    해당 재고를 체크 하면서 최종 계산된 상품에 대한 배송비및 기타 주문서와 관련된 상품정보를 변경하는 방식으로 해야함.
*/
$_result = get_item_delivery_cost( $_it_id, ($_qty ?: 1), $_price );
json_response(200, 'OK', $_result);
?>