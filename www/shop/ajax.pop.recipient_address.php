<?php
include_once("./_common.php");

if(!$member['mb_id'])
    json_response(400, '먼저 로그인하세요.');

$page = $_GET["page"] ?? 1;

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["appCd"] = "01";
$sendData["pageNum"] = $page;
$sendData["pageSize"] = 10;

if($_GET["penNm"]){
    $sendData["penNm"] = $_GET["penNm"];
}

if($_GET["penTypeCd"] && $_GET["penTypeCd"] !== "수급자구분"){
    $sendData["penTypeCd"] = $_GET["penTypeCd"];
}

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
<<<<<<< HEAD
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/recipient/selectList");
=======
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_RECIPIENT_SELECTLIST);
>>>>>>> dev
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);

$list = [];
foreach($res['data'] as $data) {
    $list[] = $data;
}

$ret = '';

if($list) {
    foreach($list as $data) {
        $address = [
            get_text($data['penNm']),
            get_text($data['penConNum']),
            get_text($data['penConPnum']),
            get_text($data['penZip']),
            get_text($data['penAddr']),
            get_text($data['penAddrDtl'])
        ];

        $address = implode(chr(30), $address);
        
        $ret .= '
            <li>
                <table class="tbl_static">
                <tr>
                    <td>수급자명</td>
                    <td>'.$data["penNm"].'</td>
                </tr>
                <tr>
                    <td>장기요양번호</td>
                    <td>'.($data["penLtmNum"] ? $data["penLtmNum"] : '-').'</td>
                </tr>
                <tr>
                    <td>주소</td>
                    <td>'.$data['penAddr'].$data['penAddrDtl'].'</td>
                </tr>
                </table>
                <a href="javascript:void(0)" class="sel_address" data-target="'.$address.'" title="선택">선택</a>
            </li>
        ';
    }

    $is_last = false;
} else {
    $ret = '
        <div class="empty_list">
            수급자가 없습니다.
        </div>
    ';
    $is_last = true;
}

json_response(200, 'OK', array(
    'is_last' => $is_last,
    'html' => $ret
));
