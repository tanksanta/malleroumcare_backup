<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "수급자 활동 알림";
include_once("./_head.php");



?>

<section class="wrap">
  <div class="sub_section_tit">수급자 활동 알림</div>
  <form method="get">
    <div class="search_box">
      
      <select name="searchtype">
        <option >수급자명</option>
        <option >품목분류명</option>
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
        알림 목록
        <div class="r_area">
          <a href="#" class="btn_gray_box">모두확인</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>일시</th>
              <th>수급자</th>
              <th>급여</th>
              <th>내용</th>
              <th>확인여부</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text_c">2021-02-02</td>
              <td class="text_c">홍길동(L11111*****)</td>
              <td class="text_c text_orange">판매</td>
              <td>‘욕창예방메트리스’ 품목 1개 사용가능햇수가 7월 20일 만료됩니다. 만료 후 해당 품목 3개 주문이 가능합니다. </td>
              <td class="text_c"><a href="#" class="btn_gray_box">확인</a></td>
            </tr>
            <tr class="bg_gray">
              <td class="text_c">2021-02-02</td>
              <td class="text_c">홍길동(L11111*****)</td>
              <td class="text_c text_green">대여</td>
              <td>‘욕창예방메트리스’ 품목 1개 사용가능햇수가 7월 20일 만료됩니다. 만료 후 해당 품목 3개 주문이 가능합니다. </td>
              <td class="text_c"><a href="#" class="btn_gray_box">확인취소</a></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="list-paging">
        <ul class="pagination pagination-sm en">
          <?php echo apms_paging(5, $page, $total_page, '?'.$qstr.'&amp;page='); ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<script>
function formatDate(date) {
  var y = date.getFullYear();
  var m = date.getMonth() + 1; // Month from 0 to 11
  var d = date.getDate();
  return '' + y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);
}

$(function() {
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
});
</script>

<?php
include_once('./_tail.php');
?>
