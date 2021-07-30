<?php
include_once('./_common.php');

if(!$is_samhwa_partner) {
  alert("파트너 회원만 접근 가능한 페이지입니다.");
}

$g5['title'] = "파트너 주문내역";
include_once("./_head.php");

$where = [];

// 주문상태
$ct_status = $_GET['ct_status'];
$ct_steps = ['준비', '출고준비', '배송', '완료'];
if($ct_status) {
  $ct_steps = array_intersect($ct_steps, $ct_status);
}
$where[] = " ( ct_status = '".implode("' OR ct_status = '", $ct_steps)."' ) ";

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

$sql_order = " ORDER BY FIELD(ct_status, '" . implode("' , '", $ct_steps) . "' ), ct_move_date desc, od_id desc ";

$result = sql_query("
  SELECT
    c.od_id,
    od_time,
    mb_entNm,
    it_name,
    ct_option,
    ct_qty,
    ct_status,
    prodMemo,
    ct_is_direct_delivery,
    ct_direct_delivery_price,
    ct_move_date,
    ct_ex_date
  {$sql_common}
  {$sql_order}
  {$sql_limit}
", true);

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
    $ct_direct_delivery_text = '배송/설치';
  }
  $row['ct_direct_delivery'] = $ct_direct_delivery_text;

  $price = intval($row['ct_direct_delivery_price']) * intval($row['ct_qty']);
  // 공급가액
  $price_p = @round(($price ?: 0) / 1.1);
  // 부가세
  $price_s = @round(($price ?: 0) / 1.1 / 10);

  $row['price_p'] = $price_p;
  $row['price_s'] = $price_s;

  $orders[] = $row;
}
?>

<section class="wrap">
  <div class="sub_section_tit">주문내역</div>
  <form method="get">
    <div class="search_box">
      <label><input type="checkbox" name="ct_status[]" value="all" <?=option_array_checked('all', $ct_status)?>/> 전체</label>, 
      <label><input type="checkbox" name="ct_status[]" value="준비" <?=option_array_checked('준비', $ct_status)?>/> 상품준비</label>, 
      <label><input type="checkbox" name="ct_status[]" value="출고준비" <?=option_array_checked('출고준비', $ct_status)?>/> 출고준비</label>, 
      <label><input type="checkbox" name="ct_status[]" value="배송" <?=option_array_checked('배송', $ct_status)?>/> 출고완료</label>, 
      <label><input type="checkbox" name="ct_status[]" value="완료" <?=option_array_checked('완료', $ct_status)?>/> 배송완료</label><br>
      
      <div class="search_date">
      	<select name="searchtype">
	        <option >주문일</option>
	        <option >출고완료일</option>
	      </select>
        <input type="text" name="fr_date" value="<?=$fr_date?>" id="fr_date" class="datepicker"/> ~ <input type="text" name="to_date" value="<?=$to_date?>" id="to_date" class="datepicker"/>
        <a href="#" id="select_date_thismonth">이번달</a>
        <a href="#" id="select_date_lastmonth">저번달</a>
      </div>
      <select name="searchtype">
        <option >사업소명</option>
        <option >품목명</option>
        <option >주문번호</option>
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
              <th>주문일</th>
              <th>주문번호</th>
              <th>사업소</th>
              <th>품목명</th>
              <th>수량</th>
              <th>상태</th>
              <th>위탁내용</th>
              <th>요청사항</th>
              <th>공급가액</th>
              <th>부가세</th>
              <th>출고예정일</th>
              <th>출고완료일</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($orders as $row) { ?>
            <tr onclick="window.location.href='partner_orderinquiry_view.php?od_id=<?=$row['od_id']?>'" class="btn_link">
              <td class="text_c"><?=date('Y-m-d', strtotime($row['od_time']))?></td>
              <td class="text_c"><?=$row['od_id']?></td>
              <td class="text_c"><?=$row['mb_entNm']?></td>
              <td><?=$row['it_name'].($row['ct_option'] ? " ({$row['ct_option']})" : '')?></td>
              <td class="text_c"><?=$row['ct_qty']?></td>
              <td class="text_c"><?=$row['ct_status']?></td>
              <td class="text_c"><?=$row['ct_direct_delivery']?></td>
              <td><?=$row['prodMemo']?></td>
              <td class="text_r"><?=number_format($row['price_p'])?>원</td>
              <td class="text_r"><?=number_format($row['price_s'])?>원</td>
              <td class="text_c"><?=date('Y-m-d', strtotime($row['ct_move_date']))?></td>
              <td class="text_c"><?=$row['ct_ex_date']?></td>
            </tr>
            <?php } ?>
            <!--<tr onclick="location.href='partner_orderinquiry_view.php'" class="btn_link">
              <td class="text_c">2021-02-02</td>
              <td class="text_c">1234</td>
              <td class="text_c">ABC</td>
              <td>123(option)</td>
              <td class="text_c">1</td>
              <td class="text_c">주문접수</td>
              <td class="text_c">배송</td>
              <td>주문시 입력한 요청사항</td>
              <td class="text_r">10,000원</td>
              <td class="text_r">1,000원</td>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">2021-02-02</td>
            </tr>
            <tr onclick="location.href='partner_orderinquiry_view.php'" class="btn_link">
              <td class="text_c">2021-02-02</td>
              <td class="text_c">1234</td>
              <td class="text_c">ABC</td>
              <td>123(option)</td>
              <td class="text_c">1</td>
              <td class="text_c">주문접수</td>
              <td class="text_c">배송/설치</td>
              <td> </td>
              <td class="text_r">10,000원</td>
              <td class="text_r">1,000원</td>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">2021-02-02</td>
            </tr>-->
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

<script>
$(function() {
  // 주문상태 체크박스
  $('input[name="ct_status[]"]').click(function() {
    var val = $(this).val();
    var checked = $(this).is(':checked');

    // 전체
    if(val == 'all') {
      $('input[name="ct_status[]"]').prop('checked', checked);
      return;
    }

    if(!checked) {
      $('input[name="ct_status[]"][value="all"]').prop('checked', false);
    }
  });
});
</script>

<?php
include_once('./_tail.php');
?>
