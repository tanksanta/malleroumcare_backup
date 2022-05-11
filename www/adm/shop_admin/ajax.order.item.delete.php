<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

if ( !$od_id || !$it_id || !$uid ) {
  $ret = array(
    'result' => 'fail',
    'msg' => '정상적인 접근이 아닙니다.',
  );
  echo json_encode($ret);
  exit;
}

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
  $ret = array(
    'result' => 'fail',
    'msg' => '해당 주문번호로 주문서가 존재하지 않습니다.',
  );
  echo json_encode($ret);
  exit;
}

if ($od['od_penId']) {
  // 계약서 삭제
  sql_query("DELETE FROM `eform_document` WHERE od_id = '{$od['od_id']}' AND penId = '{$od['od_penId']}'");

  // 주문자 정보
  $od_member = get_member($od['mb_id']);

  // 수급자 정보
  $ent_pen = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
    'usrId' => $od_member['mb_id'],
    'entId' => $od_member['mb_entId'],
    'penId' => $od['od_penId'],
  ));
  $ent_pen = $ent_pen['data'][0];

  // 삭제할 상품 말고 가져오기
  $sql = " select MT.it_id,
    MT.ct_qty,
    MT.it_name,
    MT.io_id,
    MT.io_type,
    MT.ct_option,
    MT.ct_qty,
    MT.ct_id,
    ( SELECT it_time FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_time,
    ( SELECT prodSupYn FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodSupYn,
    ( SELECT ProdPayCode FROM g5_shop_item WHERE it_id = MT.it_id ) AS prodPayCode,
    ( SELECT it_delivery_cnt FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_cnt,
    ( SELECT it_delivery_price FROM g5_shop_item WHERE it_id = MT.it_id ) AS it_delivery_price,
    MT.ordLendStrDtm,
    MT.ordLendEndDtm
  from {$g5['g5_shop_cart_table']} MT
  where od_id = '{$od_id}'
  and ct_select = '1'
  and ct_id != '{$ct_id}'
  ";
  $result = sql_query($sql);
  
  $productList = [];
  $od_prodBarNum_total = 0;
  
  for ($i=0; $row=sql_fetch_array($result); $i++) {
    # 옵션값 가져오기
    $prodColor = $prodSize = $prodOption = '';
    $prodOptions = [];

    if ($row["io_id"]) { // 옵션값이 있으면
      $io_subjects = explode(',', $row['it_option_subject']);
      $io_ids = explode(chr(30), $row["io_id"]);

      for ($io_idx = 0; $io_idx < count($io_subjects); $io_idx++) {
        switch ($io_subjects[$io_idx]) {
          case '색상':
            $prodColor = $io_ids[$io_idx];
            break;
          case '사이즈':
            $prodSize = $io_ids[$io_idx];
            break;
          default:
            $prodOptions[] = $io_ids[$io_idx];
            break;
        }
      }
    }

    if ($prodOptions && count($prodOptions)) {
      $prodOption = implode('|', $prodOptions);
    }

    # 상품목록
    for ($ii = 0; $ii < $row["ct_qty"]; $ii++) {
      $thisProductData = [];
      $thisProductData["prodId"] = $row["it_id"];
      $thisProductData["prodColor"] = $prodColor;
      $thisProductData["prodSize"] = $prodSize;
      $thisProductData["prodOption"] = $prodOption;
      $thisProductData["prodBarNum"] = "";
      $thisProductData["prodManuDate"] = date("Y-m-d");
      $thisProductData["stoMemo"] = $g5_shop_order_cart_memo;
      $thisProductData["ct_id"] = $row["ct_id"];

      $it_name = $row['it_name'];
      if($row['it_name'] !== $row['ct_option']){
        $it_name = $it_name."(".$row['ct_option'].")";
      }
      $thisProductData["itemNm"] = $it_name;
      if ($row['ordLendStrDtm'] && $row['ordLendEndDtm']) {
        $thisProductData["ordLendStrDtm"] = date("Y-m-d", strtotime($row['ordLendStrDtm']));
        $thisProductData["ordLendEndDtm"] = date("Y-m-d", strtotime($row['ordLendEndDtm']));
      }
      array_push($productList, $thisProductData);
      $od_prodBarNum_total++;
    }
  }

  $prodsSendData = [];
  $prodsData = [];
  foreach ($productList as $key => $value) {
    $prodsData["prodId"] = $value["prodId"];
    $prodsData["prodColor"] = $value["prodColor"];
    $prodsData["prodSize"] = $value["prodSize"];
    $prodsData["prodOption"] = $value["prodOption"];
    $prodsData["prodManuDate"] = $value["prodManuDate"];
    $prodsData["prodBarNum"] = $value["prodBarNum"];
    $prodsData["stoMemo"] = $value["stoMemo"];
    $prodsData["ct_id"] = $value["ct_id"];
    $prodsData["itemNm"] = $value["itemNm"];
    // var_dump(strlen($value['ordLendStrDtm']));
    if (strlen($value['ordLendStrDtm']) === 10) {
      $prodsData["ordLendStrDtm"] = $value['ordLendStrDtm'];
      $prodsData["ordLendEndDtm"] = $value['ordLendEndDtm'];
    }
    array_push($prodsSendData, $prodsData);
  }

  
  // 기존 주문 삭제
  $delete = api_post_call(EROUMCARE_API_ORDER_DELETE, array(
    'usrId' => $od_member['mb_id'],
    'penOrdId' => $od["ordId"],
  ));

  // 새 주문 생성
  $sendData = [];
  $sendData["usrId"] = $od_member["mb_id"];
  $sendData["entId"] = $od_member["mb_entId"];
  $sendData["penId"] = $od['od_penId'];
  $sendData["prods"] = $prodsSendData;
  $sendData["penOrdId"] = $od["ordId"];
  $sendData["uuid"] = $od["uuid"];
  $sendData["penId"] = $od["od_penId"];
  $sendData["delGbnCd"] = "";
  $sendData["ordWayNum"] = "";
  $sendData["delSerCd"] = "";
  $sendData["ordNm"] = $od["od_b_name"];
  $sendData["ordCont"] = ($od["od_b_hp"]) ? $od["od_b_hp"] : $od["od_b_tel"];
  $sendData["ordMeno"] = $od["od_memo"];
  $sendData["ordZip"] = $od["od_b_zip1"] . $od["od_b_zip2"];
  $sendData["ordAddr"] = $od["od_b_addr1"];
  $sendData["ordAddrDtl"] = $od["od_b_addr2"];
  $sendData["finPayment"] = strval(calc_order_price($od['od_id']));
  $sendData["payMehCd"] = "0";
  $sendData["regUsrId"] = $member["mb_id"];
  $sendData["regUsrIp"] = $_SERVER["REMOTE_ADDR"];
  $sendData["prods"] = $prodsSendData;
  $sendData["documentId"] = ($ent_pen["penTypeCd"] == "04") ? "THK101_THK102_THK001_THK002_THK003" : "THK001_THK002_THK003";
  $sendData["eformType"] = ($ent_pen["penTypeCd"] == "04") ? "21" : "00";
  $sendData["conAcco1"] = $od_member["entConAcc01"];
  $sendData["conAcco2"] = $od_member["entConAcc02"];
  $sendData["returnUrl"] = "/adm/shop_admin/samhwa_orderform.php?od_id={$od['od_id']}";

  $res = api_post_call(EROUMCARE_API_ORDER_INSERT, $sendData);

  if ($res['errorYN'] === 'N') {
      
    //cart삭제
    $sql = "DELETE FROM `g5_shop_cart` WHERE `od_id` = '{$od['od_id']}' AND `ct_id` = '{$ct_id}'";
    sql_query($sql);
    
    // 새로운 시스템 주문 아이디 등록
    sql_query("
      UPDATE g5_shop_order SET
        ordId = '{$res["data"]["penOrdId"]}',
        uuid = '{$res["data"]["uuid"]}'
      WHERE od_id = '{$od['od_id']}'
    ");

    $ret = array(
      'result' => 'success',
      'msg' => '삭제되었습니다.',
    );
  } else {
    $ret = array(
      'result' => 'fail',
      'msg' => $res['message'],
    );
  }
  echo json_encode($ret);
  exit;
}

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

//stoId cart 에서 가져옴
$sql = "SELECT `stoId` FROM `g5_shop_cart` WHERE `od_id` = '{$od_id}' AND `ct_id` = '{$ct_id}'";
$result = sql_fetch($sql);

//시스템재고 삭제
$sendData  = [];
$sendData_stoId= [];
$sendData_stoId = explode('|',$result['stoId']);
$sendData_stoId['stoId']=array_filter($sendData_stoId);
$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_DELETE_MULTI);
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData_stoId, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
curl_close($oCurl);

//cart삭제
$sql = "DELETE FROM `g5_shop_cart` WHERE `od_id` = '{$od_id}' AND `it_id` = '{$it_id}' AND `ct_id` = '{$ct_id}'";
sql_query($sql);

// 상품정보
$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);
set_order_admin_log($od_id, '상품: ' . addslashes($it['it_name']) . ', ' . $it['io_id'] .' 상품 삭제');
samhwa_order_calc($od_id);

//들어있는 바코드수 구하기
$sto_imsi="";
$sql_ct = " select `stoId` from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
$result_ct = sql_query($sql_ct);

while($row_ct = sql_fetch_array($result_ct)) {
  $sto_imsi .=$row_ct['stoId'];
}

$stoIdDataList = explode('|',$sto_imsi);
$stoIdDataList=array_filter($stoIdDataList);
$stoIdData = implode("|", $stoIdDataList);

$count_b = 0;
$sendData["stoId"] = $stoIdData;
$res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, $sendData);
$result_again = $res['data'];
for($k=0; $k < count($result_again); $k++){
  if($result_again[$k]['prodBarNum']){
    $count_b++;
  }
}

// 상품수 수정
$sql = "
  select
    count(distinct it_id, ct_uid) as cart_count,
    count(*) as delivery_count
  from
    {$g5['g5_shop_cart_table']}
  where
    od_id = '$od_id'
";
$row = sql_fetch($sql);

//바코드 od_prodBarNum_insert 조정
//order total 수 조정
$sql = "
  update
    g5_shop_order
  set
    od_prodBarNum_insert = '{$count_b}',
    od_prodBarNum_total = '".count($result_again)."',
    od_cart_count = '{$row['cart_count']}',
    od_delivery_total = '{$row['delivery_count']}'
  where
    od_id = '{$od_id}'
";
sql_query($sql);

$ret = array(
  'result' => 'success',
  'msg' => '삭제되었습니다.',
);
echo json_encode($ret);
exit;

?>
