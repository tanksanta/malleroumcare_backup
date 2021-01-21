<?php
include_once("./_common.php");
//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

$odmb = sql_fetch("SELECT * FROM {$g5["g5_member_table"]} WHERE mb_id = '{$od["mb_id"]}'");

if (!$od["od_id"]) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

// 상품목록
$sql = " select a.it_id,
				a.it_name,
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
                b.it_model
		  from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
		  where a.od_id = '$od_id'
		  group by a.it_id
		  order by a.ct_id ";

$result = sql_query($sql);

$carts = array();
$cate_counts = array();

for($i=0; $row=sql_fetch_array($result); $i++) {

    $cate_counts[$row['ct_status']] += 1;

    // 상품의 옵션정보
    $sql = " select ct_id, mb_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price, pt_msg1, pt_msg2, pt_msg3, ct_discount
                from {$g5['g5_shop_cart_table']}
                where od_id = '{$od_id}'
                    and it_id = '{$row['it_id']}'
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
                    and od_id = '{$od_id}' ";
    $sum = sql_fetch($sql);

    $row['sum'] = $sum;
    $amount['order'] += $sum['price'] - $sum['discount'];

    if ( !$od['od_send_cost'] ) {
        $od['od_send_cost'] += $sum['ct_send_cost'];
    }

    $carts[] = $row;
}

// 주문금액 = 상품구입금액 + 배송비 + 추가배송비 - 할인금액 - 추가할인금액
if ( $od['od_cart_price'] ) {
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'] - $od['od_cart_discount'] - $od['od_cart_discount2'];
}

// 입금액 = 결제금액 + 포인트
$amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

// 쿠폰금액
$amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

// 취소금액
$amount['cancel'] = $od['od_cancel_price'];

if ( !$od['od_name'] ) {
    $od['od_name'] = $member['mb_name'];
}



// 견적서 정보
$sql = " select * from g5_shop_order_estimate where od_id = '$od_id' ";
$est = sql_fetch($sql);

unset($w);
// 관리 권한 확인
if ( $is_samhwa_admin || $is_admin ) {
    $w = 'u';
}

$banks = explode(PHP_EOL, $default['de_bank_account']); 
for($i=0;$i<count($banks);$i++) {
    $banks2[$i] = explode(' ', $banks[$i]);
}
$banks = $banks2;

	# 210121 팝빌연동
	require_once "./Popbill/PopbillStatement.php";

	$LinkId = "THKC"; # 링크아이디
	$SecretKey = "SK6O74B5rFqXWhm3Fa73ESVTXwBL2vfQiWvrHE4tzlc="; # 시크릿키
	$testCorpNum = "617-86-14330"; # 팝빌 회원 사업자 번호
	$testUserID = "thkc1300"; # 팝빌 회원 아이디
	$mgtKey = ""; # 전자명세서 문서번호
	$itemCode = "121"; # 명세서 종류코드
	$memo = ""; # 메모
	$emailSubject = ""; # 발행안내메일

	define("LINKHUB_COMM_MODE", "CURL");
	$StatementService = new StatementService($LinkId, $SecretKey);
	$StatementService->IsTest(true);
	$StatementService->IPRestrictOnOff(true);
	$StatementService->UseStaticIP(false);
	$StatementService->UseLocalTimeYN(true);

	# 전자명세서 객체 생성
	$Statement = new Statement();

	$Statement->writeDate = ($est["est_time"]) ? $est["est_time"] : date("Ymd"); # 작성일자
	$Statement->purposeType = "영수"; # 영수, 청구
	$Statement->taxType = "과세"; # 과세, 영세, 면세
	$Statement->formCode = ""; # 맞춤양식코드
	$Statement->itemCode = $itemCode; # 명세서 종류 코드
	$Statement->mgtKey = $mgtKey; # 전자명세서 문서번호

	$Statement->senderCorpName = "(주)티에이치케이컴퍼니"; # 공급자 상호
	$Statement->senderCEOName = "신종호"; # 공급자 대표자 성명
	$Statement->senderAddr = "부산광역시 금정구 부산대학로 63번길 2, 403호"; # 공급자 주소
	$Statement->senderBizClass = "간판 및 광고물 외"; # 공급자 업종
	$Statement->senderBizType = "제조, 도소매 외"; # 공급자 업태
	$Statement->senderContactName = "신종호"; # 공급자 담당자명
	$Statement->senderTEL = "051-6430-1300"; # 공급자 전화번호
	$Statement->senderHP = ""; # 공급자 휴대폰 번호
	$Statement->senderEmail = "thkc1300@hanmail.net"; # 공급자 이메일

	$Statement->receiverCorpNum = $odmb["mb_giup_bnum"]; # 공급받는자 사업자 번호
	$Statement->receiverTaxRegID = ""; # 공급받는자 종사업장 식별번호, 필요시 기재. 형식은 숫자 4자리
	$Statement->receiverCorpName = $odmb["mb_name"]; # 공급받는자 상호
	$Statement->receiverCEOName = ""; # 공급받는자 대표자 성명
	$Statement->receiverAddr = "({$odmb["mb_giup_zip1"]}{$odmb["mb_giup_zip2"]}) {$odmb["mb_giup_addr1"]} {$odmb["mb_giup_addr2"]}"; # 공급받는자 주소
	$Statement->receiverBizClass = ""; # 공급받는자 업종
	$Statement->receiverBizType = ""; # 공급받는자 업태
	$Statement->receiverContactName = ""; # 공급받는자 담당자명
	$Statement->receiverTEL = $odmb["mb_tel"]; # 공급받는자 전화번호
	$Statement->receiverHP = $odmb["mb_hp"]; # 공급받는자 휴대폰 번호
	$Statement->receiverEmail = $odmb["mb_email"]; # 공급받는자 이메일

	$Statement->supplyCostTotal = ($amount["order"] * 0.9); # [필수] 공급가액 합계
	$Statement->taxTotal = ($amount["order"] * 0.1); # [필수] 세액 합계
	$Statement->totalAmount = $amount["order"]; # [필수] 합계금액 (공급가액 합계+세액합계)

	$Statement->serialNum = ""; # 기재상 일련번호 항목
	$Statement->remark1 = "";
	$Statement->remark2 = "";
	$Statement->remark3 = "";

	$Statement->businessLicenseYN = false; # 사업자등록증 첨부 여부
	$Statement->bankBookYN = false; # 통장사본 첨부 여부
	$Statement->smssendYN = false; #발행시 안내문자 전송여부

	$Statement->detailList = array();

	$a = 0;
	for($i = 0; $i < count($carts); $i++){ 
		$options = $carts[$i]["options"];
		for($k = 0; $k < count($options); $k++){
			$Statement->detailList[$a] = new StatementDetail();
			
			$Statement->detailList[$a]->serialNum = ($a + 1); # 품목 일련번호 1부터 순차기재
			$Statement->detailList[$a]->purchaseDT = ($est["est_time"]) ? $est["est_time"] : date("Ymd"); # 거래일자
			$Statement->detailList[$a]->itemName = "{$carts[$i]["it_name"]}({$options[$k]["ct_option"]})"; # 품명
			$Statement->detailList[$a]->spec = ""; # 규격
			$Statement->detailList[$a]->unit = ""; # 단위
			$Statement->detailList[$a]->qty = $options[$k]["ct_qty"]; # 수량
			$Statement->detailList[$a]->unitCost = ($options[$k]["opt_price"] / 1.1); # 개당 가격
			$Statement->detailList[$a]->supplyCost = ($options[$k]["opt_price"] / 1.1 * $options[$k]["ct_qty"]); # 적용 가격
			$Statement->detailList[$a]->tax = ""; # 세금
			$Statement->detailList[$a]->remark = ""; # 비고
			$Statement->detailList[$a]->spare1 = "";
			$Statement->detailList[$a]->spare2 = "";
			$Statement->detailList[$a]->spare3 = "";
			$Statement->detailList[$a]->spare4 = "";
			$Statement->detailList[$a]->spare5 = "";
			$a++;
		}
	}

	try {
		$result = $StatementService->RegistIssue($testCorpNum, $Statement, $memo, $testUserID, $emailSubject);
		$code = $result->code;
		$message = $result->message;
	} catch(PopbillException $pe){
		$code = $pe->getCode();
		$message = $pe->getMessage();
	}

?>

	<script type="text/javascript">
		alert("<?=$message?>");
		history.back();
	</script>