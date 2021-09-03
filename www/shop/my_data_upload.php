<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "판매/대여 정보 등록관리";
include_once("./_head.php");

$sql_common = "
  FROM
    stock_data_upload
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

$result = sql_query("
  SELECT
    *
  {$sql_common}
  ORDER BY
    sd_id DESC
  {$sql_limit}
");

$uploads = [];
for($i = 0; $row = sql_fetch_array($result); $i++) {
  $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
  $uploads[] = $row;
}

add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 5);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 6);
?>

<style>
#upload_wrap { display: none; }
.popModal #upload_wrap { display: block; }
.popModal .popModal_content { margin: 0 !important; }
</style>

<section class="wrap">
  <div class="sub_section_tit">과거공단자료 업로드</div>
  <form method="get">
    <div class="search_box">
      
      <select name="searchtype">
        <option >수급자명</option>
        <option >요양인정번호</option>
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
        <div class="r_area r_btn_area">
          <div class="tooltip_btn">
            <a href="#" id="btn_nhis" class="btn_nhis">
              공단 판매/대여 자료 엑셀업로드
              <span class="question">?</span>
            </a>
            <div class="btn_tooltip" style="width:350px;">
              과거공단자료 업로드 방법<br>
              <br>
              1. 장기요양정보시스템 로그인<br>
              2. 복지용구계약 > 계약내역 > 복지용구 계약내역 조회<br>
              3. 우측 상단 “엑셀” 클릭하여 파일 엑셀 다운로드<br>
              4. 다운로드된 엑셀파일<br>
              <br>
              <a href="https://blog.naver.com/poongki_/222493460005" class="blog" target="_blank">도움말보기<img src="<?php echo G5_URL; ?>/img/icon_blog_naver.png" /></a>
            </div>
          </div>
        </div>
      </div>
          
      <?php
      if (!get_tutorial('satin_list_tooltip')) { 
        set_tutorial('satin_list_tooltip', 1);
      ?>
      <script>
        $(document).ready(function(){
          $('.tooltip_btn .btn_tooltip').fadeIn(1000);
          setTimeout(function() {
            $('.tooltip_btn .btn_tooltip').fadeOut(1000, function() {
              $('.tooltip_btn .btn_tooltip').css('display', '');
            });
          }, 4000);
        });
      </script>
      <?php } ?>

      <div class="table_box" style="clear:both">
        <table>
          <thead>
            <tr>
              <th>No.</th>
              <th>수급자</th>
              <th>주문등록번호</th>
              <th>품목명/제품명/제품코드</th>
              <!-- <th>제품코드</th> -->
              <th>급여</th>
              <th>계약등록일</th>
              <th>판매일자/대여기간</th>
              <th>매칭여부</th>
              <th>삭제</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($uploads as $row) { ?>
            <tr>
              <td class="text_c"><?=$row['index']?></td>
              <td class="text_c"><?=$row['sd_pen_nm']?>(<?=$row['sd_pen_ltm_num']?>)</td>
              <td class="text_c"><?=$row['sd_pen_jumin']?></td>
              <td class="text_c"><?="{$row['sd_ca_name']}/{$row['sd_it_name']}"?> <br><?="{$row['sd_it_code']}-{$row['sd_it_barcode']}"?></td>
              <!-- <td class="text_c"><?="{$row['sd_it_code']}-{$row['sd_it_barcode']}"?></td> -->
              <td class="text_c text_<?=($row['sd_gubun'] == '00' ? 'orange' : 'green')?>"><?=($row['sd_gubun'] == '00' ? '판매' : '대여')?></td>
              <td class="text_c"><?=$row['sd_contract_date']?></td>
              <td class="text_c"><?=$row['sd_sale_date']?><?=($row['sd_rent_date'] != '0000-00-00' ? " ~ {$row['sd_rent_date']}" : '')?></td>
              <td class="text_c"><?=$row['sd_status'] == 0 ? '대기' : '매칭완료'?></td>
              <td class="text_c"><?php if($row['sd_status'] == 0) { ?><a href="#" class="btn_gray_box btn_delete" data-id="<?=$row['sd_id']?>">삭제</a><?php } ?></td>
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

<div id="upload_wrap">
  <form id="form_nhis" style="font-size: 14px;">
    <div class="form-group">
      <label for="datafile">판매/대여 자료 업로드</label>
      <input type="file" name="datafile" id="datafile">
      <p class="help-block">공단 판매/대여 자료를 업로드해주세요.</p>
    </div>
    <button type="submit" class="btn btn-primary">업로드</button>
  </form>
</div>

<script>
$(function() {
  $('#btn_nhis').click(function(e) {
    e.preventDefault();

    $(this).popModal({
      html: $('#form_nhis'),
      placement: 'bottomRight'
    });
  });

  $('#form_nhis').on('submit', function(e) {
    e.preventDefault();

    var fd = new FormData(document.getElementById("form_nhis"));
    $.ajax({
      url: 'ajax.my_data_upload.php',
      type: 'POST',
      data: fd,
      cache: false,
      processData: false,
      contentType: false,
      dataType: 'json'
    })
    .done(function() {
      window.location.reload();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
  });

  $(document).on('click', '.btn_delete', function(e) {
    e.preventDefault();

    if(!confirm('정말 삭제하시겠습니까?')) return;

    var sd_id = $(this).data('id');
    $.post('ajax.my_data_upload.delete.php', {
      'sd_id': sd_id
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
