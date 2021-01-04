<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

//배송방법
$delivery_arr = array("delivery01"=>"택배-선불",
        		      "delivery02"=>"오토바이퀵-선불",
                      "delivery03"=>"다마스퀵-선불",
                      "delivery04"=>"화물-선불(택배)",
                      "delivery05"=>"경동화물-선불(영업소)",
                      "delivery06"=>"전국화물-선불",
                      "delivery07"=>"고속버스-선불",
                      "delivery08"=>"택배-착불",
                      "delivery09"=>"오토바이퀵-착불",
                      "delivery10"=>"다마스퀵-착불",
                      "delivery11"=>"화물-착불(택배)",
                      "delivery12"=>"경동화물-착불(영업소)",
                      "delivery13"=>"전국화물-착불",
                      "delivery14"=>"직접수령");
$table_nm = "fm";

if($type=="partner"){
  $title_name = "-파트너";
  $table_nm = "pm";
}

$g5['title'] = '과거CS주문내역'.$title_name;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql = "select * from ".$table_nm."_cs_order a,".$table_nm."_cs_order_deli b where b.pseq=a.seq and a.cs_seq='".$seq."'";
$row = sql_fetch($sql);
if(!$row['seq']) alert('잘못된 방법으로 접근하셨습니다.');

	$order_row = sql_fetch("select * from ".$table_nm."_order where cseq='".$row['seq']."'");

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
			$payment_nm = "무통장 (".$order_row['bank_account'].")";
		    break;
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
$busniess_row = sql_fetch("select bno from ".$table_nm."_member_business where member_seq='".$row['cseq']."'");
?>

					<div id="text_size">
						<!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
						<button onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="https://signstand.co.kr/adm/img/ts01.gif" alt="기본"></button>
						<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="https://signstand.co.kr/adm/img/ts02.gif" alt="크게"></button>
						<button onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="https://signstand.co.kr/adm/img/ts03.gif" alt="더크게"></button>
					</div>
					<div class="btn_fixed_top">
					    <a href="past_csorder_list.php" class="btn btn_02">목록</a>
					</div>
	
					<section id="anc_bo_apms">
					    <h2 class="h2_frm">기본정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>주문서번호</th>
						            <td><?php echo $row['cs_seq']; ?></td>
						        </tr>
								<tr>
						            <th>주문번호</th>
						            <td><?php echo $order_row['order_seq'] ?></td>
						        </tr>
								<tr>
						            <th>주문서작성일</th>
						            <td><?php echo $row['rdate']; ?></td>
						        </tr>
								<tr>
						            <th>영업담당자</th>
						            <td><?php echo $manager_row['mname']?></td>
						        </tr>
								<tr>
						            <th>거래처</th>
						            <td><?php echo $row['depositor']; ?> / <?php echo $busniess_row['bno'] ?> / <?php echo $row['dname'] ?> 님 :  <?php echo $row['dmobile'] ?></td>
						        </tr>
					        </tbody>
					        </table>
					    </div>
					    <h2 class="h2_frm">주문정보</h2>
					    <div class="tbl_frm01 tbl_wrap">
					        <table>
					        <tbody>
								<tr>
						            <th>주문상품</th>
						            <td>
<?php
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods1 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = ($item_row['sprice']>0)?$item_row['sprice']:$item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($d_price)."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			옵션 : <?=$item_row['opt_title']?> , 옵션가격 : <?=$item_row['opt_price']?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
       echo "</p>";
    }
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods2 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = $item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			사이즈기준 : <?=$item_row['terms']?>, 사이즈 : <?=$item_row['width']."*".$item_row['height']?>, 프레임색상 : <?=$item_row['color']?>, SMPS : <?=$item_row['smpt']?>, 출력물 : <?=$item_row['ext']?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
		echo "</p>";
    }
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods3 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = $item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			사이즈 : <?=$item_row['width']."*".$item_row['height']?> 
			<?=($item_row['side'])?"/SIDE:".$item_row['side']:""?>
			<?=($item_row['terms'])?"/TERMS:".$item_row['terms']:""?>
			<?=($item_row['color'])?"/COLOR:".$item_row['color']:""?>
			<?=($item_row['line'])?"/LINE:".$item_row['line']:""?>
			<?=($item_row['smpt'])?"/SMPT:".$item_row['smpt']:""?>
			<?php if($item_row['ext_ea']>0){ ?>
			/ 수량 : <?=$item_row['ext_ea']?>개 X 단가  : <?=number_format($item_row['ext_price'])?>원 = <?=number_format($item_row['ext_sum'])?>원
			<?php
			      }else{
			?>
			/ 수량 : <?=$item_row['ea']?>개 X 단가  : <?=number_format($item_row['price'])?>원 = <?=number_format($item_row['sum'])?>원
			<?php  } ?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
		echo "</p>";
    }
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods4 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = $item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			사이즈 : <?=$item_row['width']."*".$item_row['height']?> 
			<?=($item_row['side'])?"/SIDE:".$item_row['side']:""?>
			<?=($item_row['terms'])?"/TERMS:".$item_row['terms']:""?>
			<?=($item_row['color'])?"/COLOR:".$item_row['color']:""?>
			<?=($item_row['line'])?"/LINE:".$item_row['line']:""?>
			<?=($item_row['smpt'])?"/SMPT:".$item_row['smpt']:""?>
			<?php if($item_row['ext_ea']>0){ ?>
			/ 수량 : <?=$item_row['ext_ea']?>개 X 단가  : <?=number_format($item_row['ext_price'])?>원 = <?=number_format($item_row['ext_sum'])?>원
			<?php
			      }else{
			?>
			/ 수량 : <?=$item_row['ea']?>개 X 단가  : <?=number_format($item_row['price'])?>원 = <?=number_format($item_row['sum'])?>원
			<?php  } ?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
		echo "</p>";
    }
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods5 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = $item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			사이즈기준 : <?=$item_row['terms']?>, 사이즈 : <?=$item_row['width']."*".$item_row['height']?>, 프레임색상 : <?=$item_row['color']?>, SMPS : <?=$item_row['smpt']?>, 출력물 : <?=$item_row['ext']?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
		echo "</p>";
    }
    $item_result = sql_query("select * from ".$table_nm."_cs_order_goods6 a,".$table_nm."_goods b where a.pseq='".$row['pseq']."' and a.goods_seq=b.goods_seq");
    for ($i=0; $item_row  = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['sale'];
		$d_price = $item_row['price'];
		$price = ($d_price*$item_row['ea'])-$sale_price;
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format($item_row['settle'])."원<br>".$item_row['opt_title'];
?>
		<p style="background: #eee;padding:5px;">
			수량 : <?=$item_row['ea']?>
 		    / <?=$item_row['smemo']?>
		</p>
<?php
		echo "</p>";
    }
	/*
    $item_result = sql_query("select a.goods_name,b.ea,b.price,b.member_sale from ".$table_nm."_order_item a,".$table_nm."_order_item_option b where a.order_seq='".$order_row['order_seq']."' and a.order_seq=b.order_seq and a.item_seq=b.item_seq");
    for ($i=0; $item_row    = sql_fetch_array($item_result); $i++){
		$sale_price = $item_row['member_sale']+$item_row['consumer_price'];
		$total_sale_price += $sale_price;
		$total_price += $price;
		echo "<p>".$item_row['goods_name']." / ".$item_row['ea']."개 / 가격 : ".number_format($item_row['price']*$item_row['ea'])."원 – 할인 : ".number_format($sale_price)."원 = ".number_format(($item_row['price']*$item_row['ea'])-$sale_price)."원</p>";
	}*/
?>
						           </td>
						        </tr>
								<tr>
						            <th>추가할인</th>
						            <td><?php echo number_format($total_sale_price) ?>원</td>
						        </tr>
								<tr>
						            <th>결제금액</th>
						            <td><?php echo number_format($total_price) ?>원</td>
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
						            <td><?php echo $delivery_arr[$row['d_type']]?></td>
						        </tr>
								<tr>
						            <th>수령자</th>
						            <td><?php echo $order_row['recipient_user_name'] ?></td>
						        </tr>
								<tr>
						            <th>연락처</th>
						            <td><?php echo $order_row['recipient_phone'] ?></td>
						        </tr>
								<tr>
						            <th>휴대폰</th>
						            <td><?php echo $order_row['recipient_cellphone'] ?></td>
						        </tr>
								
								<tr>
						            <th>주소</th>
						            <td><?php  echo "[".$order_row['recipient_zipcode']."]".$order_row['recipient_address_street'].$order_row['recipient_address_detail'] ?></td>
						        </tr>
								<tr>
						            <th>배송요청사항</th>
						            <td><?php echo $order_row['memo'] ?></td>
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