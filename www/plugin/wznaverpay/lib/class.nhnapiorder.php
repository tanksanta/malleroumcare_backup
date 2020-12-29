<?php
/*
주문 API

• 조회
− 상품 주문 정보 조회: GetProductOrderInfoList
− 상품 주문 변경 내역 조회: GetChangedProductOrderList
− (구) 주문 번호를 이용한 상품 주문 목록 조회: GetMigratedProductOrderList
− 상품 평가 내역 조회: GetPurchaseReviewList

• 처리
− 발주: PlaceProductOrder
− 발송 지연: DelayProductOrder
− 발송: ShipProductOrder, ShipProductOrders
− 판매 취소: CancelSale
− 취소 요청 승인: ApproveCancelApplication
− 반품 접수: RequestReturn
− 반품 요청 승인: ApproveReturnApplication
− 교환 상품 수거 완료: ApproveCollectedExchange
− 교환 상품 재발송: ReDeliveryExchange
− 반품 거부: RejectReturn
− 반품 보류: WithholdReturn
− 반품 보류 해제: ReleaseReturnHold
− 교환 거부: RejectExchange
− 교환 보류: WithholdExchange
− 교환 보류 해제: ReleaseExchangeHold

• 알림(callback)

• SANDBOX: http://sandbox.api.naver.com/Checkout/[ServiceName]
• PRODUCTION: http://ec.api.naver.com/Checkout/[ServiceName]



문의 API

• 쇼핑 문의 내역 조회: GetCustomerInquiryList
• 쇼핑 문의 답변 및 수정: AnswerCustomerInquiry

• SANDBOX: http://sandbox.api.naver.com/Checkout/CustomerInquiryService
• PRODUCTION: http://ec.api.naver.com/Checkout/CustomerInquiryService
*/

class NHNAPIORDER extends NHNAPISCL {

    private $accessLicense  = ''; //AccessLicense Key 입력, PDF파일 참조
    private $secretKey      = ''; ////SecretKey 입력, PDF파일 참조
    private $service        = 'MallService41'; // MallService41, Alpha2MallService41, MallService5
    private $operation      = '';
    private $version        = '4.1'; // API 버전(주문 API 는 5.0, 문의 API 는 1.0)
    private $targetUrl      = 'http://sandbox.api.naver.com/Checkout';
    private $ReqUrl         = '';

    private $timestamp       = '';
    private $signature       = '';
    private $secret          = '';
    private  $mallID         = '';

    public $detailLevel      = 'Full'; // 돌려 받는 데이터의 상세 정도(Compact/Full).
    public $InqTimeFrom      = ''; // 조회시작일
    public $InqTimeTo        = ''; // 조회종료일
    public $showReq          = false; // 요청메시지 출력여부
    public $PurchaseReviewClassType = 'GENERAL'; // GENERAL, PREMIUM
    public $IsAnswered       = 'false'; // 문의내역 답변여부
    public $is_cache_time    = true; // 캐싱된시간 적용여부

    public function __construct() {

        global $config, $g5, $default;

        $this->accessLicense  = $default['de_naverpayorder_AccessLicense']; // AccessLicense Key 입력, PDF파일 참조
        $this->secretKey      = $default['de_naverpayorder_SecretKey']; // SecretKey 입력, PDF파일 참조
        $this->mallID         = 'salesman1'; // salesman1 : 테스트환경

        $this->InqTimeFrom    = date("Y-m-d\TH:i:s", strtotime("-1day")); // 조회시작일
        $this->InqTimeTo      = date("Y-m-d\TH:i:s", time()); // 조회종료일

        if (!$default['de_naverpayorder_test']) { // PRODUCTION
            $this->targetUrl = 'http://ec.api.naver.com/Checkout';
            $this->mallID    = $default['de_naverpay_mid']; // 상점아이디
            $this->service   = 'MallService41'; // 상점아이디
        }
        else if ($this->service == 'Alpha2MallService41') {
            $this->mallID    = $default['de_naverpay_mid']; // 상점아이디
        }

        if ($default['de_naverpayorder_test'] && $default['de_naverpay_mid'] == 'np_vmkwn611994') { // 검수처리 완료 후 삭제
            $this->mallID    = 'salesman1'; // 상점아이디
        }
    }

    // 주문 주기적으로 체크. : 페이지에 접속할때 처리
    function ordersync_rotation($status = '') {

        $is_uodate = $this->cache_check($status);
        if ($is_uodate !== true && $aor->is_cache_time)
            return false;

        if (!$this->InqTimeFrom) {
            $this->InqTimeFrom = date("Y-m-d\TH:i:s", strtotime("-1day"));
        }

        $tptime = $this->InqTimeFrom;
        $frtime = str_replace('T', ' ', $tptime);
        $totime = $this->InqTimeTo;
        $beetwn = ceil((strtotime($totime) - strtotime($frtime)) / 60 / 60 / 24);

        // 시작일을 현재시간과 비교 해서 차이가 24시간 이상일경우 루프처리.
        for ($z = 0; $z < $beetwn; $z++) {

            $this->InqTimeFrom = date("Y-m-d\TH:i:s", strtotime($tptime."+".$z."day"));
            $this->InqTimeTo = date("Y-m-d\TH:i:s", strtotime($tptime."+".($z+1)."day"));

            $is_success = $this->orderSync($status);
        }

        if ($is_success) {
            $this->cache_build($status);
            return true;
        }
        else {
            return false;
        }
    }

    // 주문 콜백 : 네이버페이에서 이벤트가 발생할때 처리
    function ordersync_callback($status = '') {

        $is_success = $this->orderSync($status);

        if ($is_success) {
            $this->cache_build($status);
            die('RESULT=TRUE');
        }
        else {
            die('RESULT=FALSE');
        }
    }

    // 문의 주기적으로 체크. : 페이지에 접속할때 처리
    function customersync_rotation($status = '') {

        $is_uodate = $this->cache_check($status);
        if ($is_uodate !== true && $this->is_cache_time)
            return false;

        if (!$this->InqTimeFrom) {
            $this->InqTimeFrom = date("Y-m-d\TH:i:s", strtotime("-1day"));
        }

        $tptime = $this->InqTimeFrom;
        $frtime = str_replace('T', ' ', $tptime);
        $totime = $this->InqTimeTo;
        $beetwn = ceil((strtotime($totime) - strtotime($frtime)) / 60 / 60 / 24);

        // 시작일을 현재시간과 비교 해서 차이가 24시간 이상일경우 루프처리.
        for ($z = 0; $z < $beetwn; $z++) {

            $this->InqTimeFrom = date("Y-m-d\TH:i:s", strtotime($tptime."+".$z."day"));
            $this->InqTimeTo = date("Y-m-d\TH:i:s", strtotime($tptime."+".($z+1)."day"));

            if ($status == 'GetCustomerInquiryList') { // 문의내역
                $is_success = $this->inquirysync();
            }
            else { // 상품평
                $is_success = $this->reviewsync();
            }
        }

        if ($is_success) {
            $this->cache_build($status);
            return true;
        }
        else {
            return false;
        }
    }

    // 문의 콜백 : 네이버페이에서 이벤트가 발생할대 처리
    function customersync_callback($status = '') {

        if ($status == 'GetCustomerInquiryList') { // 문의내역
            $is_success = $this->inquirysync();
        }
        else { // 상품평
            $is_success = $this->reviewsync();
        }

        if ($is_success) {
            die('RESULT=TRUE');
        }
        else {
            die('RESULT=FALSE');
        }
    }

    // 상품 주문 변경 내역 동기화
    function orderSync($status = '') {

        global $default, $g5, $config;
        global $NPI_DELIVERYCOMPANYCODE;

        $is_more = true;
        $arr_data = array();
        $MoreDataTimeFrom = $InquiryExtraData = '';
        $max_cnt = 0;
        $od_pg = 'naverpay';

        while ( $is_more == true ) {

            $xml = $this->GetChangedProductOrderList($InquiryExtraData);
            $req = $xml->Body->GetChangedProductOrderListResponse;
            $responseType = (string)$req->ResponseType; // 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning)

            if (strtoupper($responseType) !== 'SUCCESS') return true;

            $ReturnedDataCount  = (int)$req->ReturnedDataCount; // 이번 응답에 포함된 데이터의 개수
            $HasMoreData        = (string)$req->HasMoreData; // 데이터가 더 존재하는지 여부
            $MoreDataTimeFrom   = (string)$req->MoreDataTimeFrom; // 주문정보가 더 있을경우 데이터의 주문 시각
            $InquiryExtraData   = (string)$req->InquiryExtraData; // 주문정보가 더 있을경우 상품 주문 번호

            if ($HasMoreData == 'true') {
                $is_more = true;
                $InqTimeFrom = str_replace('T', ' ', substr($MoreDataTimeFrom, 0, 19));
                $InqTimeFrom = str_replace(' ', 'T', date('Y-m-d H:i:s', strtotime($InqTimeFrom.'+9Hour')));
                $this->InqTimeFrom = $InqTimeFrom;
            }
            else {
                $is_more = false;
            }

            foreach ($req->ChangedProductOrderInfoList as $k => $v) {

                $OrderID = (string)$v->OrderID;
                if (    strtoupper((string)$v->LastChangedStatus) == 'PAY_WAITING' ||
                        strtoupper((string)$v->LastChangedStatus) == 'CANCELED_BY_NOPAYMENT' ||
                        strtoupper((string)$v->ProductOrderStatus) == 'PAYMENT_WAITING' ||
                        strtoupper((string)$v->ProductOrderStatus) == 'CANCELED_BY_NOPAYMENT' ) { // 입금대기,미입금취소 제외
                    continue;
                }

                $arr_data[$OrderID]['ProductOrderID'][] = (string)$v->ProductOrderID;
                $arr_data[$OrderID]['LastChangedDate'][] = (string)$v->LastChangedDate;
                $arr_data[$OrderID]['LastChangedStatus'][] = (string)$v->LastChangedStatus;
                $arr_data[$OrderID]['IsReceiverAddressChanged'][] = (string)$v->IsReceiverAddressChanged;
            }

            if ($max_cnt >= 5) { // 무한루프 방지
                break;
            }

            $max_cnt++;
        }

        if (count($arr_data) == 0) return true;

        $od_test = ($default['de_naverpayorder_test'] ? '1' : '0');
        $od_id = $tmp_OrderID = ''; // 2019-09-25 : wetoz

        foreach ($arr_data as $k => $v) {

            $OrderID = $k;
            $od_id   = preg_replace('/[^0-9]/', '', $OrderID);

            $xml = $this->GetProductOrderInfoList($v['ProductOrderID']);
            $req = $xml->Body->GetProductOrderInfoListResponse;
            $ResponseType = (string)$req->ResponseType; // 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning)
            $Error = $req->Error; // 오류(error) 정보

            if (strtoupper($ResponseType) !== 'SUCCESS') continue;

            $od_cart_count = (int)$req->ReturnedDataCount;
            $od_cart_price = $od_cancel_price = $od_send_cost = $od_send_cost2 = $od_mobile = 0;
            $od_receipt_price = 0; // 총입금액  (ProductOrder 에서 TotalPaymentAmount 를 확인 후 총합으로 처리)
            $mb_id = $od_receipt_time = $od_time = $od_settle_case = $od_tno = $MallManageCode = '';
            $od_b_name = $od_b_tel = $od_b_hp = $od_b_zip1 = $od_b_zip2 = $od_b_addr1 = $od_b_addr2 = '';
            $od_name = $od_tel = $od_hp = $od_delivery_company = $od_invoice = $od_invoice_time = $od_memo = '';
            $od_status = productorderstatus_to_yc($v['LastChangedStatus'][0]);
            $PaymentMeans = $PaymentCoreType = '';

            $arr_cart = array();
            foreach ($req->ProductOrderInfoList as $k2 => $v2) {

                $row = array();

                if (!$od_name) {
                    $GeneralPaymentAmount = (int)$v2->Order->GeneralPaymentAmount; // 일반 결제 수단 최종 결제 금액
                    $NaverMileagePaymentAmount = (int)$v2->Order->NaverMileagePaymentAmount; // 네이버페이 포인트 최종 결제 금액
                    $ChargeAmountPaymentAmount = (int)$v2->Order->ChargeAmountPaymentAmount; // 충전금 최종 결제 금액
                    $CheckoutAccumulationPaymentAmount = (int)$v2->Order->CheckoutAccumulationPaymentAmount; // 네이버페이 적립금 최종 결제 금액
                    $od_receipt_price  = $GeneralPaymentAmount + $NaverMileagePaymentAmount + $ChargeAmountPaymentAmount + $CheckoutAccumulationPaymentAmount;

                    $od_name    = $this->decrypt($this->secret, (string)$v2->Order->OrdererName); // 주문자명
                    $od_tel     = $this->decrypt($this->secret, (string)$v2->Order->OrdererTel1);
                    $od_hp      = $this->decrypt($this->secret, (string)$v2->Order->OrdererTel2);
                    $od_b_name  = $this->decrypt($this->secret, (string)$v2->ProductOrder->ShippingAddress->Name);
                    $od_b_tel   = $this->decrypt($this->secret, (string)$v2->ProductOrder->ShippingAddress->Tel1);
                    $od_b_hp    = $this->decrypt($this->secret, (string)$v2->ProductOrder->ShippingAddress->Tel2);
                    $ZipCode    = preg_replace('/[^0-9]/i', '', (string)$v2->ProductOrder->ShippingAddress->ZipCode);
                    $od_b_zip1  = substr($ZipCode, 0, 3);
                    $od_b_zip2  = substr($ZipCode, 3);
                    $od_b_addr1 = $this->decrypt($this->secret, $v2->ProductOrder->ShippingAddress->BaseAddress);
                    $od_b_addr2 = $this->decrypt($this->secret, $v2->ProductOrder->ShippingAddress->DetailedAddress);
                    $od_settle_case      = '네이버페이';
                    $od_time             = str_replace('T', ' ', substr((string)$v2->Order->OrderDate, 0, 19)); // 주문일시
                    $od_time             = date('Y-m-d H:i:s', strtotime($od_time.'+9Hour'));
                    $od_receipt_time     = $od_time;
                    $od_tno              = (string)$v2->Order->PaymentNumber;
                    $od_mobile           = (strtoupper((string)$v2->Order->PayLocationType) == 'MOBILE' ? 1 : 0);
                    $MallMemberID        = $this->decrypt($this->secret, (string)$v2->ProductOrder->MallMemberID); // 암호화된 가맹점 회원 ID(ID PLUS)
                    $od_delivery_company = (string)$v2->Delivery->DeliveryCompany;
                    $od_delivery_company = $NPI_DELIVERYCOMPANYCODE[$od_delivery_company] ? $NPI_DELIVERYCOMPANYCODE[$od_delivery_company] : $od_delivery_company;
                    $od_invoice          = (string)$v2->Delivery->TrackingNumber;
                    $od_invoice_time     = (string)$v2->Delivery->SendDate; // 발송 일시
                    if ($od_invoice_time) {
                        $od_invoice_time = str_replace('T', ' ', substr($od_invoice_time, 0, 19)); // 주문일시
                        $od_invoice_time = date('Y-m-d H:i:s', strtotime($od_invoice_time.'+9Hour'));
                    }
                    $od_send_cost        = (int)$v2->ProductOrder->DeliveryFeeAmount;
                    $PaymentMeans        = (string)$v2->Order->PaymentMeans; // 결제 수단 : 신용카드, 휴대폰, 무통장입금, 실시간계좌이체, 포인트결제, 신용카드 간편결제, 계좌 간편결제, 휴대폰 간편결제, 나중에결제 중 하나의 값을 입력한다
                    $PaymentCoreType     = (string)$v2->Order->PaymentCoreType; // 결제 구분 (네이버결제/PG결제)
                }

                $row['ProductOrderID']  = (string)$v2->ProductOrder->ProductOrderID; // 상품주문번호
                $row['it_id']           = (string)$v2->ProductOrder->ProductID; // 상품번호
                $row['it_name']         = (string)$v2->ProductOrder->ProductName; // 상품명
                $row['ProductOption']   = (string)$v2->ProductOrder->ProductOption; // 상품옵션
                $row['OptionCode']      = (string)$v2->ProductOrder->OptionCode; // 주문 등록시 사용한 옵션 코드
                $row['ct_status']       = productorderstatus_to_yc((string)$v2->ProductOrder->ProductOrderStatus); // 상품 주문 상태
                $row['ct_price']        = (string)$v2->ProductOrder->UnitPrice ? (string)$v2->ProductOrder->UnitPrice : (string)$v2->ProductOrder->TotalPaymentAmount; // 상품가격 (테스트환경일경우 TotalPaymentAmount 가 안넘어옴.)
                $row['TotalProductAmount']  = (string)$v2->ProductOrder->TotalProductAmount; // 판매총액 = 상품가격 + 옵션가격
                $row['ct_qty']              = (int)$v2->ProductOrder->Quantity; // 수량
                $row['MallManageCode']      = (string)$v2->ProductOrder->MallManageCode; // MallManageCode
                $row['ShippingMemo']        = (string)$v2->ProductOrder->ShippingMemo; // 배송메모
                $row['PlaceOrderStatus']    = (string)$v2->ProductOrder->PlaceOrderStatus; // 발주상태 : 2019-11-22

                if ($row['ShippingMemo']) {
                    $od_memo .= '상품주문번호 '.$row['ProductOrderID'].' : '.addslashes($row['ShippingMemo'])."\n";
                }

                if ($row['ct_status'] == '취소') { // 2019-04-22 : 부분취소 처리.
                    $od_cancel_price += (string)$v2->ProductOrder->TotalPaymentAmount;
                }
                else if ($row['ct_status'] == '입금' && strtoupper($row['PlaceOrderStatus']) == 'OK') {
                    $od_status = '준비';
                    $row['ct_status'] = '준비';
                }

                $arr_cart[] = $row;

                $od_cart_price  += (string)$v2->ProductOrder->TotalPaymentAmount;
            }

            if ($tmp_OrderID <> $OrderID) {
                //$od_id = $OrderID; // 2020-10-29 : wetoz
            }
            $tmp_OrderID = $OrderID;

            $od_mk = ''; // 2019-09-25 : wetoz

            foreach ($arr_cart as $k2 => $v2) {

                $ProductOrderID = trim($v2['ProductOrderID']);

                $ct = sql_fetch("select ct_id, od_id from {$g5['g5_shop_cart_table']} where ProductOrderID = '".$ProductOrderID."' ");
                if ($ct['ct_id']) {
                    $query = " update {$g5['g5_shop_cart_table']} set ct_status = '".$v2['ct_status']."' where ct_id = '".$ct['ct_id']."' ";
                    sql_query($query, true);

                    // APMS : 가정산 자동반영 - 2014.07.20
                	apms_account_auto($ct['od_id'], $ct['ct_id'], $v2['ct_status']);
                }
                else {

                    $arr_MallManageCode = explode(',', $v2['MallManageCode']);
                    $MallManageCode = $arr_MallManageCode[$k2];

                    $query = "select ct_id, mb_id, od_mk from {$g5['g5_shop_cart_naverpay_table']} where MallManageCode = '".$MallManageCode."'";
                    $ct = sql_fetch($query, true);
                    $mb_id = $ct['mb_id'];
                    $od_mk = $ct['od_mk']; // wetoz : 2019-09-16

                    if ($MallManageCode && $ct['ct_id']) {

                        // 장바구니 등록
                        $query = "insert into {$g5['g5_shop_cart_table']}(od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_select, ct_select_time, ct_status, ProductOrderID, od_naver_orderid) select '".$od_id."', mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, 1, '".G5_TIME_YMDHIS."', '".$v2['ct_status']."', '".$ProductOrderID."', '".$OrderID."' from {$g5['g5_shop_cart_naverpay_table']} where MallManageCode = '".$MallManageCode."' and ct_select = 0 ";
                        sql_query($query, true);

                        // 적용완료처리.
                        sql_query(" update {$g5['g5_shop_cart_naverpay_table']} set ct_select = 1 where MallManageCode = '".$MallManageCode."' ");
                    }
                    else {

                        $it_id = trim($v2['it_id']);
                        $it = sql_fetch(" select it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, it_point_type, it_price, it_point from {$g5['g5_shop_item_table']} where it_id = '$it_id' ");

                        $io_price = 0;
                        if ($v2['TotalProductAmount'] && ($v2['ct_price'] * $v2['ct_qty']) < $v2['TotalProductAmount']) { // 네이버페이에서 바로 주문들어온경우 io_id 정보를 등록할수없다. 옵션값만 저장함.
                            $io_price = ($v2['TotalProductAmount'] / $v2['ct_qty']) - $v2['ct_price'];
                        }

                        $query = "insert into {$g5['g5_shop_cart_table']} set
                                od_id = '".$od_id."',
                                mb_id = '".$mb_id."',
                                it_id = '".$it_id."',
                                it_name = '".addslashes($v2['it_name'])."',
                                it_sc_type = '".$it['it_sc_type']."',
                                it_sc_method = '".$it['it_sc_method']."',
                                it_sc_price = '".$it['it_sc_price']."',
                                it_sc_minimum = '".$it['it_sc_minimum']."',
                                it_sc_qty = '".$it['it_sc_qty']."',
                                ct_price = '".$v2['ct_price']."',
                                ct_qty = '".$v2['ct_qty']."',
                                ct_time = '".G5_TIME_YMDHIS."',
                                ct_select_time = '".G5_TIME_YMDHIS."',
                                ct_status = '".$v2['ct_status']."',
                                ct_option = '".addslashes($v2['ProductOption'])."',
                                io_price = '".$io_price."',
                                ct_select = '1',
                                ProductOrderID = '".$ProductOrderID."'
                        ";
                        sql_query($query, true);
                    }
                }
            }


            $od_name = addslashes($od_name);
            $od_tel = addslashes($od_tel);
            $od_hp = addslashes($od_hp);
            $od_b_name = addslashes($od_b_name);
            $od_b_tel = addslashes($od_b_tel);
            $od_b_hp = addslashes($od_b_hp);
            $od_b_addr1 = addslashes($od_b_addr1);
            $od_b_addr2 = addslashes($od_b_addr2);

            $query = "select od_id from {$g5['g5_shop_order_table']} where od_naver_orderid = '".$OrderID."'";
            $od = sql_fetch($query);

            // 주문정보 등록/수정
            if (!$od['od_id']) { // 처음등록
                $so_nb = get_uniqid_so_nb();

                // wetoz : 2020-10-29 : 결제가 완료된것만 가져오므로 od_pay_state 값은 무조건 1 로 적용
                $od_receipt_price = $od_cart_price + $od_send_cost + $od_send_cost2;
                $query = " insert into {$g5['g5_shop_order_table']} set
                            od_id = '".$od_id."',
                            mb_id = '".$mb_id."',
                            od_name = '".$od_name."',
                            od_tel = '".$od_tel."',
                            od_hp = '".$od_hp."',
                            od_b_name = '".$od_b_name."', od_b_tel = '".$od_b_tel."', od_b_hp = '".$od_b_hp."', od_b_zip1 = '".$od_b_zip1."', od_b_zip2 = '".$od_b_zip2."', od_b_addr1 = '".$od_b_addr1."', od_b_addr2 = '".$od_b_addr2."',
                            od_cart_count = '".$od_cart_count."',
                            od_cart_price = '".$od_cart_price."',
                            od_send_cost = '".$od_send_cost."',
                            od_send_cost2 = '".$od_send_cost2."',
                            od_receipt_price = '".$od_receipt_price."',
                            od_receipt_time = '".$od_receipt_time."',
                            od_status = '".$od_status."',
                            od_time = '".$od_time."',
                            od_settle_case = '".$od_settle_case."',
                            od_test = '".$od_test."',
                            od_pg = '".$od_pg."',
                            od_tno = '".$od_tno."',
                            od_delivery_company = '".$od_delivery_company."',
                            od_invoice = '".$od_invoice."',
                            od_invoice_time = '".$od_invoice_time."',
                            od_delivery_text = '".$od_invoice."',
                            od_delivery_type = 'delivery1',
                            od_mobile = '".$od_mobile."',
                            od_naver_orderid = '".$OrderID."',
                            od_naver_sync_time = '".G5_TIME_YMDHIS."',
                            od_memo = '".$od_memo."',
                            od_cancel_price = '".$od_cancel_price."',
                            so_nb = '".$so_nb."',
                            od_pay_state = '1',
                            od_naver_PaymentMeans = '".$PaymentMeans."',
                            od_naver_PaymentCoreType = '".$PaymentCoreType."',
                            od_sales_manager = '1204'
                     ";
                sql_query($query, true);

                $ot_typereceipt_cate = '';
                switch ($PaymentMeans) {
                    case '신용카드':
                    case '신용카드 간편결제':
                        $ot_typereceipt_cate = '17';
                    break;
                    case '포인트결제':
                        $ot_typereceipt_cate = '14';
                    break;
                    default:
                        $ot_typereceipt_cate = '31';
                    break;
                }

                $query = " insert g5_shop_order_typereceipt set od_id = '".$od_id."', ot_typereceipt_cate = '".$ot_typereceipt_cate."'";
                sql_query($query);

                apms_order($od_id, $od_status, $od_mk);

                // 쿠폰업데이트
                apms_coupon_update($mb_id);

                // Push - 최고관리자에게 보냄 ---------------------------------------
                $mb_list = $config['cf_admin'].','.$config['as_admin'];
                $push = array(
                    'use'=>'od',
                    'flag'=>'new',
                    'od_name'=>$od_name,
                    'od_id'=>$od_id,
                    'od_amount'=>($od_cart_price + $od_send_cost + $od_send_cost2),
                    'od_status'=>$od_status,
                    'od_memo'=>$od_memo);
                apms_push($mb_list, $od_id, $od_id, G5_URL, $push);
                // ------------------------------------------------------------------

            }
            else {

                $query_common = "";
                //if ($IsReceiverAddressChanged) {
                    $query_common .= ", od_b_name = '".$od_b_name."', od_b_tel = '".$od_b_tel."', od_b_hp = '".$od_b_hp."', od_b_zip1 = '".$od_b_zip1."', od_b_zip2 = '".$od_b_zip2."', od_b_addr1 = '".$od_b_addr1."', od_b_addr2 = '".$od_b_addr2."' ";
                //}

                $query = " update {$g5['g5_shop_order_table']} set
                            od_status = '".$od_status."',
                            od_delivery_company = '".$od_delivery_company."',
                            od_invoice = '".$od_invoice."',
                            od_invoice_time = '".$od_invoice_time."',
                            od_delivery_text = '".$od_invoice."',
                            od_delivery_type = 'delivery1',
                            od_memo = '".$od_memo."',
                            od_cancel_price = '".$od_cancel_price."'
                            ".$query_common."
                        where od_naver_orderid = '".$OrderID."'
                     ";
                sql_query($query, true);
            }
        }

        return true;
    }

    // 상품평 동기화
    function reviewsync($status = '') {

        global $default, $g5, $config;

        $xml = $this->GetPurchaseReviewList();

        // 기본 응답 메시지
        $req = $xml->Body->GetPurchaseReviewListResponse;
        $ResponseType = (string)$req->ResponseType; // 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning)
        $Error = $req->Error; // 오류(error) 정보

        if (strtoupper($ResponseType) !== 'SUCCESS')
            return false;

        // 추가 응답 메시지
        $detaillevel    = (string)$req->DetailLevel; // 돌려 받는 데이터의 상세 정도(Compact/Full)
        $version        = (string)$req->Version; // API 응답 메시지 버전
        $timestamp      = (string)$req->Timestamp; // 메시지 호출 당시 서버 시각
        $messageid      = (string)$req->MessageID; // 메시지 아이디 (추후 추적 목적으로 사용)
        $ReturnedDataCount = (int)$req->ReturnedDataCount; // 이번 응답에 포함된 데이터의 개수
        $HasMoreData    = (string)$req->HasMoreData; // 데이터가 더 존재하는지 여부

        if ($ReturnedDataCount < 1)
            return true;

        $arr_rv = array();
        foreach ($req->PurchaseReviewList as $k => $v) {

            $row = array();
            $row['it_id'] = (string)$v->ProductID;
            $row['is_name'] = $this->decrypt($this->secret, (string)$v->WriterId);
            $row['is_score'] = (int)$v->PurchaseReviewScore;
            $row['is_subject'] = (string)$v->Title;
            $row['is_content'] = (string)$v->Content;
            $row['is_time'] = str_replace('T', ' ', substr((string)$v->CreateYmdt, 0, 19)); // 작성일시
            $row['is_confirm'] = ($default['de_item_use_use'] ? '0' : '1');
            $row['is_purchasereviewid'] = (string)$v->PurchaseReviewId; // 리뷰 일련 번호
            $row['is_productorderid'] = (string)$v->ProductOrderID; // 상품주문번호

            $arr_rv[] = $row;
        }

        foreach ($arr_rv as $k => $v) {

            $is = sql_fetch("select is_id from {$g5['g5_shop_item_use_table']} where is_purchasereviewid = '".$v['is_purchasereviewid']."'");
            if ($is['is_id']) {
                continue;
            }

            $is_content = $v['is_content'] ? $v['is_content'] : $v['is_subject']; // 태그처리 필요
            $is_content = addslashes($is_content);
            $is_subject = addslashes($v['is_subject']);

            $is_score = 3;
            switch ($v['is_score']) {
                case '13':
                    $is_score = '5'; // 매우만족
                break;
                case '12':
                    $is_score = '4'; // 만족
                break;
                case '11':
                    $is_score = '3'; // 보통
                break;
                case '10':
                    $is_score = '2'; // 불만
                break;
                case '5':
                    $is_score = '5'; // 매우만족
                break;
                case '4':
                    $is_score = '4'; // 만족
                break;
                case '3':
                    $is_score = '3'; // 보통
                break;
                case '2':
                    $is_score = '2'; // 불만
                break;
                case '1':
                    $is_score = '1'; // 매우불만
                break;
            }

            $is_time = str_replace('T', ' ', substr((string)$v['is_time'], 0, 19)); // 작성일시
            $is_time = date('Y-m-d H:i:s', strtotime($is_time.'+9Hour'));

            $query = "insert into {$g5['g5_shop_item_use_table']} set it_id = '".$v['it_id']."', is_name = '".$v['is_name']."', is_score = '".$is_score."', is_subject = '".$is_subject."', is_content = '".$is_content."', is_time = '".$is_time."', is_confirm = '".$v['is_confirm']."', is_purchasereviewid = '".$v['is_purchasereviewid']."', is_productorderid = '".$v['is_productorderid']."' ";
            sql_query($query, true);

            update_use_cnt($v['it_id']); // 후기횟수
            update_use_avg($v['it_id']); // 후기평점
        }

        return true;
    }

    // 문의내역 동기화
    function inquirysync() {

        global $default, $g5, $config;

        $xml = $this->GetCustomerInquiryList();

        // 기본 응답 메시지
        $req = $xml->Body->GetCustomerInquiryListResponse;
        $ResponseType = (string)$req->ResponseType; // 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning)
        $Error = $req->Error; // 오류(error) 정보

        if (strtoupper($ResponseType) !== 'SUCCESS')
            return false;

        // 추가 응답 메시지
        $detaillevel    = (string)$req->DetailLevel; // 돌려 받는 데이터의 상세 정도(Compact/Full)
        $version        = (string)$req->Version; // API 응답 메시지 버전
        $timestamp      = (string)$req->Timestamp; // 메시지 호출 당시 서버 시각
        $messageid      = (string)$req->MessageID; // 메시지 아이디 (추후 추적 목적으로 사용)
        $ReturnedDataCount = (int)$req->ReturnedDataCount; // 이번 응답에 포함된 데이터의 개수
        $HasMoreData    = (string)$req->HasMoreData; // 데이터가 더 존재하는지 여부

        if ($ReturnedDataCount < 1)
            return true;

        $arr_iq = array();
        foreach ($req->CustomerInquiryList->CustomerInquiry as $k => $v) {

            $row = array();
            $row['iq_inquiryid'] = (string)$v->InquiryID;
            $row['iq_productorderid'] = (string)$v->ProductOrderID;
            $row['iq_orderid'] = (string)$v->OrderID;
            $row['iq_name'] = (string)$v->CustomerName;
            $row['mb_id'] = (string)$v->CustomerID;
            $row['iq_subject'] = addslashes((string)$v->Title);
            $row['iq_question'] = addslashes(preg_replace('/\r\n|\r|\n/','<br />', (string)$v->InquiryContent));
            $row['iq_time'] = str_replace('T', ' ', substr((string)$v->InquiryDateTime, 0, 19)); // 작성일시

            // 답변
            $IsAnswered = (string)$v->IsAnswered;
            if (strtoupper($IsAnswered) == 'TRUE') {
                $row['iq_answercontentid'] = (string)$v->AnswerContentID;
                $row['iq_answer'] = preg_replace('/\r\n|\r|\n/','<br />', (string)$v->AnswerContent);
            }

            $arr_iq[] = $row;
        }

        if (count($arr_iq) > 0) {
            foreach ($arr_iq as $k => $v) {

                $iq = sql_fetch("select iq_id from {$g5['g5_shop_item_qa_table']} where iq_inquiryid = '".$v['iq_inquiryid']."'");
                if ($iq['iq_id']) { // 존재하므로 업데이트

                    $query = "update {$g5['g5_shop_item_qa_table']} set iq_subject = '".$v['iq_subject']."', iq_question = '".$v['iq_question']."', iq_answercontentid = '".$v['iq_answercontentid']."', iq_answer = '".$v['iq_answer']."' where iq_id = '".$iq['iq_id']."'";
                    sql_query($query);
                }
                else {

                    // 상품번호 추출
                    $ct = sql_fetch("select it_id from {$g5['g5_shop_cart_table']} where od_id = '".$v['iq_orderid']."' and  ProductOrderID = '".$v['iq_productorderid']."'");
                    $it_id = $ct['it_id'];

                    $query = "insert into {$g5['g5_shop_item_qa_table']} set it_id = '".$it_id."', mb_id = '".$v['mb_id']."', iq_name = '".$v['iq_name']."', iq_subject = '".$v['iq_subject']."', iq_question = '".$v['iq_question']."', iq_time = '".$v['iq_time']."', iq_inquiryid = '".$v['iq_inquiryid']."', iq_productorderid = '".$v['iq_productorderid']."', iq_orderid = '".$v['iq_orderid']."', iq_answercontentid = '".$v['iq_answercontentid']."', iq_answer = '".$v['iq_answer']."'";
                    sql_query($query, false);
                }
            }
        }

        return true;
    }

    // 상품주문내역 상세조회
    function GetProductOrderInfoList($ArrProductOrderID = array()) {

        if (!$ArrProductOrderID || empty($ArrProductOrderID)) {
            return;
        }

        $this->operation = 'GetProductOrderInfoList';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:GetProductOrderInfoListRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>';

                foreach ($ArrProductOrderID as $val) {
                    $rbody .= '<mall:ProductOrderIDList>'.$val.'</mall:ProductOrderIDList>';
                }

        $rbody .= '</mall:GetProductOrderInfoListRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 상품 주문 변경 내역 조회
    function GetChangedProductOrderList($InquiryExtraData = '') {

        $this->operation = 'GetChangedProductOrderList';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        // InquiryExtraData : 조회에 사용할 추가 데이터(예: 주문 번호). 조회 기간에 해당하는 데이터 중, 주문 번호 등의 항목 이 이 값 이상의 값을 갖는 데이터를 조회한다. 이 필 드에 대한 자세한 내용은 '부록 B InquiryExtraData'를 참조한다.
        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:GetChangedProductOrderListRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <base:InquiryTimeFrom>'.$this->InqTimeFrom.'+09:00</base:InquiryTimeFrom>
                <base:InquiryTimeTo>'.$this->InqTimeTo.'+09:00</base:InquiryTimeTo>';

        if ($InquiryExtraData) {
            $rbody .= '<base:InquiryExtraData>'.$InquiryExtraData.'</base:InquiryExtraData>';
        }
        $rbody .= '<mall:LastChangedStatusCode/>
                <mall:MallID>'.$this->mallID.'</mall:MallID>
            </mall:GetChangedProductOrderListRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 주문 번호에 포함된 상품 주문 번호를 조회한다.
    function GetProductOrderIDList($OrderID='') {

        $this->operation = 'GetProductOrderIDList';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:GetProductOrderIDListRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:OrderID>'.$OrderID.'</mall:OrderID>
                <mall:MallID>'.$this->mallID.'</mall:MallID>
            </mall:GetProductOrderIDListRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 고객이 해당 가맹점에서 상품을 구매한 후 평가한 내역을 조회한다.
    function GetPurchaseReviewList() {

        $this->operation = 'GetPurchaseReviewList';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:GetPurchaseReviewListRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <base:InquiryTimeFrom>'.$this->InqTimeFrom.'+09:00</base:InquiryTimeFrom>
                <base:InquiryTimeTo>'.$this->InqTimeTo.'+09:00</base:InquiryTimeTo>
                <base:InquiryExtraData></base:InquiryExtraData>
                <mall:MallID>'.$this->mallID.'</mall:MallID>';

        if (strtoupper($this->PurchaseReviewClassType) !== 'FULL') {
            $rbody .= '<mall:PurchaseReviewClassType>'.$this->PurchaseReviewClassType.'</mall:PurchaseReviewClassType>';
        }

        $rbody .= '    </mall:GetPurchaseReviewListRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문을 발주 처리한다.
    function PlaceProductOrder($ProductOrderID='', $CheckReceiverAddressChanged='0') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'PlaceProductOrder';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:PlaceProductOrderRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:CheckReceiverAddressChanged>'.($CheckReceiverAddressChanged ? 'true' : 'false').'</mall:CheckReceiverAddressChanged>
            </mall:PlaceProductOrderRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문을 발송 지연 처리한다.
    function DelayProductOrder($ProductOrderID='', $DispatchDueDate='', $DispatchDelayReasonCode='', $DispatchDelayDetailReason='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $DispatchDelayDetailReason = strip_tags($DispatchDelayDetailReason);

        $this->operation = 'DelayProductOrder';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:DelayProductOrderRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:DispatchDueDate>'.$DispatchDueDate.'</mall:DispatchDueDate>
                <mall:DispatchDelayReasonCode>'.$DispatchDelayReasonCode.'</mall:DispatchDelayReasonCode>
                <mall:DispatchDelayDetailReason>'.$DispatchDelayDetailReason.'</mall:DispatchDelayDetailReason>
            </mall:DelayProductOrderRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문을 발송 처리한다.
    function ShipProductOrder($ProductOrderID='', $DeliveryMethodCode='', $DeliveryCompanyCode='', $TrackingNumber='', $DispatchDate='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ShipProductOrder';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ShipProductOrderRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:DeliveryMethodCode>'.$DeliveryMethodCode.'</mall:DeliveryMethodCode>
                <mall:DeliveryCompanyCode>'.$DeliveryCompanyCode.'</mall:DeliveryCompanyCode>
                <mall:TrackingNumber>'.$TrackingNumber.'</mall:TrackingNumber>
                <mall:DispatchDate>'.$DispatchDate.'</mall:DispatchDate>
            </mall:ShipProductOrderRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문을 판매 취소한다.
    function CancelSale($ProductOrderID='', $CancelReasonCode='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'CancelSale';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:CancelSaleRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:CancelReasonCode>'.$CancelReasonCode.'</mall:CancelReasonCode>
            </mall:CancelSaleRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문에 대한 취소 요청을 승인한다.
    function ApproveCancelApplication($ProductOrderID='', $EtcFeeDemandAmount=0, $Memo='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ApproveCancelApplication';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ApproveCancelApplicationRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:EtcFeeDemandAmount>'.$EtcFeeDemandAmount.'</mall:EtcFeeDemandAmount>
                <Memo>'.$Memo.'</Memo>
            </mall:ApproveCancelApplicationRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문에 대한 반품을 접수 처리한다.
    function RequestReturn($ProductOrderID='', $ReturnReasonCode='', $CollectDeliveryMethodCode='RETURN_INDIVIDUAL', $CollectDeliveryCompanyCode='', $CollectTrackingNumber='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        // '직접 반송'(RETURN_INDIVIDUAL) 만 사용하여, 판매자가 직접 택배사 및 송장번호를 입력하여 수거지시를 하는 방식으로 사용됩니다.
        if ($CollectDeliveryMethodCode !== 'RETURN_INDIVIDUAL') {
            return false;
        }

        $this->operation = 'RequestReturn';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:RequestReturnRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:ReturnReasonCode>'.$ReturnReasonCode.'</mall:ReturnReasonCode>
                <mall:CollectDeliveryMethodCode>'.$CollectDeliveryMethodCode.'</mall:CollectDeliveryMethodCode>
                <mall:CollectDeliveryCompanyCode>'.$CollectDeliveryCompanyCode.'</mall:CollectDeliveryCompanyCode>
                <mall:CollectTrackingNumber>'.$CollectTrackingNumber.'</mall:CollectTrackingNumber>
                <Memo>'.$Memo.'</Memo>
            </mall:RequestReturnRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문에 대한 반품 요청을 승인한다. (수거완료)
    function ApproveReturnApplication($ProductOrderID='', $EtcFeeDemandAmount='', $Memo='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ApproveReturnApplication';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ApproveReturnApplicationRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:EtcFeeDemandAmount>'.$EtcFeeDemandAmount.'</mall:EtcFeeDemandAmount>
                <mall:Memo>'.$Memo.'</mall:Memo>
            </mall:ApproveReturnApplicationRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 반품 진행 중인 주문을 반품 거부 처리한다
    function RejectReturn($ProductOrderID='', $RejectDetailContent='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $RejectDetailContent = strip_tags($RejectDetailContent);

        $this->operation = 'RejectReturn';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:RejectReturnRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:RejectDetailContent>'.$RejectDetailContent.'</mall:RejectDetailContent>
            </mall:RejectReturnRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 반품 진행 중인 주문을 반품 보류 처리한다. (매뉴얼에 없음)
    function WithholdReturn($ProductOrderID='', $ReturnHoldCode='', $ReturnHoldDetailContent='', $EtcFeeDemandAmount='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $ReturnHoldDetailContent = strip_tags($ReturnHoldDetailContent);

        $this->operation = 'WithholdReturn';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:WithholdReturnRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:ReturnHoldCode>'.$ReturnHoldCode.'</mall:ReturnHoldCode>
                <mall:ReturnHoldDetailContent>'.$ReturnHoldDetailContent.'</mall:ReturnHoldDetailContent>
                <mall:EtcFeeDemandAmount>'.$EtcFeeDemandAmount.'</mall:EtcFeeDemandAmount>
            </mall:WithholdReturnRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 반품 중인 주문의 반품 보류를 해제한다.
    function ReleaseReturnHold($ProductOrderID='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ReleaseReturnHold';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ReleaseReturnHoldRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
            </mall:ReleaseReturnHoldRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 특정 상품 주문에 대한 교환을 수거 완료 처리한다.
    function ApproveCollectedExchange($ProductOrderID='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ApproveCollectedExchange';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ApproveCollectedExchangeRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
            </mall:ApproveCollectedExchangeRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 교환 승인된 특정 상품 주문을 재발송 처리한다.
    function ReDeliveryExchange($ProductOrderID='', $ReDeliveryMethodCode='', $ReDeliveryCompanyCode='', $ReDeliveryTrackingNumber='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ReDeliveryExchange';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ReDeliveryExchangeRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:ReDeliveryMethodCode>'.$ReDeliveryMethodCode.'</mall:ReDeliveryMethodCode>
                <mall:ReDeliveryCompanyCode>'.$ReDeliveryCompanyCode.'</mall:ReDeliveryCompanyCode>
                <mall:ReDeliveryTrackingNumber>'.$ReDeliveryTrackingNumber.'</mall:ReDeliveryTrackingNumber>
            </mall:ReDeliveryExchangeRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 교환 진행 중인 주문을 교환 거부 처리한다.
    function RejectExchange($ProductOrderID='', $RejectDetailContent='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $RejectDetailContent = strip_tags($RejectDetailContent);

        $this->operation = 'RejectExchange';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:RejectExchangeRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:RejectDetailContent>'.$RejectDetailContent.'</mall:RejectDetailContent>
            </mall:RejectExchangeRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 교환 진행 중인 주문을 교환 보류 처리한다.
    function WithholdExchange($ProductOrderID='', $ExchangeHoldCode='', $ExchangeHoldDetailContent='', $EtcFeeDemandAmount='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $ExchangeHoldDetailContent = strip_tags($ExchangeHoldDetailContent);

        $this->operation = 'WithholdExchange';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:WithholdExchangeRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
                <mall:ExchangeHoldCode>'.$ExchangeHoldCode.'</mall:ExchangeHoldCode>
                <mall:ExchangeHoldDetailContent>'.$ExchangeHoldDetailContent.'</mall:ExchangeHoldDetailContent>
                <mall:EtcFeeDemandAmount>'.$EtcFeeDemandAmount.'</mall:EtcFeeDemandAmount>
            </mall:WithholdExchangeRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 교환 보류 중인 주문의 교환 보류를 해제한다.
    function ReleaseExchangeHold($ProductOrderID='') {

        if (!$ProductOrderID || empty($ProductOrderID)) {
            return false;
        }

        $this->operation = 'ReleaseExchangeHold';
        $this->ReqUrl    = $this->targetUrl.'/'.$this->service;

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);
        $this->secret    = $this->generateKey($this->timestamp, $this->secretKey); // 암호키 생성

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://mall.checkout.platform.nhncorp.com/" xmlns:base="http://base.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:ReleaseExchangeHoldRequest>
                <base:AccessCredentials>
                    <base:AccessLicense>'.$this->accessLicense.'</base:AccessLicense>
                    <base:Timestamp>'.$this->timestamp.'</base:Timestamp>
                    <base:Signature>'.$this->signature.'</base:Signature>
                </base:AccessCredentials>
                <base:RequestID></base:RequestID>
                <base:DetailLevel>'.$this->detailLevel.'</base:DetailLevel>
                <base:Version>'.$this->version.'</base:Version>
                <mall:ProductOrderID>'.$ProductOrderID.'</mall:ProductOrderID>
            </mall:ReleaseExchangeHoldRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 문의 API : Live 환경에서만 가능
    function GetCustomerInquiryList() {

        $this->operation = 'GetCustomerInquiryList';
        $this->ReqUrl    = $this->targetUrl.'/CustomerInquiryService';
        $this->service   = 'CustomerInquiryService';
        $this->version   = '1.0';

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://customerinquiry.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:GetCustomerInquiryListRequest>
                <mall:AccessCredentials>
                    <mall:AccessLicense>'.$this->accessLicense.'</mall:AccessLicense>
                    <mall:Timestamp>'.$this->timestamp.'</mall:Timestamp>
                    <mall:Signature>'.$this->signature.'</mall:Signature>
                </mall:AccessCredentials>
                <mall:RequestID></mall:RequestID>
                <mall:DetailLevel>'.$this->detailLevel.'</mall:DetailLevel>
                <mall:Version>'.$this->version.'</mall:Version>
                <mall:ServiceType>CHECKOUT</mall:ServiceType>
                <mall:MallID>'.$this->mallID.'</mall:MallID>
                <mall:InquiryTimeFrom>'.$this->InqTimeFrom.'+09:00</mall:InquiryTimeFrom>
                <mall:InquiryTimeTo>'.$this->InqTimeTo.'+09:00</mall:InquiryTimeTo>';

        if (strtoupper($this->IsAnswered) !== 'FULL') {
            $rbody .= '<IsAnswered>'.$this->IsAnswered.'</IsAnswered>';
        }

        $rbody .= '</mall:GetCustomerInquiryListRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 문의 답변 API : Live 환경에서만 가능
    function AnswerCustomerInquiry($InquiryID='', $AnswerContent='', $AnswerContentID='', $ActionType='INSERT') {

        if (!$InquiryID || !$AnswerContent) {
            return false;
        }

        $replace_word = array('<br>', '<br/>', '<br />');
        $AnswerContent = str_ireplace($replace_word, '&#xa;', $AnswerContent);
        $AnswerContent = strip_tags($AnswerContent);

        $this->operation = 'AnswerCustomerInquiry';
        $this->ReqUrl    = $this->targetUrl.'/CustomerInquiryService';
        $this->service   = 'CustomerInquiryService';
        $this->version   = '1.0';

        $this->timestamp = $this->getTimestamp(); //타임스탬프를 포맷에 맞게 생성
        $this->signature = $this->generateSign($this->timestamp . $this->service . $this->operation, $this->secretKey);

        $rbody = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mall="http://customerinquiry.checkout.platform.nhncorp.com/">
            <soapenv:Header/>
            <soapenv:Body>
            <mall:AnswerCustomerInquiryRequest>
                <mall:AccessCredentials>
                    <mall:AccessLicense>'.$this->accessLicense.'</mall:AccessLicense>
                    <mall:Timestamp>'.$this->timestamp.'</mall:Timestamp>
                    <mall:Signature>'.$this->signature.'</mall:Signature>
                </mall:AccessCredentials>
                <mall:RequestID></mall:RequestID>
                <mall:DetailLevel>'.$this->detailLevel.'</mall:DetailLevel>
                <mall:Version>'.$this->version.'</mall:Version>
                <mall:ServiceType>CHECKOUT</mall:ServiceType>
                <mall:MallID>'.$this->mallID.'</mall:MallID>
                <mall:InquiryID>'.$InquiryID.'</mall:InquiryID>
                <mall:AnswerContent>'.$AnswerContent.'</mall:AnswerContent>
                <mall:AnswerContentID>'.$AnswerContentID.'</mall:AnswerContentID>
                <mall:ActionType>'.$ActionType.'</mall:ActionType>
            </mall:AnswerCustomerInquiryRequest>
            </soapenv:Body>
        </soapenv:Envelope>';
        $result = wznpayRequestBody($this->showReq, $this->service, $this->operation, $this->ReqUrl, $rbody);

        return $result;
    }

    // 캐싱시간확인
    function cache_check($section = '') {

        if (!$section || empty($section)) {
            return false;
        }

        //$cache_time   = 60 * 10; // 분
        $cache_time   = 20; // 초
        $cache_file   = G5_DATA_PATH.'/cache/naverpayorder-'.$section.'.php';
        $cache_fwrite = false;
        if(!file_exists($cache_file)) {
            $cache_fwrite = true;
        } else {
            if($cache_time > 0) {
                include($cache_file);
                $filetime = filemtime($cache_file);
                if ($this->is_cache_time && !$this->InqTimeFrom) {
                    $this->InqTimeFrom = $InqTimeFrom ? $InqTimeFrom : $this->InqTimeFrom ;
                }
                if($filetime && $filetime < (G5_SERVER_TIME - $cache_time)) {
                    @unlink($cache_file);
                    $cache_fwrite = true;
                }
            }
        }

        return $cache_fwrite;
    }

    // 캐싱파일생성
    function cache_build($section = '', $npResult = array()) {

        $cache_file   = G5_DATA_PATH.'/cache/naverpayorder-'.$section.'.php';
        $handle = fopen($cache_file, 'w');
        $cache_content = "<?php\nif (!defined('_GNUBOARD_')) exit;\n\$InqTimeFrom='".date("Y-m-d\TH:i:s", strtotime("-5 minutes"))."';";
        fwrite($handle, $cache_content);
        fclose($handle);
    }

    function convertArray($object) {
        return json_decode( json_encode( $object ), 1 );
    }

    // 주문상태 동기화
    function oderdetailsync($od_id = '') {

        global $default, $g5, $config;

        if (!$od_id) {
            return;
        }

        $query = "select GROUP_CONCAT(ProductOrderID SEPARATOR ',') as poids from {$g5['g5_shop_cart_table']} where od_id = '".$od_id."' ";
        $ct = sql_fetch($query);
        $poids = $ct['poids'];
        if (!$poids) {
            return;
        }
        $ProductOrderID = explode(',', $poids);
        $xml = $this->GetProductOrderInfoList($ProductOrderID);

        $req = $xml->Body->GetProductOrderInfoListResponse;
        $ResponseType = (string)$req->ResponseType; // 호출한 API 의 성공 여부(Success/SuccessWarning/Error/Error-Warning)
        $Error = $req->Error; // 오류(error) 정보

        if (strtoupper($ResponseType) !== 'SUCCESS') {
            return;
        }

        $od_cart_count = (int)$req->ReturnedDataCount;
        $od_cancel_price = 0;

        foreach ($req->ProductOrderInfoList as $k => $v) {

            $ProductOrderID     = (string)$v->ProductOrder->ProductOrderID; // 상품주문번호
            $ProductOrderStatus = (string)$v->ProductOrder->ProductOrderStatus; // 상품 주문 상태
            $ProductOrderStatus = productorderstatus_to_yc($ProductOrderStatus);

            // 주문상태가 취소이면 TotalPaymentAmount 값을 주문취소 od_cancel_price 에 담기.
            // od_cancel_price
            if ($ProductOrderStatus == '취소') { // 2019-04-22 : 부분취소 처리.
                $od_cancel_price += (string)$v->ProductOrder->TotalPaymentAmount;
            }

            $query2 = "update {$g5['g5_shop_cart_table']} set ct_status = '".$ProductOrderStatus."' where ProductOrderID = '".$ProductOrderID."'";
            sql_query($query2);

        }

        $query = " update {$g5['g5_shop_order_table']} set od_cancel_price = '".$od_cancel_price."' where od_id = '".$od_id."'";
        sql_query($query, true);
    }

}