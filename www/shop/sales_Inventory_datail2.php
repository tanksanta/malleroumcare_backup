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
for($i = 0; $row = sql_fetch_array($thisOptionQuery); $i++) {
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

$g5['title'] = '대여재고상세';
include_once('./_head.php');

// --------------------------------------------------------------------------------------------------------------------------------------------

$sql = 'SELECT * FROM `g5_shop_item` WHERE `it_id`="'.$_GET['prodId'].'"';
$row = sql_fetch($sql);

if(!$_GET['prodId']) alert('유효하지 않은 접근입니다.');

// 대여 내구연한: 판매가능기간 지난 재고 정리
expired_rental_item_clean($_GET['prodId']);
?>
<link rel="stylesheet" href="<?=G5_CSS_URL ?>/stock_page.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<section id="stock" class="wrap">
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
    <div class="popup01 popup2" id="popup01">
      <div class="p-inner">
        <h2>상품 옵션 설정</h2>
        <button class="cls-btn p-cls-btn" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt="" onclick="popup01_hide()"></button>
        <?php include_once($item_skin_file);?>
      </div>
    </div>
    <div class="inner">
      <div class="row">
        <div class="list-more m_off"><a href="<?=G5_SHOP_URL?>/sales_Inventory2.php?&page=<?=$_GET['page']?>&searchtype=<?=$_GET['searchtype']?>&searchtypeText=<?=$_GET['searchtypeText']?>">목록</a></div>
        <!--<div class="list-more m_off"><a href="#" id="btn_multi_submit">수급자선택</a></div>-->
      </div>
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
      if($_GET['soption']=="1") {
        $sendData["prodBarNum"] = $_GET['stx'];
      }
      if($_GET['soption']=="2") {
        $sendData["searchOption"] = $_GET['stx'];
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
      if ($b_end_page > $total_page){
        $b_end_page = $total_page;
      }
      $total_block = ceil($total_page/$b_pageNum_listCnt);
      ?>
      <div class="table-wrap">
        <p class="text01">대여기간 종료일이 1달 미만 제품입니다.</p>
        <h3>보유 재고</h3>

        <form action="" class="search-box">
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

        <ul>
          <li class="head cb">
            <span class="num">
              <!--<label for="chk_stock_all">
                No.
                <input type="checkbox" name="chk_stock_all" id="chk_stock_all" value="1" style="margin-bottom: 8px;">
              </label>-->
              No.
            </span>
            <span class="product">상품(옵션)</span>
            <span class="pro-num">바코드</span>
            <span class="date">입고일</span>
            <span class="state">상태</span>
            <span class="none"></span>
          </li>
          <?php if(!$list) { ?>
          <li style="text-align:center" >
              자료가 없습니다.
          </li>
          <?php } ?>
          <div id="list_box1">
            <?php for($i=0;$i<count($list);$i++) {
            $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i

            $bg="";//대여중 일때 클래스 넣기
            $rental_btn=''; //대여 버튼
            $rental_btn2=''; //대여 버튼
            $water="";//소독중 표시
			$ordLendStrDtm_date= "";//대여기간 시작
			$ordLendEndDtm_date= "";//대여기간 종료
			$result_stock = array();
			$rows2 = array();
            //유통 / 비유통 구분
            $sql_stock ="SELECT `od_id`, `od_stock_insert_yn` FROM `g5_shop_order` WHERE `stoId` LIKE '%".$list[$i]['stoId']."%' order by od_id desc limit 1";
			$result_stock = sql_fetch($sql_stock);
            $stock_insert="1";
            if($result_stock['od_stock_insert_yn']=="Y") {
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
			//대여 날짜 변환
            $rental_date="";
            if($list[$i]['ordLendStrDtm'] && $list[$i]['ordLendEndDtm']) {
              $ordLendStrDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendStrDtm']));
              $ordLendEndDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendEndDtm']));
            } else {
				$result = sql_fetch("SELECT * FROM g5_rental_log WHERE stoId = '{$list[$i]['stoId']}' and rental_log_division = '2' and ren_person not like '%종료%' order by strdate DESC limit 1");//로그로 확인
				if($result){
					$ordLendStrDtm_date = $result['strdate'];
					$ordLendEndDtm_date = $result['enddate'];
				}else{
				  // 대여기간정보가 없는 경우 대여완료처리 테이블 조회-수급자 지정 없이 대여완료 시
				  $custom_order_result = sql_fetch("SELECT * FROM stock_custom_order WHERE sc_stoId = '{$list[$i]['stoId']}' and sc_rent_state = 'rent' ");
				  if($custom_order_result['sc_sale_date']) {
					$ordLendStrDtm_date = $custom_order_result['sc_sale_date'];
					$ordLendEndDtm_date = $custom_order_result['sc_rent_date'];
				  }
				}
            }

			$date2 = $ordLendStrDtm_date."-".$ordLendEndDtm_date;
			$sql = "SELECT a.penId,a.penNm,HEX(a.dc_id) AS UUID FROM `eform_document` AS a INNER JOIN `eform_document_item` AS b ON a.dc_id = b.dc_id WHERE b.it_barcode='".$list[$i]['prodBarNum']."' AND a.dc_status='3' AND b.it_date='".$date2."' order by a.dc_datetime DESC limit 1";
				$rows2 = sql_fetch($sql);
            //상태 메뉴
            $state_menu_all="";
            if($rows2['UUID'] == "") {
				$state_menu1='<li><a class="state-btn4" onclick="open_retal_period(this)" href="javascript:;">대여기간 수정</a></li>';
				$state_menu2='';
			}else{
				$state_menu1='';
				$state_menu2='<li><a href="'.G5_SHOP_URL.'/eform/downloadEform.php?dc_id='.$rows2['UUID'].'">계약서 확인</a></li>';
			}
            //if($result_stock['od_id']){
			//	$state_menu2='<li><a href="'.G5_SHOP_URL.'/eform/downloadEform.php?od_id='.$result_stock['od_id'].'">계약서 확인</a></li>';
			//}else{

			//}
            $state_menu3='<li class="p-btn01"><a href="javascript:;" onclick="open_designate_disinfection(this)">소독신청</a></li>';
            $state_menu4='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'01\',\'변경되었습니다.\')" >대여 가능상태</a></li>';
            $state_menu5='<li class="p-btn02" onclick="open_designate_result(this)"><a href="javascript:;">소독확인 신청</a></li>';
            $state_menu6='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'09\',\'소독 취소되었습니다.\')">소독취소</a></li>';
            $state_menu7='<li><a href="javascript:;">소독확인중</a></li>';
            $state_menu8='<li><a href="javascript:;" onclick="retal_state_change2(\''.$list[$i]['stoId'].'\',\'05\',\'불용재고로 등록되었습니다.\')" >불용재고등록</a></li>';
            $state_menu9='<li><a href="javascript:;" onclick="retal_state_change(\''.$list[$i]['stoId'].'\',\'09\')">대여종료</a></li>';
            $state_menu_del='<li><a href="javascript:;" onclick="del_stoId(\''.$list[$i]['stoId'].'\')">삭제</a></li>';
            

            //메뉴 선택  01: 재고(대여가능) 02: 재고소진(대여중) 08: 소독중 09: 대여종료
            switch ($list[$i]['stateCd']) {
              case '01':
                $state="대여가능";
                $state_menu_all = $state_menu3.$state_menu8;
                $rental_btn = '<a class="state-btn1" href="javascript:;" onclick="open_rent_popup(this)">대여</a>';
                $rental_btn2 = '<a class="state-btn1" href="javascript:;" onclick="open_rent_popup(this)">대여하기</a>';
                //$rental_btn='<a class="state-btn1" href="javascript:;"onclick="popup_control(\''.$list[$i]['prodColor'].'\',\''.$list[$i]['prodSize'].'\',\''.$list[$i]['prodOption'].'\',\''.$list[$i]['prodBarNum'].'\')">대여</a>'; //대여 버튼
                //$rental_btn2='<a class="state-btn1" href="javascript:;"onclick="popup_control(\''.$list[$i]['prodColor'].'\',\''.$list[$i]['prodSize'].'\',\''.$list[$i]['prodOption'].'\',\''.$list[$i]['prodBarNum'].'\')">대여하기</a>'; //대여 버튼
                break;
              case '02':
                $state="대여중";
                $state_menu_all = $state_menu1.$state_menu2.$state_menu9;
                $rental_btn=""; 
                $rental_date=$ordLendStrDtm_date.'~'.$ordLendEndDtm_date;
                
                //30일 미만일경우
                $srat_cal = new DateTime($ordLendStrDtm_date);
                $end_cal = new DateTime($ordLendEndDtm_date);
                $result_cal = date_diff($srat_cal, $end_cal);
                if($result_cal->days <= 30){ $bg="bg"; }
                break;
              case '08':
                $state="소독중";
                $state_menu_all = $state_menu5.$state_menu6;
                break;
              case '09':
                $state="대여종료";
                $state_menu_all = $state_menu3.$state_menu4;
                break;
              default:
                $state="";
                break;
            }
            //대여가능
            if($state=="대여가능") {
              $sql_state = "SELECT `dis_state` FROM `g5_rental_log` WHERE `stoId`= '{$list[$i]['stoId']}' AND `rental_log_division`='1' ORDER BY `dis_total_date` DESC LIMIT 1";
              $row_state = sql_fetch($sql_state);
              $dis_state = $row_state['dis_state'];
              if($dis_state=="소독완료"){
                $water='<img style="margin-left:2px; margin-bottom:3px;" src="'.G5_IMG_URL.'/water.png" alt="">';
              }
            }
            $state_menu_all=$state_menu_all.$state_menu_del;
            //N버튼
            $nimg="";
            $sql_new="select count(*) as count from `g5_rental_log` where `stoId` =  '".$list[$i]['stoId']."' and `rental_log_division` = '2';";
            $row_new = sql_fetch($sql_new);
            $nimg_flag = $row_new['count'];
            if(!$nimg_flag){ $nimg='<img style="padding-left:5px;" src="'.G5_IMG_URL.'/iconnew.png" alt="">'; }
            ?>
            <li class="list cb <?=$bg?>">
              <!--pc용-->
              <span class="num">
                <?php /*if($list[$i]['stateCd'] == '01') { ?>
                <label for="chk_stock_<?=$number?>">
                  <?=$number?>
                  <input
                    data-color="<?=$list[$i]['prodColor']?>"
                    data-size="<?=$list[$i]['prodSize']?>"
                    data-options="<?=$list[$i]['prodOption']?>"
                    data-barcode="<?=$list[$i]['prodBarNum']?>"
                    type="checkbox" name="chk_stock_<?=$number?>" id="chk_stock_<?=$number?>" class="chk_stock" style="margin-bottom:8px;
                  ">
                </label>
                <?php } else { */echo $number; /*}*/ ?>
              </span>
              <span class="product m_off">
                <?php
                if($list[$i]['prodColor']||$list[$i]['prodSize']){
                  $name = $list[$i]['prodNm'].'('.$list[$i]['prodColor'].$div.$list[$i]['prodSize'].')';
                } else {
                  $name = $list[$i]['prodNm'];
                }
                echo $name;
                echo '<button class="btn" onclick="open_change_option(this, \''.$list[$i]['prodColor'].'\', \''.$list[$i]['prodSize'].'\', \''.$list[$i]['prodOption'].'\')" style="margin-left: 10px;">옵션변경</button>';

                // 대여내구연한(사용가능기간) 설정 시
                if($row['it_rental_use_persisting_year'] && $row['it_rental_expiry_year']) {
                  $persisting_year_txt = '';

                  $inital_contract_date = $list[$i]['initialContractDate']; // 최초계약일
                  if($inital_contract_date) {
                    $inital_contract_time = strtotime($inital_contract_date);
                    $now = time();


                    $rental_expiry_time = $inital_contract_time + ( $row['it_rental_expiry_year'] * 365 * 24 * 60 * 60 );
                    $rental_persisting_time = $rental_expiry_time + ( $row['it_rental_persisting_year'] * 365 * 24 * 60 * 60 );

                    //사용가능햇수 지나기 전이면
                    if($now < $rental_expiry_time) {
                      $persisting_year_txt = '*사용가능 기간 ('.date('Y.m.d', $rental_expiry_time).' 종료)';
                    }

                    //사용가능햇수+연장사용햇수 지나기 전
                    else if($now < $rental_persisting_time) {
                      $custom_rental_price = $list[$i]['customRentalPrice'];
                      $persisting_year_txt = '*연장대여 기간 ('.number_format($custom_rental_price).'원 / '.date('Y.m.d', $rental_persisting_time).' 종료)';
                    }

                    //사용가능햇수+연장사용햇수 지났으면
                    else {
                      $persisting_year_txt = '*내구연한 종료';
                    }
                  } else {
                    // 수급자에게 공급 전 상품으로 최초계약일이 없는 상품
                    $persisting_year_txt = '*미사용';
                  }
                  if($persisting_year_txt) echo '<br>'.$persisting_year_txt;
                }
                ?>
              </span>
              <span class="pro-num m_off <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
              <?php
              //날짜 변환
              $date1=$list[$i]['modifyDtm'];
              $date2=date("Y-m-d", strtotime($date1));
              ?>
              <span class="date m_off"><?=$date2?></span>
              <span class="state m_off">
                <b><?=$state?><?=$nimg?><?=$water?><span><?=$rental_date?></span></b>
                <?=$rental_btn //대여버튼 ?>
              </span>

              <span class="none m_off">
                <div class="state-btn2" onclick="open_list(this);">
                  <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                  <ul class="modalDialog">
                    <?=$state_menu_all; ?>
                  </ul>
                </div>
                <a class="state-btn3" href="javascript:;" onclick="open_log(this,'<?=$list[$i]['stoId']?>','log_<?=$list[$i]['stoId']?>','1','page_<?=$list[$i]['stoId']?>','1','<?=$list[$i]['prodBarNum']?>')"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
              </span>

              <!--mobile용-->
              <div class="list-m">
                <div class="info-m">
                  <span class="product">
                    <?php
                    echo $name;

                    if($persisting_year_txt) echo '<br>'.$persisting_year_txt;
                    ?>
                  </span>
                  <span class="pro-num <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?> ><?=$list[$i]['prodBarNum']?></b></span>
                </div>
                <div class="info-m">
                  <span class="state">
                    <b><?=$state?><?=$nimg?><?=$water?><span><?=$rental_date?></b>
                  </span>
                  <span class="none">
                    <?=$rental_btn2?>
                    <div class="state-btn2" onclick="open_list(this);">
                      <b><img src="<?=G5_IMG_URL?>/icon_11.png" alt=""></b>
                      <ul class="modalDialog">
                        <?=$state_menu_all; ?>
                      </ul>
                    </div>
                    <a class="state-btn3" href="javascript:;"  onclick="open_log(this,'<?=$list[$i]['stoId']?>','log_<?=$list[$i]['stoId']?>','1','page_<?=$list[$i]['stoId']?>','1','<?=$list[$i]['prodBarNum']?>')" ><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
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
                        <button type="button" onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                      </div>
                    </li>
                    <li>
                      <b>담당자명</b>
                      <div class="input-box">
                        <input type="text" id="dis_perosn_<?=$list[$i]['stoId']?>">
                        <button type="button" onclick="click_x(this)"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                      </div>
                    </li>
                    <li>
                      <b>연락처</b>
                      <div class="input-box">
                        <input type="tel" id="dis_phone_<?=$list[$i]['stoId']?>">
                        <button type="button" onclick="click_x(this)"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                      </div>
                    </li>
                  </ul>
                  <div class="popup-btn">
                    <button type="submit" onclick="designate_disinfection('<?=$list[$i]['stoId']?>','dis_detail_<?=$list[$i]['stoId']?>','dis_perosn_<?=$list[$i]['stoId']?>','dis_phone_<?=$list[$i]['stoId']?>','08')" >확인</button>
                    <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                  </div>
                </div>
              </div>

              <!-- 대여 팝업 -->
              <div class="popup01 popup_rent">
                <div class="p-inner">
                  <h2>대여완료처리</h2>
                  <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                  <div class="rent_wrap">
                    등록 수급자 선택 후 처리
                    <button type="button" onclick="popup_control('<?=$list[$i]['prodColor']?>','<?=$list[$i]['prodSize']?>','<?=$list[$i]['prodOption']?>','<?=$list[$i]['prodBarNum']?>')">확인</button>
                  </div>
                  <div class="sell_desc">
                    수급자 선택없이 대여완료 처리
                  </div>
                  <form name="form_rent" id="form_rent<?=$i?>" class="form_rent" role="form" onSubmit="return rent_complete('<?=$i?>')">
                    <input type="hidden" name="stoId" value="<?=$list[$i]['stoId']?>">
                    <input type="hidden" name="prodBarNum" value="<?=$list[$i]['prodBarNum']?>">
                    <ul style="padding: 0 0 20px 0;">
                      <li>
                        <b>수급자명</b>
                        <div class="input-box">
                          <input type="text" name="penNm">
                          <button type="button" onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li>
                        <b>대여시작일</b>
                        <div class="input-box">
                          <input type="text" class="" name="strDate" id="strDate<?=$i?>" autocomplete='off'>
                          <button type="button" onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li>
                        <b>대여종료일</b>
                        <div class="input-box">
                          <input type="text" class="" name="endDate" id="endDate<?=$i?>" autocomplete='off'>
                          <button type="button" onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                    </ul>
					<script>
						$("#strDate<?=$i?>").datepicker({});
                        $("#endDate<?=$i?>").datepicker({});   
                    </script>
                    <label class="label_confirm">
                      <input type="checkbox" name="chk_confirm" class=".chk_confirm">
                      확인함
                    </label>
                    <div class="sell_desc">
                      직접 대여완료 처리하는 경우 등록된 수급자의<br>
                      청구관리 및 계약정보에 반영되지 않습니다.
                    </div>
                    <div class="popup-btn">
                      <button type="submit">확인</button>
                      <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                    </div>
                  </form>
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
                      <?php 
                      if($state=="소독중") {
                        $stoId_s=$list[$i]['stoId'];
                        $sql_sodock = "SELECT `dis_total_date` FROM `g5_rental_log` WHERE `stoId`= '{$stoId_s}' AND `rental_log_division`='1' ORDER BY `dis_total_date` DESC LIMIT 1";
                        $row_sodock = sql_fetch($sql_sodock);
                        $dis_total_date = date("Y-m-d", strtotime($row_sodock['dis_total_date']));
                      }
                      ?>
                      <li>
                        <b>소독 시작일</b>
                        <div class="input-box">
                          <input type="text" name="strdate" dateonly<?=$list[$i]['stoId']?>_ss id="strdate_<?=$list[$i]['stoId']?>" readonly value="<?=$dis_total_date?>">
                          <button type="button" onclick="click_x(this)"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li>
                        <b>소독 마감일</b>
                        <div class="input-box">
                          <input type="text" name="enddate" dateonly<?=$list[$i]['stoId']?>_ee id="enddate_<?=$list[$i]['stoId']?>" readonly>
                          <button type="button"  onclick="click_x(this)" ><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <script>
                        $("input:text[dateonly<?=$list[$i]['stoId']?>_ss]").datepicker({
                          minDate: "<?=$date2 ?>",
                        });
                        $("input:text[dateonly<?=$list[$i]['stoId']?>_ee]").datepicker({
                          minDate: "<?=$dis_total_date ?>"
                        });
                      </script>
                      <li>
                        <b>약품종류</b>
                        <div class="input-box">
                          <input type="text" name="dis_chemical" id="dis_chemical_<?=$list[$i]['stoId']?>">
                          <button type="button"  onclick="click_x(this)"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li>
                        <b>약품사용내역</b>
                        <div class="input-box">
                          <input type="text"  name="dis_chemical_history" id="dis_chemical_history_<?=$list[$i]['stoId']?>">
                          <button type="button"  onclick="click_x(this)"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li class="file-list">
                        <b>첨부파일(소독필증)</b>
                        <div class="input-box">
                          <input type="text"  name="dis_file_text" id="dis_file_text_<?=$list[$i]['stoId']?>" class="filetext" readonly>
                          <!-- <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button> -->
                        </div>
                        <div class="inputFile cb">
                          <input type="file"  name="dis_file" id="dis_file_<?=$list[$i]['stoId']?>" class="fileHidden" name=""  title="파일첨부 1 : 용량  이하만 업로드 가능">
                          <label for="dis_file_<?=$list[$i]['stoId']?>"></label>
                        </div>
                      </li>
                    </ul>
                    <div class="popup-btn">
                    <!-- designate_disinfection('<?=$list[$i]['stoId']?>','dis_detail_<?=$list[$i]['stoId']?>','dis_perosn_<?=$list[$i]['stoId']?>','dis_phone_<?=$list[$i]['stoId']?>','08 -->
                        <button type="button" onclick="designate_result('<?=$list[$i]['stoId']?>','strdate_<?=$list[$i]['stoId']?>','enddate_<?=$list[$i]['stoId']?>','dis_chemical_<?=$list[$i]['stoId']?>','dis_chemical_history_<?=$list[$i]['stoId']?>','dis_file_text_<?=$list[$i]['stoId']?>','dis_file_<?=$list[$i]['stoId']?>','designate_result_form_<?=$list[$i]['stoId']?>')">확인</button>
                        <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- 대여기록 -->
              <div class="popup01 popup3" style="width:470px;">
                <div class="p-inner">
                  <h2>대여 기록</h2>
                  <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                 <?php //수정?>
				  <div class="table-box" style="max-width:450px; padding: 0 10px;">
                    <div class="tti">
                      <h4><?=$name?></h4>
                      <span><?=$list[$i]['prodBarNum']?></span>
                    </div>
                    <table style="width:408px;">
                      <colgroup>
                        <col width="10%">
                        <col width="20%">
                        <col width="10%">
						<col width="35%">
                        <col width="25%">
                      </colgroup>
                      <thead>
                        <th style="text-align: center;">No.</th>
                        <th style="text-align: center;">내용</th>
						<th style="text-align: center;">구분</th>
                        <th style="text-align: center;">기간</th>
                        <th style="text-align: center;">문서</th>
                      </thead>
                      <tbody id="log_<?=$list[$i]['stoId']?>">
                      </tbody>
                    </table>
                  </div>
				  <?php //수정끝?>
                  <div class="pg-wrap">
                    <!-- 페이지 넣는곳 -->
                    <div id="page_<?=$list[$i]['stoId']?>"></div>
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
                      //$ordLendStrDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendStrDtm']));
                      //$ordLendEndDtm_date=date("Y-m-d", strtotime($list[$i]['ordLendEndDtm']));
                      ?>
                      <li>
                        <b>대여시작일</b>
                        <div class="input-box">
                          <input type="text" value="<?=$ordLendStrDtm_date?>" dateonly<?=$list[$i]['stoId']?>_s id="strDtm_<?=$list[$i]['stoId']?>" autocomplete='off'>
                          <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <li>
                        <b>대여종료일</b>
                        <div class="input-box">
                          <input type="text" value="<?=$ordLendEndDtm_date?>" dateonly<?=$list[$i]['stoId']?>_e id="endDtm_<?=$list[$i]['stoId']?>" autocomplete='off'>
                          <button type="button"><img src="<?=G5_IMG_URL?>/icon_09.png" alt=""></button>
                        </div>
                      </li>
                      <script>
                        $("input:text[dateonly<?=$list[$i]['stoId']?>_s]").datepicker({
                          minDate: "<?=$date2?>"
                        });
                        $("input:text[dateonly<?=$list[$i]['stoId']?>_e]").datepicker({
                          minDate: "<?=$date2?>"
                        });
                      </script>
                    </ul>
                    <div class="popup-btn">
                      <button type="button" onclick="rental_period_change('<?=$list[$i]['stoId']?>')">확인</button>
                      <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                    </div>
                  </div>
                </form>
              </div>

              <!-- 옵션변경 -->
              <div class="popup01 popup5">
                <div class="p-inner">
                  <h2>옵션변경</h2>
                  <button class="cls-btn p-cls-btn" onclick="close_popup(this)" type="button"><img src="<?=G5_IMG_URL?>/icon_08.png" alt=""></button>
                  <form name="foption" id="foption<?=$i?>" class="form item-form form_change_option" role="form">
                    <input type="hidden" name="stoId" value="<?=$list[$i]['stoId']?>">
                    <input type="hidden" name="prodBarNum" value="<?=$list[$i]['prodBarNum']?>">
                    <table class="table">
                      <?php
                      $option_1 = get_change_options($it['it_id'], $it['it_option_subject']);
                      echo $option_1;
                      ?>
                    </table>
                    <div class="popup-btn">
                      <button type="button" onClick="foptions('<?=$i?>')">저장하기</button>
                      <button type="button" class="p-cls-btn" onclick="close_popup(this)">취소</button>
                    </div>
                  </form>
                </div>
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
          <?php if(!$list) { ?>
          <li style="text-align:center" >
            자료가 없습니다.
          </li>
          <?php } ?>
          <div id="list_box2">
            <?php for($i=0;$i<count($list);$i++) {
            $number = $totalCnt-(($pageNum-1)*$sendData["pageSize"])-$i;  //넘버링 토탈 -( (페이지-1) * 페이지사이즈) - $i
            //메뉴 선택  01: 재고(대여가능) 02: 재고소진(대여중) 08: 소독중 09: 대여종료
            switch ($list[$i]['stateCd']) {
              case '03': $state="AS신청"; break;
              case '04': $state="반품";   break;
              case '05': $state="기타";   break;
              default  : $state="";      break;
            }

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
              } else {
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
              <span class="pro-num m_off <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
              <?php
              //날짜 변환
              $date1=$list[$i]['modifyDtm'];
              $date2=date("Y-m-d", strtotime($date1));
              ?>
              <span class="date m_off"><?=$date2?></span>

              <span class="none m_off" onclick="open_log(this,'<?=$list[$i]['stoId']?>','log_<?=$list[$i]['stoId']?>','1','page_<?=$list[$i]['stoId']?>','1','<?=$list[$i]['prodBarNum']?>')">
                <a href="javascript:;" class="state-btn1" onclick="retal_state_change2('<?=$list[$i]['stoId'] ?>','01','변경되었습니다.')" >대여가능</a>
                <a class="state-btn3" href="javascript:;"><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
              </span>

              <!--mobile용-->
              <div class="list-m">
                <div class="info-m">
                  <span class="product"><?=$name;?></span>
                  <span class="pro-num <?=$prodBarNumCntBtn_2;?>" data-stock="<?=$stock_insert?>" data-name="<?=$name?>" data-stoId="<?=$list[$i]['stoId']?>"><b <?=$style_prodSupYn?>><?=$list[$i]['prodBarNum']?></b></span>
                  <a href="javascript:;" style="margin-right:7px;" class="state-btn1" onclick="retal_state_change2('<?=$list[$i]['stoId'] ?>','01','변경되었습니다.')" >대여가능</a>
                  <a class="state-btn3" onclick="open_log(this,'<?=$list[$i]['stoId']?>','log_<?=$list[$i]['stoId']?>','1','page_<?=$list[$i]['stoId']?>','1','<?=$list[$i]['prodBarNum']?>')"  href="javascript:; "><img src="<?=G5_IMG_URL?>/icon_12.png" alt=""></a>
                </div>
                <div class="info-m">
                  <span class="none">
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
                      <h4><?=$list[$i]['prodNm']?><?=$name;?></h4>
                      <span><?=$list[$i]['prodBarNum']?></span>
                    </div>
                    <table>
                      <colgroup>
                        <col width="10%">
                        <col width="20%">
                        <col width="10%">
						<col width="35%">
                        <col width="25%">
                      </colgroup>
                      <thead>
                        <th style="text-align: center;">No.</th>
                        <th style="text-align: center;">내용</th>
						<th style="text-align: center;">구분</th>
                        <th style="text-align: center;">기간</th>
                        <th style="text-align: center;">문서</th>
                      </thead>
                      <tbody id="log_<?=$list[$i]['stoId']?>"></tbody>
                    </table>
                  </div>
                  <div class="pg-wrap">
                    <!-- 페이지 넣는곳 -->
                    <div id="page_<?=$list[$i]['stoId']?>"></div>
                  </div>
                </div>
              </div>
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
<!------------------------------------------------------- 대여신청 ------------------------------------------------------->
<div id="order_recipientBox">
  <div>
    <iframe src="<?php echo G5_SHOP_URL;?>/pop_recipient.php?ca_id=<?=get_search_string($ca_id)?>" style='z-index:9999' id="recipient_iframe"></iframe>
  </div>
</div>
<!-- 수급자 선택시 변경되어 넘어갈 값 -->
<form action="<?php echo $action_url; ?>"name="fitem" method="post" id="recipient_info"class="form item-form">
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
<!------------------------------------------------------- 대여신청 -------------------------------------------------------->
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
  is_multi_submit = false;
  $('#order_recipientBox').show();

  var option_value = make_option_value(prodColor, prodSize, prodOptions);

  document.getElementById('io_id_r').value = option_value.io_id;
  document.getElementById('io_value_r').value = option_value.io_value;
  document.getElementById('barcode_r').value = barcode_r;
}

function selected_recipient(penId) {
  if(is_multi_submit && cart_form) {
    cart_form.append('penId_r', penId);
    return cart_form.submit();
  }

  document.getElementById('penId_r').value = penId;
  document.getElementById('recipient_info').submit();
}
function rent_complete(a){
	var confirmed = $("#form_rent"+a).find('input[name=chk_confirm]').prop('checked');
    if(!confirmed) {
      alert('대여완료처리 안내사항을 확인해주세요.');
	  return false;
    }
    var stoId = $("#form_rent"+a).find('input[name=stoId]').val();
    var prodBarNum = $("#form_rent"+a).find('input[name=prodBarNum]').val();
    var penNm = $("#form_rent"+a).find('input[name=penNm]').val();
    var strDate = $("#form_rent"+a).find('input[name=strDate]').val();
    var endDate = $("#form_rent"+a).find('input[name=endDate]').val();

    $.post('ajax.stock.rent.php', {
      prodId: '<?=$it_id?>',
      stoId: stoId,
      prodBarNum: prodBarNum,
      penNm: penNm,
      strDate: strDate,
      endDate: endDate
    }, 'json')
    .done(function(result) {
      alert('완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
	  return false;
    });
}
function foptions(a){
	change_option($("#foption"+a));
	false;
}
$(function() {
  // 대여완료처리
  //$('.ipt_date').datepicker({});
 /* $('.form_rent').on('submit', function(e) {
    e.preventDefault();

    var confirmed = $(this).find('input[name=chk_confirm]').prop('checked');
    if(!confirmed) {
      return alert('대여완료처리 안내사항을 확인해주세요.');
    }
    var stoId = $(this).find('input[name=stoId]').val();
    var prodBarNum = $(this).find('input[name=prodBarNum]').val();
    var penNm = $(this).find('input[name=penNm]').val();
    var strDate = $(this).find('input[name=strDate]').val();
    var endDate = $(this).find('input[name=endDate]').val();

    $.post('ajax.stock.rent.php', {
      prodId: '<?=$it_id?>',
      stoId: stoId,
      prodBarNum: prodBarNum,
      penNm: penNm,
      strDate: strDate,
      endDate: endDate
    }, 'json')
    .done(function(result) {
      alert('완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $('.form_change_option').on('submit', function(e) {
    e.preventDefault();
    change_option(this);
  });
*/
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

  $('#order_recipientBox').hide();
  $("#order_recipientBox").css("opacity", 1);
  $(".listPopupBoxWrap").hide();
  $(".listPopupBoxWrap").css("opacity", 1);
});
</script>
<!-- 바코드 클릭 -->

<!-- 스크립트 -->
<script>
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

//##팝업
//대여완료처리
function open_rent_popup(e) {
  $(e).parents('.list').find('.popup_rent').stop().show();
}

//대여기간 수정
function open_retal_period(e) {
  $(e).parents('.list').find('.popup4').stop().show();
}

//대여기록 팝업
function open_log(e ,stoId,logid,page,pageid,num,barcode) {
  var logid_object= document.getElementById(logid);
  var pageid_object= document.getElementById(pageid);

  var sendData = {
    stoId:stoId,
    logid:page,
    page:page,
    pageid:page,
	barcode:barcode
  }

  $.ajax({//리스트
    url: "./ajax.rental_log.php",
    type: "POST",
    async: false,
    data: sendData,
    success: function(result) {
      logid_object.innerHTML="";
      $(logid_object).append(result);
    }
  });

  $.ajax({//페이징
    url: "./ajax.rental_log_page.php",
    type: "POST",
    async: false,
    data: sendData,
    success: function(result) {
      pageid_object.innerHTML="";
      $(pageid_object).append(result)
    }
  });

  if(num=1) {
    $(e).parents('.list').find('.popup3').stop().show();
  }
}

// 옵션변경 팝업
function open_change_option(e, prodColor, prodSize, prodOptions) {
  var $popup = $(e).parents('.list').find('.popup5').stop();

  prodOptions = prodOptions.split('|');

  if(prodColor || prodSize || (prodOptions && prodOptions[0] != '')) { // 옵션 값이 있으면
    var io_subjects = '<?=get_text($it['it_option_subject'])?>'.split(',');

    for(var i = 1; i <= io_subjects.length; i++) {
      switch(io_subjects[i-1]) {
        case '색상':
          $popup.find('.opt_change_'+i).val(prodColor);
          break;
        case '사이즈':
          $popup.find('.opt_change_'+i).val(prodSize);
          break;
        case '':
          // do nothing
          break;
        default:
          var prodOption = prodOptions.shift();
          $popup.find('.opt_change_'+i).val(prodOption);
      }
    }
  }

  $popup.show();
}

function change_option(e) {
  var $opt_change = $(e).find('.opt_change');
  var stoId = $(e).find('input[name=stoId]').val();
  var prodBarNum = $(e).find('input[name=prodBarNum]').val();

  var prodColor = '';
  var prodSize = '';
  var prodOptions = [];
  for(var i = 0; i < $opt_change.length; i++) {
    var $sel = $opt_change.eq(i);
    var subject = $sel.data('subject');
    switch(subject) {
      case '색상':
        prodColor = $sel.val();
        break;
      case '사이즈':
        prodSize = $sel.val();
        break;
      default:
        prodOptions.push($sel.val());
    }
  }

  $.post('ajax.stock.option.php', {
    stoId: stoId,
    prodBarNum: prodBarNum,
    prodColor: prodColor,
    prodSize: prodSize,
    prodOption: prodOptions.join('|')
  }, 'json')
  .done(function() {
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });

  return false;
}

//소독업체지정 팝업
function open_designate_disinfection(e){
  $(e).parents('.list').find('.popup1').stop().show();
}
//소독확인신청 팝업
function open_designate_result(e){
  $(e).parents('.list').find('.popup2').stop().show();
}
//닫기
function close_popup(e){
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

//대여상품 상태변경 api 통신 (대여종료)
function retal_state_change(stoId,stateCd) {
  var sendData = {
    usrId: "<?=$member["mb_id"]?>",
    prods: [
      {
        stoId : stoId,                       //재고아이디
        stateCd : stateCd,                 //상태값
      }
    ]
  }

  console.log(sendData);
  $.ajax({
    url: "./ajax.stock.update.php",
    type: "POST",
    async: false,
    data: sendData,
    success: function(result) {
      result = JSON.parse(result);
      if(result.errorYN == "Y") {
        alert(result.message);
      } else {
        alert('변경이 완료되었습니다.');
        window.location.reload();
      }
    }
  });
}

// 대여기간 수정
function rental_period_change(stoId) {
  var start_date = $('#strDtm_' + stoId).val();
  var end_date = $('#endDtm_' + stoId).val();

  $.post('ajax.stock.rent_date.php', {
    prodId: '<?=get_text($it_id)?>',
    stoId: stoId,
    start_date: start_date,
    end_date: end_date
  }, 'json')
  .done(function() {
    window.location.reload();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
}

//대여상품 상태변경 api 통신 (소독신청 ,소독취소) 소독취소 -> 대여가능
function retal_state_change2(stoId,stateCd,string) {
  var sendData = {
    usrId: "<?=$member["mb_id"]?>",
    prods: [
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
      } else {
        alert(string);
        window.location.reload();
      }
    }
  });
}

//소독업체 지정
function designate_disinfection(stoId,dis_detail,dis_perosn,dis_phone,stateCd) {
  var dis_detail = document.getElementById(dis_detail);
  var dis_perosn = document.getElementById(dis_perosn);
  var dis_phone = document.getElementById(dis_phone);

  if(!dis_detail.value){ alert('상세정보를 입력해주세요'); return false;}
  // if(!dis_perosn.value){ alert('담당자명을 입력해주세요'); return false;}
  // if(!dis_phone.value){ alert('연락처를 입력해주세요'); return false;}

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
      if(result=="S") {
        retal_state_change2(stoId,stateCd,"소독업체 지정이 완료 되었습니다.");
      } else {
        alert(result);
      }
    }
  });
}

//파일 이름 넣기
$(document).on('change', '.fileHidden', function() {
  ext = $(this).val().split('.').pop().toLowerCase(); //확장자
  //배열에 추출한 확장자가 존재하는지 체크
  if($.inArray(ext, ['exe']) == 1) {
    alert('지원하는 파일이 아닙니다.');
    $(this).val()="";
    return false;
  } else {
    $(this).closest("li").find(".filetext").val(this.files[0].name);
  }
});

//소독 결과 확인 지정 POST - >PHP
function designate_result(stoId,strdate,enddate,dis_chemical,dis_chemical_history,dis_file_text,dis_file,designate_result_form) {
  var strdate = document.getElementById(strdate);
  var enddate = document.getElementById(enddate);
  var dis_chemical = document.getElementById(dis_chemical);
  var dis_chemical_history = document.getElementById(dis_chemical_history);
  var dis_file_text = document.getElementById(dis_file_text);
  var dis_file = document.getElementById(dis_file);
  var designate_result_form = document.getElementById(designate_result_form);
  if(!strdate.value){ alert('소독시작 날짜를 입력해주세요'); return false;}
  if(!enddate.value){ alert('소독마감 날짜를 입력해주세요'); return false;}
  // if(!dis_chemical.value){ alert('약품종류를 입력해주세요'); return false;}
  // if(!dis_chemical_history.value){ alert('약품사용내역을 입력해주세요'); return false;}
  // if(!dis_file_text.value){ alert('파일을 선택해주세요'); return false;}
  designate_result_form.submit();
}
function click_x(this_click){
  $(this_click).closest("li").find("input").val("");
}
//페이징 처리1
function page_load(page_n) {
  page_n = parseInt(page_n);
  $.ajax({
    url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail2.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>&soption=<?=$_GET['soption']?>&stx=<?=$_GET['stx']?>",
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
    url : "<?= G5_SHOP_URL; ?>/sales_Inventory_datail2.php?prodId=<?=$it_id?>&page2="+page_n+"&prodSupYn=<?=$_GET['prodSupYn']?>&soption=<?=$_GET['soption']?>&stx=<?=$_GET['stx']?>",
    type : "get",
    async : false,
    success : function(result) {
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

//삭제
function del_stoId(stoId) {
  var prods={};
  prods['stoId'] = [stoId];
  if (confirm("정말 삭제하시겠습니까??") == true) {
    var sendData = {
      stoId: [stoId]
    }

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
include_once('./_tail.php');
?>
