<?php
include_once('./_common.php');

$g5['title'] = "직원정보";
include_once("./_head.php");

$cm_code = clean_xss_tags($_GET['cm_code']);
$cm = sql_fetch("
    SELECT * FROM center_member
    WHERE mb_id = '{$member['mb_id']}' and cm_code = '$cm_code'
");
if(!$cm['cm_id'])
    alert('해당 직원이 존재하지 않습니다.');
$cm['info'] = get_center_member_info_text($cm);

add_stylesheet('<link rel="stylesheet" href="'.THEMA_URL.'/assets/css/center.css">', 0);
?>

<section class="wrap">
    <div class="sub_section_tit clear">
        직원정보
        <div class="r_btn_area" style="float: right">
            <a href="center_member_list.php" class="btn eroumcare_btn2">목록</a>
        </div>
    </div>
    <ul class="emp_list" style="margin-top: 0">
        <li>
            <div class="emp_info_wr flex" style="padding: 15px 0;">
                <img src="<?php echo G5_DATA_URL.'/center/member/'.$cm['cm_img']; ?>" class="emp_img" onerror="this.src='/img/no_img.png';">
                <div class="emp_info">
                    <p class="name">
                        <?=$cm['cm_name']?>
                    </p>
                    <p class="info">
                        <?=$cm['info']?>
                    </p>
                    <ul class="detail">
                        <li>
                            <?php if($cm['cm_joindate']) { echo $cm['cm_joindate'] . '(입사) ~ '; if($cm['cm_retired']) { echo $cm['cm_retiredate'] . '(퇴사)'; } else { echo '활동중'; } } ?>    
                        </li>
                        <li>
                            · 연락처 : <?=$cm['cm_hp']?>
                        </li>
                        <li>
                            · 주소 : <?=$cm['cm_addr']?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="emp_btn_wr">
                <div class="mv_btn_row">
                    <a href="center_member_form.php?w=u&cm_code=<?=$cm['cm_code']?>" class="btn_etc">정보수정</a>
                </div>
                <div class="mv_btn_row">
                    <a href="#" class="btn_schedule inline">방문일정</a>
                    <a href="#" class="btn_etc">서류/서식</a>
                </div>
            </div>
        </li>
    </ul>
    <div class="mv_section">
        <div class="mv_section_hd">
            <h3>급여관리</h3>
            <div class="flex space-between">
                <select class="btn_etc" id="sel_pay">
                    <option value="2021">2021년</option>
                </select>
                <a href="#" class="btn_etc">다운로드</a>
            </div>
        </div>
        <div class="list_box">
            <table>
                <thead>
                    <tr>
                        <th>기준월</th>
                        <th>급여총액</th>
                        <th>공제</th>
                        <th>소득세</th>
                        <th>사회보험</th>
                        <th>지급급여</th>
                        <th>명세서</th>
                        <th>지급관리</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="mv_section">
        <div class="mv_section_hd flex align-items space-between">
            <h3>관리기록</h3>
            <a href="#" class="btn_etc">신규등록</a>
        </div>
        <div class="list_box">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>제목</th>
                        <th>등록일</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

<?php
include_once('./_tail.php');
?>
