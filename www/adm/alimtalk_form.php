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
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<style>
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
            입고예정일 알림
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
            <label for="al_itdate">입고예정일<strong class="sound_only">필수</strong></label>
        </th>
        <td>
            <input type="text" name="al_itdate" value="<?php echo  get_sanitize_input($al['al_itdate']) ?>" id="al_itdate" required class="datepicker required frm_input" size="100">
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

<script>
$('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

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
</script>

<?php
include_once ('./admin.tail.php');
?>