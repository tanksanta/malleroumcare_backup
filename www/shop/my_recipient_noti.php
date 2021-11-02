<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "수급자 활동 알림";
include_once("./_head.php");

$sql_common = "
  FROM
    recipient_noti
  WHERE
    mb_id = '{$member['mb_id']}'
";

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ")['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$noti_result = sql_query("
  SELECT
    *
  {$sql_common}
  ORDER BY
    rn_id DESC
  {$sql_limit}
");

$noti = [];
for($i = 0; $row = sql_fetch_array($noti_result); $i++) {
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
  $noti[] = $row;
}
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
          <a href="#" id="btn_check_all" class="btn_gray_box">모두확인</a>
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
            <?php if(!$noti) { ?>
            <tr>
              <td colspan="5" class="empty_table">알림이 없습니다.</td>
            </tr>
            <?php } ?>
            <?php
            foreach($noti as $row) {
              $limit = get_pen_category_limit($row['penLtmNum'], $row['ca_id']);
              if($limit) {
                $cur = intval($limit['num']) - intval($limit['current']);
                if($cur < 0) $cur = 0;
              } else {
                $cur = 0;
              }
            ?>
            <tr <?=($row['rn_checked_yn'] == 'Y' ? 'class="text_c"' : '')?>>\
              <td class="text_c"><?=date('Y-m-d', strtotime($row['rn_created_at']))?></td>
              <td class="text_c text_<?=(substr($row['ca_id'], 0, 2) == '10' ? 'orange' : 'green')?>"><?=(substr($row['ca_id'], 0, 2) == '10' ? '판매' : '대여')?></td>
              <td>‘<?=$row['ca_name']?>’ 품목 <?=$row['qty']?>개 사용가능햇수가 <?=date('m월 d일', strtotime($row['end_date']))?> 만료됩니다. 만료 후 해당 품목 <?=$cur?>개 주문이 가능합니다.</td>
              <td class="text_c"><a href="#" class="btn_gray_box btn_<?=($row['rn_checked_yn'] == 'Y' ? 'cancel' : 'check')?>" data-id="<?=$row['rn_id']?>"><?=($row['rn_checked_yn'] == 'Y' ? '확인취소' : '확인')?></a></td>
            </tr>
            <?php } ?>
            <!--<tr>
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
              <td>‘욕창예방메트리스’ 품목 1개 대여기간이 7월 20일 종료됩니다. 종료 후 해당 품목 3개 대여가 가능합니다. </td>
              <td class="text_c"><a href="#" class="btn_gray_box">확인취소</a></td>
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
  // 모두확인 버튼
  $('#btn_check_all').click(function(e) {
    e.preventDefault();
    $.post('my_recipient_noti.php', {
      m: 'a'
    }, 'json')
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 확인 버튼
  $('.btn_check').click(function(e) {
    e.preventDefault();

    var rn_id = $(this).data('id');

    $.post('my_recipient_noti.php', {
      rn_id: rn_id
    }, 'json')
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  // 확인취소 버튼
  $('.btn_cancel').click(function(e) {
    e.preventDefault();

    var rn_id = $(this).data('id');

    $.post('my_recipient_noti.php', {
      m: 'd',
      rn_id: rn_id
    }, 'json')
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });
});
</script>

<?php
include_once('./_tail.php');
?>
