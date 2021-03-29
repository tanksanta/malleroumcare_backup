<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

$step = $_POST['step'];
$api = $_POST["api"];

$k = false;
foreach($order_steps as $order_step) {
    if ( $order_step['val'] == $step ) {
        $k = true;
    }
}

if ( !$k ) {
    $ret = array(
        'result' => 'fail',
        'msg' => '정상적인 접근이 아닙니다.',
    );
    echo json_encode($ret);
    exit;
}

foreach($od_id as $odid) {
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$odid' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
            $ret = array(
            'result' => 'fail',
            'msg' => $odid . ' 주문번호로 주문서가 존재하지 않습니다.',
        );
        echo json_encode($ret);
        exit;
    }
}

$step_info = get_step($step);

foreach($od_id as $odid) {
    $sql = " update {$g5['g5_shop_order_table']}
        set od_status = '{$step}' ";
    $sql .= " where od_id = '{$odid}' ";
    sql_query($sql);

    set_order_admin_log($odid, '주문상태 ' . $step_info['name'] . ' 단계로 변경');
    
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$odid' ";
    $od = sql_fetch($sql);
	
	if($api){
		$curlURL = ($od["recipient_yn"] == "Y") ? "order" : "stock";
		
		$sendData = [];
		$sendData["usrId"] = $od["mb_id"];
		
		if($od["recipient_yn"] == "Y"){
			$sendData["penOrdId"] = $od["ordId"];
			$sendData["staOrdCd"] = $step_info["status{$od["recipient_yn"]}"];
			$sendData["prods"] = [];
		} else {
			$prodsList = [];
			$od["stoId"] = explode(",", $od["stoId"]);
			
			for($i = 0; $i < count($od["stoId"]); $i++){
				if($od["stoId"][$i]){
					$thisProd = [];
					$thisProd["stoId"] = $od["stoId"][$i];
					$thisProd["stateCd"] = $step_info["status{$od["recipient_yn"]}"];
					
					array_push($prodsList, $thisProd);
				}
			}
			
			$sendData["prods"] = $prodsList;
		}
		
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, 9001);
		curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/{$curlURL}/update");
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$res = curl_exec($oCurl);
		curl_close($oCurl);
	}
    
    // // PG 결제 취소
    // if($od['od_tno'] && $step == '취소') {
    //     switch($od['od_pg']) {
    //         case 'lg':
    //             require_once('./settle_lg.inc.php');
    //             $LGD_TID    = $od['od_tno'];        //LG유플러스으로 부터 내려받은 거래번호(LGD_TID)
                
    //             $xpay = new XPay($configPath, $CST_PLATFORM);
                
    //             // Mert Key 설정
    //             $xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
    //             $xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);
    //             $xpay->Init_TX($LGD_MID);
                
    //             $xpay->Set("LGD_TXNAME", "Cancel");
    //             $xpay->Set("LGD_TID", $LGD_TID);
                
    //             if ($xpay->TX()) {
    //                 //1)결제취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
    //                 /*
    //                 echo "결제 취소요청이 완료되었습니다.  <br>";
    //                 echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    //                 echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
    //                 */
    //             } else {
    //                 //2)API 요청 실패 화면처리
    //                 $msg = "결제 취소요청이 실패하였습니다.\\n";
    //                 $msg .= "TX Response_code = " . $xpay->Response_Code() . "\\n";
    //                 $msg .= "TX Response_msg = " . $xpay->Response_Msg();
                    
    //                 alert($msg);
    //             }
    //             break;
    //         case 'inicis':
    //             include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
    //             $cancel_msg = iconv_euckr('주문자 본인 취소-'.$cancel_memo);
                
    //             /*********************
    //              * 3. 취소 정보 설정 *
    //              *********************/
    //             $inipay->SetField("type",      "cancel");                        // 고정 (절대 수정 불가)
    //             $inipay->SetField("mid",       $default['de_inicis_mid']);       // 상점아이디
    //             /**************************************************************************************************
    //              * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
    //              * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
    //              * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
    //              * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
    //              **************************************************************************************************/
    //             $inipay->SetField("admin",     $default['de_inicis_admin_key']); //비대칭 사용키 키패스워드
    //             $inipay->SetField("tid",       $od['od_tno']);                   // 취소할 거래의 거래아이디
    //             $inipay->SetField("cancelmsg", $cancel_msg);                     // 취소사유
                
    //             /****************
    //              * 4. 취소 요청 *
    //              ****************/
    //             $inipay->startAction();
                
    //             /****************************************************************
    //              * 5. 취소 결과                                           	*
    //              *                                                        	*
    //              * 결과코드 : $inipay->getResult('ResultCode') ("00"이면 취소 성공)  	*
    //              * 결과내용 : $inipay->getResult('ResultMsg') (취소결과에 대한 설명) 	*
    //              * 취소날짜 : $inipay->getResult('CancelDate') (YYYYMMDD)          	*
    //              * 취소시각 : $inipay->getResult('CancelTime') (HHMMSS)            	*
    //              * 현금영수증 취소 승인번호 : $inipay->getResult('CSHR_CancelNum')    *
    //              * (현금영수증 발급 취소시에만 리턴됨)                          *
    //              ****************************************************************/
                
    //             $res_cd  = $inipay->getResult('ResultCode');
    //             $res_msg = $inipay->getResult('ResultMsg');
                
    //             if($res_cd != '00') {
    //                 alert(iconv_utf8($res_msg).' 코드 : '.$res_cd);
    //             }
    //             break;
    //         default:
    //             require_once(G5_SHOP_PATH.'/settle_kcp.inc.php');
                
    //             $_POST['tno'] = $od['od_tno'];
    //             $_POST['req_tx'] = 'mod';
    //             $_POST['mod_type'] = 'STSC';
    //             if($od['od_escrow']) {
    //                 $_POST['req_tx'] = 'mod_escrow';
    //                 $_POST['mod_type'] = 'STE2';
    //                 if($od['od_settle_case'] == '가상계좌')
    //                     $_POST['mod_type'] = 'STE5';
    //             }
    //             $_POST['mod_desc'] = iconv("utf-8", "euc-kr", '관리자 취소-'.$cancel_memo);
    //             $_POST['site_cd'] = $default['de_kcp_mid'];
    //             $kcp_json = true;
                
    //             // 취소내역 한글깨짐방지
    //             setlocale(LC_CTYPE, 'ko_KR.euc-kr');
                
    //             include G5_SHOP_PATH.'/kcp/pp_ax_hub.php';
                
    //             // locale 설정 초기화
    //             setlocale(LC_CTYPE, '');
    //     }
    //     set_order_admin_log($odid, 'PG 결제 취소 요청 : '.$od['od_pg']);
    // }
}

$ret = array(
    'result' => 'success',
    'msg' => '주문상태가 ' . $step_info['name'] . ' 단계로 변경되었습니다.',
);
$json = json_encode($ret);
echo $json;
?>