<?php
include_once('./_common.php');

$g5['title'] = "직원관리";
include_once("./_head.php");

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<form class="form-horizontal" role="form" id="fcentermember" name="fcentermember" action="center_member_update.php" onsubmit="return fcentermember_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">

    <div class="sub_section_tit">
        <?php echo $w==''?'직원등록':'정보수정'; ?>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><strong>직원정보</strong></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="cm_img"><b>대표이미지</b></label>
                <div class="col-sm-3">
                    <input type="file" name="cm_img" value="" id="cm_img" class="input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_name"><b>이름</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <input type="text" name="cm_name" value="" id="cm_name" required class="form-control input-sm" minlength="3" maxlength="20">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_code"><b>접속코드</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <input type="text" name="cm_code" value="" id="cm_code" required class="form-control input-sm">
                </div>
                <div class="desc_txt">
                    <span id="code_keyup">* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.</span>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_birth"><b>생년월일</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-2">
                    <input type="text" name="cm_birth" value="" id="cm_birth" required class="birthpicker form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label"><b>계약형태</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_cont" value="1" style="vertical-align: middle; margin: 0 5px 0 0;">정규직
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_cont" value="2" style="vertical-align: middle; margin: 0 5px 0 0;">계약직
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label"><b>분류</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_type" value="1" style="vertical-align: middle; margin: 0 5px 0 0;">일반직원
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_type" value="2" style="vertical-align: middle; margin: 0 5px 0 0;">요양보호사
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_pay"><b>급여</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-6">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_paytype" value="1" style="vertical-align: middle; margin: 0 5px 0 0;">고정
                        <input type="text" name="cm_pay" id="cm_pay" class="form-control input-sm" style="display: inline-block; width: 100px; margin: 0 5px;">원
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_paytype" value="2" style="vertical-align: middle; margin: 0 5px 0 0;">변동
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_joindate"><b>입사일</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-2">
                    <input type="text" name="cm_joindate" value="" id="cm_birth" required class="datepicker form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_retiredate"><b>퇴사일</b></label>
                <div class="col-sm-6">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_retired" value="0" style="vertical-align: middle; margin: 0 5px 0 0;">근무중
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_retired" value="1" style="vertical-align: middle; margin: 0 5px 0 0;">퇴사
                        <input type="text" name="cm_retiredate" id="cm_retiredate" class="datepicker form-control input-sm" style="display: inline-block; width: 140px; margin: 0 5px;">
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_hp"><b>연락처</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <input type="text" name="cm_hp" value="" id="cm_hp" required class="form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_addr"><b>주소</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-6">
                    <input type="text" name="cm_addr" value="" id="cm_addr" required class="form-control input-sm">
                </div>
            </div>
        </div>
    </div>
    <div class="text-center" style="margin:30px 0px;">
        <button type="button" id="btn_submit" onclick="fcentermember_submit()" class="btn btn-color">작성완료</button>
        <a href="center_member_list.php" class="btn btn-black" role="button">취소</a>
    </div>
</form>

<script>
// datepicker
$('.birthpicker').datepicker({ changeMonth: true, changeYear: true, yearRange: 'c-120:c+0', maxDate: '+0d', dateFormat: 'yy-mm-dd' });
$('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });
</script>

<?php
include_once('./_tail.php');
?>
