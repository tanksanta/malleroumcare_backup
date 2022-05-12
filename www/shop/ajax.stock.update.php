<?php
include_once('./_common.php');

$prods = $_POST['prods'];
if(is_array($prods)) {
  foreach($prods as $prod) {
    if($prod['stateCd'] == '09') { // 대여종료
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
<<<<<<< HEAD
curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/update");
=======
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_UPDATE);
>>>>>>> dev
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($_POST, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);
  echo $res;

?>
