<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

	# 바코드 갯수
	$postProdBarNumCnt = 0;

	# 210130 재고수량 및 바코드 조회
	$optionCntList = [];
	$optionBarList = [];
	if($member["mb_id"]){
		foreach($item as $itemData){
			$thisOptionCntList = [];
			$thisOptionBarList = [];
			
			if($itemData["it_optionList"]){
				foreach($itemData["it_optionList"] as $optionData){
					$sendData = [];
					$sendData["usrId"] = $member["mb_id"];
					$sendData["entId"] = $member["mb_entId"];

					$prodsSendData = [];

					$prodsData = [];
					$prodsData["prodId"] = $itemData["it_id"];
					$prodsData["prodColor"] = $optionData["color"];
					$prodsData["prodSize"] = $optionData["size"];
					array_push($prodsSendData, $prodsData);

					$sendData["prods"] = $prodsSendData;

					# 재고조회
					$oCurl = curl_init();
					curl_setopt($oCurl, CURLOPT_PORT, 9001);
					curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectList");
					curl_setopt($oCurl, CURLOPT_POST, 1);
					curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
					curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
					$res = curl_exec($oCurl);
					$stockCntList = json_decode($res, true);
					curl_close($oCurl);

					# 바코드조회
					$oCurl = curl_init();
					curl_setopt($oCurl, CURLOPT_PORT, 9001);
					curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectBarNumList");
					curl_setopt($oCurl, CURLOPT_POST, 1);
					curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
					curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
					$res = curl_exec($oCurl);
					$stockBarList = json_decode($res, true);
					curl_close($oCurl);

					# 재고목록
					$thisOptionBarSubList = [];
					$stockCntList["data"][0]["quantity"] = ($stockCntList["data"][0]["quantity"]) ? $stockCntList["data"][0]["quantity"] : 0;

					array_push($thisOptionCntList, $stockCntList["data"][0]["quantity"]);
					if($stockBarList["data"][0]["prodBarNumList"]){
						foreach($stockBarList["data"][0]["prodBarNumList"] as $barData){
							array_push($thisOptionBarSubList, $barData);
						}
					}

					array_push($thisOptionBarList, $thisOptionBarSubList);
				}
			} else {
				$sendData = [];
				$sendData["usrId"] = $member["mb_id"];
				$sendData["entId"] = $member["mb_entId"];

				$prodsSendData = [];

				$prodsData = [];
				$prodsData["prodId"] = $itemData["it_id"];
				array_push($prodsSendData, $prodsData);

				$sendData["prods"] = $prodsSendData;

				# 재고조회
				$oCurl = curl_init();
				curl_setopt($oCurl, CURLOPT_PORT, 9001);
				curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectList");
				curl_setopt($oCurl, CURLOPT_POST, 1);
				curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
				curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
				$res = curl_exec($oCurl);
				$stockCntList = json_decode($res, true);
				curl_close($oCurl);
				
					# 바코드조회
					$oCurl = curl_init();
					curl_setopt($oCurl, CURLOPT_PORT, 9001);
					curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectBarNumList");
					curl_setopt($oCurl, CURLOPT_POST, 1);
					curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
					curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
					$res = curl_exec($oCurl);
					$stockBarList = json_decode($res, true);
					curl_close($oCurl);

					# 재고목록
					$thisOptionBarSubList = [];
					$stockCntList["data"][0]["quantity"] = ($stockCntList["data"][0]["quantity"]) ? $stockCntList["data"][0]["quantity"] : 0;

					array_push($thisOptionCntList, $stockCntList["data"][0]["quantity"]);
					if($stockBarList["data"][0]["prodBarNumList"]){
						foreach($stockBarList["data"][0]["prodBarNumList"] as $barData){
							array_push($thisOptionBarSubList, $barData);
						}
					}

					array_push($thisOptionBarList, $thisOptionBarSubList);
			}
			
			$optionCntList[$itemData["it_id"]] = $thisOptionCntList;
			$optionBarList[$itemData["it_id"]] = $thisOptionBarList;
		}
	}

?>

<!-- <div class="well well-sm">
	<i class="fa fa-shopping-cart fa-lg"></i> 주문하실 상품을 확인해 주세요.
</div> -->
