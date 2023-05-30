<?php
// LastChangedStatus 값에 대한 영카트 상태정보 변환
function lastchangedstatus_to_yc($status='') {

    $return = '';
    switch ($status) {
        case 'PAY_WAITING': // 입금 대기
            $return = '주문';
        break;
        case 'PAYED': // 결제 완료
            $return = '입금';
        break;
        case 'DISPATCHED': // 발송 처리
        case 'EXCHANGE_REDELIVERY_READY': // 교환 재배송 준비
            $return = '배송';
        break;
        case 'CANCEL_REQUESTED': // 취소 요청
            $return = '취소';
        break;
        case 'RETURN_REQUESTED': // 반품 요청
        case 'RETURNED': // 반품
            $return = '반품';
        break;
        case 'EXCHANGE_REQUESTED': // 교환 요청
        case 'HOLDBACK_REQUESTED': // 구매 확정 보류 요청
        case 'EXCHANGED': // 교환
        case 'PURCHASE_DECIDED': // 완료
            $return = '완료';
        break;
        case 'CANCELED': // 취소
            $return = '취소';
        break;
    }
    return $return;
}

function productorderstatus_to_string($status='') {

    $return = '';
    switch ($status) {
        case 'PAYMENT_WAITING':
            $return = '입금대기';
        break;
        case 'PAYED':
            $return = '결제완료';
        break;
        case 'EXCHANGE_REDELIVERY_READY': // 교환 재배송 준비
        case 'DELIVERING':
            $return = '배송중';
        break;
        case 'DELIVERED':
            $return = '배송완료';
        break;
        case 'PURCHASE_DECIDED':
            $return = '구매확정';
        break;
        case 'EXCHANGE_REQUESTED': // 교환 요청
        case 'EXCHANGED':
            $return = '교환';
        break;
        case 'CANCELED':
            $return = '취소';
        break;
        case 'RETURN_REQUESTED': // 반품 요청
        case 'RETURNED':
            $return = '반품';
        break;
        case 'CANCELED_BY_NOPAYMENT':
            $return = '미입금취소';
        break;
    }
    return $return;
}

// ProductOrderStatus
function productorderstatus_to_yc($status='') {

    $return = '';
    switch ($status) {
        case 'PAYMENT_WAITING': // 입금 대기
            $return = '주문';
        break;
        case 'PAYED': // 결제 완료
            $return = '입금';
        break;
        case 'DISPATCHED': // 발송 처리
        case 'DELIVERING': // 배송 중
        case 'EXCHANGE_REDELIVERY_READY': // 교환 재배송 준비
            $return = '배송';
        break;
        case 'EXCHANGE_REQUESTED': // 교환 요청
            $return = '교환';
        break;
        case 'DELIVERED': // 배송 완료
        case 'PURCHASE_DECIDED': // 구매 확정
        case 'EXCHANGED': // 교환
            $return = '완료';
        break;
        case 'CANCELED': // 취소
        case 'CANCELED_BY_NOPAYMENT': // 미입금 취소
        case 'CANCEL_REQUESTED': // 구매자취소요청
            $return = '취소';
        break;
        case 'RETURN_REQUESTED': // 반품 요청
        case 'RETURNED': // 반품
            $return = '반품';
        break;
        default:
            $return = $status;
        break;
    }
    return $return;
}

// 발주 상태 코드
function placeorderstatus_to_string($status='') {

    $return = '';
    switch ($status) {
        case 'NOT_YET':
            $return = '발주미확인';
        break;
        case 'OK':
            $return = '발주확인';
        break;
        case 'CANCEL':
            $return = '발주확인해제';
        break;
    }
    return $return;
}

// A.1.4 : 클레임 타입 코드
$NPI_CLAIMTYPECODE = array(
    'CANCEL' => '취소',
    'RETURN' => '반품',
    'EXCHANGE' => '교환',
    'URCHASE_DECISION_HOLDBACK' => '구매 확정 보류',
    'ADMIN_CANCEL' => '직권 취소',
);

// A.1.5 : 클레임 처리 상태 코드
$NPI_CLAIMPROCESSCODE = array(
    'CANCEL_REQUEST' => '취소요청',
    'CANCELING' => '취소 처리 중',
    'CANCEL_DONE' => '취소 처리 완료',
    'CANCEL_REJECT' => '취소 철회',
    'RETURN_REQUEST' => '반품 요청',
    'COLLECTING' => '수거 처리 중',
    'COLLECT_DONE' => '수거 완료',
    'RETURN_DONE' => '반품 완료',
    'RETURN_REJECT' => '반품 철회',
    'EXCHANGE_REQUEST' => '교환 요청',
    'EXCHANGE_REDELIVERING' => '교환 재배송 중',
    'EXCHANGE_DONE' => '교환 완료',
    'EXCHANGE_REJECT' => '교환 거부',
    'PURCHASE_DECISION_HOLDBACK' => '구매 확정 보류',
    'PURCHASE_DECISION_HOLDBACK_REDELIVERING' => '구매 확정 보류 재배송 중',
    'PURCHASE_DECISION_REQUEST' => '구매 확정 요청',
    'PURCHASE_DECISION_HOLDBACK_RELEASE' => '구매 확정 보류 해제',
    'ADMIN_CANCELING' => '직권 취소 중',
    'ADMIN_CANCEL_DONE' => '직권 취소 완료',
);

// A.1.6 : 보류 상태
$NPI_HOLDBACKSTATUSCODE = array(
    'NOT_YET' => '미보류',
    'HOLDBACK' => '보류중',
    'RELEASED' => '보류해제',
);

// A.1.7 : 보류 사유 코드
$NPI_HOLDBACKREASONCODE = array(
    'SELLER_CONFIRM_NEED' => '판매자 확인 필요',
    'PURCHASER_CONFIRM_NEED' => '구매자 확인 필요',
    'SELLER_REMIT' => '판매자 직접 송금',
);

// A.1.9 : 배송 방법 코드
$NPI_DELIVERYMETHODCODE = array(
    'DELIVERY' => '택배, 등기, 소포',
    'GDFW_ISSUE_SVC' => '굿스플로 송장 출력',
    'VISIT_RECEIPT' => '방문 수령',
    'DIRECT_DELIVERY' => '직접 전달',
    'QUICK_SVC' => '퀵서비스',
    'NOTHING' => '배송 없음',
    'RETURN_DESIGNATED' => '지정 반품 택배',
    'RETURN_DELIVERY' => '일반 반품 택배',
    'RETURN_INDIVIDUAL' => '직접 반송',
);

// A.1.10 : 택배사 코드
// $NPI_DELIVERYCOMPANYCODE = array(
//     'CJGLS' => 'CJ대한통운',
//     'KGB' => '로젠택배',
//     'EPOST' => '우체국',
//     'REGISTPOST' => '우편등기',
//     'HANJIN' => '한진택배',
//     'HYUNDAI' => '롯데택배',
//     'DAESIN' => '대신택배',
//     'ILYANG' => '일양로지스',
//     'KDEXP' => '경동택배',
//     'CHUNIL' => '천일택배',
//     'CH1' => '기타 택배',
//     'HDEXP' => '합동택배',
//     'CVSNET' => 'CVSnet편의점택배',
//     'CUPARCEL' => 'CU편의점택배',
//     'KGBPS' => 'KGB택배',
// );

$NPI_DELIVERYCOMPANYCODE = array(
    'ilogen' => '로젠택배',
    'cjlogistics' => '대한통운',
    'kdexp' => '경동택배',
    'ds3211' => '대신택배',
    'hdexp' => '합동택배',
    'lotteglogis' => '롯데택배',
    'chunilps' => '천일택배',
    'epost' => '우체국택배',
);

// A.1.11 : 클레임 요청 사유 코드 (반품접수용)
$NPI_CLAIMREASONCODE_RETURN = array(
    'INTENT_CHANGED' => '구매 의사 취소',
    'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
    'WRONG_ORDER' => '다른 상품 잘못 주문',
    'DROPPED_DELIVERY' => '배송 누락',
    'BROKEN' => '상품 파손',
    'INCORRECT_INFO' => '상품 정보 상이',
    'WRONG_DELIVERY' => '오배송',
    'WRONG_OPTION' => '색상 등이 다른 상품을 잘못 배송',
    'ETC' => '기타',
    'NOT_YET_DISCUSSION' => '상호 협의가 완료되지 않은 주문 건',
    'OUT_OF_STOCK' => '재고 부족으로 인한 판매 불가',
    'SALE_INTENT_CHANGED' => '판매 의사 변심으로 인한 거부',
    'NOT_YET_PAYMENT' => '구매자의 미결제로 인한 거부',
);

// A.1.11 : 클레임 요청 사유 코드 (판매취소용)
$NPI_CLAIMREASONCODE_CANCEL = array(
    'SOLD_OUT' => '상품 품절',
    'DELAYED_DELIVERY' => '배송 지연',
    'PRODUCT_UNSATISFIED' => '서비스 불만족',
    'INTENT_CHANGED' => '구매 의사 취소',
    'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
    'WRONG_ORDER' => '다른 상품 잘못 주문',
    'INCORRECT_INFO' => '상품 정보 상이',
);

// A.1.14 : 교환 보류 사유 코드
$NPI_EXCHANGEHOLDREASONCODE = array(
    'EXCHANGE_DELIVERYFEE' => '교환 배송비 청구',
    'EXCHANGE_EXTRAFEE' => '기타 교환 비용 청구',
    'EXCHANGE_PRODUCT_READY' => '교환 상품 준비 중',
    'EXCHANGE_PRODUCT_NOT_DELIVERED' => '교환 상품 미입고',
    'EXCHANGE_HOLDBACK' => '교환 구매 확정 보류',
    'ETC' => '기타 사유',
);

// A.1.8 : 발송지연 사유코드
$NPI_DELAYREASONCODE = array(
    'PRODUCT_PREPARE' => '상품준비중',
    'CUSTOMER_REQUEST' => '고객요청',
    'CUSTOM_BUILD' => '주문제작',
    'RESERVED_DISPATCH' => '예약발송',
    'ETC' => '기타',
);

// 오퍼레이션
$NPI_OPERATIONCODE = array(
    'PlaceProductOrder' => '발주',
    'DelayProductOrder' => '발송지연',
    'ShipProductOrder' => '발송',
    'CancelSale' => '판매취소',
    'ApproveCancelApplication' => '취소승인',
    'RequestReturn' => '반품접수',
    'ApproveReturnApplication' => '반품승인',
    'RejectReturn' => '반품거부',
    'WithholdReturn' => '반품보류',
    'ReleaseReturnHold' => '반품보류해제',
    'ApproveCollectedExchange' => '교환수거완료',
    'ReDeliveryExchange' => '교환재배송',
    'RejectExchange' => '교환거부',
    'WithholdExchange' => '교환보류',
    'ReleaseExchangeHold' => '교환보류해제',
);

// 처리가능한 상태를 배열로 리턴
function npi_statususercode($ProductOrderStatus='', $PlaceOrderStatus='', $ClaimType='', $ClaimStatus='', $HoldbackStatus='', $DelayedDispatchReason='') {

    $return = array();

    //ClaimType 클레임타입코드, ClaimStatus : 클레임처리상태코드
    //echo 'ProductOrderStatus : '.$ProductOrderStatus.', PlaceOrderStatus : '. $PlaceOrderStatus.', ClaimType : '. $ClaimType.', ClaimStatus : '. $ClaimStatus.', DelayedDispatchReason : '. $DelayedDispatchReason.', HoldbackStatus : '. $HoldbackStatus.'<br />';

    switch ($ProductOrderStatus) {
        case 'PAY_WAITING': // 가져오지 않음.

        break;
        case 'PAYED': // 결제 완료
            if ($PlaceOrderStatus == 'OK') { // 발주확인
                // 발송, 발송지연, 판매취소
                $return = array('ShipProductOrder', 'DelayProductOrder', 'CancelSale');
                if ($DelayedDispatchReason) {
                    $key = array_search( 'DelayProductOrder', $return ); // 발송지연상태일경우 발송지연 오퍼 제거
                    unset($return[$key]);
                }
            }
            else { // 발주미확인 등
                // 발주, 발송, 발송지연, 판매취소
                $return = array('PlaceProductOrder', 'ShipProductOrder', 'DelayProductOrder', 'CancelSale');
            }
        break;
        case 'DELIVERING':

            if ($ClaimType == 'RETURN') { // 반품

                if ($ClaimStatus == 'RETURN_REQUEST') { // 반품요청
                    // 반품승인
                    $return = array('ApproveReturnApplication', 'RejectReturn');
                }
                else if ($ClaimStatus == 'COLLECTING') { // 수거 처리 중
                    // 반품승인
                    $return = array('ApproveReturnApplication', 'RejectReturn');
                }
                else if ($ClaimStatus == 'COLLECT_DONE') { // 수거완료
                    // 반품거부, 반품보류, 반품보류해제, 반품승인
                    $return = array('RejectReturn', 'WithholdReturn', 'ReleaseReturnHold','ApproveReturnApplication');
                }
            }
            else if ($ClaimType == 'EXCHANGE') { // 교환

                if ($ClaimStatus == 'EXCHANGE_REQUEST' || $ClaimStatus == 'COLLECTING') { // 교환요청
                    if ($HoldbackStatus == 'RELEASED') {
                        // 교환 진행 중인 주문을 교환 보류 처리
                        $return = array('WithholdExchange');
                    }
                    else {
                        // 교환수거완료, 교환 보류 중인 주문의 교환 보류를 해제
                        $return = array('ApproveCollectedExchange', 'ReleaseExchangeHold');
                    }
                }
                else if ($ClaimStatus == 'COLLECT_DONE') { // 수거완료
                    if ($HoldbackStatus == 'RELEASED') {
                        // 교환 재배송
                        $return = array('ReDeliveryExchange');
                    }
                    else {
                        // 교환 재배송, 교환 진행 중인 주문을 교환 보류 처리, 교환 보류 중인 주문의 교환 보류를 해제
                        $return = array('ReDeliveryExchange', 'WithholdExchange', 'ReleaseExchangeHold');
                    }
                }
            }
            else {
                // 반품접수
                $return = array('RequestReturn');
            }
        break;
    }

    if ($ClaimStatus == 'CANCEL_REQUEST') {
        // 취소승인
        $return = array('ApproveCancelApplication');
    }

    return $return;
}