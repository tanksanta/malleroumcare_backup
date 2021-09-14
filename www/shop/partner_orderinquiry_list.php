<?php
include_once('./_common.php');

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$g5['title'] = "파트너 주문내역";
include_once("./_head.php");

$where = [];

# 기간
$sel_date = in_array($sel_date, ['od_time', 'ct_ex_date']) ? $sel_date : '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';
if($sel_date && $fr_date && $to_date)
  $where[] = " ( {$sel_date} between '$fr_date 00:00:00' and '$to_date 23:59:59') ";

# 주문상태
$ct_status = $_GET['ct_status'];
$ct_steps = ['준비', '출고준비', '배송', '완료', '취소', '주문무효'];
if($ct_status) {
  $ct_steps = array_intersect($ct_steps, $ct_status);
}
$where[] = " ( ct_status = '".implode("' OR ct_status = '", $ct_steps)."' ) ";

# 검색어
$sel_field = in_array($sel_field, ['mb_entNm', 'it_name', 'c.od_id']) ? $sel_field : '';
$search = get_search_string($search);
if($sel_field && $search) {
  $where[] = " {$sel_field} like '%{$search}%' ";
}

$sql_search = ' and '.implode(' and ', $where);

$sql_common = "
  FROM
    {$g5['g5_shop_cart_table']} c
  LEFT JOIN
    {$g5['g5_shop_order_table']} o ON c.od_id = o.od_id
  LEFT JOIN
    {$g5['member_table']} m ON c.mb_id = m.mb_id
  WHERE
    od_del_yn = 'N' and
    ct_is_direct_delivery IN(1, 2) and
    ct_direct_delivery_partner = '{$member['mb_id']}'
    {$sql_search}
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$sql_order = " ORDER BY FIELD(ct_status, '" . implode("' , '", $ct_steps) . "' ), ct_direct_delivery_date asc, od_id desc ";

$result = sql_query("
  SELECT
    ct_id,
    c.od_id,
    od_time,
    mb_entNm,
    od_b_name,
    od_b_addr1,
    od_b_addr2,
    od_b_addr3,
    od_b_addr_jibeon,
    it_name,
    ct_option,
    ct_qty,
    ct_status,
    prodMemo,
    c.stoId,
    ct_is_direct_delivery,
    ct_direct_delivery_price,
    ct_direct_delivery_date,
    ct_ex_date
  {$sql_common}
  {$sql_order}
  {$sql_limit}
");

$orders = [];
while($row = sql_fetch_array($result)) {
  $ct_status_text = $row['ct_status'];
  switch ($ct_status_text) {
    case '보유재고등록': $ct_status_text="보유재고등록"; break;
    case '재고소진': $ct_status_text="재고소진"; break;
    case '주문무효': $ct_status_text="주문무효"; break;
    case '취소': $ct_status_text="주문취소"; break;
    case '주문': $ct_status_text="상품주문"; break;
    case '입금': $ct_status_text="입금완료"; break;
    case '준비': $ct_status_text="상품준비"; break;
    case '출고준비': $ct_status_text="출고준비"; break;
    case '배송': $ct_status_text="출고완료"; break;
    case '완료': $ct_status_text="배송완료"; break;
  }
  $row['ct_status'] = $ct_status_text;

  $ct_direct_delivery_text = '배송';
  if($row['ct_is_direct_delivery'] == '2') {
    $ct_direct_delivery_text = '설치';
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_direct_delivery_price']) * intval($row['ct_qty']);
  // 공급가액
  $price_p = @round(($price ?: 0) / 1.1);
  // 부가세
  $price_s = @round(($price ?: 0) / 1.1 / 10);

  $row['price'] = $price;
  $row['price_p'] = $price_p;
  $row['price_s'] = $price_s;

  // 배송정보
  $total_cnt_result = sql_fetch("
    SELECT count(*) as cnt
    FROM {$g5['g5_shop_cart_table']}
    WHERE
    od_id = '{$row['od_id']}' and
    ct_direct_delivery_partner = '{$member['mb_id']}'
  ");
  $row['total_cnt'] = $total_cnt_result['cnt'] ?: 0;

  $inserted_cnt_result = sql_fetch("
    SELECT count(*) as cnt
    FROM {$g5['g5_shop_cart_table']}
    WHERE
    od_id = '{$row['od_id']}' and
    ct_direct_delivery_partner = '{$member['mb_id']}' and
    ct_delivery_num <> '' and ct_delivery_num is not null
  ");
  $row['inserted_cnt'] = $inserted_cnt_result['cnt'] ?: 0;

  $orders[] = $row;
}

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<style>
.td_od_info { width: unset !important; text-align: left !important; }
.td_od_info p { margin: 0; font-size: 12px; color: #666; line-height: 1.25; }
.td_od_info p.info_head { font-size: 14px; color: #333; font-weight: bold; line-height: 1.5; }
.td_od_info span.info_delivery { display: inline-block; vertical-align: bottom; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.td_operation { width: 150px }
.td_operation a + a { margin-top: 5px; }
.td_operation a { display: block; border: 1px solid #ddd; background: #fff; padding: 5px 8px; color: #666; border-radius: 3px; font-size: 12px; text-align: center; line-height: 15px; }

#popup_box { position: fixed; width: 100vw; height: 100vh; left: 0; top: 0; z-index: 99999999; background-color: rgba(0, 0, 0, 0.6); display: table; table-layout: fixed; opacity: 0; }
#popup_box > div { width: 100%; height: 100%; display: table-cell; vertical-align: middle; }
#popup_box iframe { position: relative; width: 500px; height: 700px; border: 0; background-color: #FFF; left: 50%; margin-left: -250px; }

@media (max-width : 750px) {
  #popup_box iframe { width: 100%; height: 100%; left: 0; margin-left: 0; }
}
</style>

<section class="wrap">
  <div class="sub_section_tit">주문내역</div>
  <form method="get">
    <div class="search_box">
      <label><input type="checkbox" id="chk_ct_status_all"/> 전체</label> 
      <label><input type="checkbox" name="ct_status[]" value="준비" <?=option_array_checked('준비', $ct_status)?>/> 상품준비</label> 
      <label><input type="checkbox" name="ct_status[]" value="출고준비" <?=option_array_checked('출고준비', $ct_status)?>/> 출고준비</label> 
      <label><input type="checkbox" name="ct_status[]" value="배송" <?=option_array_checked('배송', $ct_status)?>/> 출고완료</label> 
      <label><input type="checkbox" name="ct_status[]" value="완료" <?=option_array_checked('완료', $ct_status)?>/> 배송완료</label>
      <label><input type="checkbox" name="ct_status[]" value="취소" <?=option_array_checked('취소', $ct_status)?>/> 주문취소</label> 
      <label><input type="checkbox" name="ct_status[]" value="주문무효" <?=option_array_checked('주문무효', $ct_status)?>/> 주문무효</label> 
      <br>
      
      <div class="search_date">
        <select name="sel_date">
          <option value="od_time" <?=get_selected($sel_date, 'od_time')?>>주문일</option>
          <option value="ct_ex_date" <?=get_selected($sel_date, 'ct_ex_date')?>>출고완료일</option>
        </select>
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="sel_field">
        <option value="mb_entNm" <?=get_selected($sel_field, 'mb_entNm')?>>사업소명</option>
        <option value="it_name" <?=get_selected($sel_field, 'it_name')?>>품목명</option>
        <option value="c.od_id" <?=get_selected($sel_field, 'c.od_id')?>>주문번호</option>
      </select>
      <div class="input_search">
        <input name="search" value="<?=$_GET["search"]?>" type="text">
        <button type="submit"></button>
      </div>
    </div>
  </form>
  <div class="inner">
    <div class="list_box">
      <div class="subtit">
        목록
        <div class="r_area">
          <!-- <a href="#" class="btn_gray_box">모두확인</a> -->
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr >
              <th>주문정보</th>
              <th>위탁정보</th>
              <th>상태</th>
              <th>수수료</th>
              <th>관리</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if(!$orders) echo '<tr><td colspan="5" class="empty_table">내역이 없습니다.</td></tr>';
            foreach($orders as $row) { 
            ?>
            <tr onclick="window.location.href='partner_orderinquiry_view.php?od_id=<?=$row['od_id']?>'" class="btn_link">
              <td class="td_od_info">
                <p class="info_head">
                  <?=$row['it_name'].($row['ct_option'] && $row['ct_option'] != $row['it_name'] ? " ({$row['ct_option']})" : '')?> (<?=$row['ct_qty']?>개)
                </p>
                <p>
                  주문일시 : 
                  <?=date('Y-m-d', strtotime($row['od_time']))?>
                </p>
                <p>
                  출고예정 : 
                  <?=date('Y-m-d H시', strtotime($row['ct_direct_delivery_date']))?>
                </p>
                <?php if($row['ct_ex_date']) { ?>
                <p>
                  출고완료 : 
                  <?=$row['ct_ex_date']?>
                </p>
                <?php } ?>
                <p>
                  주문번호(<?=$row['od_id']?>)
                </p>
              </td>
              <td class="td_od_info td_delivery_info">
                <p class="info_head">
                  사업소 : 
                  <?=$row['mb_entNm']?>
                </p>
                <p>
                  위탁내용 (
                  <span class="info_delivery">
                    <?=$row['ct_direct_delivery']?> : <?=$row['od_b_name']?> / 
                    <?=print_address($row['od_b_addr1'], $row['od_b_addr2'], $row['od_b_addr3'], $row['od_b_addr_jibeon'])?>
                  </span>
                  )
                </p>
                <p>
                  요청사항 : 
                  <span class="info_delivery">
                    <?=$row['prodMemo']?>
                  </span>
                </p>
              </td>
              <td class="text_c">
                <span style="<?php
                if(in_array($row['ct_status'], ['주문취소', '주문무효']))
                  echo 'color: #ff0000;';
                ?>">
                  <?=$row['ct_status']?>
                </span>
              </td>
              <td class="text_r">
                <?=number_format($row['price'])?>원
              </td>
              <td class="td_operation">
                <a href="partner_orderinquiry_excel.php?od_id=<?=$row['od_id']?>" class="btn_instructor">작업지시서 다운로드</a>
                <a href="javascript:void(0);" class="btn_delivery_info" data-id="<?=$row['od_id']?>">
                  배송정보 (<?=$row['inserted_cnt']?>/<?=$row['total_cnt']?>)
                </a>
                <a href="javascript:void(0);" class="btn_barcode_info" data-id="<?=$row['ct_id']?>">
                  <img src="/skin/apms/order/new_basic/image/icon_02.png" alt="">
                  바코드
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">  
          <?php echo apms_paging(5, $page, $total_page, '?page='); ?>
        </ul>
      </div>
    </div>
  </div>
</section>

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

$(function() {
  $("#popup_box").hide();
  $("#popup_box").css("opacity", 1);

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
    $("#popup_box > div").html('<iframe src="popup.partner_deliveryinfo.php?od_id=' + od_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });

  // 바코드 버튼
  $('.btn_barcode_info').click(function(e) {
    e.preventDefault();
    e.stopPropagation();

    var ct_id = $(this).data('id');
    $("body").addClass('modal-open');
    $("#popup_box > div").html('<iframe src="popup.partner_barcodeinfo.php?ct_id=' + ct_id + '">');
    $("#popup_box iframe").load(function() {
      $("#popup_box").show();
    });
  });
});
</script>

<?php
include_once('./_tail.php');
?>
