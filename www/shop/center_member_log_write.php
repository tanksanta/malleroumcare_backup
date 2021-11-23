<?php
include_once('./_common.php');

$g5['title'] = "관리기록";
include_once("./_head.php");

$cm_code = clean_xss_tags($_GET['cm_code']);
$cm = sql_fetch("
    SELECT * FROM center_member
    WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
");

if(!$cm['cm_id'])
    alert('해당 직원이 존재하지 않습니다.');

$w = get_search_string($_GET['w']) ?: '';
if($w == 'u') {
    $cl_id = get_search_string($_GET['cl_id']);
    $cl = sql_fetch("
        SELECT * FROM center_member_log
        WHERE cl_id = '$cl_id' and mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
    ");
}

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
include_once(G5_EDITOR_LIB);

$title = $cl['cl_title'] ?: '';
$content = $cl['cl_content'] ?: '';

$editor_html = editor_html('cl_content', $content, true);
$editor_js = '';
$editor_js .= get_editor_js('cl_content', true);
$editor_js .= chk_editor_js('cl_content', true);
?>

<form class="form-horizontal" role="form" id="fcenterlog" name="fcenterlog" action="center_member_log_update.php" onsubmit="return fcenterlog_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
    <div class="sub_section_tit">직원관리</div>
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="cl_id" value="<?php echo $cl_id ?: ''; ?>">
    <input type="hidden" name="cm_code" value="<?php echo $cm_code; ?>">

    <div class="panel panel-default">
        <div class="panel-heading"><strong>관리기록</strong></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-2 control-label"><b>직원명</b></label>
                <div class="col-sm-3">
                    <div class="control-label">
                        <strong><?=$cm['cm_name']?></strong>
                    </div>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cl_title"><b>제목</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-10">
                    <input type="text" name="cl_title" value="<?=$title?>" id="cl_title" required class="form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cl_content"><b>내용</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-10">
                    <?php echo $editor_html; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center" style="margin:30px 0px;">
        <button type="submit" id="btn_submit" class="btn btn-color">작성완료</button>
        <a href="javascript:history.back();" class="btn btn-black" role="button">취소</a>
    </div>
</form>

<script>
function fcenterlog_submit() {
    <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

    return true;
}
</script>

<?php
include_once('./_tail.php');
?>
