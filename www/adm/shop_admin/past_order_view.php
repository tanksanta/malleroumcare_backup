<?php
$sub_menu = '400420';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$table_nm = "".$table_nm."";

$table_nm = "fm";

if($type=="partner"){
  $title_name = "-파트너";
  $table_nm = "pm";
}

$g5['title'] = '과거주문내역'.$title_name;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql = "select * from ".$table_nm."_order where order_seq='".$seq."'";
$row = sql_fetch($sql);
if(!$row['order_seq']) alert('잘못된 방법으로 접근하셨습니다.');


	switch($row['payment']){
		case "card":
			$payment_nm = "카드";
		    break;
	    case "point";
			$payment_nm = "포인트";
		    break;
	    case "account";
			$payment_nm = "실시간계좌이체";
		    break;
	    case "vitual";
			$payment_nm = "가상계좌";
		    break;
	    case "cellphone";
			$payment_nm = "휴대폰";
		    break;
	    case "bank";
			$payment_nm = "무통장 (".$row['bank_account'].")";
		    break;
	}

	switch($row['shipping_method']){
		case "delivery":
			$delivery_nm = "택배(선불)";
		    break;
		case "quick":
			$delivery_nm = "오토바이";
		    break;
		default:
			$delivery_nm = "직접수령";
	}

	switch($row['typereceipt']){
		case 1:
			$typereceipt = "세금계산서";
		    break;
		case 2:
			$typereceipt = "현금영수증";
		    break;
		default:
			$typereceipt = "발급안함";
	}

$manager_row = sql_fetch("select mname from ".$table_nm."_cs_manager where mseq='".$row['mseq']."'");
?>



					<div class="btn_fixed_top">
					    <a href="past_order_list.php" class="btn btn_02">목록</a>
					</div>
					<section id="anc_bo_apms">
					    <h2 class="h2_frm">기본정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>주문번호</th>
						            <td><?php echo $row['order_seq']; ?></td>
						        </tr>
								<tr>
						            <th>주문일시</th>
						            <td><?php echo substr($row['regist_date'],0,16)?></td>
						        </tr>
								<tr>
						            <th>매니저</th>
						            <td><?php echo $manager_row['mname'] ?></td>
						        </tr>
					        </tbody>
					        </table>
					    </div>
					    <h2 class="h2_frm">주문정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>주문자</th>
						            <td><?php echo $row['order_user_name'] ?></td>
						        </tr>
								<tr>
						            <th>주문상품</th>
						            <td>
<?php
    $item_result = sql_query("select a.goods_name,b.ea,b.price,b.member_sale from ".$table_nm."_order_item a,".$table_nm."_order_item_option b where a.order_seq='".$row['order_seq']."' and a.order_seq=b.order_seq and a.item_seq=b.item_seq");
    for ($i=0; $item_row    = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['member_sale']+$item_row['consumer_price'];
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price']*$item_row['ea'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format(($item_row['price']*$item_row['ea'])-$sale_price)."원</p>";
	}
?>
						           </td>
						        </tr>
								<tr>
						            <th>총상품금액</th>
						            <td><?php echo number_format($row['settleprice']-$row['shopping_cost']) ?>원</td>
						        </tr>
								<tr>
						            <th>배송비</th>
						            <td><?php echo number_format($row['shopping_cost']) ?>원</td>
						        </tr>
								<tr>
						            <th>결제금액</th>
						            <td><?php echo number_format($row['settleprice']) ?>원</td>
						        </tr>
								<tr>
						            <th>관리자메모</th>
						            <td><?php echo $row['admin_memo'] ?></td>
						        </tr>
					        </tbody>
					        </table>
					    </div>
					    <h2 class="h2_frm">배송정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>배송분류</th>
						            <td><?php echo $delivery_nm?></td>
						        </tr>
								<tr>
						            <th>수령자</th>
						            <td><?php echo $row['recipient_user_name'] ?></td>
						        </tr>
								<tr>
						            <th>연락처</th>
						            <td><?php echo $row['recipient_phone'] ?></td>
						        </tr>
								<tr>
						            <th>휴대폰</th>
						            <td><?php echo $row['recipient_cellphone'] ?></td>
						        </tr>
								
								<tr>
						            <th>주소</th>
						            <td><?php  echo "[".$row['recipient_zipcode']."]".$row['recipient_address_street'].$row['recipient_address_detail'] ?></td>
						        </tr>
								<tr>
						            <th>배송요청사항</th>
						            <td><?php echo $row['memo'] ?></td>
						        </tr>
					        </tbody>
					        </table>
					    </div>
					    <h2 class="h2_frm">결제정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>결제수단</th>
						            <td><?php echo $payment_nm ?></td>
						        </tr>
								<tr>
						            <th>매출증빙</th>
						            <td><?php echo $typereceipt?> <? /*/ 2020-07-29*/?></td>
						        </tr>
					        </tbody>
					        </table>
					    </div>
					</section>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>