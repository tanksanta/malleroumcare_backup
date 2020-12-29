<?php
include_once('./_common.php');

header('Content-Type: application/json');

$od_ids = array();
if ( is_array($od_id) ) {
    $od_ids = $od_id;
}else{
    $od_ids[0] = $od_id;
}

foreach($od_ids as $od_id) {
    if (!$od_id) {
        continue;
    }
    
    $od_id = trim($od_id);

    //------------------------------------------------------------------------------
    // 주문서 정보
    //------------------------------------------------------------------------------
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
        continue;
    }

	$url = 'https://r.sabangnet.co.kr/RTL_API/xml_order_invoice.html?xml_url=https://'.$_SERVER['HTTP_HOST'].'/sabangnet/xml_sendnum.php?od_id='.$od['od_id'];
	$ch = cURL_init();

	cURL_setopt($ch, CURLOPT_URL, $url);
	cURL_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = iconv("UTF-8","EUC-KR",cURL_exec($ch));
	cURL_close($ch); 

	$object = simplexml_load_string($response,'SimpleXMLElement', LIBXML_NOCDATA);
	$json = json_encode($object);
	$obj  = json_decode($json, TRUE);

    $total_cnt = $obj["HEADER"]["TOTAL_COUNT"]; //주문내역수량

    if($total_cnt>0){
	 
	  if($total_cnt>1){

		  for($i=0; $i<$total_cnt; $i++){

			$data_arr = $obj["DATA"][$i];

			$result = $data_arr["RESULT"];

			if($result=="SUCCESS"){
				$ret = array(
						'result' => 'success',
						'msg' => '전송이 완료되었습니다.',
				);
			}else{
				$result_msg = $data_arr["RESULT_MSG"];

				$ret = array(
						'result' => 'fail',
						'msg' => $result,
				);
			 }
		}
	  }else{
		$data_arr = $obj["DATA"];
		$result = $data_arr["RESULT"];

		if($result=="SUCCESS"){
			$ret = array(
					'result' => 'success',
					'msg' => '전송이 완료되었습니다.',
			);
		}else{
			$result_msg = $data_arr["RESULT_MSG"];

			$ret = array(
					'result' => 'fail',
					'msg' => $result,
			);
		 }
	  }
	}
}

if(!$ret){
	$ret = array(
			'result' => 'fail',
			'msg' => '전송할 정보가 없습니다.',
		);
}
$json = json_encode($ret);
echo $json;
?>
