<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/common_cron.php');

header('Content-Type: text/html; charset=utf-8');
$url = 'https://r.sabangnet.co.kr/RTL_API/xml_order_info.html?xml_url=https://signstand.co.kr/sabangnet/xml_order_send.php';
$ch = cURL_init();

cURL_setopt($ch, CURLOPT_URL, $url);
cURL_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = cURL_exec($ch);
cURL_close($ch); 

//echo $response;

$object = simplexml_load_string($response,'SimpleXMLElement', LIBXML_NOCDATA);
$json = json_encode($object);
$obj  = json_decode($json, TRUE);

$total_cnt = $obj["HEADER"]["TOTAL_COUNT"]; //주문내역수량

if($total_cnt>1){

	for($i=0; $i<$total_cnt; $i++){

		$data_arr = $obj["DATA"][$i];

		$od_id = $data_arr["IDX"];      //사방넷주문번호
		$sabang_od_id = $data_arr["ORDER_ID"]; //쇼핑몰주문번호
		$mall_id = $data_arr["MALL_ID"]; //주문쇼핑몰
		$order_status = $data_arr["ORDER_STATUS"]; //주문상태
		$delv_msg = (is_array($data_arr["DELV_MSG"])==1)?"":$data_arr["DELV_MSG"]; //배송요청사항
		$product_id = $data_arr["MALL_PRODUCT_ID"]; //상품코드
		$product_name = $data_arr["PRODUCT_NAME"]; //상품명
		$product_cnt = $data_arr["SALE_CNT"]; //주문수량
		$option_nm = $data_arr["SKU_VALUE"]; //옵션명
		$product_amount = $data_arr["SALE_COST"]; //주문가격
		$product_total_amount = $data_arr["PAY_COST"]; //결제가격
		$delivery_price = $data_arr["DELV_COST"]; //배송비
		$user_id = (is_array($data_arr["USER_ID"])==1)?"":$data_arr["USER_ID"]; //주문쇼핑몰 ID
		$user_name = $data_arr["USER_NAME"]; //주문자명
		$user_tel = (is_array($data_arr["USER_TEL"])==1)?"":$data_arr["USER_TEL"]; //주문자전화번호
		$user_cel = $data_arr["USER_CEL"]; //주문자핸드폰번호
		$user_email = (is_array($data_arr["USER_EMAIL"])==1)?"":$data_arr["USER_EMAIL"]; //주문자이메일

		$recv_name = $data_arr["RECEIVE_NAME"]; //받는분 성함
		$recv_tel = (is_array($data_arr["RECEIVE_TEL"])==1)?"":$data_arr["RECEIVE_TEL"]; //받는분 연락처
		$recv_cel = $data_arr["RECEIVE_CEL"]; //받는분 핸드폰번호
		$recv_zipcode1 = substr($data_arr["RECEIVE_ZIPCODE"],0,3); //받는분 우편번호
		$recv_zipcode2 = substr($data_arr["RECEIVE_ZIPCODE"],3,2); //받는분 우편번호
		$recv_addr = $data_arr["RECEIVE_ADDR"]; //받는분 주소
		$od_receipt_time = $data_arr["REG_DATE"]; //수집일자

		$cart_row=sql_fetch("select count(*) as cnt from {$g5['g5_shop_order_table']} where sabang_od_id like '%".$od_id."%'");

		if($cart_row['cnt']==0){

            $it = sql_fetch("select * from g5_shop_matching where oit_id='".$product_id."'");

			//it_sc_price='{$it['it_sc_price']}',it_sc_qty='{$it['it_sc_qty']}',ct_price='{$it['ct_price']}', ct_point='{$it['ct_point']}',


            //기존 주문과 동일한게 있다면
     		$sabang_order_row=sql_fetch("select od_id,sabang_od_id from {$g5['g5_shop_order_table']} where sabang_order_id='".$sabang_od_id."'");
			$d_od_id = "";
			if($sabang_order_row['od_id']){
				$d_od_id = $od_id;
				$od_id = $sabang_order_row['od_id'];
			}

            if($it['it_id']){
     			$cart_in_sql = "insert into {$g5['g5_shop_cart_table']} set od_id='{$od_id}',
                it_id='{$it['it_id']}', it_name='".addslashes($it['it_name'])."', it_sc_type='{$it['it_sc_type']}', it_sc_method='{$it['it_sc_method']}', it_sc_minimum='{$it['it_sc_minimum']}', ct_price='".$product_amount."', it_sc_qty='".$product_cnt."', ct_status='오픈마켓', ct_sendcost='".$delivery_price."',  ct_point_use='0', ct_stock_use='0', ct_option='$io_value', ct_qty='{$it['ct_qty']}', ct_notax='{$it['it_notax']}', ct_option='".$option_nm."', io_type='{$it['io_type']}',ct_time=now(),pt_old_name='".$product_name."',pt_old_opt='".$option_nm."'";    
				
				$product_amount = $it['ct_price'] * $it['ct_qty'];
			}else{
     			$cart_in_sql = "insert into {$g5['g5_shop_cart_table']} set od_id='".$od_id."',it_id='".$product_id."',it_name='".$product_name."',ct_status='오픈마켓',ct_qty='".$product_cnt."',ct_price='".$product_amount."',ct_sendcost='".$delivery_price."', ct_option='".$option_nm."',io_price='0' ,ct_time=now(),pt_old_name='".$product_name."',pt_old_opt='".$option_nm."'";
			}

			//echo $cart_in_sql."<br>";

			sql_query($cart_in_sql);

			if($d_od_id==""){
				$order_in_sql = "insert into {$g5['g5_shop_order_table']} set od_id='".$od_id."',od_name='".$user_name."',od_email='".$user_email."',od_tel='".$user_tel."',od_hp='".$user_cel."',od_b_name='".$recv_name."',od_b_tel='".$recv_tel."',od_b_hp='".$recv_cel."',od_b_zip1='".$recv_zipcode1."',od_b_zip2='".$recv_zipcode2."',od_b_addr1='".$recv_addr."',od_memo='".$delv_msg."',od_cart_count=1,od_delivery_price='".$delivery_price."',od_cart_price='".$product_total_amount."',od_status='오픈마켓',od_settle_case='신용카드',od_receipt_price='".$product_total_amount."',od_receipt_time='".$od_receipt_time."',od_pay_state='1',od_time=now(),od_send_admin_memo='[오픈마켓] ".$mall_id."/".$user_id."',od_writer='openmarket',sabang_od_id='".$od_id."',sabang_order_id='".$sabang_od_id."',sabang_market='".$mall_id."'";

				//echo $order_in_sql."<br>";

				sql_query($order_in_sql);

				$ot_typereceipt_cate=($mall_id=="오너클랜")?25:16;

				$typereceipt_in_sql = "insert into g5_shop_order_typereceipt set od_id='".$od_id."',ot_typereceipt_cate='".$ot_typereceipt_cate."',ot_typereceipt=0";

				sql_query($typereceipt_in_sql);

		    }else{
				$order_up_sql = "update {$g5['g5_shop_order_table']} set od_cart_count=od_cart_count+1,od_delivery_price=od_delivery_price+".$delivery_price.",od_cart_price=od_cart_price+".$product_total_amount.",od_receipt_price=od_receipt_price+".$product_total_amount.",sabang_od_id=concat(sabang_od_id,',',".$d_od_id.") where sabang_order_id='".$sabang_od_id."'";

				sql_query($order_up_sql);
			}
		}	

	}

if($iframe){
	echo "<script>alert('전송이 완료되었습니다.');location.href='about:blank';</script>";
}else{
	echo "success";
}
    exit;

}else if($total_cnt==1){

		$data_arr = $obj["DATA"];

		$od_id = $data_arr["IDX"];      //사방넷주문번호
		//$od_id = $data_arr["ORDER_ID"]; //쇼핑몰주문번호
		$mall_id = $data_arr["MALL_ID"]; //주문쇼핑몰
		$order_status = $data_arr["ORDER_STATUS"]; //주문상태
		$delv_msg = (is_array($data_arr["DELV_MSG"])==1)?"":$data_arr["DELV_MSG"]; //배송요청사항
		$product_id = $data_arr["MALL_PRODUCT_ID"]; //상품코드
		$product_name = $data_arr["PRODUCT_NAME"]; //상품명
		$product_cnt = $data_arr["SALE_CNT"]; //주문수량
		$option_nm = $data_arr["SKU_VALUE"]; //옵션명		
		$product_amount = $data_arr["SALE_COST"]; //주문가격
		$product_total_amount = $data_arr["PAY_COST"]; //주문가격
		$delivery_price = $data_arr["DELV_COST"]; //배송비
		$user_id = (is_array($data_arr["USER_ID"])==1)?"":$data_arr["USER_ID"]; //주문쇼핑몰 ID
		$user_name = $data_arr["USER_NAME"]; //주문자명
		$user_tel = (is_array($data_arr["USER_TEL"])==1)?"":$data_arr["USER_TEL"]; //주문자전화번호
		$user_cel = $data_arr["USER_CEL"]; //주문자핸드폰번호
		$user_email = (is_array($data_arr["USER_EMAIL"])==1)?"":$data_arr["USER_EMAIL"]; //주문자이메일

		$recv_name = $data_arr["RECEIVE_NAME"]; //받는분 성함
		$recv_tel = (is_array($data_arr["RECEIVE_TEL"])==1)?"":$data_arr["RECEIVE_TEL"]; //받는분 연락처
		$recv_cel = $data_arr["RECEIVE_CEL"]; //받는분 핸드폰번호
		$recv_zipcode = explode("-",$data_arr["RECEIVE_ZIPCODE"]); //받는분 우편번호
		$recv_addr = $data_arr["RECEIVE_ADDR"]; //받는분 주소
		$od_receipt_time = $data_arr["REG_DATE"]; //수집일자

		$cart_row=sql_fetch("select count(*) as cnt from {$g5['g5_shop_order_table']} where od_id='".$od_id."'");

		if($cart_row['cnt']==0){

            $it = sql_fetch("select * from g5_shop_matching where oit_id='".$product_id."'");

            //기존 주문과 동일한게 있다면
     		$sabang_order_row=sql_fetch("select od_id,sabang_od_id from {$g5['g5_shop_order_table']} where sabang_order_id='".$sabang_od_id."'");
			$d_od_id = "";
			if($sabang_order_row['od_id']){
				$d_od_id = $od_id;
				$od_id = $sabang_order_row['od_id'];
			}

            if($it['it_id']){
     			$cart_in_sql = "insert into {$g5['g5_shop_cart_table']} set od_id='{$od_id}',
                it_id='{$it['it_id']}', it_name='".addslashes($it['it_name'])."', it_sc_type='{$it['it_sc_type']}', it_sc_method='{$it['it_sc_method']}', it_sc_price='{$it['it_sc_price']}', it_sc_minimum='{$it['it_sc_minimum']}', it_sc_qty='".$product_cnt."', ct_status='오픈마켓', ct_price='".$product_amount."', ct_point='{$it['ct_point']}', ct_point_use='0', ct_stock_use='0', ct_option='".$option_nm."', ct_sendcost='".$delivery_price."', ct_qty='{$it['ct_qty']}', ct_notax='{$it['it_notax']}', io_price='{$it['io_price']}',ct_time=now(),pt_old_name='".$product_name."',pt_old_opt='".$option_nm."'";    
				
				$product_total_amount = $it['ct_price'] * $it['ct_qty'];
			}else{
     			$cart_in_sql = "insert into {$g5['g5_shop_cart_table']} set od_id='".$od_id."',it_id='".$product_id."',it_name='".$product_name."',ct_status='오픈마켓',ct_qty='".$product_cnt."',ct_price='".$product_amount."',ct_sendcost='".$delivery_price."', ct_option='".$option_nm."',io_price='0',ct_time=now(),pt_old_name='".$product_name."',pt_old_opt='".$option_nm."'";
			}

			//echo $cart_in_sql."<br>";

			sql_query($cart_in_sql);

			if($d_od_id==""){

				$order_in_sql = "insert into {$g5['g5_shop_order_table']} set od_id='".$od_id."',od_name='".$user_name."',od_email='".$user_email."',od_tel='".$user_tel."',od_hp='".$user_cel."',od_b_name='".$recv_name."',od_b_tel='".$recv_tel."',od_b_hp='".$recv_cel."',od_b_zip1='".$recv_zipcode[0]."',od_b_zip2='".$recv_zipcode[1]."',od_b_addr1='".$recv_addr."',od_memo='".$delv_msg."',od_cart_count=1,od_cart_price='".$product_total_amount."',od_delivery_price='".$delivery_price."',od_status='오픈마켓',od_settle_case='신용카드',od_receipt_price='".$product_total_amount."',od_receipt_time='".$od_receipt_time."',od_pay_state='1',od_time=now(),od_send_admin_memo='[오픈마켓] ".$mall_id."/".$user_id."',od_writer='openmarket',sabang_market='".$mall_id."'";

				//echo $order_in_sql."<br>";

				sql_query($order_in_sql);

				$ot_typereceipt_cate=($mall_id=="오너클랜")?25:16;

				$typereceipt_in_sql = "insert into g5_shop_order_typereceipt set od_id='".$od_id."',ot_typereceipt_cate='".$ot_typereceipt_cate."',ot_typereceipt=0";

				sql_query($typereceipt_in_sql);

		    }else{
				$order_up_sql = "update {$g5['g5_shop_order_table']} set od_cart_count=od_cart_count+1,od_delivery_price=od_delivery_price+".$delivery_price.",od_cart_price=od_cart_price+".$product_total_amount.",od_receipt_price=od_receipt_price+".$product_total_amount.",sabang_od_id=concat(sabang_od_id,',',".$d_od_id.") where sabang_order_id='".$sabang_od_id."'";

				sql_query($order_up_sql);
			}
		}	

if($iframe){
	echo "<script>alert('전송이 완료되었습니다.');location.href='about:blank';</script>";
}else{
	echo "success";
}
    exit;
}
if($iframe){
	echo "<script>alert('가져올 주문데이터가 없습니다.');location.href='about:blank';</script>";
}else{
     echo "order list no";
}
?>