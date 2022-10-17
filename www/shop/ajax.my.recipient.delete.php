<?php
include_once("./_common.php");

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["penId"] = $id;

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_RECIPIENT_SELECTLIST);
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);

$data = $res["data"][0];
if(!$data){
  json_response(500, '존재하지 않는 데이터입니다');
}


$sql = "SELECT count(c.ct_id) as cnt FROM g5_shop_cart as c 
INNER JOIN g5_shop_order as o ON c.od_id = o.od_id
WHERE 
  c.mb_id = '{$member['mb_id']}'
  AND (c.ct_pen_id = '{$id}' OR o.od_penId = '{$id}') 
  AND c.ordLendEndDtm > now()
";

$count = sql_fetch($sql);

if ($count['cnt']) {
  json_response(500, '대여중인 품목이 있는 수급자는 삭제가 불가능합니다');
}

$data["penExpiDtm"] = explode(" ~ ", $data["penExpiDtm"]);

$sendData = [];
$sendData["penId"] = $data["penId"];
$sendData["entId"] = $member["mb_entId"];
$sendData["usrId"] = $member["mb_id"];
$sendData["delYn"] = "Y";

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_RECIPIENT_UPDATE);
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);
 
$delete_macro = "DELETE FROM macro_request WHERE mb_id = '".$member['mb_id']."' AND recipient_num = '".str_replace('L', '', $data['penLtmNum'])."';";
$delete_hist = "DELETE FROM pen_purchase_hist WHERE ENT_ID = '".$member['mb_entId']."' AND PEN_LTM_NUM = '".$data['penLtmNum']."';";

sql_query($delete_macro);
sql_query($delete_hist);

json_response(200, 'OK');

?>
