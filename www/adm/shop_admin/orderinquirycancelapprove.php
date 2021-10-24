<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id'");

if (!$od['od_id']) {
    alert("존재하는 주문이 아닙니다.");
}

$sql = "select *
        from g5_shop_order_cancel_request
        where od_id = '{$od['od_id']}'";

$cancel_request_row = sql_fetch($sql);

if ($cancel_request_row['request_type'] == "cancel")
    $status = "취소 승인";
if ($cancel_request_row['request_type'] == "return")
    $status = "반품 승인";

$sql = "update g5_shop_order_cancel_request
        set
            request_status = '{$status}',
            approved = 1
        where
            od_id = '{$od_id}'
        ";
sql_query($sql);


// 무통장 취소 처리
if ($cancel_request_row['request_type'] == "cancel") {

    // 장바구니 자료 취소

    $mb = get_member($od['mb_id']);

    //시스템 취소
    $stateCd ="06";
    $sql = "select * from g5_shop_cart where od_id = '{$od['od_id']}'";
    $sql_result = sql_query($sql);
    while ($row = sql_fetch_array($sql_result)) {
            $stoId=$stoId.$result_ct_s['stoId'];
            $usrId=$result_ct_s['mb_id'];
            $entId=$result_ct_s['mb_entId'];
    }

    $stoIdDataList = explode('|',$stoId);
    $stoIdDataList=array_filter($stoIdDataList);
    $stoIdData = implode("|", $stoIdDataList);
    $sendData["stoId"] = $stoIdData;
    $res = get_eroumcare2(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
    $result_again =$res['data'];
    $new_sto_ids = array_map(function($data) {
        global $stateCd;
        return array(
            'stoId' => $data['stoId'],
            'prodBarNum' => $data['prodBarNum'],
            'stateCd' => $stateCd
        );
    }, $result_again);
    $api_data = array(
        'usrId' => $usrId,
        'entId' => $entId,
        'prods' => $new_sto_ids,
    );
    $api_result = get_eroumcare(EROUMCARE_API_STOCK_UPDATE, $api_data);

    sql_query(" update {$g5['g5_shop_cart_table']} set ct_status = '취소' where od_id = '$od_id' ");

    // 주문 취소
    $cancel_memo = addslashes(strip_tags($cancel_request_row['request_reason']));
    $cancel_price = $od['od_cart_price'];
    
    $sql = " update {$g5['g5_shop_order_table']}
            set od_send_cost = '0',
                od_send_cost2 = '0',
                od_sales_discount = '0',
                od_receipt_price = '0',
                od_receipt_point = '0',
                od_misu = '0',
                od_cancel_price = '$cancel_price',
                od_cart_coupon = '0',
                od_coupon = '0',
                od_send_coupon = '0',
                od_status = '취소',
                od_shop_memo = concat(od_shop_memo,\"\\n취소 요청 관리자 승인 - ".G5_TIME_YMDHIS." (취소이유 : {$cancel_memo})\")
            where od_id = '$od_id' ";
    
    sql_query($sql);

    set_order_admin_log($od_id, '취소 요청 관리자 승인');

    // 주문취소 회원의 포인트를 되돌려 줌
    if ($od['od_receipt_point'] > 0)
        insert_point($od['mb_id'], $od['od_receipt_point'], "주문번호 $od_id 취소 요청 관리자 승인");
    
    // 쿠폰 취소
    sql_query("
      DELETE FROM
        g5_shop_coupon_log
      WHERE
        od_id = '{$od_id}'
    ");

    // 알림톡 발송
    $carts_result = sql_fetch(" select count(*) as cnt, it_name from g5_shop_cart where od_id = '$od_id' group by od_id ");
    $it_name_txt = $carts_result['it_name'];
    if($carts_result['cnt'] > 1)
        $it_name_txt .= ' 외 ' . ($carts_result['cnt'] - 1) . '건';
    send_alim_talk('OD_CANCEL_'.$od_id, $mb["mb_hp"], 'ent_order_cancel', "[주문취소 안내]\n{$mb['mb_entNm']}님, 고객님의 주문이 정상 취소처리 되었습니다.\n\n■ 주문일시 : ".date('Y/m/d H:i', strtotime($od['od_time']))."\n■ 주문번호 : {$od_id}\n■ 주문내역 : {$it_name_txt}\n■ 배송지 : {$od['od_b_addr1']} {$od['od_b_addr2']} {$od['od_b_addr3']} {$od['od_b_addr_jibeon']}");
}

// 반품 처리
if ($cancel_request_row['request_type'] == "return") {
    $sql = "update {$g5['g5_shop_order_table']}
        set
            od_status = '입고대기'
        where
            od_id = '{$od_id}'
        ";
    sql_query($sql);
}

// PG 결제 취소
if($od['od_tno']) {
    switch($od['od_pg']) {
        case 'lg':
            require_once('./settle_lg.inc.php');
            $LGD_TID    = $od['od_tno'];        //LG유플러스으로 부터 내려받은 거래번호(LGD_TID)
            
            $xpay = new XPay($configPath, $CST_PLATFORM);
            
            // Mert Key 설정
            $xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
            $xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);
            $xpay->Init_TX($LGD_MID);
            
            $xpay->Set("LGD_TXNAME", "Cancel");
            $xpay->Set("LGD_TID", $LGD_TID);
            
            if ($xpay->TX()) {
                //1)결제취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
                /*
                echo "결제 취소요청이 완료되었습니다.  <br>";
                echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
                echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
                */
            } else {
                //2)API 요청 실패 화면처리
                $msg = "결제 취소요청이 실패하였습니다.\\n";
                $msg .= "TX Response_code = " . $xpay->Response_Code() . "\\n";
                $msg .= "TX Response_msg = " . $xpay->Response_Msg();
                
                alert($msg);
            }
            break;
        case 'inicis':
            include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
            $cancel_msg = iconv_euckr('주문자 본인 취소-'.$cancel_memo);
            
            /*********************
             * 3. 취소 정보 설정 *
             *********************/
            $inipay->SetField("type",      "cancel");                        // 고정 (절대 수정 불가)
            $inipay->SetField("mid",       $default['de_inicis_mid']);       // 상점아이디
            /**************************************************************************************************
             * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
             * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
             * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
             * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
             **************************************************************************************************/
            $inipay->SetField("admin",     $default['de_inicis_admin_key']); //비대칭 사용키 키패스워드
            $inipay->SetField("tid",       $od['od_tno']);                   // 취소할 거래의 거래아이디
            $inipay->SetField("cancelmsg", $cancel_msg);                     // 취소사유
            
            /****************
             * 4. 취소 요청 *
             ****************/
            $inipay->startAction();
            
            /****************************************************************
             * 5. 취소 결과                                           	*
             *                                                        	*
             * 결과코드 : $inipay->getResult('ResultCode') ("00"이면 취소 성공)  	*
             * 결과내용 : $inipay->getResult('ResultMsg') (취소결과에 대한 설명) 	*
             * 취소날짜 : $inipay->getResult('CancelDate') (YYYYMMDD)          	*
             * 취소시각 : $inipay->getResult('CancelTime') (HHMMSS)            	*
             * 현금영수증 취소 승인번호 : $inipay->getResult('CSHR_CancelNum')    *
             * (현금영수증 발급 취소시에만 리턴됨)                          *
             ****************************************************************/
            
            $res_cd  = $inipay->getResult('ResultCode');
            $res_msg = $inipay->getResult('ResultMsg');
            
            if($res_cd != '00') {
                alert(iconv_utf8($res_msg).' 코드 : '.$res_cd);
            }
            break;
        default:
            require_once('./settle_kcp.inc.php');
            
            $_POST['tno'] = $od['od_tno'];
            $_POST['req_tx'] = 'mod';
            $_POST['mod_type'] = 'STSC';
            if($od['od_escrow']) {
                $_POST['req_tx'] = 'mod_escrow';
                $_POST['mod_type'] = 'STE2';
                if($od['od_settle_case'] == '가상계좌')
                    $_POST['mod_type'] = 'STE5';
            }
            $_POST['mod_desc'] = iconv("utf-8", "euc-kr", '주문자 본인 취소-'.$cancel_memo);
            $_POST['site_cd'] = $default['de_kcp_mid'];
            
            // 취소내역 한글깨짐방지
            setlocale(LC_CTYPE, 'ko_KR.euc-kr');
            
            include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
            
            // locale 설정 초기화
            setlocale(LC_CTYPE, '');
    }
    set_order_admin_log($od['od_id'], 'PG 결제 취소 요청 : '.$od['od_pg']);
}

goto_url($_SERVER['HTTP_REFERER']);
?>