<?php
$sub_menu = '500500';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$bn_id = preg_replace('/[^0-9]/', '', $bn_id);

$html_title = '배너';
$g5['title'] = $html_title.'관리';

if ($w=="u")
{
    $html_title .= ' 수정';
    $sql = " select * from {$g5['g5_shop_banner_table']} where bn_id = '$bn_id' ";
    $bn = sql_fetch($sql);
}
else
{
    $html_title .= ' 입력';
    $bn['bn_url']        = "http://";
    $bn['bn_begin_time'] = date("Y-m-d 00:00:00", time());
    $bn['bn_end_time']   = date("Y-m-d 00:00:00", time()+(60*60*24*31));
}

// 접속기기 필드 추가
if(!sql_query(" select bn_device from {$g5['g5_shop_banner_table']} limit 0, 1 ")) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_banner_table']}`
                    ADD `bn_device` varchar(10) not null default '' AFTER `bn_url` ", true);
    sql_query(" update {$g5['g5_shop_banner_table']} set bn_device = 'pc' ", true);
}

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>
<style>
    #bn_position, #bn_new_win {
        font-size: 12px;        
        height: 32px;  
        width:200px;
    }
    #bn_bimg_del {
        width: 30px; height: 30px;
    }
    #bn_status, #bn_begin_chk, #bn_end_chk {        
        width: 25px; height: 25px;
    }
    .frm_input {
        font-size: 12px;
        height: 32px;  
    }  
</style>

<form name="fbanner" action="./bannerformupdate.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="bn_id" value="<?php echo $bn_id; ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="bn_position">표시분류</label></th>
        <td>
            <?php echo help("사업소 : 쇼핑몰화면 사업소메인 배너에 출력합니다.\n파트너 : 쇼핑몰 파트너화면(index.php)에만 출력합니다."); ?>
            <select name="bn_position" id="bn_position">
                <option value="사업소" <?php echo get_selected($bn['bn_position'], '사업소'); ?>>사업소</option>
                <option value="파트너" <?php echo get_selected($bn['bn_position'], '파트너'); ?>>파트너</option>
        </select>
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="bn_alt">배너 타이틀 </label></th>
        <td>
            <?php echo help("img 태그의 alt, title 에 해당되는 내용입니다.\n배너에 마우스를 오버하면 이미지의 설명이 나옵니다."); ?>
            <input type="text" name="bn_alt" value="<?php echo get_text($bn['bn_alt']); ?>" id="bn_alt" class="frm_input" size="80">
        </td>
    </tr>

    <tr>
        <th scope="row"><label for="bn_url">링크</label></th>
        <td>
            <?php echo help("배너클릭시 이동하는 주소입니다."); ?>
            
            <input type="text" name="bn_url" size="80" value="<?php echo get_sanitize_input($bn['bn_url']); ?>" id="bn_url" class="frm_input">
            <select name="bn_new_win" id="bn_new_win">
                <option value="0" <?php echo get_selected($bn['bn_new_win'], 0); ?>>링크로 열기</option>
                <option value="1" <?php echo get_selected($bn['bn_new_win'], 1); ?>>새창으로 열기</option>
            </select>
        </td>
    </tr>

    <tr>
        <th scope="row">이미지</th>
        <td>
            <input type="file" name="bn_bimg">
            <?php
            $bimg_str = "";
            $bimg = G5_DATA_PATH."/banner/{$bn['bn_id']}";
            if (file_exists($bimg) && $bn['bn_id']) {
                $size = @getimagesize($bimg);
                if($size[0] && $size[0] > 750)
                    $width = 750;
                else
                    $width = $size[0];

                $bimg_str = '<img src="'.G5_DATA_URL.'/banner/'.$bn['bn_id'].'" >';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">이미지 정보</th>
        <td>
            <?php
                if($size){
                    echo("<div>");
                    echo("너비 : " . $size[0] . "px &nbsp; &nbsp; | &nbsp; &nbsp; 높이 : " . $size[1] . "px  &nbsp; &nbsp; ");
                    echo'<input type="checkbox" name="bn_bimg_del" value="1" id="bn_bimg_del"> <label for="bn_bimg_del">이미지 삭제</label>';
                    echo("</div>");
                }
                if($bimg_str) {
                    echo '<div class="banner_or_img" style="width:1060px; height:'.$size[1].'px; text-align:center; background-color: #f0f0f0; ">';
                    echo $bimg_str;
                    echo '</div>';
                } else {
                    echo("
                        <div style='width:1060px; height:200px; text-align:center; background-color: #e6e6e6;padding: 90px 0px; font-size: 35px; color: #aeaeae; font-weight: bold;'>
                            배너 등록 후 확인이 가능 합니다.
                        </div>
                    ");
                }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bn_begin_time">시작일시</label></th>
        <td>
            <?php echo help("배너 게시 시작일시를 설정합니다."); ?>
            <input type="text" name="bn_begin_time" value="<?php echo $bn['bn_begin_time']; ?>" id="bn_begin_time" class="frm_input"  size="21" maxlength="19">
            <input type="checkbox" name="bn_begin_chk" value="<?php echo date("Y-m-d 00:00:00", time()); ?>" id="bn_begin_chk" onclick="if (this.checked == true) this.form.bn_begin_time.value=this.form.bn_begin_chk.value; else this.form.bn_begin_time.value = this.form.bn_begin_time.defaultValue;">
            <label for="bn_begin_chk">오늘</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bn_end_time">종료일시</label></th>
        <td>
            <?php echo help("배너 게시 종료일시를 설정합니다."); ?>
            <input type="text" name="bn_end_time" value="<?php echo $bn['bn_end_time']; ?>" id="bn_end_time" class="frm_input" size=21 maxlength=19>
            <input type="checkbox" name="bn_end_chk" value="<?php echo date("Y-m-d 23:59:59", time()+60*60*24*31); ?>" id="bn_end_chk" onclick="if (this.checked == true) this.form.bn_end_time.value=this.form.bn_end_chk.value; else this.form.bn_end_time.value = this.form.bn_end_time.defaultValue;">
            <label for="bn_end_chk">오늘+31일</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bn_end_time">사용여부</label></th>
        <td>
            <?php echo help("배너의 사용 여부를 결정 합니다."); ?>
            <input type="radio" name="bn_status" value="Y" id="bn_status"<?php echo (($bn['bn_status']=="Y")?" checked":""); ?>> <label for="bn_status">사용</label>
            &nbsp;  &nbsp;  &nbsp;  &nbsp; 
            <input type="radio" name="bn_status" value="N" id="bn_status"<?php echo (($bn['bn_status']=="N")?" checked":""); ?>> <label for="bn_status">미사용</label>
        </td>
    </tr>
    </tbody>
    </table>
</div>


<input type="hidden" name="bn_order" value="<?php echo $bn['bn_order']; ?>">
<input type="hidden" name="bn_title" value="<?php echo $bn['bn_title']; ?>">
<input type="hidden" name="bn_border" value="<?php echo $bn['bn_border']; ?>">
<input type="hidden" name="bn_content" value="<?php echo $bn['bn_content']; ?>">
<input type="hidden" name="bn_bgcolor" value="<?php echo $bn['bn_bgcolor']; ?>">
<input type="hidden" name="bn_device" value="<?php echo ($bn['bn_device'])?($bn['bn_device']):"both"; ?>">


<div class="btn_fixed_top">
    <a href="./bannerlist.php" class="btn_02 btn">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
