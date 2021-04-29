<?php
include_once('./_common.php');

$od_ids = explode('|', $od_id);
$prodList = [];
$prodListCnt = 0;

$infos = array();
foreach($od_ids as $od_id) {

    //------------------------------------------------------------------------------
    // 주문서 정보
    //------------------------------------------------------------------------------
    $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
    $od = sql_fetch($sql);
    if (!$od['od_id']) {
        continue;
    } else {
		if($od["ordId"]){
			$sendData = [];
			$sendData["penOrdId"] = $od["ordId"];
			$sendData["uuid"] = $od["uuid"];

			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_PORT, 9901);
			curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/order/selectList");
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$res = curl_exec($oCurl);
			curl_close($oCurl);

			$result = json_decode($res, true);
			$result = $result["data"];

			if($result){
				$ordZip = [];
				$ordZip[0] = substr($result[0]["ordZip"], 0, 3);
				$ordZip[1] = substr($result[0]["ordZip"], 3, 2);

				sql_query("
					UPDATE {$g5["g5_shop_order_table"]} SET
						  mb_id = '{$result[0]["usrId"]}'
						, od_penId = '{$result[0]["penId"]}'
						, od_delivery_text = '{$result[0]["ordWayNum"]}'
						, od_delivery_company = '{$result[0]["delSerCd"]}'
						, od_b_name = '{$result[0]["ordNm"]}'
						, od_b_tel = '{$result[0]["ordCont"]}'
						, od_memo = '{$result[0]["ordMeno"]}'
						, od_b_zip1 = '{$ordZip[0]}'
						, od_b_zip2 = '{$ordZip[1]}'
						, od_b_addr1 = '{$result[0]["ordAddr"]}'
						, od_b_addr2 = '{$result[0]["ordAddrDtl"]}'
						, payMehCd = '{$result[0]["payMehCd"]}'
						, eformYn = '{$result[0]["eformYn"]}'
						, staOrdCd = '{$result[0]["staOrdCd"]}'
					WHERE od_id = '{$od["od_id"]}'
				");

				$od = sql_fetch("SELECT * FROM {$g5["g5_shop_order_table"]} WHERE od_id = '{$od["od_id"]}'");

				foreach($result as $data){
					$thisProductData = [];

					$thisProductData["prodId"] = $data["prodId"];
					$thisProductData["prodColor"] = $data["prodColor"];
					$thisProductData["stoId"] = $data["stoId"];
					$thisProductData["prodBarNum"] = $data["prodBarNum"];
					$thisProductData["penStaSeq"] = $data["penStaSeq"];
					array_push($prodList, $thisProductData);
				}
			}
		} else {
			$stoIdData = $od["stoId"];
			$stoIdData = explode(",", $stoIdData);
			$stoIdDataList = [];
			foreach($stoIdData as $data){
				array_push($stoIdDataList, $data);
			}
			$stoIdData = implode("|", $stoIdDataList);
		}
	}

    // 상품목록
    $sql = " select a.it_id,
                    a.it_name,
                    a.od_id,
                    a.cp_price,
                    a.ct_notax,
                    a.ct_send_cost,
                    a.it_sc_type,
                    a.pt_it,
                    a.pt_id,
                    b.ca_id,
                    b.ca_id2,
                    b.ca_id3,
                    b.pt_msg1,
                    b.pt_msg2,
                    b.pt_msg3,
                    a.ct_status,
                    b.it_model,
                    a.ct_uid,
                    a.ct_stock_qty,
						a.prodMemo
            from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
            where a.od_id = '{$od['od_id']}'
			AND b.prodSupYn = 'Y'
            group by a.it_id, a.ct_uid
            order by a.ct_id ";

    $result = sql_query($sql);

    $carts = array();
    $cate_counts = array();
    $it_ids = array();

    for($i=0; $row=sql_fetch_array($result); $i++) {

        $cate_counts[$row['ct_status']] += 1;

        // 상품의 옵션정보
        $sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_stock_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount, od_id, ct_uid
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '{$od['od_id']}'
                        and it_id = '{$row['it_id']}'
                        and ct_uid = '{$row['ct_uid']}'
                    order by io_type asc, ct_id asc ";
        $res = sql_query($sql);

        $row['options_span'] = sql_num_rows($res);

        $row['options'] = array();
        for($k=0; $opt=sql_fetch_array($res); $k++) {

            $opt_price = 0;

            if($opt['io_type'])
                $opt_price = $opt['io_price'];
            else
                $opt_price = $opt['ct_price'] + $opt['io_price'];

            $opt['opt_price'] = $opt_price;

            if (!in_array($opt['it_id'], $it_ids)) {
                $it_ids[] = $opt['it_id'];
            }

            // 소계
            $opt['ct_price_stotal'] = $opt_price * $opt['ct_qty'] - $opt['ct_discount'];
            $opt['ct_point_stotal'] = $opt['ct_point'] * $opt['ct_qty'] - $opt['ct_discount'];

            $row['options'][] = $opt;
        }


        // 합계금액 계산
        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                        SUM(ct_qty) as qty,
                        SUM(ct_discount) as discount,
                        SUM(ct_send_cost) as sendcost
                    from {$g5['g5_shop_cart_table']}
                    where it_id = '{$row['it_id']}'
                        and od_id = '{$od['od_id']}' ";
        $sum = sql_fetch($sql);

        $row['sum'] = $sum;

        $carts[] = $row;

    }

    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'];

    // 입금액 = 결제금액 + 포인트
    $amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

    // 쿠폰금액
    $amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

    // 취소금액
    $amount['cancel'] = $od['od_cancel_price'];

    // 견적서 정보
    $sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
    $est = sql_fetch($sql);

    $delivery = get_delivery_step($od['od_delivery_type']);

    // 메모
    $sql = "SELECT * FROM g5_shop_order_admin_memo WHERE od_id = '{$od['od_id']}' ORDER BY om_no DESC";
    $memo = sql_fetch($sql);

    $infos[] = array(
        'od' => $od,
        'amount' => $amount,
        'carts' => $carts,
        'delivery' => $delivery,
        'it_ids' => $it_ids,
        'memo' => $memo,
    );

}

$title = '주문서출고처리';
include_once('./pop.head.php');
?>

<style>
	* { margin: 0; padding: 0; position: relative; box-sizing: border-box; }
	html, body { width: 100%; float: left; font-size: 12px; text-align: center; padding: 5px; }
	.text-left { text-align: left !important; }
	.text-right { text-align: right !important; }
	p { margin-bottom: 0; }

	#pbreak { width: 100%; float: left; }
	#pbreak > table { width: 100%; float: left; table-layout: fixed; margin-bottom: 10px; }
	#pbreak > table:last-of-type { margin-bottom: 0; }

	.titleTable > thead > tr > th { text-align: center; padding-bottom: 10px; border-bottom: 1px solid #000; }
	.titleTable > thead > tr > th > h3 { display: inline-block; border-bottom: 3px solid #000; }
	.titleTable > tbody > tr > * { border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 2px 10px; }
	.titleTable > tbody > tr > *:first-of-type { border-left: 1px solid #000; }
	.titleTable > tbody > tr > th { background-color: #F5F5F5; }

	.prodsTable { border: 1px solid #000; border-bottom: 0; }
	.prodsTable tr > * { border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: center; padding: 2px 10px; }
	.prodsTable tr > *:last-child { border-right: 0; }
	.prodsTable > thead > tr > th { background-color: #F5F5F5; }

	.footTable { border: 1px solid #000; border-bottom: 0; }
	.footTable tr > * { border-right: 1px solid #000; border-bottom: 1px solid #000; text-align: center; padding: 2px 10px; }
	.footTable tr > *:last-child { border-right: 0; }
	.footTable tr > th { background-color: #F5F5F5; }
</style>

<?php
$index = 0;
foreach($infos as $info) {
	$totalCnt = 0;
?>

    <div class="pbreak" id="pbreak">

    	<table class="titleTable">
    		<colgroup>
    			<col width="15%">
    			<col width="35%">
    			<col width="15%">
    			<col width="35%">
    		</colgroup>

    		<thead>
    			<tr>
    				<th colspan="4">
    					<h3>출 고 증</h3>
    				</th>
    			</tr>
    		</thead>

    		<tbody>
    			<tr>
    				<th>발주처</th>
    				<td class="text-left"><?=$info["od"]["od_name"]?></td>
    				<th>출고일자</th>
    				<td class="text-left"><?=date("Ymd")?></td>
    			</tr>
    			<tr>
    				<th>이름(상호)</th>
    				<td colspan="3" class="text-left"><?=$info["od"]["od_name"]?></td>
    			</tr>
    			<tr>
    				<th>연락처</th>
    				<td colspan="3" class="text-left"><?=$info["od"]["od_hp"]?></td>
    			</tr>
    			<tr>
    				<th>주소</th>
    				<td colspan="3" class="text-left"><?=$info["od"]["od_b_addr1"]?> <?=$info["od"]["od_b_addr2"]?></td>
    			</tr>
    		</tbody>
    	</table>

    	<table class="prodsTable">
    		<colgroup>
    			<col width="30%">
    			<col width="10%">
    			<col width="40%">
    			<col width="20%">
    		</colgroup>

    		<thead>
    			<tr>
    				<th>품목명[규격]</th>
    				<th>수량</th>
    				<th>바코드</th>
    				<th>적요</th>
    			</tr>
    		</thead>

    		<tbody>
    		<?php
				for($i = 0; $i < count($info["carts"]); $i++){
					$options = $info["carts"][$i]["options"];

					$prodMemo = "";

					for($k = 0; $k < count($options); $k++){
						if(($options[$k]["ct_qty"] - $options[$k]["ct_stock_qty"]) <= 0){
							continue;
						}

						$totalCnt += $options[$k]["ct_qty"] - $options[$k]["ct_stock_qty"];

						$prodMemo = ($prodMemo) ? $prodMemo : $info["carts"][$i]["prodMemo"];
			?>
  				<tr>
  					<td class="text-left"><?=$info["carts"][$i]["it_name"]?> <?=($info["carts"][$i]["it_name"] != $options[$k]["ct_option"]) ? "({$options[$k]["ct_option"]})" : ""?></td>
  					<td class="text-right"><?=($options[$k]["ct_qty"] - $options[$k]["ct_stock_qty"])?></td>
  					<td class="text-left">
  					<?php
						$defaultBarCntCheck =$options[$k]["ct_stock_qty"];
						for($b = 0; $b < $options[$k]["ct_qty"]; $b++){
							if($defaultBarCntCheck > 0){
								$defaultBarCntCheck--;
								$prodListCnt++;
								continue;
							}
					?>
  						<p class="prodBarNumItem_<?=$prodList[$prodListCnt]["penStaSeq"]?> <?=$stoIdDataList[$prodListCnt]?>">
  							<?=$prodList[$prodListCnt]["prodBarNum"]?>
  						</p>
  					<?php $prodListCnt++; } ?>
  					</td>
  					<td class="text-left"><?=$prodMemo?></td>
  				</tr>
   			<?php } } ?>
    		</tbody>
    	</table>

    	<table class="footTable">
    		<colgroup>
    			<col width="20%">
    			<col width="30%">
    			<col width="20%">
    			<col width="30%">
    		</colgroup>

    		<tbody>
    			<tr>
    				<th>수량</th>
    				<td class="text-right"><?=number_format($totalCnt)?></td>
    				<th>인수</th>
    				<td class="text-right">인</td>
    			</tr>
    		</tbody>
    	</table>

	</div>

<?php if ( count($infos) - 1 > $index ) { ?>
    <div class="endline"></div><br style="height:0; line-height:0">
<?php
}
$index++;
}
?>

<script type="text/javascript">
	$(function(){

		var stoldList = [];
		var stoIdData = "<?=$stoIdData?>";
		if(stoIdData){
			var sendData = {
				stoId : stoIdData
			}

			$.ajax({
				url : "https://system.eroumcare.com/api/pro/pro2000/pro2000/selectPro2000ProdInfoAjaxByShop.do",
				type : "POST",
				dataType : "json",
				contentType : "application/json; charset=utf-8;",
				data : JSON.stringify(sendData),
				success : function(res){
					$.each(res.data, function(key, value){
						$("." + value.stoId).text(value.prodBarNum);
					});

					if(res.data){
						stoldList = res.data;
					}

					document.execCommand("print", false, null) || window.print();
				}
			});
		} else {
			document.execCommand("print", false, null) || window.print();
		}

	})
</script>

<?php
include_once('./pop.tail.php');
?>
