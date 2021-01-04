<?php
$sub_menu = "777106";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

// 체크된 자료 삭제
if (isset($_POST['chk']) && is_array($_POST['chk'])) {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        $dt_no = $_POST['chk'][$i];
        sql_query(" delete from g5_domain_thema where dt_no = '$dt_no' ", true);
    }
}

$sql_common = " from g5_domain_thema as a ";
$sql_order = ' order by dt_no asc ';

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * 
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '도메인별 테마 관리';
include_once('./admin.head.php');

$colspan = 7;
?>
<style>
.kookis_ov {
    margin:10px 0 30px 0;
}
.kookis_ov .ov_now {
    background-color:#3f51b5;
    border:1px solid #3f51b5;
    color:#fff;
    border-radius:20px;
    padding:5px 10px;
    margin-right:10px;
}
.kookis_ov .ov_another {
    background-color:#fff;
    border:1px solid #3f51b5;
    color:#3f51b5;
    border-radius:20px;
    padding:5px 10px;
    margin-right:10px;
}
.kookis_ov .ov_another:hover {
    background-color:#3f51b5;
    border:1px solid #3f51b5;
    color:white;
}
</style>
<div class="local_ov01 local_ov">
        <?php echo $listall ?>
        <span class="btn_ov01"><span class="ov_txt">건수</span><span class="ov_num">  <?php echo number_format($total_count) ?>개</span></span>
</div>

<div class="local_desc01 local_desc">
    <p>도메인별로 테마를 관리할 수 있습니다.<!--<br/>이곳에 없는 도메인으로 접속시 아미나빌더에 설정된 테마로 연결됩니다.--><br/>이곳에 없는 도메인으로 사이트 접속이 불가능합니다.</p>
</div>

<form name="fmembercatelist" id="fmembercatelist" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo $token ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" style="width:50px;">번호</th>
        <th scope="col">도메인</th>
        <th scope="col" style="width:200px;">테마폴더명</th>
        <th scope="col" style="width:130px;">테마고유키</th>
        <th scope="col" style="width:380px;">메모</th>
        <th scope="col" style="width:100px;">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);

    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $word ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['dt_no'] ?>" id="chk_<?php echo $i ?>">
        </td>
        <td style="font-size:11px;text-align:left;"><?php echo $row['dt_no']; ?></td>
        <td style="text-align:left;"><?php echo $row['dt_domain']; ?></td>
        <td><?php echo $row['dt_thema']; ?></td>
        <td><?php echo $row['dt_key']; ?></td>
        <td><?php echo $row['dt_memo']; ?></td>
        <td style="width:100px;text-align:center;">
            <a class="mng_mod btn btn_02" href="./domain_thema_list_form.php?w=u&dt_no=<?php echo $row['dt_no']; ?>">수정</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</div>

<?php if ($is_admin == 'super'){ ?>
<div class=" btn_fixed_top">
    <button type="submit" class="btn btn_02">선택삭제</button>
    <a href="./domain_thema_list_form.php" class="btn btn_01">추가</a>
</div>
<?php } ?>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $('#fmembercatelist').submit(function() {
        if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
            if (!is_checked("chk[]")) {
                alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
                return false;
            }

            return true;
        } else {
            return false;
        }
    });
});
</script>

<?php
include_once('./admin.tail.php');
?>
