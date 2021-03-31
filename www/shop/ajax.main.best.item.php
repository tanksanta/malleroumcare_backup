<?php

	include_once("./_common.php");

	$prodsList = [];
	$prodsResultList = [];
	$productSQL = sql_query("
		SELECT *
		FROM g5_shop_item
		WHERE ( ".get_category_where(30)." OR ".get_category_where(60)." )
		AND ca_id = '{$no}'
		ORDER BY it_id DESC
	");

	$optionProductList = [];
	for($i = 0; $row = sql_fetch_array($productSQL); $i++){
		$thisOptionList = [];
		$prodsList[] = $row;

		# 210204 옵션
		$thisOptionSQL = sql_query("
			SELECT io_id
			FROM g5_shop_item_option
			WHERE it_id = '{$row["it_id"]}'
		");
		for($ii = 0; $subRow = sql_fetch_array($thisOptionSQL); $ii++){
			array_push($thisOptionList, $subRow["io_id"]);
		}

		$optionProductList[$row["it_id"]] = $thisOptionList;
	}

	# 210204 재고조회
	$sendData = [];
	$sendData["usrId"] = $member["mb_id"];
	$sendData["entId"] = $member["mb_entId"];

	$prodsSendData = [];
	if($optionProductList){
		foreach($optionProductList as $it_id => $data){
			$stockQtyList[$it_id] = 0;

			if($data){
				foreach($data as $optionData){
					$prodsData = [];
					$prodsData["prodId"] = $it_id;
					$prodsData["prodColor"] = explode(chr(30), $optionData)[0];
					$prodsData["prodSize"] = explode(chr(30), $optionData)[1];

					array_push($prodsSendData, $prodsData);
				}
			} else {
				$prodsData = [];
				$prodsData["prodId"] = $it_id;
				$prodsData["prodColor"] = "";
				$prodsData["prodSize"] = "";

				array_push($prodsSendData, $prodsData);
			}
		}
	}

	$sendData["prods"] = $prodsSendData;

	for($i = 0; $i < count($prodsList); $i++){
		$row = $prodsList[$i];
		$img = apms_it_thumbnail($row, 400, 400, false, true);

		if(!$img["src"] && $row["it_img1"]){
			$img["src"] = G5_DATA_URL."/item/{$row["it_img1"]}";
			$img["org"] = G5_DATA_URL."/item/{$row["it_img1"]}";
		}

		if(!$img["src"]){
			$img["src"] = G5_URL."/shop/img/no_image.gif";
		}
		
		$prodsResultList["data"][$row["it_id"]]["ca_id"] = substr($row["ca_id"], 0, 2);
		$prodsResultList["data"][$row["it_id"]]["prodSupYn"] = $row["prodSupYn"];
		$prodsResultList["data"][$row["it_id"]]["img"] = $img["src"];
		$prodsResultList["data"][$row["it_id"]]["it_name"] = $row["it_name"];
		$prodsResultList["data"][$row["it_id"]]["it_model"] = $row["it_model"];
		$prodsResultList["data"][$row["it_id"]]["it_price"] = 0;
		$prodsResultList["data"][$row["it_id"]]["it_price_discount"] = 0;
		
		if($member["mb_id"]){
			if($member["mb_level"] == "3"){
				if($_COOKIE["viewType"] != "basic"){
					$prodsResultList["data"][$row["it_id"]]["it_price_discount"] = $row["it_cust_price"];
				}
				$prodsResultList["data"][$row["it_id"]]["it_price"] = ($_COOKIE["viewType"] == "basic") ? $row["it_cust_price"] : $row["it_price"];
			} else {
				$prodsResultList["data"][$row["it_id"]]["it_price"] = $row["it_price"];
			}
		} else {
			$prodsResultList["data"][$row["it_id"]]["it_price"] = $row["it_cust_price"];
		}
		
		$prodsResultList["data"][$row["it_id"]]["it_img_3d"] = (json_decode($row["it_img_3d"], true)) ? true : false;
	}

	$prodsResultList["sendData"] = $sendData;

	echo json_encode($prodsResultList, JSON_UNESCAPED_UNICODE);
		
?>