<?php
include_once('./_common.php');

//header('Content-type: application/json');


$keyword = str_replace(' ', '', trim($_REQUEST["term"]));
$eform = $_GET['eform'];

// 22.12.07 : 서원 - 검색어가 없을 경우 DB전체 검색되던 부분 중단 처리.
if( !$keyword ) {
  $rows = [];
  echo json_encode($rows);
  exit();
}

$sql_list = [];
          $sql = "SELECT sum(ct_qty) as cnt FROM g5_shop_cart
              WHERE it_id = '{$_GET['prodId']}' AND mb_id = '{$member['mb_id']}'
              AND (ct_status = '주문' OR ct_status = '입금' OR ct_status = '준비' OR ct_status = '출고준비' OR ct_status = '배송');";
          $sql_result = sql_fetch($sql);
          if ($sql_result['cnt'] > 0) {
            // $option = str_replace("색상:", "", $row['ct_option']);
            // $option = str_replace("사이즈:", "", $option);
            // $option = str_replace(" ", "", $option);
            $data = array(
              'prodColor' => $option,
              'prodSize' => '',
              'prodNm' => $row['it_name'],
              'stoId' => '',
              'prodBarNum' => '',
              'regDtm' => '배송중 : ' . $sql_result['cnt'],
              'isShippingCnt' => 'Y'
            );
            $sql_list[] = $data;
          }
//판매재고 리스트
          $sendData = [];
          $prodsSendData = [];
          $sendData["usrId"] = $member["mb_id"];
          $sendData["entId"] = $member["mb_entId"];
          $sendData["prodId"] = $_GET['prodId'];//$_GET['prodId'];
          $sendData["pageNum"] = 1;
          $sendLength = 999999999;
          $sendData["pageSize"] = $sendLength;
          $sendData["stateCd"] =['01'];
          if($_GET['soption']=="1"){
            $sendData["prodBarNum"]=$_GET['stx'];
          }
          if($_GET['soption']=="2"){
            $sendData["searchOption"] =$_GET['stx'];
          }

          $oCurl = curl_init();
          curl_setopt($oCurl, CURLOPT_PORT, 9901);
          curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_SELECT_DETAIL_LIST);
          curl_setopt($oCurl, CURLOPT_POST, 1);
          curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
          $res = curl_exec($oCurl);
          $res = json_decode($res, true);
          curl_close($oCurl);
		  //print_r($res);
          $list = [];
          if($res["data"]){
            $list = $res["data"];
          }
# 페이징

		$return_arr[] = $keyword;
			for($i=0;$i<count($list);$i++) {
				if ($list[$i]['isShippingCnt'] !== 'Y') {
					if(strpos($list[$i]['prodBarNum'], $keyword) !== false){
						//$row["keyword"] = $keyword;
						//$row["barcode"] = $list[$i]['prodBarNum'];
						$return_arr[] = $list[$i]['prodBarNum'];
					}					
				}else {}
				//$return_arr[] = $list[$i]['prodBarNum'];
            }

echo json_encode($return_arr);
//echo $return_arr;
