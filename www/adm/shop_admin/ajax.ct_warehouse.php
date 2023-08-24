<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    //상품 지정 출하창고 부터 조회
	$sql = "select it_default_warehouse from g5_shop_item where it_id='".$_POST['it_id']."'"; 
	$row = sql_fetch($sql);
	$ct_wh = $row["it_default_warehouse"];
		
	if($_POST['partner'] != ""){//파트너 선택이 있을 경우        
        $sql = "select mb_partner_default_warehouse from g5_member where mb_id='".$_POST['mb_id']."'"; 
		$row = sql_fetch($sql);
		$ct_wh = ($row["mb_partner_default_warehouse"] != "")?$row["mb_partner_default_warehouse"]:$ct_wh;//파트너에 등록된 출하창고가 없을 경우 상품 기본 출하창고로 지정        
    }
	$ret = array(
            'ct_wh' => $ct_wh,
		   );
    $json = json_encode($ret);
    echo $json;
?>