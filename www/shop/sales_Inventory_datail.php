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

// ----------------------------------------------------------

// 공통쿼리
if ( THEMA_KEY == 'partner') {
	$it_sql_common = " it_use_partner = '1' and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%') $type_where ";
}else{
	$it_sql_common = " it_use = '1' and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%') $type_where ";
}
//$it_sql_common = " it_use = '1' and (ca_id like '{$ca_id}%' or ca_id2 like '{$ca_id}%' or ca_id3 like '{$ca_id}%') $type_where ";




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

	include_once(G5_SHOP_PATH.'/settle_naverpay.inc.php');
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
$sql = 'SELECT * FROM `g5_shop_item` WHERE `it_id`="'.$_GET['prodId'].'"';
$row = sql_fetch($sql);

?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
<section id="stock" class="wrap" >
        <div class="list-more m_off"><a href="<?=G5_SHOP_URL?>/sales_Inventory.php?&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>">목록</a></div>
        <h2>판매 재고 상세</h2>
        <div class="stock-view">
            <div class="product-view">
                <div class="pro-image">
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
                            <span><?=$row['ProdPayCode']?></span>
                        </li>
                        <li>
                            <span>가격</span>
                            <span><?=number_format($row['it_cust_price'])?> 원</span>
                        </li>
                    </ul>
                    <div class="info-btn">
                        <div class="info-btn-area">
                            <a href="javascript:popup01_show();" class="btn-01">신규상품주문</a>
                            <a href="<?=G5_SHOP_URL?>/item.php?it_id=<?=$row['it_id']?>" class="btn-02">상세정보</a>
                        </div>
                        <p>*보유 재고 등록 가능</p>
                    </div>
                </div>
            </div>
            <script>
            function popup01_show(){
                document.getElementById('popup01').style.display = 'block';
            };
            function popup01_hide(){
                document.getElementById('popup01').style.display = 'none'
            };
            </script>

			<div class="popup01 popup2" id="popup01">
				<div class="p-inner">
					<div class="pop_tit">상품 옵션 설정</div>
					<button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt="" onclick="popup01_hide()"></button>
					<?php include_once($item_skin_file);?>
				</div>
			</div>


            <div class="inner">
                <div class="table-wrap">
                    <div class="tit_area">
                    	<h3>보유 재고</h3>
                    
	                    <form action="">
	                            <input type="hidden" name="prodId" value="<?=$_GET['prodId']?>">
	                            <input type="hidden" name="page" value="<?=$_GET['page']?>">
	                            <input type="hidden" name="searchtype" value="<?=$_GET['searchtype']?>">
	                            <input type="hidden" name="searchtypeText" value="<?=$_GET['searchtypeText']?>">
	                            <input type="hidden" name="prodSupYn" value="<?=$_GET['prodSupYn']?>">
	                            <div class="search-box">
	                                <select name="soption" id="">
	                                    <option value="1" <?=$_GET['soption'] == "1" ? 'selected' : '' ?> >바코드</option>
	                                    <option value="2" <?=$_GET['soption'] == "2" ? 'selected' : '' ?> >옵션명</option>
	                                </select>
	                                <div class="input-search">
	                                    <input name="stx" value="<?=$_GET["stx"]?>" type="text">
	                                    <button  type="submit"></button>
	                                </div>
	                            </div>
	                    </form>
                    </div>
                    

                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="date">입고일</span>
                            <span class="order">판매</span>
                            <span class="del">삭제</span>
                        </li>
						<?php
						//판매재고 리스트
						$sendLength = 5;
						$sendData = [];
                        $prodsSendData = [];
						$sendData["usrId"] = $member["mb_id"];
						$sendData["entId"] = $member["mb_entId"];
						$sendData["prodId"] = $_GET['prodId'];
						$sendData["pageNum"] = ($_GET["page2"]) ? $_GET["page2"] : 1;
						$sendData["pageSize"] = $sendLength;
                        $sendData["stateCd"] =['01'];
                        if($_GET['soption']=="1"){
                            $sendData["prodBarNum"]=$_GET['stx'];
                        }
                        if($_GET['soption']=="2"){
                            $sendData["searchOption"] =$_GET['stx'];
                        }

						$oCurl = curl_init();
						curl_setopt($oCurl, CURLOPT_PORT, 9901);
						curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/selectDetailList");
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
						?>
                        <!-- 수급자신청 -->
                        <style>
                            /* .popup{display:none;} */
                            #order_recipientBox{
                                position:absolute;
                                /* top:20%; */
                                left:50%;
                                width:100px;
                                height:100px;
                                background:#f00;
                                margin:-50px 0 0 -50px;
                            }
                            #order_recipientBox { display:none;height:500px; }
                            #order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
                            #order_recipientBox iframe { position: relative; width: 700px; height: 500px; border: 0; background-color: #FFF; left: 50%; margin-left: -350px; }
                            @media (max-width : 750px){
                                #popup{width:100%; height:100%; }
                                #order_recipientBox {
                                background-color:#fff;width: 100%; height: 100%; top:100px; left: 0; margin-left: 0; }
                                #order_recipientBox iframe { position: fixed; top:0;margin: 0 auto; left: 0; right:0; width: 100%; height: 100%;  }
                            }
                        </style>
                        <script>
                            $('#order_recipientBox').hide();
                            function popup_control(io_value_r_color,io_value_r_size,barcode_r){
                                $('#order_recipientBox').show();
                                wrapWindowByMask();
                                var io_value_r_v="";
                                if(io_value_r_color){io_value_r_v="색상:"+io_value_r_color; }
                                if(io_value_r_color&&io_value_r_size){io_value_r_color=io_value_r_color+""; }
                                if(io_value_r_color&&io_value_r_size){io_value_r_v=io_value_r_v+" / "; }
                                if(io_value_r_size){io_value_r_v=io_value_r_v+ "사이즈:"+io_value_r_size;}
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
                        <!-- 수급자신청 -->
                        <?php if(!$list){ ?>
                            <li style="text-align:center" >
                                자료가 없습니다.
                            </li>
                        <?php } ?>
                        <div id="list_box1">
						<?php for($i=0;$i<count($list);$i++){
							$number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
                            if($list[$i]['prodColor']&&$list[$i]['prodSize']){ $div="/";}else{$div="";}
                        ?>
                        <?php
                            //유통 / 비유통 구분
                            $sql_stock ="SELECT `od_id`, `od_stock_insert_yn` FROM `g5_shop_order` WHERE `stoId` LIKE '%".$list[$i]['stoId']."%'";
                            $result_stock = sql_fetch($sql_stock);
                            $stock_insert="1";
                            if($result_stock['od_stock_insert_yn']=="Y"){
                                $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                                $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
                                $stock_insert ="2";
                            }else{
                                if($_GET['prodSupYn'] == "N" ){
                                    $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                                    $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
                                }else{
                                    $style_prodSupYn='style="border-color: #0000;background-color: #0000; cursor :default;"';
                                    $prodBarNumCntBtn_2="";
                                }
                            }
                        ?>
                        <!--반복-->
                        <li class="list cb">
                            <!--pc용-->
                            <span class="num"><?=$number?></span>
                            <span class="product m_off">
                            <?php 
                                if($list[$i]['prodColor']||$list[$i]['prodSize']){ 
                                    $name= $list[$i]['prodNm'].'('.$list[$i]['prodColor'].$div.$list[$i]['prodSize'].')'; 
                                }else{ 
                                    $name=$list[$i]['prodNm'];
                                } 
                                echo $name;
                            ?>
                            </span>
                            <span class="pro-num m_off <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                            <?php
                                //날짜 변환
                                $date1=$list[$i]['regDtm'];
                                $date2=date("Y-m-d H:i", strtotime($date1));
                            ?>
                            <span class="date m_off"><?=$date2?></span>
                            <span class="order m_off">
                                <a href="javascript:;" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodBarNum']?>')">수급자선택</a>
                            </span>
                            <span class="del m_off">
                                <a href="javascript:;" onclick="del_stoId('<?=$list[$i]['stoId']?>')">
                                    삭제
                                </a>
                            </span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product"><?=$list[$i]['prodNm']?>
                                    <?=$name?>
                                    </span>
                                    <span class="pro-num <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                                </div>
                                <div class="info-m">
                                    <span class="date"><?=$date2?></span>
                                    <span class="order">
                                        <a href="javascript:;" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodBarNum']?>')" >수급자선택</a>
                                    </span>
                                    <span class="order2">
                                        <a href="javascript:;" onclick="del_stoId('<?=$list[$i]['stoId']?>')">삭제</a>
                                    </span>
                                </div>
                            </div>
                        </li>
						<?php } ?>
                        </div>
                    </ul>
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



                <div class="table-wrap table-wrap2">
                    <h3>판매 완료</h3>
                    <ul>
                        <li class="head cb">
                            <span class="num">No.</span>
                            <span class="product">상품(옵션)</span>
                            <span class="pro-num">바코드</span>
                            <span class="name">수급자</span>
                            <span class="date">종료일</span>
                            <span class="check">계약서</span>
                        </li>
                        <?php
						//판매완료 리스트
						$sendLength = 5;
						$sendData = [];
                        $prodsSendData = [];
						$sendData["usrId"] = $member["mb_id"];
						$sendData["entId"] = $member["mb_entId"];
						$sendData["prodId"] = $_GET['prodId'];
						$sendData["pageNum"] = ($_GET["page2"]) ? $_GET["page2"] : 1;
						$sendData["pageSize"] = $sendLength;
                        $sendData["stateCd"] =['02','07'];
                        if($_GET['soption']=="1"){
                            $sendData["prodBarNum"]=$_GET['stx'];
                        }
                        if($_GET['soption']=="2"){
                            $sendData["searchOption"] =$_GET['stx'];
                        }
						$oCurl = curl_init();
						curl_setopt($oCurl, CURLOPT_PORT, 9901);
						curl_setopt($oCurl, CURLOPT_URL, "https://system.eroumcare.com/api/stock/selectDetailList");
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
						?>
                      <?php if(!$list){ ?>
                            <li style="text-align:center" >
                                자료가 없습니다.
                            </li>
                        <?php } ?>
                        <div id="list_box2">
						<?php for($i=0;$i<count($list);$i++){
							$number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
                            if($list[$i]['prodColor']&&$list[$i]['prodSize']){ $div="/";}else{$div="";}
						?>
                        <?php
                            //유통 / 비유통 구분
                            $sql_stock ="SELECT `od_id`, `od_stock_insert_yn` FROM `g5_shop_order` WHERE `stoId` LIKE '%".$list[$i]['stoId']."%'";
                            $result_stock = sql_fetch($sql_stock);
                            $stock_insert="1";
                            if($result_stock['od_stock_insert_yn']=="Y"){
                                $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                                $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
                                $stock_insert ="2";
                            }else{
                                if($_GET['prodSupYn'] == "N" ){
                                    $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                                    $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
                                }else{
                                    $style_prodSupYn='style="border-color: #0000;background-color: #0000; cursor :default;"';
                                    $prodBarNumCntBtn_2="";
                                }
                            }
                            ?>
                        <li class="list cb">
                             <!--pc용-->
                            <span class="num"><?=$number?></span>
                            <span class="product m_off"><?=$list[$i]['prodNm']?> 
                            <?php 
                                if($list[$i]['prodColor']||$list[$i]['prodSize']){ 
                                    $name= $list[$i]['prodNm'].'('.$list[$i]['prodColor'].$div.$list[$i]['prodSize'].')'; 
                                }else{ 
                                    $name=$list[$i]['prodNm'];
                                } 
                                echo $name;
                            ?>
                        </span>
                            <span class="pro-num m_off <?=$prodBarNumCntBtn_2;?>"  data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                            <span class="name m_off"><a href="<?=G5_SHOP_URL?>/my.recipient.update.php?id=<?=$list[$i]['penId']?>"><?=$list[$i]['penNm']?></a></span>
                            <?php
                                //날짜 변환
                                $date1=$list[$i]['modifyDtm'];
                                $date2=date("Y-m-d H:i", strtotime($date1));
                            ?>
                            <span class="date m_off"><?=$date2?></span>
                            <!--mobile용-->
                            <div class="list-m">
                                <div class="info-m">
                                    <span class="product"><?=$list[$i]['prodNm']?><?=$name;?></span>
                                    <span class="pro-num <?=$prodBarNumCntBtn_2;?>"  data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                                </div>
                                <div class="info-m">
                                    <span class="name"><a href="<?=G5_SHOP_URL?>/my.recipient.update.php?id=<?=$list[$i]['penId']?>"><?=$list[$i]['penNm']?></a></span>
                                    <span class="date"><?=$date2?></span>
                                </div>
                            </div>
                            <span class="check">
                                <a href="<?=$list[$i]['eformUrl']?>">확인</a>
                            </span>
                        </li>
						<?php } ?>
                        </div>
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
    </section>
<!-- 바코드 클릭 -->
<style>
    .listPopupBoxWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
    .listPopupBoxWrap > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
    .listPopupBoxWrap iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

    @media (max-width : 750px){
        .listPopupBoxWrap iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
    }
</style>
<script type="text/javascript">
    $(function(){
        //바코드 클릭시 팝업
        $(document).on("click", ".prodBarNumCntBtn_2", function(e){
            e.preventDefault();
            var stoId = $(this).attr("data-stoId");
            var name = encodeURIComponent($(this).attr("data-name"));
            $("#popupProdBarNumInfoBox > div").append("<iframe src='/adm/shop_admin/popup.prodBarNum.form_5.php?stoId="+ stoId + "&name="+name+"'>");
            $("#popupProdBarNumInfoBox iframe").load(function(){
                $("#popupProdBarNumInfoBox").show();
            });
        });

        $(".listPopupBoxWrap").hide();
        $(".listPopupBoxWrap").css("opacity", 1);
    })
</script>
<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap">
    <div>
    </div>
</div>
<!-- 바코드 클릭 -->



    <script>
    //날짜 변환함수
    function date_change(str) {
        var y = str.substr(0, 4);
        var m = str.substr(4, 2);
        var d = str.substr(6, 2);
        return new Date(y,m-1,d);
    }

    //페이징 처리1
    function page_load(page_n) {
        page_n = parseInt(page_n);
        $.ajax({
            url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>",
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
            url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>",
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

    function wrapWindowByMask(){
        //화면의 높이와 너비를 구한다.
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();
        //마스크의 높이와 너비를 화면 것으로 만들어 전체 화면을 채운다.
        $('#mask').css({'width':maskWidth,'height':maskHeight});
        // $('html, body').css({'height':'100%','min-height':'100%','overflow':'hidden','touch-action':'none'});
        //마스크의 투명도 처리
        $('#mask').fadeTo("slow",0.8);
    }
    $("#thisPopupCloseBtn").click(function(e){
		alert('z');
    });
    function del_stoId(stoId){
        var prods={};
        prods['stoId'] = [stoId];
        if (confirm("정말 삭제하시겠습니까??") == true){


             var sendData = {
                stoId: [stoId]
            }

            console.log(sendData);
            $.ajax({
                url : "./ajax.stock.delete.php",
                type : "POST",
                async : false,
                data : sendData,
                success : function(result){
                    result = JSON.parse(result);
                    if(result.errorYN == "Y"){
                        alert(result.message);
                    } else {
                        alert('삭제되었습니다.');
                        window.location.reload();
                    }
                }
            });
        }
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
