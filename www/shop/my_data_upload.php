<?php
include_once('./_common.php');

if(!$member['mb_id']) {
  alert("접근 권한이 없습니다.");
  exit;
}

$g5['title'] = "판매/대여 정보 등록관리";
include_once("./_head.php");

add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 5);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 6);
?>

<style>
#upload_wrap { display: none; }
.popModal #upload_wrap { display: block; }
.popModal .popModal_content { margin: 0 !important; }
</style>

<section class="wrap">
  <div class="sub_section_tit">판매/대여 정보 등록관리</div>
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
          <a href="#" id="btn_nhis" class="btn_nhis">건보 판매/대여 자료 업로드</a>
        </div>
      </div>
      <div class="table_box">
        <table>
          <thead>
            <tr>
              <th>No.</th>
              <th>수급자</th>
              <th>주문등록번호</th>
              <th>품목명/제품명</th>
              <th>제품코드</th>
              <th>급여</th>
              <th>계약등록일</th>
              <th>판매일자/대여기간</th>
              <th>매칭여부</th>
              <th>삭제</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text_c">2</td>
              <td class="text_c">홍길동(L1709001651)</td>
              <td class="text_c">000000-1*****</td>
              <td class="text_c">욕창예방 매트리스/YB-1104A</td>
              <td class="text_c">H12060031003-200200001435</td>
              <td class="text_c text_orange">판매</td>
              <td class="text_c">2020-08-24</td>
              <td class="text_c">2020-08-24</td>
              <td class="text_c">매칭완료</td>
              <td class="text_c"><a href="#" class="btn_gray_box">삭제</a></td>
            </tr>
            <tr>
              <td class="text_c">1</td>
              <td class="text_c">홍길동(L1709001651)</td>
              <td class="text_c">000000-1*****</td>
              <td class="text_c">욕창예방 매트리스/YB-1104A</td>
              <td class="text_c">H12060031003-200200001435</td>
              <td class="text_c text_green">대여</td>
              <td class="text_c">2020-08-24</td>
              <td class="text_c">2020-08-24 ~ 2020-08-24</td>
              <td class="text_c">대기</td>
              <td class="text_c"><a href="#" class="btn_gray_box">삭제</a></td>
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
      processData: false,
      contentType: false,
      dataType: 'json'
    })
    .done(function(result) {
      console.log(result);
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
