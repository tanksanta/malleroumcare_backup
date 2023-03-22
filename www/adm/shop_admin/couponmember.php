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
</style>

<div id="sch_member_frm" class="new_win scp_new_win">
  <h1>쿠폰 적용 회원선택</h1>

  <form id="form_member" name="fmember" method="get">
  <div id="scp_list_find">
    <label for="mb_name">회원이름</label>
    <input type="text" name="mb_name" id="mb_name" value="<?php echo get_text($mb_name); ?>" class="frm_input" size="20">
    <input type="submit" value="검색" class="btn_frmline">
  </div>
  <div id="tbl_member" class="tbl_head01 tbl_wrap new_win_con">
    <table>
      <caption>검색결과</caption>
      <thead>
        <tr>
          <th>회원이름</th>
          <th>회원아이디</th>
          <th>선택</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
  </form>

  <div id="paging"></div>

  <div class="btn_wrap">
    <button id="btn_submit">선택</button>
    <button id="btn_close" onclick="window.close();">취소</button>
  </div>
</div>

<script>
var selected = [];

function sel_member_id(id) {
  for(var i = 0; i < selected.length; i++) {
    if(selected[i] === id)
      return;
  }

  selected.push(id);
  update_submit_button();
  update_select_button();
}

function update_submit_button() {
  $('#btn_submit').text(selected.length + '명 등록');
}

function update_select_button() {
  var dataTable = {};
  for(var i = 0; i < selected.length; i++) {
    var mb_id = selected[i];
    dataTable[mb_id] = true;
  }

  $('.btn_03').each(function() {
    var mb_id = $(this).data('id');
    if(dataTable[mb_id]) {
      $(this).css({
        'background': '#888'
      });
    }
  });
}

function get_members(data) {
  $.post('ajax.couponmember.php', data, 'json')
  .done(function(result) {
    $('#tbl_member tbody').html(result.data.html);
    $('#paging').html(result.data.paging);
    update_select_button();
  })
  .fail(function($xhr) {
    var data = $xhr.responseJSON;
    alert(data && data.message);
  });
}

$(function() {
  get_members();
  update_submit_button();

  $(document).on('click', '#paging a', function(e) {
    e.preventDefault();

    get_members($(this).attr('href'));
  });

  $('#form_member').on('submit', function(e) {
    e.preventDefault();

    get_members({
      keyword: $('#mb_name').val()
    });
  });

  $('#btn_submit').on('click', function() {
    var f = window.opener.document.fcouponform;
    var id = selected.join(',');
    f.mb_id.value = id;

    window.close();
  });
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
