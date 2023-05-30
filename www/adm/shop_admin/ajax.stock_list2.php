<?php
include_once('./_common.php');
auth_check($auth[$sub_menu], "r");
header('Content-type: application/json');
if(!$member["mb_id"]){
  json_response(400, '먼저 로그인하세요.');
  exit;
}

if($_POST["stoId"] != ""){//상품관리코드 확인
	$sto_imsi = $_POST["stoId"];
		$stoIdDataList = explode('|',$sto_imsi);
		$stoIdDataList = array_filter($stoIdDataList);
		$stoIdData = implode("|", $stoIdDataList);
		$res = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, array(
		'stoId' => $stoIdData
		));
		$result_again = $res['data'];

	
	$_ct_barcode = [];

	  $sql_ct = " select `ct_id`, `stoId`, `ct_barcode` from {$g5['g5_shop_cart_table']} where ct_id = '".$_POST["ct_id"]."' ";
	  $result_ct = sql_query($sql_ct);

	  while($row_ct = sql_fetch_array($result_ct)) {
		$sto_imsi .= $row_ct['stoId'];
		
		if( $row_ct['ct_barcode'] ) 
		  $_ct_barcode[ $row_ct['ct_id'] ] = json_decode( $row_ct['ct_barcode'], true);
	  }
	
	$_barcode_list =[];
    if( $_ct_barcode ) {
      foreach ($_ct_barcode as $key => $val) { foreach ($val as $key2 => $val2) { $_barcode_list[ $key2 ] = $val2; } }
    }
	$stock_list2 = [];
    if ($result_again && count($result_again)) {
      foreach ($result_again as $stock) {
        $stock_list2[] = array(
          'prodId' => $stock['prodId'],
          'stoId' => $stock['stoId'],
          'prodBarNum' => $stock['prodBarNum']
        );

        if( $_barcode_list[ $stock['stoId'] ] == $stock['prodBarNum'] )
          unset($_barcode_list[ $stock['stoId'] ]);
        
      }

      //print_r( $_barcode_list );

      if ($result_again && count($result_again)) {
        foreach ($_barcode_list as $key => $val) {
          $stock_list2[] = array(
            'stoId' => $key,
            'prodBarNum' => $val,
            'del'=>'Y'
          );
        }
      }
      
    }
	$data = $stock_list2;
	echo json_encode($data);
	exit;
}else{
	json_response(400, '재고정보가 없습니다.');
	exit;
}

?>