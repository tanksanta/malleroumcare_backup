<?php
$sub_menu = '500050';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '수급자연결관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

if (!$is_development) {
    alert('준비중 입니다.');
}

$sql_common = " from g5_recipient_link ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<div class="local_ov01 local_ov">
    <form name="flist" class="local_sch01 local_sch">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <select name="sel_field" id="sel_field">
        <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>수급자</option>
        <option value="a.it_id" <?php echo get_selected($sel_field, 'a.it_id'); ?>>사업소</option>
    </select>

    <label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="frm_input required">
    <input type="submit" value="검색" class="btn_submit">

    </form>

    <div style="text-align:right">
        <a class="btn btn_01 btn" href="<?php echo G5_ADMIN_URL; ?>/shop_admin/recipient_link_form.php">등록</a>
    </div>
</div>

<div class="btn_fixed_top">
    <a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/recipient_link_form.php" class="btn_01 btn">등록</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="th_id">ID</th>
        <th scope="col" id="th_info">수급자정보</th>
        <th scope="col" id="th_address">주소</th>
        <th scope="col" id="th_end">연결사업소</th>
        <th scope="col" id="th_state">상태</th>
        <th scope="col" id="th_datetime">최근 수정 시간</th>
        <th scope="col" id="th_edit" style="width:100px">비고</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * from g5_recipient_link $sql_search
          order by rl_id desc
          limit $from_record, $rows  ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td headers="th_id" class="td_num"><?php echo $row['rl_id']; ?></td>
        <td headers="th_info">
            <?php echo get_text($row['rl_name']); ?>
            <?php echo $row['rl_ltm'] ? '(' . $row['rl_ltm'] . ')' : ''; ?>
        </td>
        <td headers="th_address">
            <?php echo get_text($row['rl_addr1']); ?>
            <?php echo get_text($row['rl_addr2']); ?>
            <?php echo get_text($row['rl_addr3']); ?>
        </td>
        <td headers="th_end" class="">

        </td>
        <td headers="th_state" class="" style="text-align:center">
            <?php echo $recipient_state[$row['rl_state']]; ?>
        </td>
        <td headers="th_datetime" class="td_datetime"><?php echo $row['rl_updated_at']; ?></td>
        <td headers="th_edit" class="" style="text-align:center;">
            <a target="_blank" href="./recipient_link_form.php?'.$qstr.'&amp;w=u&amp;rl_id=<?php echo $row['rl_id']; ?>" class="btn btn_03">수정</a>
        </td>
    </tr>

    <?php
    }
    if ($i == 0) {
    echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
jQuery(function($) {
    $(".sbn_img_view").on("click", function() {
        $(this).closest(".td_img_view").find(".sbn_image").slideToggle();
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
