<?php
include_once("./_common.php");

# 회원검사
if(!$member["mb_id"])
  json_response(500, '접근 권한이 없습니다.');

if(!$_POST["dc_id"])
  json_response(500, '정상적이지 않은 접근입니다.');

$response = array();
$N_count = 0;
$Y_count = 0;
$msg = "";
$it_code = "";
$sql = "SELECT ei.*, i.prodSupYn, i.it_soldout
FROM `eform_document_item` AS ei
LEFT OUTER JOIN `g5_shop_item` AS i ON i.it_name = ei.it_name AND i.ProdPayCode = ei.it_code 
AND i.ca_id LIKE (CASE
WHEN ei.gubun = '00' THEN '10%'
WHEN ei.gubun = '01' THEN '20%'
END)
WHERE ei.dc_id = UNHEX('".$_POST["dc_id"]."')
ORDER BY ei.it_code ASC";
$result = sql_query($sql);
while($row = sql_fetch_array($result)){
	if($row["prodSupYn"] == "N" || $row["it_soldout"] == "1"){
		$N_count++;
		$it_msg = ($row["prodSupYn"] == "N")?"비유통":"품절";
		if($it_code != $row["it_code"]){
			$msg .= $row["it_name"]." (".$it_msg.")\n";
		}
	}else{
		$Y_count++;
	}
	$it_code = $row["it_code"];
}

$response["N_count"] = $N_count;//주문 불가 상품 개수
$response["Y_count"] = $Y_count;//주문 가능 상품 개수
$response["msg"] = $msg;//주문불가 상품 메세지

header('Content-type: application/json');
echo json_encode($response);
?>