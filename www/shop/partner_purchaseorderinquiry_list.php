<?php
include_once('./_common.php');

if(!$is_samhwa_partner && !($member['mb_type'] === 'supplier')) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$g5['title'] = "파트너 발주내역";
include_once("./_head.php");

$manager_mb_id = get_session('ss_manager_mb_id');

$where = [];

# 기간
$sel_date = in_array($sel_date, ['od_time', 'ct_ex_date']) ? $sel_date : '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if($sel_date && $fr_date && $to_date)
  $where[] = " ( {$sel_date} between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

# 작업상태
$incompleted = $_GET['incompleted'];
if($incompleted && count($incompleted) == 1) {
  foreach($incompleted as $ic) {
    if($ic == '0') {
      // 진행중인 작업
      $where[] = "
        ( (ct_delivery_num is not null and ct_delivery_num != '')
        or (o.od_partner_manager is not null and o.od_partner_manager != '')
        or ct_status in ('출고완료', '입고완료', '마감완료') )
      ";
    }
    else if($ic == '1') {
      // 미 진행중인 작업
      $where[] = "
        ( (ct_delivery_num is null or ct_delivery_num = '') 
        and (o.od_partner_manager is null or o.od_partner_manager = '')
        and ct_status = '발주완료' )
      ";
    }
  }
}

# 담당자는 본인으로 지정된 주문만 보기
if($manager_mb_id) {
  $where[] = "
    o.od_partner_manager = '$manager_mb_id'
  ";
}

# 주문상태
$ct_status = $_GET['ct_status'];
$ct_steps = ['발주완료','출고완료','입고완료','마감완료','발주취소','관리자발주취소'];
if($ct_status) {
  $ct_steps = array_intersect($ct_steps, $ct_status);
}

//$where[] = " ( ct_status = '".implode("' OR ct_status = '", $ct_steps)."' ) ";
$where[] = " ( ct_status like '%".implode("%' OR ct_status like '%", $ct_steps)."%' ) ";

# 검색어
$attrs = ['all', 'mb_entNm', 'it_name', 'c.od_id', 'od_b_name', 'od_partner_manager'];
$sel_field = in_array($sel_field, $attrs) ? $sel_field : '';
$search = get_search_string($search);
if($sel_field && $search) {
  if($sel_field == 'all') {
    $where_all = [];
    foreach($attrs as $attr) {
      if($attr != 'all') {
        if($attr == 'mb_entNm') {
          $where_all[] = " ( mb_entNm like '%{$search}%' or (mb_temp = TRUE and mb_name like '%{$search}%') ) ";
        } else if($attr == 'od_partner_manager') {
          $where_all[] = " ( ( select mb_name from g5_member where mb_id = o.od_partner_manager ) like '%{$search}%' ) ";
        } else {
          $where_all[] = " {$attr} like '%{$search}%' ";
        }
      }
    }
    $where[] = ' ( ' . implode(' OR ', $where_all) . ' ) ';
  } else if($sel_field == 'mb_entNm') {
    $where[] = " ( mb_entNm like '%{$search}%' or (mb_temp = TRUE and mb_name like '%{$search}%') ) ";
  } else if($sel_field == 'od_partner_manager') {
    $where[] = " ( ( select mb_name from g5_member where mb_id = o.od_partner_manager ) like '%{$search}%' ) ";
  } else {
    $where[] = " {$sel_field} like '%{$search}%' ";
  }
}

$sql_search = ' and '.implode(' and ', $where);

$sql_common = "
  FROM
    purchase_cart c
  LEFT JOIN
    purchase_order o ON c.od_id = o.od_id
  LEFT JOIN
    {$g5['member_table']} m ON c.mb_id = m.mb_id
  WHERE
    od_del_yn = 'N' and
    ct_supply_partner = '{$member['mb_id']}'
    {$sql_search}
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$sql_order = " ORDER BY FIELD(ct_status, '" . implode("' , '", $ct_steps) . "' ), ct_move_date desc, od_id desc ";

$result = sql_query("
  SELECT
    ct_id,
    c.od_id,
    od_time,
    m.mb_temp,
    m.mb_name,
    mb_entNm,
    od_b_name,
    od_b_hp,
    od_b_tel,
    od_b_addr1,
    od_b_addr2,
    od_b_addr3,
    od_b_addr_jibeon,
    od_partner_manager,
    it_name,
    ct_option,
    ct_qty,
    if(ct_status='관리자발주취소','발주취소',ct_status) as ct_status,
    ct_status as ct_status_info,
    prodMemo,
    c.stoId,
    ct_delivery_num,
    ct_is_direct_delivery,
    ct_price,
    ct_direct_delivery_price,
    ct_direct_delivery_date,
    ct_rdy_date,
    ct_ex_date,
    ct_barcode_insert,
    m.mb_tel,
    m.mb_hp,
    ct_warehouse,
    ct_warehouse_address,
    ct_warehouse_phone,
    ct_delivery_expect_date,
    ct_part_info
  {$sql_common}
  {$sql_order}
  {$sql_limit}
");

$orders = [];
while($row = sql_fetch_array($result)) {
  $ct_status_text = $row['ct_status'];
  $row['ct_status'] = $ct_status_text;

  $ct_direct_delivery_text = '배송';
  if($row['ct_is_direct_delivery'] == '2') {
    $ct_direct_delivery_text = '설치';
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_price']) * intval($row['ct_qty']);
  // 공급가액
  $price_p = @round(($price ?: 0) / 1.1);
  // 부가세
  $price_s = @round(($price ?: 0) / 1.1 / 10);

  $row['price'] = $price;
  $row['price_p'] = $price_p;
  $row['price_s'] = $price_s;

  $row['ct_barcode_insert'] = $row['ct_barcode_insert'] ?: 0;

  // 배송정보
  $total_cnt_result = sql_fetch("
    SELECT count(*) as cnt
    FROM purchase_cart
    WHERE
    od_id = '{$row['od_id']}' and
    ct_supply_partner = '{$member['mb_id']}'
  ");
  $row['total_cnt'] = $total_cnt_result['cnt'] ?: 0;

  $inserted_cnt_result = sql_fetch("
    SELECT count(*) as cnt
    FROM purchase_cart
    WHERE
    od_id = '{$row['od_id']}' and
    ct_supply_partner = '{$member['mb_id']}' and
    ct_delivery_num <> '' and ct_delivery_num is not null
  ");
  $row['inserted_cnt'] = $inserted_cnt_result['cnt'] ?: 0;

  // 임시회원의경우 mb_entNm 대신 mb_name 출력
  if($row['mb_temp']) {
    $row['mb_entNm'] = $row['mb_name'];
  }

  // 미진행중인 작업
  if (!$row['ct_delivery_num'] && !$row['od_partner_manager'] && in_array($row['ct_status'], ['발주완료'])) {
    $row['incompleted'] = true;
  }

  $orders[] = $row;
}

$qstr = "?sel_date={$sel_date}&amp;fr_date={$fr_date}&amp;to_date={$to_date}&amp;sel_field={$sel_field}&amp;search={$search}";
if($ct_status) {
  foreach($ct_status as $status) {
    $qstr .= "&amp;ct_status%5B%5D={$status}";
  }
}
if($incompleted) {
  foreach($incompleted as $ic) {
    $qstr .= "&amp;incompleted%5B%5D={$ic}";
  }
}

// 담당자
$manager_result = sql_query("
  select * from g5_member
  where mb_type = 'manager' and mb_manager = '{$member['mb_id']}'
");
$managers = [];
while($manager = sql_fetch_array($manager_result)) {
  $managers[] = $manager;
}

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<style>
form.clear:after { display: table; content: ' '; clear: both; }
.r_area { font-size: 12px; font-weight: normal; }
.r_area span { margin-right: 5px; }
.r_area select { border-radius: 3px; border: 1px solid #ddd; width: 100px; height: 30px; font-size: 12px; color: #555; padding-left: 10px; }
.r_area .btn_blk { background: #333; color: #fff; width: 85px; padding: 4px 0; height: 30px; border-radius: 3px; margin: 0 5px; font-size: 12px; }
.r_area .btn_wht { background: #fff; border: 1px solid #ddd; color: #666; width: 100px; padding: 4px 0; height: 30px; border-radius: 3px; font-size: 12px; }

.list_box table td:first-child { padding: 0; width: 40px; }

.td_od_info { width: unset !important; text-align: left !important; position: relative; }
.td_od_info p { margin: 0; font-size: 12px; color: #666; line-height: 1.25; }
.td_od_info p.info_head { font-size: 14px; color: #333; font-weight: bold; line-height: 1.5; }
.td_od_info span.info_delivery { display: inline-block; vertical-align: bottom; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.td_od_info img.icon_link { display: none; position: absolute; width: 24px; height: 24px; top: 50%; right: 10px; transform: translateY(-50%); }
tr.hover .td_od_info img.icon_link { display: block; }
tr.hover { background-color: #fbf9f7 !important; }

.td_od_info .btn_change, .btn_install_report { display: inline-block; vertical-align: middle; font-size: 12px; line-height: 1; padding: 5px 8px; border-radius: 3px; border: 1px solid #e6e1d7; color: #666; background: #fff; }
.btn_install_report.done { background: #f3f3f3; }

.td_operation { width: 100px }
.td_operation a + a { margin-top: 5px; }
.td_operation a { display: block; border: 1px solid #ddd; background: #fff; padding: 5px 8px; color: #666; border-radius: 3px; font-size: 12px; text-align: center; line-height: 15px; }
.td_operation a.disabled {
  background-color:#ddd;
}

.td_od_info a { border: 1px solid #ddd; background: #fff; padding: 1px 5px; color: #666; border-radius: 3px; font-size: 10px; text-align: center; line-height: 15px; }

.sel_manager { display: block; margin: 0 auto 5px auto; border-radius: 3px; border: 1px solid #ddd; width: 100px; height: 25px; font-size: 12px; color: #555; }

#change_wrap { display: none; }
.popModal { font-size: 12px; line-height: 22px; padding: 10px; cursor: default; }
.popModal .popModal_content { margin: 0; }
.popModal .title { color: #666; margin-bottom: 5px; }
.popModal input[type="text"] { background: #fff; color: #666; border: 1px solid #ddd; text-align: center; width: 110px; }
.popModal select { background: #fff; color: #666; border: 1px solid #ddd; height: 24px; width: 55px; }
.popModal .btn_submit { display: block; padding: 4px; border-radius: 3px; background: #f1a73a; color: #fff; margin: 5px auto 0 auto; width: 100px; }

#popup_box { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_box > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_box iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

.ct_status_mode_wr {
  display: inline-block;
  margin: 0 !important;
}
.ct_status_mode_wr input[type="radio"] {
  margin: 8px 0;
  width: 14px;
  height: 14px;
}
.ct_status_mode_wr label {
  margin: 5px 10px 5px 0;
  line-height: 20px;
}

@media (max-width : 750px) {
  #popup_box iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}

.table_box .step td {
  font-weight: normal;
  height: 44px;
  color: white;
}
</style>

<section class="wrap">
  <div class="sub_section_tit">발주내역</div>
  <form method="get" class="clear">
    <div class="search_box">
      <label><input type="checkbox" id="chk_ct_status_all"/> 전체</label> 
      <label><input type="checkbox" name="ct_status[]" value="발주완료" <?=option_array_checked('발주완료', $ct_status)?>/> 발주완료</label>
      <label><input type="checkbox" name="ct_status[]" value="출고완료" <?=option_array_checked('출고완료', $ct_status)?>/> 출고완료</label>
      <label><input type="checkbox" name="ct_status[]" value="입고완료" <?=option_array_checked('입고완료', $ct_status)?>/> 입고완료</label>
      <label><input type="checkbox" name="ct_status[]" value="마감완료" <?=option_array_checked('마감완료', $ct_status)?>/> 마감완료 </label>
      <label><input type="checkbox" name="ct_status[]" value="발주취소" <?=option_array_checked('발주취소', $ct_status)?>/> 발주취소 </label>
      <br>
      작업상태 :
      <label><input type="checkbox" id="chk_incompleted_all"/> 전체</label> 
      <label><input type="checkbox" name="incompleted[]" value="0" <?=option_array_checked('0', $incompleted)?>/> 진행중인 작업</label>
      <label><input type="checkbox" name="incompleted[]" value="1" <?=option_array_checked('1', $incompleted)?>/> 미 진행중인 작업</label>
      <br>
      
      <div class="search_date">
        <select name="sel_date">
          <option value="od_time" <?=get_selected($sel_date, 'od_time')?>>발주일</option>
          <option value="ct_ex_date" <?=get_selected($sel_date, 'ct_ex_date')?>>출고완료일</option>
        </select>
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker" autocomplete="off"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker" autocomplete="off"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="sel_field">
        <option value="all">전체</option>
        <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>품목명</option>
        <option value="c.od_id" <?=get_selected($sel_field, 'c.od_id')?>>발주번호</option>
      </select>
      <div class="input_search">
        <input name="search" value="<?=$_GET["search"]?>" type="text">
        <button type="submit"></button>
      </div>
    </div>
  </form>
  <div class="inner">
    <form id="form_ct_status">
    <div class="list_box">
      <div class="subtit">
        목록
        <div class="r_area">
          <span class="ct_status_mode_wr">
            <input type="radio" name="ct_status_mode" value="ct_status" id="ct_status_mode" checked>
            <label for="ct_status_mode">주문상태</label>
            <input type="radio" name="ct_status_mode" value="manager" id="ct_status_mode2">
            <label for="ct_status_mode2">담당자지정</label>
          </span>
          <select name="ct_status">
            <option value="출고완료" selected>출고완료</option>
            <option value="발주취소">발주취소</option>
          </select>
          <select name="manager" style="display: none;">
              <option value="">미지정</option>
              <?php foreach($managers as $manager) { ?>
              <option value="<?=$manager['mb_id']?>">[직원] <?=$manager['mb_name']?></option>
              <?php } ?>
          </select>
          <button type="button" id="btn_ct_status" class="btn_blk">변경하기</button>
          <button type="button" id="btn_excel" class="btn_wht">엑셀다운로드</button>
        </div>
      </div>
      <div class="table_box">
        <table>
          <colgroup>
            <col width="3%">
            <col width="33%">
            <col width="22%">
            <col width="15%">
            <col width="12%">
            <col width="15%">
          </colgroup>
          <thead>
            <tr>
              <th>
                <input type="checkbox" id="chk_all">
              </th>
              <th>발주정보</th>
              <th>배송지</th>
              <th>담당자/상태</th>
              <th>발주금액</th>
              <th>관리</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(!$orders) echo '<tr><td colspan="6" class="empty_table">내역이 없습니다.</td></tr>';
              // 시작 -->
              // 서원 : 22.10.26 - 구매/발주 기능개선 요청건
              //

              // 발주 단계 별 건수
              $cnt_ct_status = [];
              foreach($orders as $row) {
                 if(!$cnt_ct_status[$row['ct_status']]) {
                     $cnt_ct_status[$row['ct_status']] = 1;
                 } else {
                     $cnt_ct_status[$row['ct_status']] += 1;
                 }
              }
              $_check_ct_status = "";
              foreach($orders as $row) {

                if( $_check_ct_status != $row['ct_status']){
                  $_txt = $_check_ct_status = $row['ct_status'];

                  switch ($_check_ct_status) {
                    case '발주완료': $_bg_color = "#88a825"; $_txt = $_txt."(출고전)"; break;
//                    case '발주승인': $_bg_color = "#FFC000"; $_txt = $_txt."(출고전)"; break;
//                    case '부분출고': $_bg_color = "#953735"; $_txt = $_txt."(출고후)"; break;
                    case '출고완료': $_bg_color = "#32841c"; $_txt = $_txt."(출고후)"; break;
//                    case '부분입고': $_bg_color = "#93CDDD"; $_txt = $_txt."(출고후)"; break;
                    case '입고완료': $_bg_color = "#36a6de"; $_txt = $_txt."(출고후)"; break;
                    case '마감완료': $_bg_color = "#604A7B"; break;
//                    case '파트너발주취소': $_bg_color = "#002060"; break;
                     case '관리자발주취소': $_bg_color = "#6f6f6f"; break;
                   case '발주취소': $_bg_color = "#6f6f6f"; break;
                    default: break;
                  }
                  echo ('
                    <tr class="step">
                      <td colspan="5" style="text-align:left; padding-left: 15px; background-color:'.$_bg_color.'; border:0px;"> ' . $_txt .' </td>
                      <td colspan="1" style="text-align:right; padding-right: 15px; background-color:'.$_bg_color.'; border:0px;"> ' . "총 ".$cnt_ct_status[$row['ct_status']]."건".' </td>
                    </tr>
                  ');

                }

                //
                // 종료 -->
            ?>
            <tr data-link="partner_purchaseorderinquiry_view.php?od_id=<?=$row['od_id']?>" class="btn_link" data-id="<?=$row['od_id']?>">
              <td class="td_chk">
                <input type="checkbox" name="ct_id[]" value="<?=$row['ct_id']?>">
              </td>
              <td class="td_od_info">
                <p class="info_head">
                  <?=$row['it_name'].($row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '')?> (<?=$row['ct_qty']?>개)
                  <?php 
                  if($row['incompleted']) {
                    echo '<span style="color: #ef7c00; font-size: 12px;"><i class="fa fa-circle" aria-hidden="true"></i></span>';
                  }
                  ?>
                </p>

                <p> 발주일 : <?=date('Y-m-d', strtotime($row['od_time']))?> </p>

                <?php if($row['ct_rdy_date']) { ?>
                <p> 출고준비 : <?=date('Y-m-d (H:i)', strtotime($row['ct_rdy_date']))?> </p>
                <?php } ?>

                <p>입고예정일 : <?php $ct_part_info = json_decode($row['ct_part_info'],true)[1]; $ct_part_info_indt = $ct_part_info['_in_dt'] ? date('Y-m-d', strtotime($ct_part_info['_in_dt'])) : ''; echo $ct_part_info_indt; ?> <!--<?php if(!in_array($row['ct_status'], ['발주취소', '관리자발주취소'])) { ?><a href="./partner_purchaseorderinquiry_view.php?od_id=<?=$row['od_id']?>" class="btn_edit_delivery_info">변경</a><?php }?>--></p>

                <?php if($ct_part_info['_out_dt']) { ?>
                <p> 출고완료 : <?=$ct_part_info['_out_dt']?> </p>
                <?php } ?>

                <p> 발주번호(<?=$row['od_id']?>) </p>
                <img src="<?=THEMA_URL?>/assets/img/icon_link_orderlist.png" class="icon_link">
              </td>
              <td class="td_od_info td_delivery_info">
                <p class="info_head">
                  <?=$row['ct_warehouse']?>
                </p>
                <p>
                  <?=$row['ct_warehouse_address']?>
                </p>
                <p>
                  <?=$row['ct_warehouse_phone']?>
                </p>
              </td>
              <td class="td_status text_c">
                <?php
                if($row['ct_status'] != '발주취소' && $row['ct_status'] != '관리자발주취소') { // 발주취소 건은 담당자 출력 안 함
                    if ($manager_mb_id) {
                        $manager_txt = '미지정';
                        if ($row['od_partner_manager']) {
                            $manager = get_member($row['od_partner_manager']);
                            $manager_txt = '[직원] ' . $manager['mb_name'];
                        }
                        echo "<div>{$manager_txt}</div>";
                    } else {
                        ?>
                        <select class="sel_manager" data-id="<?= $row['od_id'] ?>">
                            <option value="">미지정</option>
                            <?php foreach ($managers as $manager) { ?>
                                <option value="<?= $manager['mb_id'] ?>" <?= get_selected($row['od_partner_manager'], $manager['mb_id']) ?>>
                                    [직원] <?= $manager['mb_name'] ?></option>
                            <?php } ?>
                        </select>
                    <?php }
                }?>
                <span style="<?php
                if(in_array($row['ct_status'], ['발주취소', '관리자발주취소']))
                  echo 'color: #ff0000;';
                ?>">
                  <?=$row['ct_status']?>
                </span>
              </td>
              <td class="text-center">
              <?php if(in_array($row['ct_status'], ['발주취소', '관리자발주취소'])) {?>
                  ―
              <?php } else {?>
                  <?=number_format($row['price'])?>원
              <?php }?>
              </td>
              <td class="td_operation">
                <?php
                  $_ck = false;
                  if(in_array($row['ct_status'], ['발주완료','출고완료','입고완료','마감완료'])) { $_ck = true; }
                  if(!in_array($row['ct_status'], ['발주취소', '관리자발주취소'])) {
                ?>
                <a href="javascript:void(0);" class="btn_delivery_info <?php echo ($row['ct_delivery_num']&&($_ck))? 'disabled' : ''; ?>" data-id="<?=$row['od_id']?>">
                  배송정보
                  <?php if ($row['ct_delivery_num']&&($_ck)) {echo "입력완료";} else {
                      foreach ($orders as $od_info) {
                          if ($od_info['od_id'] == $row['od_id']) {
                              echo "(" . $od_info['inserted_cnt'] . "/" . $od_info['total_cnt'] . ")";
                              break;
                          }
                      };
                  }?>
                </a>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">  
          <?php echo apms_paging(5, $page, $total_page, $qstr.'&amp;page='); ?>
        </ul>
      </div>
    </div>
    </form>
  </div>
</section>

<div id="change_wrap">
  <form id="form_change_date">
    <div class="title">입고예상일시</div>
    <input type="hidden" name="od_id">
    <input type="hidden" name="ct_id">
    <input type="text" name="ct_delivery_expect_date" class="change_datepicker">
    <select name="ct_delivery_expect_time">
      <?php
      for($i = 0; $i < 24; $i++) {
        $time = str_pad($i, 2, '0', STR_PAD_LEFT);
        echo '<option value="'.$time.'">'.$time.'시</option>';
      }
      ?>
    </select>
    <button class="btn_submit">확인</button>
  </form>
</div>

<div id="popup_box">
  <div></div>
</div>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

function checkCtStatusAll() {
  var total = $('input[name="ct_status[]"]').length;
  var checkedTotal = $('input[name="ct_status[]"]:checked').length;
  $("#chk_ct_status_all").prop('checked', total <= checkedTotal); 
}

function checkIncompletedAll() {
  var total = $('input[name="incompleted[]"]').length;
  var checkedTotal = $('input[name="incompleted[]"]:checked').length;
  $("#chk_incompleted_all").prop('checked', total <= checkedTotal); 
}

$(function() {
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

  checkIncompletedAll();
  // 작업상태 전체 선택 체크박수
  $('#chk_incompleted_all').click(function() {
    var checked = $(this).is(':checked');
    $('input[name="incompleted[]"]').prop('checked', checked);
  });
  // 작업상태 체크박스
  $('input[name="incompleted[]"]').click(function() {
    checkIncompletedAll();
  });

  checkCtStatusAll();
  // 주문상태 전체 선택 체크박스
  $('#chk_ct_status_all').click(function() {
    var checked = $(this).is(':checked');
    $('input[name="ct_status[]"]').prop('checked', checked);
  });
  // 주문상태 체크박스
  $('input[name="ct_status[]"]').click(function() {
    checkCtStatusAll();
  });

  // 클릭 시 주문상세 이동
  $('tr.btn_link').click(function(e) {
    if($('.popModal').has(e.target).length)
      return;
    
    if($(e.target).closest('.td_status').length > 0)
      return;

    var link = $(this).data('link');
    window.location.href = link;
  });

  // 입고예상일 변경 datepicker
  $('.change_datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99"
  });

  // 입고예상일 변경
  $('.btn_change').click(function(e) {
    e.stopPropagation();

    $form = $('#form_change_date');
    $form.find('input[name="od_id"]').val($(this).data('odid'));
    $form.find('input[name="ct_id"]').val($(this).data('ctid'));
    $form.find('input[name="ct_delivery_expect_date"]').val($(this).data('date'));
    $form.find('select[name="ct_delivery_expect_time"]').val($(this).data('time')).change();

    $(this).popModal({
      html: $form,
      placement: 'bottomLeft',
      showCloseBut: false
    });
  });
  $('#form_change_date').submit(function(e) {
    e.preventDefault();

    var od_id = $(this).find('input[name="od_id"]').val();
    var ct_id = $(this).find('input[name="ct_id"]').val();

    var send_data = {};
    send_data['od_id'] = od_id;
    send_data['ct_id'] = [ ct_id ];
    send_data['ct_delivery_expect_date_' + ct_id] = $(this).find('input[name="ct_delivery_expect_date"]').val();
    send_data['ct_delivery_expect_time_' + ct_id] = $(this).find('select[name="ct_delivery_expect_time"]').val();

    $.post('ajax.supply_partner_deliverydate.php', send_data, 'json')
    .done(function() {
      alert('변경이 완료되었습니다.');
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 기간 - datepicker
  $('.datepicker').datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "yy-mm-dd",
    showButtonPanel: true,
    yearRange: "c-99:c+99",
    maxDate: "+0d"
  });

  // 기간 - 이번달 버튼
  $('#select_date_thismonth').click(function(e) {
    e.preventDefault();

    var today = new Date(); // 오늘
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 이번달 1일
    $('#fr_date').val(formatDate(today));
  });
  // 기간 - 저번달 버튼
  $('#select_date_lastmonth').click(function(e) {
    e.preventDefault();

    var today = new Date();
    today.setDate(0); // 지난달 마지막일
    $('#to_date').val(formatDate(today));
    today.setDate(1); // 지난달 1일
    $('#fr_date').val(formatDate(today));
  });

  // 작업지시서 버튼
  $('.btn_instructor').click(function(e) {
    e.stopPropagation();
  });

  // 배송정보 버튼
  $('.btn_delivery_info').click(function(e) {
    e.preventDefault();
    e.stopPropagation();

    var od_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.supply_partner_deliveryinfo.php?od_id=' + od_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 바코드 버튼
  // $('.btn_barcode_info').click(function(e) {
  //   e.preventDefault();
  //   e.stopPropagation();
  //
  //   var ct_id = $(this).data('id');
  //   $("body").addClass('modal-open');
  //   $("#popup_box > div").html('<iframe src="popup.partner_barcodeinfo.php?ct_id=' + ct_id + '">');
  //   $("#popup_box iframe").load(function() {
  //     $("#popup_box").show();
  //   });
  // });

  // 마우스 오버시 같은 주문 강조
  $('tr.btn_link').hover(
    function() {
      var od_id = $(this).data('id');
      var $tr = $('tr.btn_link[data-id="' + od_id + '"]');
      if($tr.length >= 2)
        $tr.addClass('hover');
    },
    function() {
      var od_id = $(this).data('id');
      $('tr.btn_link[data-id="' + od_id + '"]').removeClass('hover');
    }
  );

  // 체크박스 클릭
  $('#chk_all').click(function() {
    if($(this).prop('checked')) {
      $('input[name="ct_id[]"]').prop('checked', true);
    } else {
      $('input[name="ct_id[]"]').prop('checked', false);
    }
  });
  $('input[name="ct_id[]"]').click(function() {
    var $chk_all = $('#chk_all');
    if($('input[name="ct_id[]"]').length === $('input[name="ct_id[]"]:checked').length)
      $chk_all.prop('checked', true);
    else
      $chk_all.prop('checked', false);
  });
  $('.td_chk').click(function(e) {
    e.stopPropagation();
    if(e.target !== $(this).find('input[name="ct_id[]"]')[0])
      $(this).find('input[name="ct_id[]"]').click();
  });

  // 주문상태 변경
  $('#btn_ct_status').click(function() {
    $('#form_ct_status').submit();
  });
  $('#form_ct_status').on('submit', function(e) {
    e.preventDefault();

    // 주문상태 변경
    if($('select[name="ct_status"]').val() == '발주취소' && !confirm('주문취소 후 상태 변경은 불가능합니다. 취소하시겠습니까?')) {
      return false;
    }

    var ct_status_mode = $('input[name="ct_status_mode"]:checked').val();

    if(ct_status_mode == 'manager') {
      // 담당자지정
      $.post('ajax.supply_partner_manager_bulk.php', $(this).serialize(), 'json')
      .done(function() {
        alert('담당자 지정이 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    } else {
      // 주문상태 변경
      if($('select[name="ct_status"]').val() == '발주취소' && !confirm('주문취소 후 상태 변경은 불가능합니다. 취소하시겠습니까?')) {
        return false;
      }

      $.post('ajax.supply_partner_ctstatus.php', $(this).serialize(), 'json')
      .done(function() {
        alert('주문상태 변경이 완료되었습니다.');
        window.location.reload();
      })
      .fail(function($xhr) {
        var data = $xhr.responseJSON;
        alert(data && data.message);
      });
    }
  });

  // 엑셀 다운로드
  $('#btn_excel').click(function() {
    alert("준비중")
    // var ct_id = [];
    // var $item = $("input[name='ct_id[]']:checked");
    // $item.each(function() {
    //   ct_id.push($(this).val());
    // });
    //
    // if(!ct_id.length)
    //   return alert('선택한 주문이 없습니다.');
    //
    // $.fileDownload('partner_order_excel.php', {
    //   httpMethod: "POST",
    //   data: {
    //     ct_id: ct_id
    //   }
    // });
  });

  // 담당자 선택
  var loading_manager = false;
  $('.sel_manager').change(function() {
    if(loading_manager)
      return alert('로딩중입니다. 잠시후 다시 시도해주세요.');
    
    var od_id = $(this).data('id');
    var manager = $(this).val();
    var manager_name = $(this).find('option:selected').text();
    
    loading_manager = true;
    $.post('ajax.supply_partner_manager.php', {
      od_id: od_id,
      manager: manager
    }, 'json')
    .done(function() {
      $('.sel_manager[data-id="' + od_id + '"]').val(manager);
      alert(manager_name + ' 담당자로 변경되었습니다.');
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    })
    .always(function() {
      loading_manager = false;
    })
  });

  function check_ct_status_mode() {
    var ct_status_mode = $('input[name="ct_status_mode"]:checked').val();
    if(ct_status_mode == 'manager') {
      // 담당자 지정
      $('select[name="ct_status"]').hide();
      $('select[name="manager"]').show();
    } else {
      // 주문상태
      $('select[name="ct_status"]').show();
      $('select[name="manager"]').hide();
    }
  }
  $('input[name="ct_status_mode"]').change(function() {
    check_ct_status_mode();
  });

  check_ct_status_mode();

});
</script>

<?php
include_once('./_tail.php');
?>
