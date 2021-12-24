<?php
include_once('./_common.php');

// 테스트
// $jobCustCd = "101000";
// $apiHost = 'https://devapigw.llogis.com:10100'; 
// $igtCode = "eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJDMDEwNDI4IiwiYXVkIjoiQzAxMDQyOCIsIm5hbWUiOiJ0aGtjMTMwMCIsInNjb3BlIjoiUlNfQUREUiIsImV4cCI6MTUzNTEzNTU5OTk5OSwiaWF0IjoxNjM4ODU4NjY0fQ.uVd4Ea7rc3phkyQn9IiM5Tc-gYpK2ocrKIRvUn_62z8";

// 운영
$jobCustCd = "234678";
$apiUrl = 'https://apigw.llogis.com:10100';
$igtCode = "eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJDMDEyNTY1IiwiYXVkIjoiQzAxMjU2NSIsIm5hbWUiOiJ0aGtjMTMwMCIsInNjb3BlIjoiUlNfQUREUiIsImV4cCI6MTUzNTEzNTU5OTk5OSwiaWF0IjoxNjM5NTM0MDYwfQ.vdRf46jgtWCgGLfUsvGHVPR64iY6AAPHawCmnhHrSiY";

$apiUrl = $apiHost . '/api/pid/cus/714a/apiSndOut';
$zipApiUrl = $apiHost . '/api/address/post-list';
$headers = array(
    'Authorization:IgtAK ' . $igtCode,
    'Content-Type: application/json'
);

function edi_info($cart) {
    global $jobCustCd;

    $edi = array();
    $edi['jobCustCd']       = $jobCustCd; //거래처코드(고정)
    $edi['ustRtgSctCd']		= "01"; //(01:출고 02:반품)
    $edi['ordSct']	    	= "1"; //오더구분 (1:일반 2:교환 3:AS)
    $edi['fareSctCd']		= "01"; //운임구분(01:현불,02:착불,03:신용)
    $take_no                = $cart['od_id'] . '_' . $cart['ct_id'];
    $edi['ordNo']	    	= $take_no; //주문번호

    // 송하인
    $edi['snperNm'] 		= '이로움';
    $edi['snperTel']		= '032-562-6608';
    $edi['snperZipcd']		= '22850';
    $edi['snperAdr']    	= '인천 서구 정서진 8로 5 403동';
    
    // 수취인
    $edi['acperNm']	    	= $cart['od_b_name'];
    $edi['acperTel']		= $cart['od_b_tel'] ? $cart['od_b_tel'] : $cart['od_b_hp'];
    $edi['acperCpno']		= $cart['od_b_hp'] ? $cart['od_b_hp'] : $cart['od_b_tel'];
    $edi['acperAdr']	    = $cart['od_b_addr1'] . ' ' . $cart['od_b_addr2'];
    $edi['acperZipcd']      = $cart['od_b_zip1'] . $cart['od_b_zip2'];
    if (strlen($edi['acperZipcd']) < 5) {
        $zip = get_addr_zip($edi['acperAdr']);
        $edi['acperZipcd'] = $zip;
    }

    $edi['boxTypCd']	    = "A"; //박스크기(A,B,C,D,E,F)
    $edi['sumFare'] 	    = $cart['ct_delivery_price']; // 상품 배송비 //현/착불 기본운임


    $it_name = $cart["it_name"];
    if($it_name != $cart["ct_option"]){
        $it_name .= " ({$cart["ct_option"]})";
    }
    $it_name .= ' ' . $cart['ct_qty'] . '개';
    $edi['gdsNm']       = $it_name; // 상품명

    $edi['dlvMsgCont']			= $cart['od_memo']; //배달메세지
    $edi['cusMsgCont']			= $cart['od_memo']; //고객메세지

    return $edi;
}

function get_addr_zip($addr) {
    global $zipApiUrl, $headers;

    $req['mode'] = "road";
    $req['scwd'] = $addr;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zipApiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req));
    $res = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($res, true);
    $list = $result['list'];
    if (is_array($list) && count($list) > 0) {
        $result_zip = $list[0];
        return $result_zip['BAS_AREA_CD'];
    }
}

header('Content-Type: application/json');

$return_success = 0;
$return_failed = 0;

$where = '';
$ct_id = $_POST['ct_id'];
if ($ct_id) {
    $where = " AND c.ct_id = {$ct_id} ";
}
$sql = "SELECT 
    c.*, 
    o.od_name,
    o.od_addr1,
    o.od_addr2,
    o.od_tel,
    o.od_hp,
    o.od_b_name,
    o.od_b_addr1,
    o.od_b_addr2,
    o.od_b_tel,
    o.od_b_hp,
    o.od_id,
    o.od_b_zip1,
    o.od_b_zip2,
    o.od_memo
FROM 
    g5_shop_cart as c 
    LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
WHERE 
    c.ct_status = '출고준비'
    AND c.ct_delivery_cnt > 0 -- 박스개수 1개 이상
    AND c.ct_delivery_company = 'lotteglogis' -- 롯데택배
    AND ( c.ct_combine_ct_id IS NULL OR c.ct_combine_ct_id = '') -- 합포가 아닌것
    AND ( c.ct_delivery_num IS NULL OR c.ct_delivery_num = '') -- 송장번호 없는것
    AND c.ct_edi_result = 0 -- 아직 api 전송 하지 않은것
    AND c.ct_is_direct_delivery = 0 -- 직배송 아닌것
    -- and o.od_id = '2021122215392411'
    {$where}
ORDER BY c.ct_move_date ASC, o.od_id ASC
LIMIT 200
";

$cart_result = sql_query($sql);
$carts = array();
while ( $row2 = sql_fetch_array($cart_result) ) {
    $carts[] = $row2;
}

// $sql_v=[];
// $sql_v['v']=$sql;
// $json = json_encode($carts);
// echo $json;
// return false;
$snd_list = array();
foreach($carts as $cart) {

    // 추가 금액
    // $extraAmt				= 0;
    // if( $gprice > 500000 && $gprice <= 1000000 ){
    //     $extraAmt			= round($priceAmt * 0.5);
    // }else if( $gprice > 1000000 && $gprice <= 2000000 ){
    //     $extraAmt			= round($priceAmt * 0.8);
    // }else if( $gprice > 2000000 && $gprice <= 3000000 ){
    //     $extraAmt			= $priceAmt;
    // }
    // $extraAmt				= $extraAmt * $qty;

    ### 2018-09-11 :: 할증 및 상품 가격 0원
    $extraAmt				= 0;
    $gprice					= 0;

    // 박스 갯수만큼 송장 생성
    for($i = 0; $i < $cart['ct_delivery_cnt']; $i++) {
        $edi = edi_info($cart);

        $invoice_sql = "SELECT invoice FROM lotteglogis_invoice_num WHERE use_yn = '0' ORDER BY invoice ASC LIMIT 1;";
        $invoice_result = sql_fetch($invoice_sql);
        $invoice = $invoice_result['invoice'];
    
        $invoice_sql = "UPDATE lotteglogis_invoice_num SET use_yn = '1', use_dt = now() WHERE invoice = '{$invoice}' LIMIT 1;";
        sql_query($invoice_sql);
    
        $mod = (int)$invoice % 7;
        $edi['invNo']	    = $invoice . $mod; //송장번호

        array_push($snd_list, $edi);
    }

    // 합포
    $sql = "SELECT c.*, 
        o.od_name,
        o.od_addr1,
        o.od_addr2,
        o.od_tel,
        o.od_hp,
        o.od_b_name,
        o.od_b_addr1,
        o.od_b_addr2,
        o.od_b_tel,
        o.od_b_hp,
        o.od_id,
        o.od_b_zip1,
        o.od_b_zip2,
        o.od_memo 
    FROM  g5_shop_cart as c 
    LEFT JOIN g5_shop_order as o ON c.od_id = o.od_id
    WHERE c.ct_combine_ct_id = '{$cart['ct_id']}';";
    $combine_result = sql_query($sql);
    $j = 2;
    $combine_list = [];
    while ($combine_row = sql_fetch_array($combine_result)) {
        $edi['bdpkSctCd'] = 'Y'; //본상품 합포장 여부를 Y 로 변경
        $edi['bdpkKey'] = $edi['invNo']; //본상품 합포장 KEY를 송장번호로 설정
        $edi['bdpkRpnSeq'] = 1; //본상품 합포장 순번 1로 설정
    
        $combine_edi = edi_info($combine_row);
        $combine_edi['bdpkSctCd'] = 'Y'; //합포장 여부
        $combine_edi['bdpkKey'] = $edi['invNo']; //합포장 KEY
        $combine_edi['bdpkRpnSeq'] = $j; //합포장 순번
        $j++;

        array_push($combine_list, $combine_edi);
    }

    if (count($combine_list) > 0) {
        $snd_list = array_merge($snd_list, $combine_list);
    }
}

if (count($snd_list) == 0) {
    $ret = array(
        'result' => 'fail',
        'msg' => '전송할 데이터가 없습니다.',
    );
    echo json_encode($ret);
    exit;
}

$post_arr = array(
    'snd_list' => $snd_list
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_arr));
$res = curl_exec($ch);
curl_close($ch);
$result = json_decode($res, true);


$result_msg = '';
if (is_array($result) && array_key_exists('rtn_list', $result)) {
    foreach($result['rtn_list'] as $rtn) {
        $result_msg = $rtn['rtnMsg'];
        $ordNo = explode("_", $rtn['ordNo']);
        $od_id = $ordNo[0];
        $ct_id = $ordNo[1];
        if ($rtn['rtnCd'] == 'S') {
            $return_success++;    
            $update_invoice_query = " I.use_dt = now(), I.result = 'S', I.ordNo = '{$rtn['ordNo']}' ";
            $update_cart_query = " C.ct_edi_date = now(), C.ct_edi_result = '1' ";
            if ($rtn['invNo']) {
                $update_cart_query .= " , C.ct_delivery_num = IF(C.ct_delivery_num='', '{$rtn['invNo']}', CONCAT(C.ct_delivery_num, '|{$rtn['invNo']}')) ";
            }
        }
        else {
            $return_failed++;    
            $update_invoice_query = " I.use_dt = now(), I.result = 'E', I.ordNo = '{$rtn['ordNo']}', I.rtnMsg = '{$rtn['rtnMsg']}' ";
            $update_cart_query = "C.ct_edi_date = now(), C.ct_edi_msg = '{$rtn['rtnMsg']}' ";
        }
        $invNo = substr($rtn['invNo'], 0, strlen($string)-1);
        $sql = "UPDATE lotteglogis_invoice_num as I
            SET {$update_invoice_query}
            WHERE I.invoice = '{$invNo}'
            LIMIT 1;
        ";
        sql_query($sql);        
        $sql = "UPDATE g5_shop_cart as C 
            SET {$update_cart_query}
            WHERE C.ct_id = '{$ct_id}'
            LIMIT 1; 
        ";
        sql_query($sql);        
    }
}
else {
    $ret = array(
        'result' => 'fail',
        'msg' => '리턴값이 잘못되었습니다. 관리자에게 문의해 주세요.',
    );
    echo json_encode($ret);
    exit;
}


set_order_admin_log($cart['od_id'], $it_name . ' 롯데택배 API 전송');

// $ret = array(
//     'result' => 'success',
//     'msg' => 'EDI 전송이 완료되었습니다.',
// );

if ($return_success) { 
    $result = 'success';
}else{
    $result = 'fail';
}

$ret = array(
    'result' => $result,
    'msg' => '롯데택배 API 전송이 '. $return_success . '개 완료되었습니다. (' . $return_failed .'개 실패)',
    'return_success' => $return_success,
    'return_failed' => $return_failed,
);
$json = json_encode($ret);
echo $json;
?>