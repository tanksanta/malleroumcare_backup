<?php
include_once('./_common.php');

$prods = $_POST['prods'];
if(is_array($prods)) {
  foreach($prods as $prod) {
    if($prod['stateCd'] == '09') { // 대여종료
       $sql = "SELECT * FROM `g5_rental_log` WHERE stoId='{$prod['stoId']}'
        ORDER BY dis_total_date DESC LIMIT 1 ";
	   $row = sql_fetch($sql);
		$now = date("Y-m-d");
		$dis_total_date=G5_TIME_YMDHIS;
	   if(strtotime($now)<strtotime($row["enddate"])){//중도 해지 로그 생성
		   $rental_log_Id="rental_log".round(microtime(true)).rand();
		   $sql = " insert into `g5_rental_log` set
				  `rental_log_Id` = '$rental_log_Id',
				  `stoId` = '{$prod['stoId']}',
				  `ordId` = '{$row['ordId']}',
				  `strdate` = '{$row['strdate']}',
				  `enddate` = '{$row['enddate']}',
				  `dis_total_date` = '$dis_total_date',
				  `ren_person` = '종료<br>(".$now.")',
				  `ren_eformUrl` = 'cancel',
				  `rental_log_division` = '2' ";
		  sql_query($sql);
	   }
	  //추가
	  sql_query("
        UPDATE
          stock_custom_order
        SET
          sc_rent_state = 'done',
          sc_updated_at = NOW()
        WHERE
          sc_stoId = '{$prod['stoId']}' and
          sc_rent_state = 'rent'
      ");
    }
  }
}
// header("Content-Type: application/json");

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_UPDATE);
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);
  echo $res;

?>
