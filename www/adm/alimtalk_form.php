<?php
$sub_menu = '200400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$w = $_GET['w'];

if($w == 'u') {
    // 수정
    $al_id = get_search_string($_GET['al_id']);
    $al = sql_fetch(" select * from g5_alimtalk where al_id = '$al_id' ", true);
    if(!$al)
        alert('존재하지 않는 알림톡입니다.');
    
    $al['member'] = [];
    $sql = "
        select
            m.*
        from
            g5_alimtalk_member a
        left join
            g5_member m on a.mb_id = m.mb_id
        where
            al_id = '$al_id'
        order by
            a.mb_id asc
    ";
    $mb_result = sql_query($sql);

    while($mb = sql_fetch_array($mb_result)) {
        $al['member'][] = $mb;
    }
}

$g5['title'] = '회원 알림톡/푸시 입력';
include_once('./admin.head.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/jquery.flexdatalist.css">', -1);
add_javascript('<script src="'.G5_JS_URL.'/jquery.flexdatalist.js"></script>', 0);
add_javascript('<script src="'.G5_JS_URL.'/popModal/popModal.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/popModal/popModal.min.css">', 0);
?>

<style>
#upload_wrap { display: none; }
.popModal #upload_wrap { display: block; }
.popModal .popModal_content { margin: 0 !important; }
.popModal .form-group { margin-bottom: 15px; }
.popModal label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: 700; }
.popModal input[type=file] { display: block; }
.popModal .help-block { padding: 0; display: block; margin-top: 5px; margin-bottom: 10px; color: #737373; }

.flexdatalist-results li {
  font-size:12px;
}
.flexdatalist-results span:not(:first-child):not(.highlight) {
  font-size: 80%;
  color: rgba(0, 0, 0, 0.50);
}
#mb_id_list {}
#mb_id_list .mb {
  display: inline-block;
  margin: 5px 5px 0 0;
  font-size: 12px;
  padding: 5px 8px;
  color: #333;
  background-color: #eee;
  border-radius: 3px;
  cursor: pointer;
}
</style>

<form name="falimtalkform" id="falimtalkform" action="./alimtalk_update.php" onsubmit="return falimtalkform_check(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>" id="w">
<input type="hidden" name="al_id" value="<?php echo $al['al_id'] ?>" id="al_id">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">
            <label for="al_subject">제목<strong class="sound_only">필수</strong></label>
        </th>
        <td>
            <input type="text" name="al_subject" value="<?php echo  get_sanitize_input($al['al_subject']) ?>" id="al_subject" required class="required frm_input" size="100">
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>대상<strong class="sound_only">필수</strong></label>
        </th>
        <td>
            <input type="radio" name="al_type" value="0" id="al_type_0" <?=option_array_checked($al['al_type'], [0, ''])?>>
            <label for="al_type_0">전체사업소</label>
            <input type="radio" name="al_type" value="1" id="al_type_1" <?=option_array_checked($al['al_type'], [1])?>>
            <label for="al_type_1">사업소선택</label>
            <input type="text" id="mb_id" class="frm_input" size="50">
            <button type="button" id="excelupload" class="btn btn_03">엑셀업로드</button>
            <div id="mb_id_list">
                <?php
                if($al['al_type'] == 1 && $al['member']) {
                    foreach($al['member'] as $mb) {
                ?>
                <div class="mb">
                    <input type="hidden" name="mb_id[]" value="<?=$mb['mb_id']?>">
                    <input type="hidden" name="deleted[]" value="0">
                    <?="{$mb['mb_name']}({$mb['mb_id']})"?>
                    <i class="fa fa-times" aria-hidden="true"></i>
                </div>
                <?php
                    }
                }
                ?>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label>분류</label>
        </th>
        <td>
            <input type="radio" name="al_cate" value="0" id="al_cate_0" <?=option_array_checked($al['al_cate'], [0, ''])?>>
            <label for="al_cate_0">입고예정일 알림</label>
            <input type="radio" name="al_cate" value="1" id="al_cate_1" <?=option_array_checked($al['al_cate'], [1])?>>
            <label for="al_cate_1">출고예정일 알림</label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="al_itname">상품명<strong class="sound_only">필수</strong></label>
        </th>
        <td>
            <input type="text" name="al_itname" value="<?php echo  get_sanitize_input($al['al_itname']) ?>" id="al_itname" required class="required frm_input" size="100">
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="al_itdate">날짜<strong class="sound_only">필수</strong></label>
        </th>
        <td>
            <input type="text" name="al_itdate" value="<?php echo  get_sanitize_input($al['al_itdate']) ?>" id="al_itdate" required class="required frm_input" size="100">
        </td>
    </tr>
    <tr id="num_tr" style="<?php echo ($al['al_cate'] == 1 ? '' : 'display:none')?>">
        <th scope="row">
            <label for="al_itcount">수량</label>
        </th>
        <td>
            <input type="text" name="al_itcount" value="<?php echo  get_sanitize_input($al['al_itcount']) ?>" id="al_itcount" required class="frm_input" size="10">
        </td>
    </tr>
    </tbody>
    </table>
</div>
<div class="btn_fixed_top ">
    <input type="submit" class="btn_submit btn" accesskey="s" value="확인">
    <a href="./alimtalk_list.php" class="btn btn_02">취소</a>
</div>
</form>

<div id="upload_wrap">
  <form id="form_excel_upload" style="font-size: 14px;">
    <div class="form-group">
      <label for="datafile">엑셀 업로드</label>
      <input type="file" name="datafile" id="datafile">
      <p class="help-block">
        상품관리에서 다운로드받으신 사업소목록 엑셀을 업로드하시면 추가됩니다.
      </p>
    </div>
    <button type="submit" class="btn btn-primary">업로드</button>
  </form>
</div>

<script>
function falimtalkform_check(f) {
    return true;
}

function select_mb_id(obj) {
    var $mb = $('<div class="mb">');
    $mb.append(
        '<input type="hidden" name="mb_id[]" value="' + obj.mb_id + '">',
        obj.mb_name + '(' + obj.mb_id + ')',
        '<i class="fa fa-times" aria-hidden="true"></i>'
    );

    $('#mb_id_list').append($mb);
}

$(document).on('click', '#mb_id_list .mb', function() {
    if($(this).find('input[name="deleted[]"]').length > 0) {
        $(this).find('input[name="deleted[]"]').val(1);
        $(this).hide();
    } else {
        $(this).remove();
    }
});

$('#mb_id').flexdatalist({
    minLength: 1,
    url: '/adm/ajax.get_mb_id.php',
    cache: true, // cache
    searchContain: true, // %검색어%
    noResultsText: '"{keyword}"으로 검색된 내용이 없습니다.',
    visibleProperties: ["mb_name", "mb_id"],
    searchIn: ["mb_id","mb_name"],
    selectionRequired: true,
    focusFirstResult: true,
}).on("select:flexdatalist",function(event, obj, options) {
    select_mb_id(obj);
    $('#mb_id').val('').next().focus();
});

$('input[name="al_cate"]:radio').change(function() {
    if (this.value == 0) {
        $('#num_tr').hide();
    }
    else {
        $('#num_tr').show();
    }
})

// 사업소목록 엑셀 업로드
$('#excelupload').click(function() {
  $(this).popModal({
    html: $('#form_excel_upload'),
    placement: 'bottomRight',
    showCloseBut: false
  });
});
$('#form_excel_upload').submit(function(e) {
  e.preventDefault();

  var fd = new FormData(document.getElementById("form_excel_upload"));
  $.ajax({
      url: 'ajax.alimtalk_excel_upload.php',
      type: 'POST',
      data: fd,
      cache: false,
      processData: false,
      contentType: false,
      dataType: 'json'
    })
    .done(function(result) {
      var data = result.data;
      if(data.length && data.length > 0) {
        for(var i = 0; i < data.length; i++) {
            select_mb_id(data[i]);
        }
      }
      alert('업로드하신 ' + data.length + '개 사업소가 선택되었습니다.');
      $('#excelupload').popModal("hide");
      $('#al_type_1').click();
    })
    .fail(function($xhr) {
      var data = $xhr.responseJSON;
      alert(data && data.message);
    });
});
</script>

<?php
include_once ('./admin.tail.php');
?>