<?php
include_once('./_common.php');
define('_INVENTORY_', true);

// 비회원인 경우
if (!$is_member) {
  goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

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

} else {
  if ( THEMA_KEY == 'partner') {
    if (!($it['ca_use'] && $it['it_use_partner'])) {
      alert('판매가능한 상품이 아닙니다.');
    }
  }else{
    // if (!($it['ca_use'] && $it['it_use'])) { // 230503 판매 불가 제품이더라고 재고 상세 페이지는 확인 할 수 있도록 변경
    if (!$it['ca_use']) {
      alert('판매가능한 상품이 아닙니다.');
    }
  }

  $it['pt_explan'] = $it['pt_mobile_explan'] = '';
}

// 보안서버경로
if (G5_HTTPS_DOMAIN)
  $action_url = G5_HTTPS_DOMAIN.'/'.G5_SHOP_DIR.'/simple_eform.php';
else
  $action_url = './simple_eform.php';

// 상품품절체크
if(G5_SOLDOUT_CHECK)
  $is_soldout = is_soldout($it['it_id']);

// 주문가능체크
$is_orderable = true;
if ( THEMA_KEY == 'partner') {
  if(!$it['it_use_partner'] || $it['it_tel_inq'] || $is_soldout) {
    $is_orderable = false;
  }
} else {
  // if(!$it['it_use'] || $it['it_tel_inq'] || $is_soldout) { // 230503 판매 불가 제품이더라고 재고 상세 페이지는 확인 할 수 있도록 변경
  if($it['it_tel_inq'] || $is_soldout) {
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

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

// Page ID
$pid = ($pid) ? $pid : 'inquiry';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');

$skin_row = array();
$skin_row = apms_rows('order_'.MOBILE_.'skin, order_'.MOBILE_.'set');
$skin_name = $skin_row['order_'.MOBILE_.'skin'];
$order_skin_path = G5_SKIN_PATH.'/apms/order/'.$skin_name;
$order_skin_url = G5_SKIN_URL.'/apms/order/'.$skin_name;

// 설정값 불러오기
@include_once($order_skin_path.'/config.skin.php');

$g5['title'] = '판매재고상세';
include_once('./_head.php');

$sql = 'SELECT * FROM `g5_shop_item` WHERE `it_id`="'.$_GET['prodId'].'"';
$row = sql_fetch($sql);
?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css?v=20210826">
<section id="stock" class="wrap" >
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
		<?php if($it['prodSupYn'] == "N"){?>
		alert("비유통 상품은 신규상품주문이 제한되어 있습니다.");
	<?php }else{?>
      document.getElementById('popup01').style.display = 'block';
	<?php }?>
    };
    function popup01_hide(){
      document.getElementById('popup01').style.display = 'none'
    };
    </script>

    <div class="popup01 popup2" id="popup01">
      <div class="p-inner">
        <h2>상품 수량 및 옵션 설정</h2>
        <button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt="" onclick="popup01_hide()"></button>
        <?php include_once($item_skin_file);?>
      </div>
    </div>


    <div class="inner">
      <div class="row">
        <div class="list-more m_off"><a href="<?=G5_SHOP_URL?>/sales_Inventory.php?&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>">목록</a></div>
        <!--<div class="list-more m_off"><a href="#" id="btn_multi_submit">수급자선택</a></div>-->
      </div>
      <div class="table-wrap">
        <div class="tit_area" style="height:45px;">
          <h2 style="width:150px;float:left;margin-top:15px;font-weight: 500;">보유 재고</h2>

          <form action="">
            <input type="hidden" name="prodId" value="<?=$_GET['prodId']?>">
            <input type="hidden" name="page" value="<?=$_GET['page']?>">
            <input type="hidden" name="searchtype" value="<?=$_GET['searchtype']?>">
            <input type="hidden" name="searchtypeText" value="<?=$_GET['searchtypeText']?>">
            <input type="hidden" name="prodSupYn" value="<?=$_GET['prodSupYn']?>">
            <div class="search-box">
              <select name="soption">
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
            <span class="num">
              <!--<label for="chk_stock_all">
                No.
                <input type="checkbox" name="chk_stock_all" id="chk_stock_all" value="1" style="margin-bottom: 8px;">
              </label>-->
              No.
            </span>
            <span class="product" style="width: 43%;">상품(옵션)</span>
            <span class="pro-num">바코드</span>
            <span class="date">입고일</span>
            <!--<span class="order">판매</span>-->
            <span class="del"></span>
          </li>
          <?php
          $sql_list = [];
          $sql = "SELECT sum(ct_qty) as cnt FROM g5_shop_cart
              WHERE it_id = '{$_GET['prodId']}' AND mb_id = '{$member['mb_id']}' AND od_del_yn = 'N'
              AND (ct_status = '주문' OR ct_status = '입금' OR ct_status = '준비' OR ct_status = '출고준비');";
          $sql_result = sql_fetch($sql);
          if ($sql_result['cnt'] > 0) {
            // $option = str_replace("색상:", "", $row['ct_option']);
            // $option = str_replace("사이즈:", "", $option);
            // $option = str_replace(" ", "", $option);
            $data = array(
              'prodColor' => $option,
              'prodSize' => '',
              'prodNm' => $row['it_name'],
              'stoId' => '',
              'prodBarNum' => '',
              'regDtm' => '배송중 : ' . $sql_result['cnt'],
              'isShippingCnt' => 'Y'
            );
            $sql_list[] = $data;
          }
          //판매재고 리스트
          $sendData = [];
          $prodsSendData = [];
          $sendData["usrId"] = $member["mb_id"];
          $sendData["entId"] = $member["mb_entId"];
          $sendData["prodId"] = $_GET['prodId'];
          $sendData["pageNum"] = ($_GET["page2"]) ? $_GET["page2"] : 1;
          $sendLength = 5;
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
          curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_SELECT_DETAIL_LIST);
          curl_setopt($oCurl, CURLOPT_POST, 1);
          curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
          $res = curl_exec($oCurl);
          $res = json_decode($res, true);
          curl_close($oCurl);
		  //print_r($res);
          $list = [];
          if($res["data"]){
            $list = $res["data"];
          }

          # 페이징
          $totalCnt = $res["total"];
          // $totalCnt = count($list);
          $pageNum = $sendData["pageNum"]; # 페이지 번호
          if ($pageNum == 1) {
            $list = array_merge($sql_list, $list);
          }
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
          <?php if(!$list) { ?>
          <li style="text-align:center" >
            자료가 없습니다.
          </li>
          <?php } ?>
          <div id="list_box1">
            <?php for($i=0;$i<count($list);$i++) {
            $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
            if ($pageNum == 1 && count($sql_list) > 0) {
              $number += 1;
            }
            if($list[$i]['prodColor']&&$list[$i]['prodSize']){ $div="/";} else { $div=""; }

            //유통 / 비유통 구분
            $sql_stock ="SELECT `od_id`, `od_stock_insert_yn` FROM `g5_shop_order` WHERE `stoId` LIKE '%".$list[$i]['stoId']."%' order by od_id desc limit 1";
            $result_stock = sql_fetch($sql_stock);
            $stock_insert="1";
            if($result_stock['od_stock_insert_yn']=="Y"){
              $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
              $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
              $stock_insert ="2";
            } else {
              if($_GET['prodSupYn'] == "N" ) {
                $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
                $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
              } else {
                $style_prodSupYn='style="border-color: #0000;background-color: #0000; cursor :default;"';
                $prodBarNumCntBtn_2="";
              }
            }
            ?>
            <!--반복-->
            <li class="list cb">
              <!--pc용-->
              <span class="num">
                <!--<label for="chk_stock_<?=$number?>">
                  <?=$number?>
                  <input
                    data-color="<?=$list[$i]['prodColor']?>"
                    data-size="<?=$list[$i]['prodSize']?>"
                    data-options="<?=$list[$i]['prodOption']?>"
                    data-barcode="<?=$list[$i]['prodBarNum']?>"
                    type="checkbox" name="chk_stock_<?=$number?>" id="chk_stock_<?=$number?>" class="chk_stock m_off" style="margin-bottom:8px;
                  ">
                </label>-->
                <?=$number?>
              </span>
              <span class="product m_off" style="width: 43%;">
                <?php 
                if($list[$i]['prodColor']||$list[$i]['prodSize']) { 
                  $name = $list[$i]['prodNm'].'('.$list[$i]['prodColor'].$div.$list[$i]['prodSize'].')'; 
                } else { 
                  $name = $list[$i]['prodNm'];
                } 
                echo $name;
                ?>
              </span>
              <?php if ($list[$i]['isShippingCnt'] !== 'Y') { ?>
                <span class="pro-num m_off <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
              <?php } else { ?>
                <span class="pro-num m_off">미등록</span>
              <?php } ?>
              <?php
                //날짜 변환
                $date1=$list[$i]['regDtm'];
                if ($list[$i]['isShippingCnt'] !== 'Y') {
                  $date2=date("Y-m-d H:i", strtotime($date1));  
              ?>
                <span class="date m_off"><?=$date2?></span>
              <?php } else { ?>
                <span class="date m_off" style="color:#f08606;"><?=$date1?></span>
              <?php } ?>
              <!--<span class="order m_off">
                <a href="javascript:;" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodOption']?>','<?=$list[$i]['prodBarNum']?>')">수급자선택</a>
              </span>-->
              <?php if ($list[$i]['isShippingCnt'] !== 'Y') { ?>
                <span class="del m_off">
                  <div class="state-btn2" onclick="open_list(this);">
                    <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                    <ul class="modalDialog">
                      <li class="p-btn01"><a href="javascript:;" onclick="del_stoId('<?=$list[$i]['stoId']?>')">삭제</a></li>
                      <li class="p-btn01"><a href="javascript:;" onclick="open_sell_popup(this)">판매완료처리</a></li>
                    </ul>
                  </div>
                </span> 
              <?php } ?>
              
              <!--mobile용-->
              <div class="list-m">
                <div class="info-m">
                  <span class="product">
                    <!-- <?=$list[$i]['prodNm']?> -->
                    <?=$name?>
                  </span>
                  <?php if ($list[$i]['isShippingCnt'] !== 'Y') { ?>
                    <span class="pro-num <?=$prodBarNumCntBtn_2?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                  <?php } else { ?>
                    <span class="pro-num m_off">미등록</span>
                  <?php } ?>
                </div>
                <div class="info-m">
                  <?php if ($list[$i]['isShippingCnt'] !== 'Y') { ?>
                    <span class="date"><?=$date2?></span>
                  <?php } else { ?>
                    <span class="date" style="color:#f08606;"><?=$date1?></span>
                  <?php } ?>                  
                  <!--<span class="order">
                    <a href="javascript:;" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodOption']?>','<?=$list[$i]['prodBarNum']?>')" >수급자선택</a>
                  </span>-->
                  <?php if ($list[$i]['isShippingCnt'] !== 'Y') { ?>
                    <span class="order2">
                      <a href="javascript:;" onclick="del_stoId('<?=$list[$i]['stoId']?>')">삭제</a>
                    </span>
                  <?php } ?>
                </div>
              </div>

              <!-- 판매완료처리 -->
              <div class="popup01 popup_sell">
                <div class="p-inner">
                  <h2>판매완료처리</h2>
                  <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                  <div class="rent_wrap">
                    등록 수급자 선택 후 처리
                    <button type="button" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodOption']?>','<?=$list[$i]['prodBarNum']?>')">확인</button>
                  </div>
                  <div class="sell_desc">
                    수급자 선택없이 판매완료 처리
                  </div>
                  <form name="form_sell" id="form_sell<?=$i?>" class="form_sell" role="form" onSubmit="return sell_complete('<?=$i?>')">
                    <input type="hidden" name="stoId" value="<?=$list[$i]['stoId']?>">
                    <input type="hidden" name="prodBarNum" value="<?=$list[$i]['prodBarNum']?>">
                    <ul style="padding: 20px 0;">
                      <li>
                        <b>수급자명</b>
                        <div class="input-box">
                          <input type="text" name="penNm">
                          <button type="button" onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                    </ul>
                    <label class="label_confirm">
                      <input type="checkbox" name="chk_confirm" class=".chk_confirm">
                      확인함
                    </label>
                    <div class="sell_desc">
                      직접 판매완료 처리하는 경우 등록된 수급자의<br>
                      청구관리 및 계약정보에 반영되지 않습니다.
                    </div>
                    <div class="popup-btn">
                      <button type="submit">확인</button>
                      <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                    </div>
                  </form>
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
          if($_GET['soption']=="1") {
            $sendData["prodBarNum"]=$_GET['stx'];
          }
          if($_GET['soption']=="2") {
            $sendData["searchOption"] =$_GET['stx'];
          }
          $oCurl = curl_init();
          curl_setopt($oCurl, CURLOPT_PORT, 9901);
          curl_setopt($oCurl, CURLOPT_URL, EROUMCARE_API_STOCK_SELECT_DETAIL_LIST);
          curl_setopt($oCurl, CURLOPT_POST, 1);
          curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
          $res = curl_exec($oCurl);
          $res = json_decode($res, true);
          curl_close($oCurl);

          $list = [];
          if($res["data"]) {
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
          if ($b_end_page > $total_page) {
            $b_end_page = $total_page;
          }
          $total_block = ceil($total_page/$b_pageNum_listCnt);
          ?>
          <?php if(!$list) { ?>
          <li style="text-align:center" >
            자료가 없습니다.
          </li>
          <?php } ?>
          <div id="list_box2">
            <?php for($i=0;$i<count($list);$i++) {
              $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
              if($list[$i]['prodColor']&&$list[$i]['prodSize']){ $div="/"; }else{ $div=""; }
            ?>
            <?php
            //유통 / 비유통 구분
            $sql_stock ="SELECT `od_id`, `od_stock_insert_yn` FROM `g5_shop_order` WHERE `stoId` LIKE '%".$list[$i]['stoId']."%' order by od_id desc limit 1";
            $result_stock = sql_fetch($sql_stock);
            $stock_insert="1";
            if($result_stock['od_stock_insert_yn']=="Y") {
              $style_prodSupYn='style="border-color:#ddd;background-color: #fff;"';
              $prodBarNumCntBtn_2="prodBarNumCntBtn_2";
              $stock_insert ="2";
            } else {
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
              <span class="product m_off">
                <?php 
                if($list[$i]['prodColor']||$list[$i]['prodSize']) {
                  $name = $list[$i]['prodNm'].'('.$list[$i]['prodColor'].$div.$list[$i]['prodSize'].')'; 
                } else {
                  $name = $list[$i]['prodNm'];
                }
                echo $name;
                ?>
              </span>
              <span class="pro-num m_off" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
              <span class="name m_off">
                <?php
                 //날짜 변환
				  $date1=$list[$i]['modifyDtm'];
				  $date2=date("Y-m-d H:i", strtotime($date1));
				if(!$list[$i]['penId']) {
                  // 재고에 수급자 주문 정보가 없으면 공단자료업로드 DB에서 매칭된 수급자 정보를 찾아봄
                  $my_data_result = sql_fetch("
                    SELECT
                      sd_pen_ltm_num as ltm_num
                    FROM
                      stock_data_upload
                    WHERE
                      mb_id = '{$member['mb_id']}' and
                      sd_status = 1 and sd_gubun = '00' and
                      sd_it_code = '{$row['ProdPayCode']}' and sd_it_barcode = '{$list[$i]['prodBarNum']}'
                  ");
                  if($my_data_result['ltm_num']) {
                    $pen_result = api_post_call(EROUMCARE_API_RECIPIENT_SELECTLIST, array(
                      'usrId' => $member['mb_id'],
                      'entId' => $member['mb_entId'],
                      'penLtmNum' => $my_data_result['ltm_num']
                    ));
                    if($pen_result['errorYN'] == 'N') {
                      $pen_result = $pen_result['data'] ? $pen_result['data'][0] : null;
                      if($pen_result) {
                        $list[$i]['penId'] = $pen_result['penId'];
                        $list[$i]['penNm'] = $pen_result['penNm'];
                      }
                    }
                  }else{//추가
					$sql = "SELECT a.penId,a.penNm,HEX(a.dc_id) AS UUID,dc_sign_send_datetime FROM `eform_document` AS a INNER JOIN `eform_document_item` AS b ON a.dc_id = b.dc_id WHERE b.it_barcode='".$list[$i]['prodBarNum']."' 
					AND a.dc_status='3' and b.it_code='".$list[$i]['prodPayCode']."'";
					$rows2 = sql_fetch($sql);
					$list[$i]['penId'] = $rows2['penId'];
                    $list[$i]['penNm'] = $rows2['penNm'];
				  }
                }else{//추가
					$rows2 = array();
					$sql = "SELECT a.penId,a.penNm,HEX(a.dc_id) AS UUID,dc_sign_send_datetime FROM `eform_document` AS a INNER JOIN `eform_document_item` AS b ON a.dc_id = b.dc_id WHERE b.it_barcode='".$list[$i]['prodBarNum']."'
					AND a.dc_status='3' AND a.penId !='' and b.it_code='".$list[$i]['prodPayCode']."' 
					AND LEFT(a.dc_sign_datetime,10) = '".substr($date2,0,10)."' ORDER BY a.dc_sign_datetime DESC LIMIT 1";
					$rows2 = sql_fetch($sql);
					$list[$i]['penId'] = $rows2['penId'];
                    $list[$i]['penNm'] = $rows2['penNm'];
				}

                if($list[$i]['penId']) {
                  echo '<a href="'.G5_SHOP_URL.'/my_recipient_update.php?id='.$list[$i]['penId'].'">'.$list[$i]['penNm'].'</a>';
				  //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================
					//echo '<a href="javascript:swal(\'사용 제한\',\'수급자 조회조건 개선으로 간편조회 및\n일부 서비스가 일시 중단되었습니다.\n서비스 재개는 추후 공지를 통해 안내드리겠습니다.\',\'error\');false;">'.$list[$i]['penNm'].'</a>';
					//======================================================================================================= 
                } else {
                  // 직접 판매완료처리한 상품인지 조회
                  $custom_order_result = sql_fetch(" select * from stock_custom_order where sc_stoId = '{$list[$i]['stoId']}' ");
                  echo $custom_order_result['sc_penNm'] ?: '';
                }
                ?>
              </span>

              <span class="date m_off"><?=$date2?></span>
              <!--mobile용-->
              <div class="list-m">
                <div class="info-m">
                  <span class="product"><?=$list[$i]['prodNm']?><?=$name;?></span>
                  <span class="pro-num"  data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                </div>
                <div class="info-m">
                  <span class="name">
				  <a href="<?=G5_SHOP_URL?>/my_recipient_update.php?id=<?=$list[$i]['penId']?>"><?=$list[$i]['penNm']?></a>
				 <?php //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================
				//echo '<a href="javascript:swal(\'사용 제한\',\'수급자 조회조건 개선으로 간편조회 및\n일부 서비스가 일시 중단되었습니다.\n서비스 재개는 추후 공지를 통해 안내드리겠습니다.\',\'error\')false;">'.$list[$i]['penNm'].'</a>';
					//======================================================================================================= ?>
				  </span>
                  <span class="date"><?=$date2?></span>
                </div>
              </div>
              <span class="check">
                <?php /*if($result_stock['od_id']) { ?>
                <a href="<?=G5_SHOP_URL.'/eform/downloadEform.php?od_id='.$result_stock['od_id']?>">확인</a>
                <?php } */?>
				<?php if($rows2['UUID']) { 
				  if($rows2['dc_sign_send_datetime'] == "0000-00-00 00:00:00"){?>
                <a href="<?=G5_SHOP_URL.'/eform/downloadEform.php?dc_id='.$rows2['UUID']?>">확인</a>
                <?php }else{?>
					<a href="javascript:;" onclick="mds_download('<?=$rows2['UUID']?>','1')">확인</a>  
				 <?php }
			  }?>
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

<!-- 수급자신청 -->
<div id="order_recipientBox">
  <div>
    <iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php?ca_id=<?=get_search_string($ca_id)?>" style='z-index:9999' ></iframe>
  </div>
</div>
<!-- 수급자 선택시 변경되어 넘어갈 값 -->
<form action="<?php echo $action_url; ?>"name="fitem" method="post" id="recipient_info" class="form item-form">
<input type="hidden" name="sales_inventory" value="1">                                              <!-- 판매완료처리 -->
<input type="hidden" name="sw_direct" value="1">                                              <!-- 바로가기 -->
  <input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>" id="it_id_r">                   <!-- 상품아이디 -->
  <input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0" it="io_type_r">            <!-- 옵션타입 -->
  <input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="" id="io_id_r">                 <!-- 옵션 값 -->
  <input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="" id="io_value_r">           <!-- 옵션 명 -->
  <input type="hidden" class="io_price" value="0" id="io_price_r">                                  <!-- 가격 -->
  <input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>" id="io_stock_r"><!-- 재고 -->
  <input type="hidden" name="ct_qty[<?php echo $it_id; ?>][]" value="1"id="ct_qty_r">               <!-- 수량 -->
  <input type="hidden" name="barcode_r" value="1" id="barcode_r">                                       <!-- 바코드 -->
  <input type="hidden" name="penId_r" value="1" id="penId_r">                                           <!-- penId -->
  <input type="hidden" name="recipient_info" value="1">                            <!-- 구분 -->
  <input type="hidden" name="it_msg1[]" value="<?php echo $it['pt_msg1']; ?>">
  <input type="hidden" name="it_msg2[]" value="<?php echo $it['pt_msg2']; ?>">
  <input type="hidden" name="it_msg3[]" value="<?php echo $it['pt_msg3']; ?>">
</form>
<!-- 바코드 클릭 -->
<div id="popupProdBarNumInfoBox" class="listPopupBoxWrap">
  <div></div>
</div>
<style>
#order_recipientBox { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#order_recipientBox > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#order_recipientBox iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }
@media (max-width : 750px) {
  #order_recipientBox iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
#ui-datepicker-div { z-index: 999999 !important; }
.state-btn1:hover{ color: #fff; }

.listPopupBoxWrap { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
.listPopupBoxWrap > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
.listPopupBoxWrap iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px) {
  .listPopupBoxWrap iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
</style>
<script>
var is_multi_submit = false;
var cart_form = null;

function CartForm() {
  this.data = [];
  this.append('sales_inventory', '1');
  this.append('sw_direct', '1');
  this.append('it_id[]', '<?=get_text($it_id)?>');
  this.append('io_price', '0');
  this.append('io_stock', '<?=get_text($it['it_stock_qty'])?>');
  this.append('it_msg1[]', '<?=get_text($it['pt_msg1'])?>');
  this.append('it_msg2[]', '<?=get_text($it['pt_msg2'])?>');
  this.append('it_msg3[]', '<?=get_text($it['pt_msg3'])?>');
}
CartForm.prototype.append = function append(key, value) {
  this.data.push({
    key: key,
    value: value
  });
}
CartForm.prototype.addOption = function addOption(io_id, io_value) {
  this.append('io_type[<?=get_text($it_id)?>][]', '0');
  this.append('io_id[<?=get_text($it_id)?>][]', io_id);
  this.append('io_value[<?=get_text($it_id)?>][]', io_value);
  this.append('ct_qty[<?=get_text($it_id)?>][]', '1');
}
CartForm.prototype.submit = function submit() {
  var form = $(document.createElement("form"))
  .attr({"method": 'post', "action": '<?=$action_url?>'});

  for(var i = 0; i < this.data.length; i++) {
    var key = this.data[i].key;
    var val = this.data[i].value;
    $(document.createElement("input"))
    .attr({ "type": "hidden", "name": key, "value": val })
    .appendTo( form );
  }

  form.appendTo( document.body ).submit();
}

// 여러 상품 체크 주문 시
function multi_submit() {
  if($('.chk_stock:checked').length === 0)
    return alert('선택한 재고가 없습니다.');

  is_multi_submit = true;
  cart_form = new CartForm();

  var barcode_r = [];
  $.each($('.chk_stock:checked'), function(index, item) {
    var color = $(item).data('color');
    var size = $(item).data('size');
    var options = $(item).data('options');
    var barcode = $(item).data('barcode');
    barcode_r.push(barcode);

    var option_value = make_option_value(color, size, options);
    cart_form.addOption(option_value.io_id, option_value.io_value);
  });

  cart_form.append('barcode_r', barcode_r.join('|'));

  $('#order_recipientBox').show();
}

function make_option_value(prodColor, prodSize, prodOptions) {
  var io_id = [];
  var io_value = [];

  prodOptions = prodOptions.split('|');

  if(prodColor || prodSize || (prodOptions && prodOptions.length)) { // 옵션 값이 있으면
    var io_subjects = '<?=get_text($it['it_option_subject'])?>'.split(',');

    for(var i = 0; i < io_subjects.length; i++) {
      switch(io_subjects[i]) {
        case '색상':
          io_id.push(prodColor);
          io_value.push('색상:'+prodColor);
          break;
        case '사이즈':
          io_id.push(prodSize);
          io_value.push('사이즈:'+prodSize);
          break;
        case '':
          // do nothing
          break;
        default:
          var prodOption = prodOptions.shift();
          io_id.push(prodOption);
          io_value.push(io_subjects[i]+':'+prodOption);
      }
    }
  }

  io_id = io_id.join(String.fromCharCode(30));
  io_value = io_value.join(' / ');

  var res = {
    io_id: io_id,
    io_value: io_value
  }

  return res;
}

function popup_control(prodColor, prodSize, prodOptions, barcode_r) {
  //수급자 조회 관련 추가, 개발완료 시 삭제 필요====================================================================
		//swal('사용 제한','수급자 조회조건 개선으로 간편조회 및\n일부 서비스가 일시 중단되었습니다.\n서비스 재개는 추후 공지를 통해 안내드리겠습니다.','error')
		//false;

  
  is_multi_submit = false;
  $('#order_recipientBox').show();

  var option_value = make_option_value(prodColor, prodSize, prodOptions);

  document.getElementById('io_id_r').value = option_value.io_id;
  document.getElementById('io_value_r').value = option_value.io_value;
  document.getElementById('barcode_r').value = barcode_r;
  
  //=======================================================================================================
}

function selected_recipient(penId) {
  // console.log(penId);
  if(is_multi_submit && cart_form) {
    cart_form.append('penId_r', penId);
    return cart_form.submit();
  }

  document.getElementById('penId_r').value=penId;
  document.getElementById('recipient_info').submit();
}

//항목 펼치기
function open_list(e) {
  $(".modalDialog").removeClass('on');
  $(e).find('ul').toggleClass('on');
  $(e).parents('.list').siblings('.list').find('ul').removeClass('on');
}

// modal 다른곳 클릭하면 꺼지게
$('body').click(function(event) {
  if(!$(event.target).closest('.state-btn2').length && !$(event.target).is('.state-btn2')) {
    $(".modalDialog").removeClass('on');
  }
});

$(function() {

  $('#chk_stock_all').change(function() {
    var checked = $(this).prop('checked');
    $('.chk_stock').prop('checked', checked);
  });

  $('.chk_stock').change(function() {
    var checked = false;
    if($('.chk_stock').length === $('.chk_stock:checked').length)
      checked = true;
    $('#chk_stock_all').prop('checked', checked);
  });

  $('#btn_multi_submit').click(function(e) {
    e.preventDefault();
    multi_submit();
  });

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

  $("#order_recipientBox").hide();
  $("#order_recipientBox").css("opacity", 1);
  $(".listPopupBoxWrap").hide();
  $(".listPopupBoxWrap").css("opacity", 1);

});
</script>

<script>
//날짜 변환함수
function date_change(str) {
  var y = str.substr(0, 4);
  var m = str.substr(4, 2);
  var d = str.substr(6, 2);
  return new Date(y, m-1, d);
}

//페이징 처리1
function page_load(page_n) {
  page_n = parseInt(page_n);
  $.ajax({
    url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>&soption=<?=$_GET['soption']?>&stx=<?=$_GET['stx']?>",
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
    url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>&soption=<?=$_GET['soption']?>&stx=<?=$_GET['stx']?>",
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

$("#thisPopupCloseBtn").click(function(e) {
  alert('z');
});
function del_stoId(stoId) {
  var prods={};
  prods['stoId'] = [stoId];
  if (confirm("정말 삭제하시겠습니까??") == true) {

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

function click_x(this_click){
  $(this_click).closest("li").find("input").val("");
}

//닫기
function close_popup(e){
  $(e).parents('.popup01').stop().hide();
}

function open_sell_popup(e, stoId, prodBarNum) {
  var $popup = $(e).parents('.list').find('.popup_sell').stop();
  $popup.show();
}
/*
$(function() {
  $('.form_sell').on('submit', function(e) {
    e.preventDefault();
    var confirmed = $(this).find('input[name=chk_confirm]').prop('checked');
    if(!confirmed) {
      return alert('판매완료처리 안내사항을 확인해주세요.');
    }
    var stoId = $(this).find('input[name=stoId]').val();
    var prodBarNum = $(this).find('input[name=prodBarNum]').val();
    var penNm = $(this).find('input[name=penNm]').val();

    sell_stoId(stoId, prodBarNum, penNm);
  });
});
*/
function sell_complete(a){
    var confirmed = $("#form_sell"+a).find('input[name=chk_confirm]').prop('checked');
    if(!confirmed) {
      alert('판매완료처리 안내사항을 확인해주세요.');
	  return false;
    }
    var stoId = $("#form_sell"+a).find('input[name=stoId]').val();
    var prodBarNum = $("#form_sell"+a).find('input[name=prodBarNum]').val();
    var penNm = $("#form_sell"+a).find('input[name=penNm]').val();

    sell_stoId(stoId, prodBarNum, penNm);
	return false;
}
function sell_stoId(stoId, prodBarNum, penNm) {
  if(!confirm("판매완료처리 후 다시 재고등록으로 변경은 불가능합니다.\n완료처리하시겠습니까?"))
    return;

  $.post('ajax.stock.sell.php', {
    prodId: '<?=$it_id?>',
    stoId: stoId,
    prodBarNum: prodBarNum,
    penNm: penNm
  }, 'json')
  .done(function(result) {
    alert('완료되었습니다.');
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
}

// 계약서,감사추적인증서 보기 
	function mds_download(dc_id,gubun) {//1:계약서,2:감사추적인증서
 		$.post('ajax.eform_mds_api.php', {
			dc_id:dc_id,
			gubun:gubun,
			div:'view_doc'
		})
		.done(function(data) {
			if(data.api_stat != "1"){
				loading_onoff('off');
				alert("API 통신 장애가 있습니다. 잠시 후 이용해 주세요.");
				return false;				
			}
			if(data.url != "url생성실패"){				
				loading_onoff('off');
				window.open(data.url, "PopupDoc", "width=1000,height=1000");
			}else{
				alert(data.url);//url 생성실패 알림
			}
		})
		.fail(function($xhr) {
		  var data = $xhr.responseJSON;
		  alert(data && data.message);
		});	
	}

	function loading_onoff(a){
		if(a == "on" ){
			$('body').css('overflow-y', 'hidden');
			$('#loading').show();
		}else{
			$('body').css('overflow-y', 'scroll');
			$('#loading').hide(); 
		}
	}
</script>
<?php
include_once('./_tail.php');
?>
