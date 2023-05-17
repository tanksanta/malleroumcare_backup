<?php
include_once('./_common.php');
define('_INVENTORY_', true);

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

$g5['title'] = '보유 급여상품 관리';

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
///----------------------------------설정값----------------------------------







//판매재고 토탈
$res = api_post_call(EROUMCARE_API_STOCK_LIST, array(
    'usrId' => $member["mb_id"],
    'entId' => $member["mb_entId"],
    'gubun' => '00',
));

//추가
$sale_total_count = 0; 
for($i=0; $i<count($res["data"]); $i++){
	$sale_total_count = $sale_total_count+$res["data"][$i]["quantity"];
}
//추가끝
$sales_Inventory_total=$res['total'];//판매재고 토탈

//대여재고 토탈
$res = api_post_call(EROUMCARE_API_STOCK_LIST, array(
    'usrId' => $member["mb_id"],
    'entId' => $member["mb_entId"],
    'gubun' => '01',
));
$sales_Inventory_total2=$res['total'];//대여재고 토탈

//---------------------토탈---------------------------------------



// $sql_item_id="SELECT * FROM `g5_shop_item` WHERE `ca_id` LIKE '20%'";
// $row_item_id = sql_query($sql_item_id);
// while ($row = sql_fetch_array($row_item_id))
// {
//     echo $row['it_id'].'<br>';
//     echo $row['ca_id'].'<br>';

// }

//판매재고 리스트
$send_length = (int)$send_length ?: 10;
$send_sort = $send_sort ?: "add";

$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["gubun"] = "00";
$sendData["pageNum"] = ($_GET["page"]) ? $_GET["page"] : 1;
$sendData["pageSize"] = $send_length;
$sendData["sort"] = $send_sort;

if($_GET['searchtype']){
    if($_GET['searchtype']=="1"){
        $sendData["prodNm"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";
    }else{
        //추가
		if($_GET["searchtypeText"] != ""){
			$sql = 'SELECT  `it_id` as prodId FROM `g5_shop_item` WHERE `ProdPayCode`="'.$_GET["searchtypeText"].'"';
			$result = sql_query($sql);
			$count = sql_num_rows($result);
			$row = sql_fetch($sql);
			$prodId = ($row['prodId'] != "")? $row['prodId'] : $_GET["searchtypeText"]; 
			$sendData["prodId"] = $prodId;
		}else{
			$prodId = "";
		}
		
		//추가끝
		//$sendData["prodId"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";		
    }
}
//추가
$list = [];
$totalCnt = 0;
if($count>1){
	//print_r($result);
	while($rows = sql_fetch_array($result)){
		//echo $rows['prodId'];
		$prodId = ($rows['prodId'] != "")? $rows['prodId'] : $_GET["searchtypeText"]; 
		$sendData["prodId"] = $prodId;
		$res = api_post_call(EROUMCARE_API_STOCK_LIST, $sendData);
		if($res["data"]){
			array_push($list,$res["data"]);
		}
		$totalCnt += $res["total"];
		//print_r($res["data"]);
	}
	
}else{
$res = api_post_call(EROUMCARE_API_STOCK_LIST, $sendData);


if($res["data"]){
    $list = $res["data"];
}
 //print_r($list);

# 페이징
$totalCnt = $res["total"];	
}

//추가끝
//$res = api_post_call(EROUMCARE_API_STOCK_LIST, $sendData);

//$list = [];
//if($res["data"]){
//    $list = $res["data"];
//}
//print_r($res);

# 페이징
//$totalCnt = $res["total"];
$pageNum = $sendData["pageNum"]; # 페이지 번호
$listCnt = $send_length; # 리스트 갯수 default 10

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
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
    <title>판매재고목록</title>
    <section id="stock" class="wrap stock-list">
        <div class="sub_section_tit">보유 급여상품 관리</div>
        <div class="r_btn_area">
            <a href="#" class="btn eroumcare_btn2" id="prod_control_list" onclick="popCtrlList()" title="제품관리대장">제품관리대장</a>
            <a href="#" class="btn eroumcare_btn2" id="excel_download" title="엑셀다운로드">엑셀다운로드</a>
            <a href="#" class="btn eroumcare_btn2 add_sales_inventory" title="품목추가">품목추가</a>
        </div>
        <ul class="stock-tab">
            <li class="active"><a href="<?=G5_SHOP_URL?>/sales_Inventory.php">판매상품재고<i class="num">(총 <?=$sales_Inventory_total?>종)</i></a></li>
            <li><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php">대여상품재고<i class="num">(총 <?=$sales_Inventory_total2?>종)</i></a></li>
        </ul>
        <div class="inner">
            <form action="" method="get" class="stock-form" name="stock_form" onsubmit="return stockFormSubmit()">
                <div class="search-box">
                    <select name="searchtype" id="">
                        <option value="1" <?=$_GET['searchtype'] == "1" ? 'selected' : '' ?> >상품명</option>
                        <option value="2" <?=$_GET['searchtype'] == "2" ? 'selected' : '' ?> >제품코드</option>
                    </select>
                    <div class="input-search">
                        <input name="searchtypeText" value="<?=$_GET["searchtypeText"]?>" type="text">
                        <button  type="submit"></button>
                    </div>
                </div>
                <div class="right-box">
                    <input type="checkbox" name="show_simple" <?php echo $show_simple?'checked':''; ?> value="1" id="show_simple">
                    <label for="show_simple">간략보기</label>
                    <select name="send_length" id="send_length">
                        <option value="10" <?php echo $send_length == '10' ? 'selected="selected"' : ''; ?>>10개씩 보기</option>
                        <option value="20" <?php echo $send_length == '20' ? 'selected="selected"' : ''; ?>>20개씩 보기</option>
                        <option value="30" <?php echo $send_length == '30' ? 'selected="selected"' : ''; ?>>30개씩 보기</option>
                        <option value="50" <?php echo $send_length == '50' ? 'selected="selected"' : ''; ?>>50개씩 보기</option>
                        <option value="100" <?php echo $send_length == '100' ? 'selected="selected"' : ''; ?>>100개씩 보기</option>
                    </select>

                    <select name="send_sort" id="send_sort">
                        <option value="add" <?php echo $send_sort == 'add' ? 'selected="selected"' : ''; ?>>최근구매순</option>
                        <option value="itemNm" <?php echo $send_sort == 'itemNm' ? 'selected="selected"' : ''; ?>>품목순</option>
                        <option value="prodNm" <?php echo $send_sort == 'prodNm' ? 'selected="selected"' : ''; ?>>품명순</option>
                        <option value="qty" <?php echo $send_sort == 'qty' ? 'selected="selected"' : ''; ?>>재고수량순</option>
                    </select>

                    <input type="hidden" value="00" name="gubun" />
<!--                    <a href="#" class="btn eroumcare_btn2 small" id="excel_download" style="border-radius: 0 !important;padding: 1px 7px;" title="엑셀다운로드">엑셀다운로드</a>-->
                </div>
            </form>
            <div class="table-wrap">
                <ul>
                    <li class="head cb">
                        <span class="num">No.</span>
                        <span class="product">상품정보</span>
                        <span class="pro-num">제품코드</span>
                        <span class="stock">수량(총 <?=number_format($sale_total_count)?>개)</span>
                        <span class="order">판매완료</span>
                        <span class="price">급여가</span>
                    </li>
                    <?php if(!$list){ ?>
                            <li style="text-align:center" >
                                자료가 없습니다.
                            </li>
                        <?php } ?>
                    <?php for($i=0; $i<count($list); $i++){
						//추가
						$prodId = ($count>1)? $list[$i][0]['prodId']: $list[$i]['prodId'];//$list[$i]['prodId']  대체
						$prodSupYn = ($count>1)? $list[$i][0]['prodSupYn']: $list[$i]['prodSupYn'];//$list[$i]['prodSupYn']  대체
						$itemNm = ($count>1)? $list[$i][0]['itemNm']: $list[$i]['itemNm'];//$list[$i]['itemNm']  대체
						$prodNm = ($count>1)? $list[$i][0]['prodNm']: $list[$i]['prodNm'];//$list[$i]['prodNm']  대체
						$prodPayCode = ($count>1)? $list[$i][0]['prodPayCode']: $list[$i]['prodPayCode'];//$list[$i]['prodPayCode']  대체
						$quantity = ($count>1)? $list[$i][0]['quantity']: $list[$i]['quantity'];//$list[$i]['quantity']  대체
						$orderQuantity = ($count>1)? $list[$i][0]['orderQuantity']: $list[$i]['orderQuantity'];//$list[$i]['orderQuantity']  대체
						$it_cust_price = ($count>1)? $list[$i][0]['it_cust_price']: $list[$i]['it_cust_price'];//$list[$i]['it_cust_price']  대체
						//추가 끝
                        $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
                        $sql = 'SELECT  `it_taxInfo`, `it_img1`, `it_cust_price` FROM `g5_shop_item` WHERE `it_id`="'.$prodId.'"';
						$row = sql_fetch($sql);
                        $sql2 = "SELECT sum(ct_qty) as cnt FROM g5_shop_cart 
                            WHERE it_id = '{$prodId}' AND mb_id = '{$member['mb_id']}' 
                            AND (ct_status = '주문' OR ct_status = '입금' OR ct_status = '준비' OR ct_status = '출고준비');";
                        $row2 = sql_fetch($sql2);
						//추가
						if(($quantity+$orderQuantity)>0){
						//추가 끝
                    ?>
                    <!--반복-->
                    <a href="<?=G5_SHOP_URL?>/sales_Inventory_datail.php?prodId=<?=$prodId?>&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>&prodSupYn=<?=$prodSupYn?>">
                    <li class="list cb">
                        <span class="num"><?=$number?></span><!-- 넘버링 -->
                        <span class="product" >
                            <div class="info">
                                <?php if (!$show_simple) { ?>
                                <div class="img"  style="min-width:90px; min-height:90px;">
                                    <img src="/data/item/<?=$row["it_img1"]?>" alt="">
                                </div>
                                <?php } ?>
                                <div class="text">
                                    <div class="info-01">
                                    <?php if ($show_simple) { ?>
                                        <i>[<?=$itemNm?>] <b><?=$prodNm?></b></i><!--품목명 -->
                                    <?php } else { ?>
                                        <i>[<?=$itemNm?>]</i><!--품목명 -->
                                        <p><?=$prodNm?></p><!-- 제품명 -->
                                        <p><?=$prodSupYn == "Y" ? '유통' : '비유통' ?>/<?=$row["it_taxInfo"]?></p><!--유통/과세 -->
                                    <?php } ?>
                                    </div>
                                    <!--mobile 용-->
                                    <div class="info-02">
                                        <span class="pro-num"><?=$prodPayCode?></span><!--상품아이디-->
                                        <span class="stock">
                                            <?php if ($row2['cnt'] > 0) { ?>
                                                <p style="font-weight:normal; color:#f08606;">배송중 : <?=$row2['cnt']?>개</p><br>
                                            <?php } ?>
                                            <?=$quantity?>개 남음
                                        </span><!--주문재고수량-->
                                        <span class="order">판매완료 <?=$orderQuantity?>개</span><!--판매완료 개수-->
                                        <span class="price"><?=number_format($it_cust_price);?>원</span><!--급여가-->
                                    </div>
                                </div>
                            </div>
                        </span>
                        <!--pc 용-->
                        <span class="pro-num m_off"><?=$prodPayCode?></span>
                        <span class="stock m_off">
                            <?=$quantity?>개
                            <?php if ($row2['cnt'] > 0) { ?>
                                <p style="font-weight:normal; color:#f08606;">배송중 : <?=$row2['cnt']?>개</p>
                            <?php } ?>
                        </span><!--주문재고수량-->
                        <span class="order m_off"><?=$orderQuantity?>개</span><!--판매완료 개수-->
                        <span class="price m_off"><?=number_format($it_cust_price);?>원</span><!--급여가-->
                    </li>
                    </a>
                    <?php //추가 
						}
						//추가끝
					}?>
                </ul>
            </div>
            <div class="pg-wrap">
                <div>
                    <?php if($pageNum >$b_pageNum_listCnt){ ?><a href="?searchtype=<?php echo $searchtype; ?>&searchtypeText=<?php echo $searchtypeText; ?>&show_simple=<?php echo $show_simple; ?>&gubun=<?php echo $gubun; ?>&send_length=<?php echo $send_length; ?>&send_sort=<?php echo $send_sort; ?>&page=1"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
                    <?php if($block > 1){ ?><a href="?searchtype=<?php echo $searchtype; ?>&searchtypeText=<?php echo $searchtypeText; ?>&show_simple=<?php echo $show_simple; ?>&gubun=<?php echo $gubun; ?>&send_length=<?php echo $send_length; ?>&send_sort=<?php echo $send_sort; ?>&page=<?=($b_start_page-1)?>"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
                    <?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="?searchtype=<?php echo $searchtype; ?>&searchtypeText=<?php echo $searchtypeText; ?>&show_simple=<?php echo $show_simple; ?>&gubun=<?php echo $gubun; ?>&send_length=<?php echo $send_length; ?>&send_sort=<?php echo $send_sort; ?>&page=<?=$j?>"><?=$j?></a><?php } ?>
                    <?php if($block < $total_block){ ?><a href="?searchtype=<?php echo $searchtype; ?>&searchtypeText=<?php echo $searchtypeText; ?>&show_simple=<?php echo $show_simple; ?>&gubun=<?php echo $gubun; ?>&send_length=<?php echo $send_length; ?>&send_sort=<?php echo $send_sort; ?>&page=<?=($b_end_page+1)?>"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
                    <?php if($block < $total_block){ ?><a href="?searchtype=<?php echo $searchtype; ?>&searchtypeText=<?php echo $searchtypeText; ?>&show_simple=<?php echo $show_simple; ?>&gubun=<?php echo $gubun; ?>&send_length=<?php echo $send_length; ?>&send_sort=<?php echo $send_sort; ?>&page=<?=$total_page?>"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
                </div>
            </div>
        </div>
    </section>

<div id="add_sales_inventory_popup">
    <div class="add_sales_inventory_popup_close">
        <i class="fa fa-times"></i>
    </div>
    <iframe name="iframe" id="add_sales_inventory_popup_iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<style>
#popup_order_add {
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index: 999;
  background-color: rgba(0, 0, 0, 0.6);
  display:none;
}
#popup_order_add > div {
  width: 30%;
  max-width: 80%;
  min-height: 400px;
  height: 40%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
#popup_order_add > div iframe {
  width:100%;
  height:100%;
  border: 0;
  background-color: #FFF;
}
</style>
<div id="popup_order_add">
  <div id="content">dd</div>
</div>
<script>
$(document).ready(function() {
    // 상품 추가
    $('#add_sales_inventory_popup').click(function(e) {
        $('#add_sales_inventory_popup').hide();
    });

    $('.add_sales_inventory').click(function(e) {
        e.preventDefault();

        var url = './pop.stock.item.add.php';

        var is_mobile = navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1;
        $('#add_sales_inventory_popup_iframe').attr('src', url);

        $('#add_sales_inventory_popup').show();
    });

    $('#excel_download').click(function() {
        var form = document['stock_form'];
        form.action = "./sales_inventory_excel.php";
        form.submit();
    });
    $('#show_simple, #send_length, #send_sort').change(function() {
        stockFormSubmit();
    });

});
function stockFormSubmit() {
    var form = document['stock_form'];
    form.action = "";
    form.submit();
}
function popCtrlList() {
    $("#popup_order_add > div").html("<iframe src='./pop.control.list.php'></iframe>");
    $("#popup_order_add iframe").load(function(){
        $("#popup_order_add").show();
        $('#hd').css('z-index', 3);
        $('#popup_order_add iframe').contents().find('.mb_id_flexdatalist').focus();
    });
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
