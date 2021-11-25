<?php
include_once('./_common.php');

$g5['title'] = "급여지급";
include_once("./_head.php");

$cm_code = clean_xss_tags($_GET['cm_code']);
$cm = sql_fetch("
    SELECT * FROM center_member
    WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
");

if(!$cm['cm_id'])
    alert('해당 직원이 존재하지 않습니다.');

$year = preg_replace('/[^0-9]/', '', $_GET['year']);
$month = preg_replace('/[^0-9]/', '', $_GET['month']);
if(!($year && $month))
    alert('유효한 요청이 아닙니다.');

$cp = sql_fetch("
    SELECT * FROM center_member_pay
    WHERE
        mb_id = '{$member['mb_id']}' and
        cm_code = '$cm_code' and
        cp_year = '$year' and
        cp_month = '$month'
");

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<form class="form-horizontal" role="form" id="fcenterpay" name="fcenterpay" action="center_member_pay_result.php" onsubmit="return fcenterpay_submit();" method="post" enctype="multipart/form-data" autocomplete="off">
    <div class="sub_section_tit">직원관리</div>
    <input type="hidden" name="w" value="<?php if($cp['cp_month']) echo 'u';?>">
    <input type="hidden" name="cm_code" value="<?php echo $cm_code; ?>">
    <input type="hidden" name="year" value="<?php echo $year; ?>">
    <input type="hidden" name="month" value="<?php echo $month; ?>">

    <div class="panel panel-default">
        <div class="panel-heading"><strong>급여지급</strong></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-2 control-label"><b>직원명</b></label>
                <div class="col-sm-3">
                    <div class="control-label">
                        <strong><?=$cm['cm_name']?></strong>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label"><b>지급월</b></label>
                <div class="col-sm-3">
                    <div class="control-label">
                        <strong><?="{$year}년 {$month}월"?></strong>
                    </div>
                </div>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cp_total"><b>급여총액</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cp_total" value="<?=number_format($cp['cp_total']) ?: '0'?>" id="cp_total" class="ipt_number form-control input-sm">
                </div>
                <strong class="cp_won">원</strong>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cp_deduction"><b>공제</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cp_deduction" value="<?=number_format($cp['cp_deduction']) ?: '0'?>" id="cp_deduction" class="ipt_number form-control input-sm">
                </div>
                <strong class="cp_won">원</strong>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cp_tax"><b>소득세</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cp_tax" value="<?=number_format($cp['cp_tax']) ?: '0'?>" id="cp_tax" class="ipt_number form-control input-sm">
                </div>
                <strong class="cp_won">원</strong>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cp_insurance"><b>사대보험</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cp_insurance" value="<?=number_format($cp['cp_insurance']) ?: '0'?>" id="cp_insurance" class="ipt_number form-control input-sm">
                </div>
                <strong class="cp_won">원</strong>
            </div>
            <div class="form-group has-feedback">
                <label class="col-sm-2 control-label" for="cp_pay"><b>지급급여</b></label>
                <div class="col-sm-2">
                    <input type="text" name="cp_pay" value="<?=number_format($cp['cp_pay']) ?: '0'?>" id="cp_pay" class="ipt_number form-control input-sm">
                </div>
                <strong class="cp_won">원</strong>
            </div>
        </div>
    </div>
    <div class="text-center" style="margin:30px 0px;">
        <button type="submit" id="btn_submit" class="btn btn-color">작성완료</button>
        <a href="javascript:history.back();" class="btn btn-black" role="button">취소</a>
    </div>
</form>

<script>
function fcenterpay_submit() {
    return true;
}

$(document).on('input propertychange paste keyup change', '.ipt_number', function() {
    var input = $(this).val();

    input = input.replace(/[\D\s\._\-]+/g, "");

    if(input !== '') {
        input = input ? parseInt( input, 10 ) : 0;
        $(this).val(input.toLocaleString());
    } else {
        $(this).val('');
    }
});
</script>

<?php
include_once('./_tail.php');
?>
