<?php
include_once('./_common.php');

$g5['title'] = "직원관리";
include_once("./_head.php");

if($w == 'u') {
    $cm_code = clean_xss_tags($_POST['cm_code']);
    $cm = sql_fetch("
        SELECT * FROM center_member
        WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
    ");

    if(!$cm['cm_id'])
        alert('해당 직원이 존재하지 않습니다.');
}

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<form class="form-horizontal" role="form" id="fcentermember" name="fcentermember" action="center_member_update.php" onsubmit="return fcentermember_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>">

    <div class="sub_section_tit">
        <?php echo $w == '' ? '직원등록':'정보수정'; ?>
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
                    <input type="text" name="cm_name" value="<?=$cm['cm_name'] ?: ''?>" id="cm_name" required class="form-control input-sm" minlength="3" maxlength="20">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_code"><b>접속코드</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-3">
                    <input type="text" name="cm_code" value="<?=$cm['cm_code'] ?: ''?>" id="cm_code" required <?php if($w) echo 'readonly' ?> class="form-control input-sm">
                </div>
                <div class="desc_txt">
                    <span id="code_keyup">* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.</span>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_birth"><b>생년월일</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cm_birth" value="<?=$cm['cm_birth'] ?: ''?>" id="cm_birth" class="birthpicker form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label"><b>계약형태</b></label>
                <div class="col-sm-3">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_cont" value="1" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_cont'], '1')?>>정규직
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_cont" value="2" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_cont'], '2')?>>계약직
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label"><b>분류</b></label>
                <div class="col-sm-3">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_type" value="1" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_type'], '1')?>>일반직원
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_type" value="2" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_type'], '2')?>>요양보호사
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_pay"><b>급여</b><strong class="sound_only">필수</strong></label>
                <div class="col-sm-6">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_paytype" value="1" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_paytype'], '1')?>>고정
                        <input type="text" name="cm_pay" value="<?=$cm['cm_pay'] ?: ''?>" id="cm_pay" class="form-control input-sm" style="display: inline-block; width: 100px; margin: 0 5px;">원
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_paytype" value="2" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_paytype'], '2')?>>변동
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_joindate"><b>입사일</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cm_joindate" value="<?=$cm['cm_joindate'] ?: ''?>" id="cm_joindate" class="datepicker form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_retiredate"><b>퇴사일</b></label>
                <div class="col-sm-6">
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_retired" value="0" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_retired'], '0')?>>근무중
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="cm_retired" value="1" style="vertical-align: middle; margin: 0 5px 0 0;" <?=option_array_checked($cm['cm_retired'], '1')?>>퇴사
                        <input type="text" name="cm_retiredate" value="<?=$cm['cm_retiredate'] ?: ''?>" id="cm_retiredate" class="datepicker form-control input-sm" style="display: inline-block; width: 140px; margin: 0 5px;">
                    </label>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_hp"><b>연락처</b></label>
                <div class="col-sm-3">
                    <input type="text" name="cm_hp" value="<?=$cm['cm_hp'] ?: ''?>" id="cm_hp" class="form-control input-sm">
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cm_addr"><b>주소</b></label>
                <div class="col-sm-6">
                    <input type="text" name="cm_addr" value="<?=$cm['cm_addr'] ?: ''?>" id="cm_addr" class="form-control input-sm">
                </div>
            </div>
        </div>
    </div>
    <div class="text-center" style="margin:30px 0px;">
        <button type="submit" id="btn_submit" class="btn btn-color">작성완료</button>
        <a href="center_member_list.php" class="btn btn-black" role="button">취소</a>
    </div>
</form>

<script>
// datepicker
$('.birthpicker').datepicker({ changeMonth: true, changeYear: true, yearRange: 'c-120:c+0', maxDate: '+0d', dateFormat: 'yy-mm-dd' });
$('.datepicker').datepicker({ changeMonth: true, changeYear: true, dateFormat: 'yy-mm-dd' });

function fcentermember_submit() {
    var $form = $('#fcentermember');

    // 이름 체크
    if($('#cm_name').val() == '') {
        alert('이름을 입력해주세요.');
        $('#cm_name').focus();
        return false;
    }

    // 접속코드 체크
    var code_msg = check_code();
    if(code_msg) {
        alert(code_msg);
        $('#cm_code').focus();
        return false;
    }

    // 급여 체크
    var cm_paytype = $('input[name="cm_paytype"]:checked').val();
    if(cm_paytype == '1') {
        var cm_pay = parseInt($('#cm_pay').val());
        if(!(cm_pay > 0)) {
            alert('급여를 입력해주세요.');
            $('#cm_pay').focus();
            return false;
        }
    } else if(cm_paytype != '2') {
        alert('급여를 입력해주세요.');
        $('#cm_pay').focus();
        return false;
    }

    return true;
}

// 접속코드 체크
var cm_code_check_timer = null;
$('#cm_code').on('keyup change input', function() {
    if(cm_code_check_timer)
        clearTimeout(cm_code_check_timer);

    cm_code_check_timer = setTimeout(function() {
        check_code();
    }, 500);
});
function check_code() {
    var cm_code = $('#cm_code').val();

    var ret = '접속코드를 입력해주세요.';

    $.ajax('ajax.center_check_code.php', {
        type: 'POST',
        cache: false,
        async: false,
        data: {
            cm_code: cm_code
        },
        dataType: 'json',
        success: function(result) {
            var message = result.message;
            if(message) {
                $('#code_keyup').text('* ' + message).css('color', '#4788d4');
            }

            ret = false;
        },
        error: function($xhr) {
            var message = $xhr.responseJSON.message;
            if(message) {
                $('#code_keyup').text('* ' + message).css('color', '#d44747');
                ret = message;
            } else {
                $('#code_keyup').text('* 방문기록, 교육정보 열람 시 본인 확인을 위해 필요한 접속코드 입니다.').css('color', '#333333');
            }
        }
    });

    return ret;
}
</script>

<?php
include_once('./_tail.php');
?>
