<?php
$sub_menu = "777106";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

if ( $dt_no ) {
    $sql = " select * from g5_domain_thema where dt_no = '{$dt_no}'";
    $dt = sql_fetch($sql);
    $g5['title'] .= $dt['dt_domain'] . ' 도메인 테마 수정 ';
}else{
    $g5['title'] .= '도메인별 테마 등록 ';
}

include_once('./admin.head.php');

// 사용여부
$c_view_y        = $cate['c_view'] == 'Y'      ? 'checked="checked"' : '';
$c_view_n        = $cate['c_view'] == 'N'      ? 'checked="checked"' : '';

?>

<form name="domainthema" id="domainthema" action="./domain_thema_list_form_update.php" onsubmit="return domainthema(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="dt_no" value="<?php echo $dt_no ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="dt_domain">도메인<?php echo $sound_only ?></label></th>
        <td colspan="3">
            <?php echo help('www는 적지 않으셔도 됩니다.'); ?>
            <input type="text" name="dt_domain" value="<?php echo $dt['dt_domain'] ?>" id="dt_domain" class="frm_input required" size="30"  maxlength="30" required>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dt_thema">테마 폴더명<?php echo $sound_only ?></label></th>
        <td colspan="3">
            <input type="text" name="dt_thema" value="<?php echo $dt['dt_thema'] ?>" id="dt_thema" class="frm_input required" size="30"  maxlength="30" required>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dt_key">테마 키</label></th>
        <td colspan="3">
            <?php echo help('테마 키는 해당 도메인으로 회원가입시 추후 회원을 구분하기 위하여 설정되는 값입니다.<br>일반몰:default, 파트너몰:partner '); ?>
            <input type="text" name="dt_key" value="<?php echo $dt['dt_key'] ?>" id="dt_key" class="frm_input required" size="30"  maxlength="30" required>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="dt_memo">메모</label></th>
        <td colspan="3">
            <!--<input type="text" name="dt_memo" value="<?php echo $dt['dt_memo'] ?>" id="dt_memo" class="frm_input required" size="30"  maxlength="30" required>-->
            <textarea name="dt_memo" id="dt_memo"><?php echo html_purifier($dt['dt_memo']); ?></textarea>
        </td>
    </tr>

    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./domain_thema_list.php" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
function fdomainthema_submit(f)
{

    return true;
}

$(document).ready(function(){
});
</script>
<?php

include_once('./admin.tail.php');
?>
