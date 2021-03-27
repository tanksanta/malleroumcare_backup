<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// StyleSheet
add_stylesheet('<link rel="stylesheet" href="'.$skin_url.'/style.css" type="text/css" media="screen">',0);

// 목록헤드
if(isset($wset['ohead']) && $wset['ohead']) {
	add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/head/'.$wset['ohead'].'.css" media="screen">', 0);
	$head_class = 'list-head';
} else {
	$head_class = (isset($wset['ocolor']) && $wset['ocolor']) ? 'tr-head border-'.$wset['ocolor'] : 'tr-head border-black';
}

// 헤더 출력
if($header_skin)
	include_once('./header.php');

	# 스킨경로	
	$SKIN_URL = G5_SKIN_URL.'/apms/order/'.$skin_name;

?>

<?php // 주문서폼 시작 - id 변경불가 & 삭제하면 안됨 ?>

<link rel="stylesheet" href="<?=$SKIN_URL?>/css/product_order_210324.css">
<form name="forderform" id="forderform" method="post" action="<?php echo $action_url; ?>" autocomplete="off" role="form" class="form-horizontal">
    
    <input type="radio" id="od_settle_pay_end" name="od_settle_case" value="월 마감 정산" style="display: none;" checked>
    <input type="radio" id="od_settle_bank" name="od_settle_case" value="무통장" style="display: none;">
    <input type="checkbox" name="od_stock_insert_yn" id="od_stock_insert_yn" style="display: none;">
    
	<input type="hidden" name="penId" id="penId">
	<input type="hidden" name="penTypeCd" id="penTypeCd">
	<input type="hidden" name="searchUsrId" id="searchUsrId" value="123456789">
	<input type="hidden" name="shoBasSeq" id="shoBasSeq" value="12">
	<input type="hidden" name="prodBarNum" id="prodBarNum" value="">
	<input type="hidden" name="ordNm" id="ordNm" value="김예비">
	<input type="hidden" name="ordCont" id="ordCont" value="010-2551-8080">
	<input type="hidden" name="ordZip" id="ordZip" value="46241">
	<input type="hidden" name="ordAddr" id="ordAddr" value="부산 금정구 부산대학로63번길 2">
	<input type="hidden" name="ordAddrDtl" id="ordAddrDtl" value="(장전동) 1">
	<input type="hidden" name="ordMemo" id="ordMemo" value="">
	<input type="hidden" name="payMehCd" id="payMehCd" value="00">
    
    <input type="text" name="penNm" id="penNm" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penTypeNm" id="penTypeNm" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penExpiDtm" id="penExpiDtm" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penAppEdDtm" id="penAppEdDtm" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penConPnum" id="penConPnum" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penConNum" id="penConNum" class="form-control input-sm" readonly style="display: none;">
    <input type="text" name="penAddr" id="penAddr" class="form-control input-sm" readonly style="display: none;">
<!--    <input type="text" name="penMoney" id="penMoney" class="form-control input-sm" readonly>-->

    <section id="pro-order" class="wrap order-list">
        <h2 class="tti">
            주문신청
            <p>주문 방법을 선택하세요.</p>
        </h2>
        <div class="detail-tab">
            <ul>
                <li class="on" data-type="order">
                    <span></span>
                    <h4>상품 주문</h4>
                    <p>
                        주문하신 상품은 재고로 등록됩니다. <br>
                        비유통 상품은 배송되지 않습니다.
                    </p>
                </li>
                <li data-type="order_pen" id="c_recipient">
                    <span></span>
                    <h4>수급자 주문</h4>
                    <p>
                        수급자와 계약하기 위한 주문입니다. <br>
                        주문시 수급자의 전자서명이 필요합니다.
                    </p>
                </li>
                <li data-type="stock_insert">
                    <span></span>
                    <h4>보유재고 등록</h4>
                    <p>
                        현재 보유하신 재고를 <br>
                        이로움에 등록할 수 있습니다.
                    </p>
                </li>
            </ul>
        </div>