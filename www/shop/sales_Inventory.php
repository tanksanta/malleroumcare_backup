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
///----------------------------------설정값----------------------------------







//판매재고 토탈
$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["gubun"] = "00";

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectListForEnt");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);
$sales_Inventory_total=$res['total'];//대여재고 토탈

//대여재고 토탈
$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["gubun"] = "01";

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectListForEnt");
curl_setopt($oCurl, CURLOPT_POST, 1);
curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($oCurl);
$res = json_decode($res, true);
curl_close($oCurl);
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
$sendLength = 10;
$sendData = [];
$sendData["usrId"] = $member["mb_id"];
$sendData["entId"] = $member["mb_entId"];
$sendData["gubun"] = "00";
$sendData["pageNum"] = ($_GET["page"]) ? $_GET["page"] : 1;
$sendData["pageSize"] = $sendLength;

if($_GET['searchtype']){
    if($_GET['searchtype']=="1"){
        $sendData["prodNm"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";
    }else{
        $sendData["prodId"] = ($_GET["searchtypeText"]) ? $_GET["searchtypeText"] : "";
    }
}

$oCurl = curl_init();
curl_setopt($oCurl, CURLOPT_PORT, 9901);
curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/selectListForEnt");
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

?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
    <title>판매재고목록</title>
    <section id="stock" class="wrap stock-list">
        <h2>보유재고관리</h2>
        <ul class="stock-tab">
            <li class="active"><a href="<?=G5_SHOP_URL?>/sales_Inventory.php">판매재고<i class="num">(<?=$sales_Inventory_total?>)</i></a></li>
            <li><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php">대여재고<i class="num">(<?=$sales_Inventory_total2?>)</i></a></li>
        </ul>
        <div class="inner">
            <form action="">
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
            </form>
            <div class="table-wrap">
                <ul>
                    <li class="head cb">
                        <span class="num">No.</span>
                        <span class="product">상품정보</span>
                        <span class="pro-num">제품코드</span>
                        <span class="stock">재고</span>
                        <span class="order">판매완료</span>
                        <span class="price">급여가</span>
                    </li>
                    <?php if(!$list){ ?>
                            <li style="text-align:center" >
                                자료가 없습니다.
                            </li>
                        <?php } ?>
                    <?php for($i=0; $i<count($list); $i++){
                        $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
                        $sql = 'SELECT  `it_taxInfo`, `it_img1`, `it_cust_price` FROM `g5_shop_item` WHERE `it_id`="'.$list[$i]['prodId'].'"';
                        $row = sql_fetch($sql);
                    ?>
                    <!--반복-->
                    <a href="<?=G5_SHOP_URL?>/sales_Inventory_datail.php?prodId=<?=$list[$i]['prodId']?>&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>&prodSupYn=<?=$list[$i]['prodSupYn']?>">
                    <li class="list cb">
                        <span class="num"><?=$number?></span><!-- 넘버링 -->
                        <span class="product" >
                            <div class="info">
                                <div class="img"  style="min-width:90px; min-height:90px;">
                                    <img src="/data/item/<?=$row["it_img1"]?>" alt="">
                                </div>
                                <div class="text">
                                    <div class="info-01">
                                        <i>[<?=$list[$i]['itemNm']?>]</i><!--품목명 -->
                                        <p><?=$list[$i]['prodNm']?></p><!-- 제품명 -->
                                        <p><?=$list[$i]['prodSupYn'] == "Y" ? '유통' : '비유통' ?>/<?=$row["it_taxInfo"]?></p><!--유통/과세 -->
                                    </div>
                                    <!--mobile 용-->
                                    <div class="info-02">
                                        <span class="pro-num"><?=$list[$i]['prodPayCode']?></span><!--상품아이디-->
                                        <span class="stock"><?=$list[$i]['quantity']?>개 남음</span><!--주문재고수량-->
                                        <span class="order">판매완료: <?=$list[$i]['orderQuantity']?>개</span><!--판매완료 개수-->
                                        <span class="price"><?=number_format($row['it_cust_price']);?>원</span><!--급여가-->
                                    </div>
                                </div>
                            </div>
                        </span>
                        <!--pc 용-->
                        <span class="pro-num m_off"><?=$list[$i]['prodPayCode']?></span>
                        <span class="stock m_off"><?=$list[$i]['quantity']?>개</span><!--주문재고수량-->
                        <span class="order m_off"><?=$list[$i]['orderQuantity']?>개</span><!--판매완료 개수-->
                        <span class="price m_off"><?=number_format($row['it_cust_price']);?>원</span><!--급여가-->
                    </li>
                    </a>
                    <?php } ?>
                </ul>
            </div>
            <div class="pg-wrap">
                <div>
                    <?php if($pageNum >$b_pageNum_listCnt){ ?><a href="?page=1"><img src="<?=G5_IMG_URL?>/icon_04.png" alt=""></a><?php } ?>
                    <?php if($block > 1){ ?><a href="?page=<?=($b_start_page-1)?>"><img src="<?=G5_IMG_URL?>/icon_05.png" alt=""></a><?php } ?>
                    <?php for($j = $b_start_page; $j <=$b_end_page; $j++){ ?><a href="?page=<?=$j?>"><?=$j?></a><?php } ?>
                    <?php if($block < $total_block){ ?><a href="?page=<?=($b_end_page+1)?>"><img src="<?=G5_IMG_URL?>/icon_06.png" alt=""></a><?php } ?>
                    <?php if($block < $total_block){ ?><a href="?page=<?=$total_page?>"><img src="<?=G5_IMG_URL?>/icon_07.png" alt=""></a><?php } ?>
                </div>
            </div>
        </div>
    </section>

<?php
if($is_inquiry_sub) {
	if(!USE_G5_THEME) @include_once(THEMA_PATH.'/tail.sub.php');
	include_once(G5_PATH.'/tail.sub.php');
} else {
	include_once('./_tail.php');
}
?>
