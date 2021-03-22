<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/item.php');
    return;
}

include_once(G5_LIB_PATH.'/iteminfo.lib.php');

//이벤트
$ev_id = (isset($ev_id) && $ev_id) ? $ev_id : '';
if($ev_id) {
	$ev = sql_fetch(" select * from {$g5['g5_shop_event_table']} where ev_id = '$ev_id' and ev_use = 1 ");
	if (!$ev['ev_id']) $ev_id = '';
}

// 타입지정
$type_where = $type_qstr = '';
if(isset($type) && $type) {
	$type_where = " and it_type{$type} = '1'";
	$type_qstr = '&amp;type='.$type;
}
// 페이지 초기화
$is_item = true;
$itempage = $page;
$page = 0;

$it_id = get_search_string(trim($_GET['prodId']));

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql_ca = ($ca_id) ? "b.ca_id = '{$ca_id}'" : "a.ca_id = b.ca_id";
$sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_id = '$it_id' and $sql_ca ";
$it = sql_fetch($sql);

# 210131 옵션목록
$thisOptionList = [];
$thisOptionQuery = sql_query("SELECT * FROM g5_shop_item_option WHERE it_id = '{$it["it_id"]}' ORDER BY io_no ASC");
for($i = 0; $row = sql_fetch_array($thisOptionQuery); $i++){
	$row["io_id"] = explode(chr(30), $row["io_id"]);

	$rowOptionData = [];
	$rowOptionData["color"] = $row["io_id"][0];
	$rowOptionData["size"] = $row["io_id"][1];

	array_push($thisOptionList, $rowOptionData);
}
$it["optionList"] = $thisOptionList;

if (!$it['it_id'])
    alert('자료가 없습니다.');

// 멤버쉽 확인 ------------------------
if (function_exists('apms_membership_item')) {
	apms_membership_item($it['it_id']);
}

// 이용권한 확인 ------------------------
$is_author = ($is_member && $it['pt_id'] && $it['pt_id'] == $member['mb_id']) ? true : false;
$is_purchaser = apms_admin($xp['xp_manager']);
$is_remaintime = '';
if (!$is_purchaser && !$is_auther) {
	$purchase = apms_it_payment($it['it_id']);
	$is_purchaser = ($purchase['ct_qty'] > 0) ? true : false;
	if($it['pt_day'] > 0) { //기간제 상품일 경우
		$is_remaintime = strtotime($purchase['pt_datetime']) + ($it['pt_day'] * $purchase['ct_qty'] * 86400);
		$is_purchaser = ($is_remaintime >= G5_SERVER_TIME) ? true : false;
	}
}

// 분류코드
$ca_id = ($ca_id) ? $ca_id : $it['ca_id'];

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
//$sql = " select ca_{$mobile}skin_dir, ca_include_head, ca_include_tail, ca_cert_use, ca_adult_use from {$g5['g5_shop_category_table']} where ca_id = '{$ca_id}' ";
$sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '{$ca_id}' ";
$ca = sql_fetch($sql);

// 테마체크
$at = apms_ca_thema($ca_id, $ca);
if(!defined('THEMA_PATH')) {
	include_once(G5_LIB_PATH.'/apms.thema.lib.php');
}

if ($is_admin || $is_author || $is_purchaser) {
	;
} else {
	if ( THEMA_KEY == 'partner') {
		if (!($it['ca_use'] && $it['it_use_partner'])) {
			alert('판매가능한 상품이 아닙니다.');
		}
	}else{
		if (!($it['ca_use'] && $it['it_use'])) {
			alert('판매가능한 상품이 아닙니다.');
		}
	}

	$it['pt_explan'] = $it['pt_mobile_explan'] = '';
}
// 공통쿼리
if ( THEMA_KEY == 'partner') {
	$it_sql_common = " it_use_partner = '1' and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%') $type_where ";
}else{
	$it_sql_common = " it_use = '1' and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%') $type_where ";
}
// 보안서버경로
if (G5_HTTPS_DOMAIN)
    $action_url = G5_HTTPS_DOMAIN.'/'.G5_SHOP_DIR.'/cartupdate.php';
else
    $action_url = './cartupdate.php';
// 관련상품의 개수를 얻음
$item_relation_count = 0;
if($default['de_rel_list_use']) {
    $sql = " select count(*) as cnt from {$g5['g5_shop_item_relation_table']} a left join {$g5['g5_shop_item_table']} b on (a.it_id2=b.it_id) where a.it_id = '{$it['it_id']}' and b.it_use='1' ";
    $row = sql_fetch($sql);
    $item_relation_count = $row['cnt'];
}
$is_relation = ($item_relation_count > 0) ? true : false;
// 상품품절체크
if(G5_SOLDOUT_CHECK)
    $is_soldout = is_soldout($it['it_id']);

// 주문가능체크
$is_orderable = true;
if ( THEMA_KEY == 'partner') {
	if(!$it['it_use_partner'] || $it['it_tel_inq'] || $is_soldout) {
		$is_orderable = false;
	}
}else{
	if(!$it['it_use'] || $it['it_tel_inq'] || $is_soldout) {
		$is_orderable = false;
	}
}
// 주문폼 출력체크
$is_orderform = 1;
if($it['pt_order']) {
    $is_orderable = false;
	$is_orderform = '';
}
if($is_orderable) {
    // 선택 옵션
    $option_item = get_item_options($it['it_id'], $it['it_option_subject'], '');
    // 추가 옵션
    $supply_item = get_item_supply($it['it_id'], $it['it_supply_subject'], '');
    // 상품 선택옵션 수
    $option_count = 0;
    if($it['it_option_subject']) {
        $temp = explode(',', $it['it_option_subject']);
        $option_count = count($temp);
    }
    // 상품 추가옵션 수
    $supply_count = 0;
    if($it['it_supply_subject']) {
        $temp = explode(',', $it['it_supply_subject']);
        $supply_count = count($temp);
    }
}



// 스킨경로
$item_skin = apms_itemview_skin($at['item'], $ca_id, $it['ca_id']);
$item_skin_path = G5_SKIN_PATH.'/apms/item/'.$item_skin;
$item_skin_url = G5_SKIN_URL.'/apms/item/'.$item_skin;
$item_skin_file = $item_skin_path.'/item.skin2.php';
?>


<?php
include_once('./_common.php');

if(USE_G5_THEME && defined('G5_THEME_PATH')) {
    require_once(G5_SHOP_PATH.'/yc/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = '{$member['mb_id']}' AND od_del_yn = 'N' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' AND od_del_yn = 'N' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 스킨 체크
list($order_skin_path, $order_skin_url) = apms_skin_thema('shop/order', $order_skin_path, $order_skin_url); 

// 스킨설정
$wset = array();
if($skin_row['order_'.MOBILE_.'set']) {
	$wset = apms_unpack($skin_row['order_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
	@include ($demo_setup_file);
}

// 설정값 불러오기
$is_inquiry_sub = false;
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '주문내역조회';

if($is_inquiry_sub) {
	include_once(G5_PATH.'/head.sub.php');
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/head.sub.php');
} else {
	include_once('./_head.php');
}

$skin_path = $order_skin_path;
$skin_url = $order_skin_url;

// 셋업
$setup_href = '';
if(is_file($skin_path.'/setup.skin.php') && ($is_demo || $is_designer)) {
	$setup_href = './skin.setup.php?skin=order&amp;name='.urlencode($skin_name).'&amp;ts='.urlencode(THEMA);
}

// --------------------------------------------------------------------------------------------------------------------------------------------







$sql = 'SELECT * FROM `g5_shop_item` WHERE `it_id`="'.$_GET['prodId'].'"';
$row = sql_fetch($sql);
?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<style>
    #order_recipientBox { position:absolute;left:50%; width:100px; height:100px; background:#f00; margin:-50px 0 0 -50px; }
    #order_recipientBox { display:none;height:500px; }
    #order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
    #order_recipientBox iframe { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }
    @media (max-width : 750px){
        #popup{width:100%; height:100%; }
        #order_recipientBox {
        background-color:#fff;width: 100%; height: 100%; top:100px; left: 0; margin-left: 0; }
        #order_recipientBox iframe { width: 100%; height: 100%; top:100px; left: 0; margin-left: 0; }
    }
    #ui-datepicker-div { z-index: 999999 !important; }
</style>

<section id="stock" class="wrap" >
    <div class="list-more"><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php?&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>">목록</a></div>
        <h2>대여 재고 상세</h2>
        <div class="stock-view view2">
            <div class="product-view">
                <div class="pro-image" >
                    <img src="/data/item/<?=$row['it_img1']?>" alt="">
                </div>
                    <div class="info-list">
                    <ul>
                        <li>
                            <span>유통</span>
                            <span>이로움</span>
                        </li>
                        <li>
                            <span>세금</span>
                            <span><?=$row['it_taxInfo']?></span>
                        </li>
                        <li>
                            <span>제품코드</span>
                            <span><?=$row['it_id']?></span>
                        </li>
                        <li>
                            <span>가격</span>
                            <span><?=number_format($row['it_cust_price'])?> 원</span>
                        </li>
                    </ul>
                    <div class="info-btn">
                        <div>
                            <a href="javascript:popup01_show();" class="btn-01">신규재고등록</a>
                            <a href="<?=G5_SHOP_URL?>/item.php?it_id=<?=$row['it_id']?>" class="btn-02">상세정보</a>
                        </div>
                        <p>*보유 재고 등록 가능</p>
                    </div>
                </div>
            </div>
            <div class="popup01 popup2" id="popup01">
				<div class="p-inner">
					<h2>상품 옵션 설정</h2>
					<button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt="" onclick="popup01_hide()"></button>
					<?php include_once($item_skin_file);?>
				</div>
			</div>
            <div class="inner">
                <div class="table-wrap">
                    <p class="text01">대여기간 종료일이 1달 미만 제품입니다.</p>
                    <h3>보유 재고</h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">입고일</span>
                            <span class="state">상태</span>
                            <span class="none"></span>
                        </li>
<!------------------------------------------------------- 대여신청 ------------------------------------------------------->
<script>
    $('#order_recipientBox').hide();
    function popup_control(io_value_r_color,io_value_r_size,barcode_r){
        $('#order_recipientBox').show();
        var io_value_r_v="";
        if(io_value_r_color){io_value_r_v="색상:"+io_value_r_color; }
        if(io_value_r_color&&io_value_r_size){io_value_r_color=io_value_r_color+""; }
        if(io_value_r_color&&io_value_r_size){io_value_r_v=io_value_r_v+" / "; }
        if(io_value_r_size){io_value_r_v=io_value_r_v+ "사이즈:"+io_value_r_size;}
        // alert();
        // return false;
        document.getElementById('io_id_r').value=io_value_r_color+io_value_r_size;
        document.getElementById('io_value_r').value=io_value_r_v;
        document.getElementById('barcode_r').value=barcode_r;
    }
    function selected_recipient(penId){
        document.getElementById('penId_r').value=penId;
        document.getElementById('recipient_info').submit();
    }
</script>
<div id="order_recipientBox">
        <iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php" style='z-index:9999' ></iframe>
</div>
<!-- 수급자 선택시 변경되어 넘어갈 값 -->
<form action="<?php echo $action_url; ?>"name="fitem" method="post" id="recipient_info"class="form item-form">
    <input type="hidden" name="sw_direct" value="1" id="">                                              <!-- 바로가기 -->
    <input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>" id="it_id_r">                   <!-- 상품아이디 -->
    <input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0" it="io_type_r">            <!-- 옵션타입 -->
    <input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="" id="io_id_r">                 <!-- 옵션 값 -->
    <input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="" id="io_value_r">           <!-- 옵션 명 -->
    <input type="hidden" class="io_price" value="0" id="io_price_r">                                  <!-- 가격 -->
    <input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>" id="io_stock_r"><!-- 재고 -->
    <input type="hidden" name="ct_qty[<?php echo $it_id; ?>][]" value="1"id="ct_qty_r">               <!-- 수량 -->
    <input type="hidden" name="barcode_r" value="1" id="barcode_r">                                       <!-- 바코드 -->
    <input type="hidden" name="penId_r" value="1" id="penId_r">                                           <!-- penId -->
    <input type="hidden" name="recipient_info" value="1" id="recipient_info">                            <!-- 구분 -->
    <input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
    <input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
    <input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
</form>
<!------------------------------------------------------- 대여신청 -------------------------------------------------------->
                        <?php
						//보유재고 리스트 보유재고 api 통신
						$sendLength = 5;
						$sendData = [];
						$sendData["usrId"] = $member["mb_id"];
						$sendData["entId"] = $member["mb_entId"];
						$sendData["prodId"] = $_GET['prodId'];
						$sendData["pageNum"] = ($_GET["page2"]) ? $_GET["page2"] : 1;
						$sendData["pageSize"] = $sendLength;
                        // 01: 재고(대여가능) 02: 재고소진(대여중) 03: AS신청 04: 반품 05: 기타 06: 재고대기 07: 주문대기 08: 소독중 09: 대여종료
						$sendData["stateCd"] =['01','02','08','09'];
						$oCurl = curl_init();
						curl_setopt($oCurl, CURLOPT_PORT, 9001);
						curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectDetailList");
						curl_setopt($oCurl, CURLOPT_POST, 1);
						curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
						curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = curl_exec($oCurl);
						$res = json_decode($res, true);
						curl_close($oCurl);
						$list = [];
						if($res["data"]){
							$list = $res["data"];
						}
                        // print_r($list);

						# 페이징
						$totalCnt = $res["total"];
						$pageNum = $sendData["pageNum"]; # 페이지 번호
						$listCnt = $sendLength; # 리스트 갯수 default 10

						$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
						$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
						$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지 
						$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
						$total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
						// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
						if ($b_end_page > $total_page){ 
							$b_end_page = $total_page;
						}
						$total_block = ceil($total_page/$b_pageNum_listCnt);

                        //반복 시작
						?>
                        <div id="list_box1">    
                            <?php for($i=0;$i<count($list);$i++){ 
                                $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i	
                                $bg="";//대여중 일때 클래스 넣기
                                $rental_btn=''; //대여 버튼
                                //상태 메뉴
                                $state_menu_all="";
                                $state_menu1='<li><a class="state-btn4" onclick="open_retal_period(this)" href="javascript:;">대여기간 수정</a></li>';
                                $state_menu2='<li><a href="'.$list[$i]['eformUrl'].'">계약서 확인</a></li>';
                                $state_menu3='<li class="p-btn01"><a href="javascript:;" onclick="open_designate_disinfection(this)">소독신청</a></li>';
                                $state_menu4='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'01\',\'변경되었습니다.\')" >대여 가능상태</a></li>';
                                $state_menu5='<li class="p-btn02" onclick="open_designate_result(this)"><a href="javascript:;">소독확인 신청</a></li>';
                                $state_menu6='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'09\',\'소독 취소되었습니다.\')">소독취소</a></li>';
                                $state_menu7='<li><a href="javascript:;">소독확인중</a></li>';
                                $state_menu8='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'05\',\'불용재고로 등록되었습니다.\')" >불용재고등록</a></li>';
                                $state_menu9='<li><a href="javascript:;" onclick="retal_state_change(\''.$list[$i]['stoId'].'\',\'09\')">대여종료</a></li>';

                                //메뉴 선택  01: 재고(대여가능) 02: 재고소진(대여중) 08: 소독중 09: 대여종료
                                switch ($list[$i]['stateCd']) {
                                    case '01': $state="대여가능"; $state_menu_all=$state_menu3.$state_menu8; 
                                    $rental_btn='<a class="state-btn1" href="javascript:;"onclick="popup_control(\''.$list[$i]['prodColor'].'\',\''.$list[$i]['prodSize'].'\',\''.$list[$i]['prodBarNum'].'\')">대여</a>'; //대여 버튼
                                    $rental_btn2='<a class="state-btn1" href="javascript:;"onclick="popup_control(\''.$list[$i]['prodColor'].'\',\''.$list[$i]['prodSize'].'\',\''.$list[$i]['prodBarNum'].'\')">대여하기</a>'; //대여 버튼
                                    break;
                                    case '02': $state="대여중";  $state_menu_all = $state_menu1.$state_menu2.$state_menu9; $bg="bg"; $rental_btn=""; break;
                                    case '08': $state="소독중";  $state_menu_all =  $state_menu5.$state_menu6;break;
                                    case '09': $state="대여종료"; $state_menu_all=$state_menu3.$state_menu4.$state_menu1; break;
                                    default  : $state=""; break;
                                }
                                
                            ?>
                            <li class="list cb <?=$bg?>">
                                <!--pc용-->
                                <span class="num"><?=$number?></span>
                                <span class="product m_off"><?=$list[$i]['prodNm']?> <?php if($list[$i]['prodColor']||$list[$i]['prodSize']){ echo $list[$i]['prodColor'].'/'.$list[$i]['prodSize']; }else{ echo "(옵션 없음)"; } ?></span>
                                <?php
                                    if($_GET['prodSupYn'] == "N" ){
                                        $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                                    }else{
                                        $style_prodSupYn='style="border-color: #0000;background-color: #0000;"';
                                    }
                                ?>
                                <span class="pro-num m_off"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                                <?php 
                                //날짜 변환
                                $date1=$list[$i]['modifyDtm'];
                                $date2=date("Y-m-d", strtotime($date1));
                                ?>
                                <span class="date m_off"><?=$date2?></span>
                                <span class="state m_off">
                                    <b><?=$state?><img style="padding-left:5px;" src="<?=G5_IMG_URL?>/iconnew.png" alt=""> </b>
                                    <?=$rental_btn //대여버튼 ?>
                                </span>


                                <span class="none m_off">
                                    <div class="state-btn2" onclick="open_list(this);">
                                        <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                        <ul>
                                            <?=$state_menu_all; ?>
                                        </ul>
                                    </div>
                                    <a class="state-btn3" href="javascript:;" onclick="open_log(this)"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                </span>


                                <!--mobile용-->
                                <div class="list-m">
                                    <div class="info-m">
                                        <span class="product"><?=$list[$i]['prodNm']?> <?php if($list[$i]['prodColor']||$list[$i]['prodSize']){ echo $list[$i]['prodColor'].'/'.$list[$i]['prodSize']; }else{ echo "(옵션 없음)"; } ?></span>
                                        <span class="pro-num"><b <?=$style_prodSupYn?> ><?=$list[$i]['prodBarNum']?></b></span>
                                    </div>
                                    <div class="info-m">
                                        <span class="state">
                                            <b>대여가능<img style="padding-left:5px;" src="<?=G5_IMG_URL?>/iconnew.png" alt=""> </b>
                                        </span>
                                        <span class="none">
                                            <?=$rental_btn2?>
                                            <!-- <a class="state-btn1" href="javascript:;">대여하기</a> -->
                                            <div class="state-btn2" onclick="open_list(this);">
                                                <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                                                <ul>
                                                    <?=$state_menu_all; ?>
                                                </ul>
                                            </div>
                                            <a class="state-btn3" href="javascript:;"  onclick="open_log(this)" ><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                        </span>
                                    </div>
                                </div>

                                <!--팝업 위치는 li 바로 하위[li태그자식]로 넣어주세요. -->
                                <!-- 소독업체지정 -->
                                <div class="popup01 popup1">
                                    <div class="p-inner">
                                        <h2>소독업체 지정</h2>
                                        <button class="cls-btn p-cls-btn" type="button" onclick="close_popup(this)" ><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                        <ul>
                                            <li>
                                                <b>상세정보</b>
                                                <div class="input-box">
                                                    <input type="text" id="dis_detail_<?=$list[$i]['stoId']?>">
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                            <li>
                                                <b>담당자명</b>
                                                <div class="input-box">
                                                    <input type="text" id="dis_perosn_<?=$list[$i]['stoId']?>">
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                            <li>
                                                <b>연락처</b>
                                                <div class="input-box">
                                                    <input type="tel" id="dis_phone_<?=$list[$i]['stoId']?>">
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="popup-btn">
                                            <button type="submit" onclick="designate_disinfection('<?=$list[$i]['stoId']?>','dis_detail_<?=$list[$i]['stoId']?>','dis_perosn_<?=$list[$i]['stoId']?>','dis_phone_<?=$list[$i]['stoId']?>','08')" >확인</button>
                                            <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 소독결과 확인 -->
                                <div class="popup01 popup2">
                                    <form action="<?=G5_SHOP_URL?>/update_designate_result.php" id="designate_result_form_<?=$list[$i]['stoId']?>" method="POST" enctype="multipart/form-data" autocomplete="off">
                                    <input type="hidden" name="member" value="<?php echo $member['mb_id']?>">
                                    <input type="hidden" name="stoId" value="<?=$list[$i]['stoId']?>">
                                    <div class="p-inner">
                                        <h2>소독 결과 확인</h2>
                                        <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                        <ul>
                                            <li>
                                                <b>소독일자</b>
                                                <div class="input-box">
                                                    <input type="text" name="dis_date" dateonly id="dis_date_<?=$list[$i]['stoId']?>" readonly>
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                            <li>
                                                <b>약품종류</b>
                                                <div class="input-box">
                                                    <input type="text" name="dis_chemical" id="dis_chemical_<?=$list[$i]['stoId']?>">
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                            <li>
                                                <b>약품사용내역</b>
                                                <div class="input-box">
                                                    <input type="text"  name="dis_chemical_history" id="dis_chemical_history_<?=$list[$i]['stoId']?>">
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                            </li>
                                            <li class="file-list">
                                                <b>첨부파일(소독필증)</b>
                                                <div class="input-box">
                                                    <input type="text"  name="dis_file_text" id="dis_file_text_<?=$list[$i]['stoId']?>" class="filetext" readonly>
                                                    <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                </div>
                                                <div class="inputFile cb">
                                                    <input type="file"  name="dis_file" id="dis_file_<?=$list[$i]['stoId']?>" class="fileHidden" name=""  title="파일첨부 1 : 용량  이하만 업로드 가능">
                                                    <label for="dis_file_<?=$list[$i]['stoId']?>"></label>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="popup-btn">
                                        <!-- designate_disinfection('<?=$list[$i]['stoId']?>','dis_detail_<?=$list[$i]['stoId']?>','dis_perosn_<?=$list[$i]['stoId']?>','dis_phone_<?=$list[$i]['stoId']?>','08 -->
                                            <button type="button" onclick="designate_result('<?=$list[$i]['stoId']?>','dis_date_<?=$list[$i]['stoId']?>','dis_chemical_<?=$list[$i]['stoId']?>','dis_chemical_history_<?=$list[$i]['stoId']?>','dis_file_text_<?=$list[$i]['stoId']?>','dis_file_<?=$list[$i]['stoId']?>','designate_result_form_<?=$list[$i]['stoId']?>')">확인</button>
                                            <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>

                                <!-- 대여기록 -->
                                <div class="popup01 popup3">
                                    <div class="p-inner">
                                        <h2>대여 기록</h2>
                                        <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                        <div class="table-box">
                                            <div class="tti">
                                                <h4>상품명(옵션명)</h4>
                                                <span>123456497</span>
                                            </div>
                                            <table>
                                                <colgroup>
                                                    <col width="10%">
                                                    <col width="30%">
                                                    <col width="30%">
                                                    <col width="30%">
                                                </colgroup>
                                                <thead>
                                                    <th>No.</th>
                                                    <th>내용</th>
                                                    <th>기간</th>
                                                    <th>문서</th>
                                                </thead>
                                                <tbody>
                                                    <?php //for(){ 
                                                        $sql_v=
                                                        "SELECT g5_disinfection.disId,
                                                                g5_disinfection.stoId,
                                                                g5_disinfection.dis_detail,
                                                                g5_disinfection.dis_perosn,
                                                                g5_disinfection.dis_date,
                                                                g5_disinfection.dis_chemical,
                                                                g5_disinfection.dis_chemical_history,
                                                                g5_disinfection.dis_file,
                                                                g5_disinfection.dis_total_date,
                                                                g5_rental.renId,
                                                                g5_rental.stoId,
                                                                g5_rental.ordId,
                                                                g5_rental.ren_person,
                                                                g5_rental.ren_date1,
                                                                g5_rental.ren_date2,
                                                                g5_rental.ren_eformUrl
                                                        FROM g5_disinfection left join g5_rental 
                                                        WHERE g5_rental.stoId = '".$list[$i]['stoId']."' order by ";    
                                                        
                                                    ?>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>홍길동 대여</td>
                                                        <td>01/20~02/11</td>
                                                        <td>
                                                            <a href="javascript:;">계약서</a>
                                                        </td>
                                                    </tr>
                                                    <?php// } ?>

                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="pg-wrap">
                                            <div>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a>
                                                <a href="javascript:;" class="on">1</a>
                                                <a href="javascript:;">2</a>
                                                <a href="javascript:;">3</a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 대여기간 수정 -->
                                <div class="popup01 popup4">
                                    <form action="">
                                        <div class="p-inner">
                                            <h2>대여기간 수정</h2>
                                            <button onclick="close_popup(this)" class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                            <ul>
                                                <?php
                                                     //날짜 변환
                                                    $ordLendStrDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendStrDtm']));
                                                    $ordLendEndDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendEndDtm']));
                                                ?>
                                                <li>
                                                    <b>대여시작일</b>
                                                    <div class="input-box">
                                                        <input type="text" value="<?=$ordLendStrDtm_date?>" dateonly id="strDtm_<?=$list[$i]['stoId']?>">
                                                        <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                    </div>
                                                </li>
                                                <li>
                                                    <b>대여종료일</b>
                                                    <div class="input-box">
                                                        <input type="text" value="<?=$ordLendEndDtm_date?>" dateonly id="endDtm_<?=$list[$i]['stoId']?>">
                                                        <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                                                    </div>
                                                </li>

                                            </ul> 
                                            <div class="popup-btn">
                                                <!-- ordId,stoId,ordLendStrDtm,ordLendEndDtm -->
                                                <button type="button" onclick="retal_period_change('<?php echo $list[$i]['penOrdId']?>','<?=$list[$i]['stoId']?>','strDtm_<?=$list[$i]['stoId']?>','endDtm_<?=$list[$i]['stoId']?>')">확인</button>
                                                <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </li>
                            <?php } //반복끝 ?>
                        </div>
                    </ul>
                    <i class="text02">* 이로움몰에서 구매한 바코드는 관리자 문의 후 수정이 가능합니다.</i>
                </div>
                
                <!-- 페이징 -->
                <div class="pg-wrap" id="pagin_1">
                    <div id="numbering_zone1">
                        <?php if($pageNum >$b_pageNum_listCnt){ ?><a href="javascript:page_load('1')"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
                        <?php if($block > 1){ ?><a href="javascript:page_load('<?=($b_start_page-1)?>')"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
                        <?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="javascript:page_load('<?=$j?>')"><?=$j?></a><?php } ?>
                        <?php if($block < $total_block){ ?><a href="javascript:page_load('<?=($b_end_page+1)?>')"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
                        <?php if($block < $total_block){ ?><a href="javascript:page_load('<?=$total_page?>')"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
                    </div>
                </div>







                <!-- 불용재고 -->
                <div class="table-wrap table-wrap2">
                    <h3>불용 재고 <i>(분실 및 파손으로 운영이 불가능한 제품)</i></h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">종료일</span>
                            <span class="none"></span>
                        </li>

                        <?php
						//보유재고 리스트 보유재고 api 통신
						$sendLength = 5;
						$sendData = [];
						$sendData["usrId"] = $member["mb_id"];
						$sendData["entId"] = $member["mb_entId"];
						$sendData["prodId"] = $_GET['prodId'];
						$sendData["pageNum"] = ($_GET["page2"]) ? $_GET["page2"] : 1;
						$sendData["pageSize"] = $sendLength;
                        // 01: 재고(대여가능) 02: 재고소진(대여중) 03: AS신청 04: 반품 05: 기타 06: 재고대기 07: 주문대기 08: 소독중 09: 대여종료
						$sendData["stateCd"] =['03','04','05'];
						$oCurl = curl_init();
						curl_setopt($oCurl, CURLOPT_PORT, 9001);
						curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectDetailList");
						curl_setopt($oCurl, CURLOPT_POST, 1);
						curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
						curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
						curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
						$res = curl_exec($oCurl);
						$res = json_decode($res, true);
						curl_close($oCurl);
						$list = [];
						if($res["data"]){
							$list = $res["data"];
						}

						# 페이징
						$totalCnt = $res["total"];
						$pageNum = $sendData["pageNum"]; # 페이지 번호
						$listCnt = $sendLength; # 리스트 갯수 default 10

						$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
						$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
						$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지 
						$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
						$total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
						// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
						if ($b_end_page > $total_page){ 
							$b_end_page = $total_page;
						}
						$total_block = ceil($total_page/$b_pageNum_listCnt);

                        //반복 시작
						?>
                        <div id="list_box2">
                            <?php for($i=0;$i<count($list);$i++){ 
                                $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i	
                                //메뉴 선택  01: 재고(대여가능) 02: 재고소진(대여중) 08: 소독중 09: 대여종료
                                switch ($list[$i]['stateCd']) {
                                    case '03': $state="AS신청"; break;
                                    case '04': $state="반품";   break;
                                    case '05': $state="기타";   break;
                                    default  : $state="";      break;
                                }
                            ?>

                            <li class="list cb">
                                <!--pc용-->
                                <span class="num"><?=$number?></span>
                                <span class="product m_off"><?=$list[$i]['prodNm']?> <?php if($list[$i]['prodColor']||$list[$i]['prodSize']){ echo $list[$i]['prodColor'].'/'.$list[$i]['prodSize']; }else{ echo "(옵션 없음)"; } ?></span>
                                <span class="pro-num m_off"><b><?=$list[$i]['prodBarNum']?></b></span>
                                <?php 
                                //날짜 변환
                                $date1=$list[$i]['modifyDtm'];
                                $date2=date("Y-m-d", strtotime($date1));
                                ?>
                                <span class="date m_off"><?=$date2?></span>
                                <span class="none m_off" onclick="open_log(this)">
                                    <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                </span>
                                <!--mobile용-->
                                <div class="list-m">
                                    <div class="info-m">
                                        <span class="product"><?=$list[$i]['prodNm']?> <?php if($list[$i]['prodColor']||$list[$i]['prodSize']){ echo $list[$i]['prodColor'].'/'.$list[$i]['prodSize']; }else{ echo "(옵션 없음)"; } ?></span>
                                        <span class="pro-num" ><b><?=$list[$i]['prodBarNum']?></b></span>
                                    </div>
                                    <div class="info-m">
                                        <span class="none">
                                            <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                                        </span>
                                    </div>
                                </div>

                                   <!-- 대여기록 -->
                                   <div class="popup01 popup3">
                                    <div class="p-inner">
                                        <h2>대여 기록</h2>
                                        <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                                        <div class="table-box">
                                            <div class="tti">
                                                <h4>상품명(옵션명)</h4>
                                                <span>123456497</span>
                                            </div>
                                            <table>
                                                <colgroup>
                                                    <col width="10%">
                                                    <col width="30%">
                                                    <col width="30%">
                                                    <col width="30%">
                                                </colgroup>
                                                <thead>
                                                    <th>No.</th>
                                                    <th>내용</th>
                                                    <th>기간</th>
                                                    <th>문서</th>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>3</td>
                                                        <td>홍길동 대여</td>
                                                        <td>01/20~02/11</td>
                                                        <td>
                                                            <a href="javascript:;">계약서</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2</td>
                                                        <td>ABC 업체소독</td>
                                                        <td>01/20~02/11</td>
                                                        <td>
                                                            <a href="javascript:;">소독 확인서</a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>1</td>
                                                        <td>홍길동 대여</td>
                                                        <td>01/20~02/11</td>
                                                        <td>
                                                            <a href="javascript:;">계약서</a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="pg-wrap">
                                            <div>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a>
                                                <a href="javascript:;" class="on">1</a>
                                                <a href="javascript:;">2</a>
                                                <a href="javascript:;">3</a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a>
                                                <a href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        </div>
                        <!--반복-->
                    </ul>
                </div>
                <!-- 페이징2 -->
                <div class="pg-wrap" id="pagin_2">
                    <div id="numbering_zone1">
                        <?php if($pageNum >$b_pageNum_listCnt){ ?><a href="javascript:page_load2('1')"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
                        <?php if($block > 1){ ?><a href="javascript:page_load2('<?=($b_start_page-1)?>')"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
                        <?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="javascript:page_load2('<?=$j?>')"><?=$j?></a><?php } ?>
                        <?php if($block < $total_block){ ?><a href="javascript:page_load2('<?=($b_end_page+1)?>')"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
                        <?php if($block < $total_block){ ?><a href="javascript:page_load2('<?=$total_page?>')"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
                    </div>
                </div>
                
            </div>
        </div>




<!-- 스크립트 -->
<script>
     //항목 펼치기
    function open_list(e){
        $(e).find('ul').toggleClass('on');
        $(e).parents('.list').siblings('.list').find('.').find('ul').removeClass('on');
    }

    //##팝업
    //대여기간 수정
    function open_retal_period(e){
        $(e).parents('.list').find('.popup4').stop().show();
    }
    // $('.state-btn4').on('click',function(){
        
    // });

    //대여기록 팝업
    function open_log(e){
        $(e).parents('.list').find('.popup3').stop().show();
    }

    //소독업체지정 팝업
    function open_designate_disinfection(e){
        $(e).parents('.list').find('.popup1').stop().show();
    }
    //소독확인신청 팝업
    // $('.p-btn02').on('click',function(){
    //     $(this).parents('.list').find('.popup2').stop().show();
    // });
    function open_designate_result(e){
        $(e).parents('.list').find('.popup2').stop().show();
    }
    function close_popup(e){
        // alert('z');
        // e.stopPropagation();
        $(e).parents('.popup01').stop().hide();
    }

    //신규재고 등록
    function popup01_show(){
        document.getElementById('popup01').style.display = 'block';
    };
    function popup01_hide(){
        document.getElementById('popup01').style.display = 'none'
    };

    //날짜 선택
    $.datepicker.setDefaults({
        dateFormat : 'yy-mm-dd',
        prevText: '이전달',
        nextText: '다음달',
        monthNames: ['01','02','03','04','05','06','07','08','09','10','11','12'],
        monthNamesShort: ['01','02','03','04','05','06','07','08','09','10','11','12'],
        dayNames: ["일", "월", "화", "수", "목", "금", "토"],
        dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
        dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
        showMonthAfterYear: true,
        changeMonth: true,
        changeYear: true,
        yearRange : "c-150:c+10"
    });
    //날짜 넣기
    $("input:text[dateonly]").datepicker({});
            
    //대여기간수정 api 통신
    function retal_period_change(penOrdId,stoId,strDtm,endDtm){
        var ordLendStrDtm = document.getElementById(strDtm);
        var ordLendEndDtm = document.getElementById(endDtm);
        var sendData = {
            usrId : "<?=$member["mb_id"]?>",
            penOrdId : penOrdId,  
            prods : [
                {
                    stoId : stoId,                      //재고아이디
                    ordLendStrDtm : ordLendStrDtm.value, //대여시작일
                    ordLendEndDtm : ordLendEndDtm.value  //대여종료일
                }
            ]
        }
        $.ajax({
            url : "./ajax.order.update.php",
            type : "POST",
            async : false,
            data : sendData,
            success : function(result){
                result = JSON.parse(result);

                if(result.errorYN == "Y"){
                    alert(result.message);
                } else {
                    alert('변경이 완료되었습니다.');
                    $(ordLendStrDtm).parents('.popup01').stop().hide();
                    
                }
            }
        });
    }

    //대여상품 상태변경 api 통신 (대여종료)
    function retal_state_change(stoId,stateCd){
        var sendData = {
            usrId : "<?=$member["mb_id"]?>",
            prods : [
                {
                    stoId : stoId,                       //재고아이디
                    stateCd : stateCd,                 //상태값
                }
            ]
        }

        console.log(sendData);
        $.ajax({
            url : "./ajax.stock.update.php",
            type : "POST",
            async : false,
            data : sendData,
            success : function(result){
                result = JSON.parse(result);
                if(result.errorYN == "Y"){
                    alert(result.message);
                } else {
                    alert('변경이 완료되었습니다.');
                    window.location.reload();
                }
            }
        });
    }

    //대여상품 상태변경 api 통신 (소독신청 ,소독취소) 소독취소 -> 대여가능
    function retal_state_change2(stoId,stateCd,string){
        var sendData = {
            usrId : "<?=$member["mb_id"]?>",
            prods : [
                {
                    stoId : stoId,                       //재고아이디
                    stateCd : stateCd,                 //상태값
                }
            ]
        }
        $.ajax({
            url : "./ajax.stock.update.php",
            type : "POST",
            async : false,
            data : sendData,
            success : function(result){
                result = JSON.parse(result);
                if(result.errorYN == "Y"){
                    alert(result.message);
                    return false;
                }else{
                    alert(string);
                    window.location.reload();
                }
            }
        });
    }

    //소독업체 지정
    function designate_disinfection(stoId,dis_detail,dis_perosn,dis_phone,stateCd){
        var dis_detail = document.getElementById(dis_detail);
        var dis_perosn = document.getElementById(dis_perosn);
        var dis_phone = document.getElementById(dis_phone);

        if(!dis_detail.value){ alert('상세정보를 입력해주세요'); return false;}
        if(!dis_perosn.value){ alert('담당자명을 입력해주세요'); return false;}
        if(!dis_phone.value){ alert('연락처를 입력해주세요'); return false;}
        
        var sendData = {
            stoId : stoId,
            dis_detail : dis_detail.value,
            dis_perosn : dis_perosn.value,
            dis_phone : dis_phone.value,
            dis_new : '1'
        };

        $.ajax({
            url : "./ajax.designate_disinfection.php",
            type : "POST",
            async : false,
            data : sendData,
            success : function(result){
                if(result=="S"){
                    retal_state_change2(stoId,stateCd,"소독업체 지정이 완료 되었습니다.");
                }else{
                    alert(result);
                }
            }
        });
    }

    //파일 이름 넣기
    $(document).on('change', '.fileHidden', function() {
        ext = $(this).val().split('.').pop().toLowerCase(); //확장자
        //배열에 추출한 확장자가 존재하는지 체크
        if($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
            alert('이미지 파일이 아닙니다.');
            $(this).val()="";
            return false;
        }else{
            $(this).closest("li").find(".filetext").val(this.files[0].name);
        }
    });

    //소독 결과 확인 지정 POST - >PHP 
    function designate_result(stoId,dis_date,dis_chemical,dis_chemical_history,dis_file_text,dis_file,designate_result_form){
        var dis_date = document.getElementById(dis_date);
        var dis_chemical = document.getElementById(dis_chemical);
        var dis_chemical_history = document.getElementById(dis_chemical_history);
        var dis_file_text = document.getElementById(dis_file_text);
        var dis_file = document.getElementById(dis_file);
        var designate_result_form = document.getElementById(designate_result_form);
        if(!dis_date.value){ alert('날짜를 입력해주세요'); return false;}
        if(!dis_chemical.value){ alert('약품종류를 입력해주세요'); return false;}
        if(!dis_chemical_history.value){ alert('약품사용내역을 입력해주세요'); return false;}
        if(!dis_file_text.value){ alert('파일을 선택해주세요'); return false;}
        designate_result_form.submit();
    }

    //페이징 처리1
    function page_load(page_n) {
        page_n = parseInt(page_n);
        $.ajax({
            url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail2.php?prodId=<?=$it_id?>&page2="+page_n,
            type : "get",
            async : false,
            success : function(result){
                var list_box1 =$(result).find("#list_box1").html();
                var pagin_1 =$(result).find("#pagin_1").html();
                $("#list_box1").html("");
                $("#pagin_1").html("");
                $("#list_box1").append(list_box1);
                $("#pagin_1").append(pagin_1);
            }
        });
        $("input:text[dateonly]").datepicker({});
    }
    //페이징 처리2
    function page_load2(page_n) {
        page_n = parseInt(page_n);
        $.ajax({
            url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail2.php?prodId=<?=$it_id?>&page2="+page_n,
            type : "get",
            async : false,
            success : function(result){
                var list_box2 =$(result).find("#list_box2").html();
                var pagin_2 =$(result).find("#pagin_2").html();
                $("#list_box2").html("");
                $("#pagin_2").html("");
                $("#list_box2").append(list_box2);
                $("#pagin_2").append(pagin_2);
            }
        });
        $("input:text[dateonly]").datepicker({});
    }

</script>




<?php
if($is_inquiry_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>
  
  
  