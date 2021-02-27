<?php

	$saleCntList = [30, 50, 21, 40, 10];
	$qty = 49;
	$ct_sale_cnt = 0;

	for($saleCnt = 0; $saleCnt < count($saleCntList); $saleCnt++){
		if($saleCntList[$saleCnt] <= $qty){
			if($ct_sale_cnt < $saleCntList[$saleCnt]){
				$ct_sale_cnt = $saleCntList[$saleCnt];
			}
		}
	}

	echo $ct_sale_cnt;

?>