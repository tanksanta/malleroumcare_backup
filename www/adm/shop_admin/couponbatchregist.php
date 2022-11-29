<?php
$sub_menu = '400800';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$html_title = '회원검색';

$g5['title'] = $html_title;
include_once(G5_PATH.'/head.sub.php');

$sql_common = " from {$g5['member_table']} ";
$sql_where = " where mb_id <> '{$config['cf_admin']}' and mb_leave_date = '' and mb_intercept_date ='' ";

if($mb_name){
  $mb_name = preg_replace('/\!\?\*$#<>()\[\]\{\}/i', '', strip_tags($mb_name));
  $sql_where .= " and mb_name like '%".sql_real_escape_string($mb_name)."%' ";
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common . $sql_where;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select mb_id, mb_name
            $sql_common
            $sql_where
            order by mb_id
            limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'mb_name='.urlencode($mb_name);
?>

<style>
body {
  margin-bottom: 60px !important;
}

.new_win h1{
    font-size: 1.4em;
}

.btn_wrap {
  width: 100%;
  height: 60px;
  position: fixed;
  left: 0;
  bottom: 0;
  background-color: #fff;
  display: -ms-flexbox;
  display: -webkit-flex;
  display: flex;
}

.btn_wrap button {
  font-size: 16px;
  font-weight: bold;
  border: 0;
}

#btn_submit {
  -ms-flex: 1;
  -webkit-flex: 1;
  flex: 1;
  background-color: #383838;
  color: #fff;
}
#btn_close {
  width: 100px;
}
#btn_close {
  width: 100px;
}

#tbl_member {
    height: 500px;
}

#sch_member {
    display: inline-block;
    padding: 3px 30px;
    height: 40px;
    border: 0;
    background: #3c3c3c;
    border-radius: 5px;
    color: #fff;
    text-decoration: none;
    vertical-align: top;
    float: right;
    margin-top: 5px;
    margin-right: 10px;
    font-size: medium;
}
</style>

<div id="batch_reg_member_frm" class="new_win scp_new_win">
  <h1>회원 일괄등록</h1>

  <form id="form_member" name="fmember" method="get">
<!--  <div id="scp_list_find">-->
<!--    <label for="keyword" style="margin-right: 10px">검색</label>-->
<!--    <input type="text" name="keyword" id="keyword" value="--><?php //echo get_text($mb_name); ?><!--" class="frm_input" size="30" placeholder="회원이름 또는 회원아이디로 검색">-->
<!--    <input type="submit" value="검색" class="btn_frmline">-->
<!--  </div>-->
  <div id="tbl_member" class="tbl_head01 tbl_wrap new_win_con">
    <table style="position : relative; height: 300px; margin-bottom: 20px">
<!--      <caption>검색결과</caption>-->
<!--      <thead>-->
<!--        <tr>-->
<!--          <th>회원이름</th>-->
<!--          <th>회원아이디</th>-->
<!--          <th>선택</th>-->
<!--        </tr>-->
<!--      </thead>-->
      <colgroup>
        <col width="20%"/>
        <col width="80%"/>
      </colgroup>
      <tbody>
        <tr>
            <td style="background-color: #F2F2F2; font-weight: normal; font-size: larger; text-align: center;">회원</br>아이디 입력</td>
          <td>
              <textarea id="sch_mb_id" rows="10" style="font-size: medium" placeholder="&#13;&#10;&#13;&#10;회원ID를 입력하세요. &#13;&#10;아이디는 enter로 구분됩니다. &#13;&#10;Ex)&#13;&#10;id1 &#13;&#10;id2 &#13;&#10;id3"></textarea>
              <p style="position : absolute; bottom : 0;float: left; margin-left: 10px; vertical-align: text-bottom;"> ※ ID는 줄바꿈으로 구분</p>
              <button id="sch_member">조회하기</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div>
        <label for="result_check" style="margin-right: 10px; font-size: medium;  margin-bottom: 20px">조회결과 </label>
        <input type="text" name="result_check" id="result_check" value="" class="frm_input" style="padding: 15px 10px; width: 80%;" readonly disabled>
        <p class="result_check_txt" style=" font-size: medium;">※ 총 0개 중 0개의 회원이 조회되었습니다.</p>
    </div>
    <div>
        <label for="result_none" style="margin-right: 10px; font-size: medium;  margin-bottom: 20px">조회실패 </label>
        <input type="text" name="result_none" id="result_none" value="" class="frm_input" style="padding: 15px 10px; width: 80%;" readonly disabled>
        <p class="result_none_txt" style=" font-size: medium;">※ 총 0건의 ID 조회에 실패하였습니다.</p>
    </div>
  </div>
  </form>

<!--  <div id="paging"></div>-->

  <div class="btn_wrap">
    <button id="btn_submit">선택</button>
    <button id="btn_close" onclick="window.close();">취소</button>
  </div>
</div>

<script>
var result_check = [];

function inArray(val, arrValue) {
    for(i=0; i < arrValue.length; i++) {
        if(arrValue[i] == val) return true;
    }

    return false;
}

$(function() {
  $(document).on('click', '#sch_member', function(e) {
    e.preventDefault();

    var mbid = document.getElementById("sch_mb_id").value;
    var mbid_list = mbid.split(/(?:\r\n|\r|\n)/g).filter(i=>i.length !== 0);

    $.post('ajax.couponmember.php', {
            page: "batchReg",
            mbIdList: mbid_list
    }, 'json')
    .done(function(result) {
      result_check = result.data.check;

      var mbid_none = [];
      for(var i = 0; i < mbid_list.length; i++){
          if(!inArray(mbid_list[i], result_check)){
              mbid_none.push(mbid_list[i]);
          }
      }

      $('.result_check_txt').text("※ 총 "+mbid_list.length+"개 중 "+result_check.length+"개의 회원이 조회되었습니다.");
      $('#result_check').val(result_check.join());

      $('.result_none_txt').text("※ 총 "+mbid_none.length+"건의 ID 조회에 실패하였습니다.");
      $('#result_none').val(mbid_none.join());

      $('#btn_submit').text(result_check.length + '명 등록');
      // 조회결과  : result_check ,로 합쳐서 출력하고, 개수 아래에 넣기
      // 조회실패 : mbid_none ,로 합쳐서 출력하고, 개수 아래에 넣기


    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert("잠시 후 다시 시도해주세요.");
    });
  });

  /*
  $('#form_member').on('submit', function(e) {
    e.preventDefault();

    // 회원 검색시
    get_members({
      keyword: $('#keyword').val()
    });
  });
*/

  $('#btn_submit').on('click', function() {
    var f = window.opener.document.fcouponform;
    var id = result_check.join(',');
    f.mb_id.value = id;

    window.close();
  });
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
