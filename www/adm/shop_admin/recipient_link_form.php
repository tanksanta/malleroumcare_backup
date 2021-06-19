<?php
$sub_menu = "500051";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
if ($w == '')
{
    $sound_only = '<strong class="sound_only">필수</strong>';

    $html_title = '추가';
}
else if ($w == 'u')
{
    $sql = "SELECT * FROM g5_recipient_link WHERE rl_id = '{$rl_id}'";
    $rl = sql_fetch($sql);

    if (!$rl['rl_id'])
        alert('존재하지 않는 수급자입니다.');

    $html_title = '수정';
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 인정정보
$recipient_yes        =  $rl['rl_ltm']       ? 'checked="checked"' : '';
$recipient_no         = !$rl['rl_ltm']       ? 'checked="checked"' : '';

$g5['title'] .= '수급자 연결 '.$html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>
<style>
label {
    margin-right:10px;
}
.accept {
    cursor:pointer;
}
</style>
<form name="frecipient_link" id="frecipient_link" action="./recipient_link_form_update.php" onsubmit="return frecipient_link_submit();" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="rl_id" value="<?php echo $rl['rl_id'] ?>">
<input type="hidden" name="token" value="<?php echo $token; ?>">

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
        <th scope="row"><label for="rl_name">수급자명<strong class="sound_only">필수</strong></label></th>
        <td colspan="3">
            <input type="text" name="rl_name" value="<?php echo $rl['rl_name'] ?>" id="rl_name" class="frm_input" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_hp">연락처</label></th>
        <td colspan="3">
            <?php $rl_hp =explode('-',$rl['rl_hp']); ?>
            <input type="text" name="rl_hp1" value="<?=$rl_hp[0]?>" id="rl_hp1" class="frm_input"size="15" maxlength="3">
            <input type="text" name="rl_hp2" value="<?=$rl_hp[1]?>" id="rl_hp2" class="frm_input" size="15" maxlength="4">
            <input type="text" name="rl_hp3" value="<?=$rl_hp[2]?>" id="rl_hp3" class="frm_input" size="15" maxlength="4">
        </td>
    </tr>
    <tr>
        <th scope="row">주소</th>
        <td colspan="3" class="td_addr_line">
            <label for="rl_zip" class="sound_only">우편번호</label>
            <input type="text" name="rl_zip" value="<?php echo $rl['rl_zip1'].$rl['rl_zip2']; ?>" id="rl_zip" class="frm_input readonly" size="5" maxlength="6">
            <button type="button" class="btn_frmline" onclick="win_zip('frecipient_link', 'rl_zip', 'rl_addr1', 'rl_addr2', 'rl_addr3', 'rl_addr_jibeon');">주소 검색</button><br>
            <input type="text" name="rl_addr1" value="<?php echo $rl['rl_addr1'] ?>" id="rl_addr1" class="frm_input readonly" size="60">
            <label for="rl_addr1">기본주소</label><br>
            <input type="text" name="rl_addr2" value="<?php echo $rl['rl_addr2'] ?>" id="rl_addr2" class="frm_input" size="60">
            <label for="rl_addr2">상세주소</label>
            <br>
            <input type="text" name="rl_addr3" value="<?php echo $rl['rl_addr3'] ?>" id="rl_addr3" class="frm_input" size="60">
            <label for="rl_addr3">참고항목</label>
            <input type="hidden" name="rl_addr_jibeon" value="<?php echo $rl['rl_addr_jibeon']; ?>"><br>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_hp">인정정보</label></th>
        <td colspan="3">
            <input type="radio" name="recipient" id="recipient_no" value="off" <?php echo $recipient_no; ?>>
            <label for="recipient_no">예비수급자</label>
            <input type="radio" name="recipient" id="recipient_yes" value="on" <?php echo $recipient_yes; ?>>
            <label for="recipient_yes">수급자정보:</label>
            L <input type="text" name="rl_ltm" value="<?php echo $rl['rl_ltm'] ?>" id="rl_ltm" class="frm_input" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_hp">보호자 관계</label></th>
        <td colspan="3">
           <select class="frm_input form-control input-sm penProRel" name="rl_pen_type" id="rl_pen_type">
                <option value="00" <?=($rl["rl_pen_type"] == "00") ? "selected" : ""?>>처</option>
                <option value="01" <?=($rl["rl_pen_type"] == "01") ? "selected" : ""?>>남편</option>
                <option value="02" <?=($rl["rl_pen_type"] == "02") ? "selected" : ""?>>자</option>
                <option value="03" <?=($rl["rl_pen_type"] == "03") ? "selected" : ""?>>자부</option>
                <option value="04" <?=($rl["rl_pen_type"] == "04") ? "selected" : ""?>>사위</option>
                <option value="05" <?=($rl["rl_pen_type"] == "05") ? "selected" : ""?>>형제</option>
                <option value="06" <?=($rl["rl_pen_type"] == "06") ? "selected" : ""?>>자매</option>
                <option value="07" <?=($rl["rl_pen_type"] == "07") ? "selected" : ""?>>손</option>
                <option value="08" <?=($rl["rl_pen_type"] == "08") ? "selected" : ""?>>배우자 형제자매</option>
                <option value="09" <?=($rl["rl_pen_type"] == "09") ? "selected" : ""?>>외손</option>
                <option value="10" <?=($rl["rl_pen_type"] == "10") ? "selected" : ""?>>부모</option>
                <option value="11" <?=($rl["rl_pen_type"] == "11") ? "selected" : ""?>>직접입력</option>
            </select>
            <input type="text" name="rl_pen_type_etc" value="<?=$rl["rl_pen_type_etc"]?>" class=" frm_input form-control input-sm" <?=($rl["rl_pen_type"] == "11") ? "" : "style='display:none'"?>>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_pen_name">보호자명<strong class="sound_only">필수</strong></label></th>
        <td colspan="3">
            <input type="text" name="rl_pen_name" value="<?php echo $rl['rl_pen_name'] ?>" id="rl_pen_name" class="frm_input" size="15" maxlength="20">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_hp">보호자 연락처</label></th>
        <td colspan="3">
            <?php $rl_pen_hp =explode('-',$rl['rl_pen_hp']); ?>
            <input type="text" name="rl_pen_hp1" value="<?=$rl_pen_hp[0]?>" id="rl_pen_hp1" class="frm_input" size="15" maxlength="3">
            <input type="text" name="rl_pen_hp2" value="<?=$rl_pen_hp[1]?>" id="rl_pen_hp2" class="frm_input" size="15" maxlength="4">
            <input type="text" name="rl_pen_hp3" value="<?=$rl_pen_hp[2]?>" id="rl_pen_hp3" class="frm_input" size="15" maxlength="4">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="rl_request">요청사항</label></th>
        <td colspan="3">
            <textarea id="rl_request" name="rl_request" rows="5"><?php echo html_purifier($rl['rl_request']); ?></textarea>
        </td>
    </tr>

    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./recipient_link_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <!-- <input type="submit" value="확인" class="btn_submit btn" accesskey='s'> -->
    <input type="button" onclick="frecipient_link_submit()" id="btn_submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
function frecipient_link_submit()
{   
     var f = document.getElementById("frecipient_link");

    if(!f.rl_name.value){
        alert('수급자명을 입력하세요.');
        f.rl_name.focus();
        return false;
    }
    if(!f.rl_hp1.value){
        alert('연락처를 입력하세요.');
        f.rl_hp1.focus();
        return false;
    }
    if(!f.rl_zip.value){
        alert('주소를 입력하세요.');
        f.rl_zip.focus();
        return false;
    }
    if(!f.rl_pen_name.value){
        alert('보호자명을 입력하세요.');
        f.rl_pen_name.focus();
        return false;
    }
    if(!f.rl_pen_hp1.value){
        alert('보호자 연락처를 입력하세요.');
        f.rl_pen_hp1.focus();
        return false;
    }

    f.submit();

    return true;
}
$(function() {
    $('#recipient_no').click(function() {
        $('#rl_ltm').val('');
    })
    $('#rl_ltm').click(function() {
        $("input:radio[name='recipient']:radio[value='on']").prop('checked', true);
    })

    
    $("#rl_pen_type").change(function(){
        if($(this).val() == "11"){
            $("#frecipient_link input[name='rl_pen_type_etc']").prop("readonly", false);
            $("#frecipient_link input[name='rl_pen_type_etc']").show();
        } else {
            $("#frecipient_link input[name='rl_pen_type_etc']").prop("readonly", true);
            $("#frecipient_link input[name='rl_pen_type_etc']").val("");
            $("#frecipient_link input[name='rl_pen_type_etc']").hide();
        }
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
