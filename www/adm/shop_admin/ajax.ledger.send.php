<?php
include_once('./_common.php');

header('Content-Type: application/json');

//원장 보내는 날짜가 10일 전이면 이전달 내용을 보내기
if (date('j') <= 10) {
  $firstdayofmonth = mktime(0,0,0, date('m'), 1, date('Y'));
  $prev_month = strtotime('-1 month', $firstdayofmonth);
  $fr_date = date('Y-m-01', $prev_month);
  $to_date = date('Y-m-t', $prev_month);
}

$send_data = $_POST['send_data'];
if (!$send_data) {
    $ret = array(
        'result' => 'fail',
        'msg' => '잘못된 요청입니다.',
    );
    echo json_encode($ret);
    exit;
}

$send_mail_arr = array();
$send_fax_arr = array();

foreach($send_data as $data) {
    $send_type = $data['send_type'];
    $ent_id = $data['ent_id'];
    $ent_name = $data['ent_name'];
    $mb_manager = $data['mb_manager'];
    $receiver = $data['receiver'];

    if ($send_type == 'E') {

        $mail_contents = '
            <div style="background-color:#f9f9f9;width:100%;max-width:800px;padding:30px;">
            <div style="padding-bottom:30px;border-bottom:1px solid #cfcfcf;">
                <div style="color:#333333;position:relative;width:70%;float:left;">
                    <p style="font-size:20px;padding:0;margin:0;">이로움 장기요양기관 통합관리플랫폼</p>
                    <b style="font-size:30px;">' . date('Y년 m월') . ' 거래처원장</p>
                </div>
                <div style="clear:both;"></div>
            </div>
            <div style="margin-top:50px;border-bottom:1px solid #cfcfcf;padding-bottom:20px;text-align: center;">
                <p style="font-size:18px;margin:0;text-align:left;padding-bottom:30px;">안녕하세요. 이로움 ' . $mb_manager . ' 담당자입니다.<br>항상 저희 이로움 플랫폼을 이용해 주셔서 진심으로 감사드립니다.<br><br>' . $ent_name . ' 사업소에서 거래하신 내역을 송부하였으니 확인 바랍니다.<br>더욱더 노력하는 이로움플랫폼이 되겠습니다.<br><br></p>
                <a href="' . G5_ADMIN_URL . '/shop_admin/ledger_excel.php?mb_id=' . $ent_id . '&fr_date=' . $fr_date . '&to_date=' . $to_date . '" target="_blank" style="background-color:#0aa2cd;display:inline-block;text-align:center;padding: 12px 60px;color:white;text-decoration:none;margin:20px auto;font-size:18px;">거래처원장 다운로드</a>
            </div>
            <p style="font-size:12px;color:#656565;margin:30px auto;text-align:center;">
                대표자: ' . $default['de_admin_company_owner'] . ' | 사업자등록번호: ' . $default['de_admin_company_saupja_no'] . ' | 통신판매신고번호: ' . $default['de_admin_tongsin_no'] . ' <br/>
                개인정보보호관리자: ' . $default['de_admin_info_name'] . ' | 주소: ' . $default['de_admin_company_addr'] . '
                <br/><br/>
                Copyright © ' . $default['de_admin_company_name'] . ' All rights reserved.
            </p>
            </div>
        ';

        $mb_id = $ent_id;
        ob_start();
        include('ledger_excel.php');
        $excelData = ob_get_contents();
        ob_end_clean();

        array_push($send_mail_arr, array(
            'subject' => '[이로움 장기요양기관 통합관리플랫폼] ' . date('m월') . ' 거래처원장 송부드립니다.',
            'content' => $mail_contents,
            'receiver' => trim($receiver),
            'file' => [
              'name' => 'ledger.xls',
              'data' => $excelData
            ],
        ));

        set_send_ledger_log($ent_id, $send_type, $receiver);
    }
    else if ($send_type == 'F') {
        // 팩스번호에서 숫자만 취한다
        $receive_number = preg_replace("/[^0-9]/", "", $receiver);  // 수신자번호 (회원님의 핸드폰번호)

        $Receivers[] = array(
            'rcv' => $receive_number,
            'rcvnm' => $ent_name
        );

        $mb_id = $ent_id;
        ob_start();
        include('ledger_excel.php');
        $excelData = ob_get_contents();
        ob_end_clean();

        array_push($send_fax_arr, array(
            'excel' => $excelData,
            'filename' => 'ledger.xls',
            'rcvnm' => $ent_name,
            'rcv' => $receive_number
        ));

/*        if ($response) {
            $ret = array(
                'result' => 'fail',
                'msg' => $response,
            );
            echo json_encode($ret);
            exit;   
        }
*/
        set_send_ledger_log($ent_id, $send_type, $receive_number);
    }
}

if (count($send_mail_arr) > 0) {
    // echo 'console.log("' . var_dump($send_mail_arr) . '")';
    include_once(G5_LIB_PATH.'/mailer.lib.php');
    mailer_multiple($config['cf_admin_email_name'], $config['cf_admin_email'], $send_mail_arr);
}

if (count($send_fax_arr) > 0) {
    // echo 'console.log("' . var_dump($send_mail_arr) . '")';
    include_once(G5_LIB_PATH.'/fax.lib.php');
    $response = sendFax($send_fax_arr);
    if ($response) {
        $ret = array(
            'result' => 'fail',
            'msg' => $response,
        );
        echo json_encode($ret);
        exit;   
    }
}



$ret = array(
    'result' => 'success',
    'msg' => '발송하였습니다.',
);

echo json_encode($ret);
?>