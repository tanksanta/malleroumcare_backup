<?php
$sub_menu = '200400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = "
    FROM
        g5_alimtalk
";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} order by al_id desc limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$list = [];
for($i = 0; $row = sql_fetch_array($result); $i++) {
    if($row['al_type'] == 1) {
        $row['member'] = [];

        $sql = "
            select
                m.*
            from
                g5_alimtalk_member a
            left join
                g5_member m on a.mb_id = m.mb_id
            where
                al_id = '{$row['al_id']}'
            order by
                a.mb_id asc
        ";
        $mb_result = sql_query($sql);

        while($mb = sql_fetch_array($mb_result)) {
            $row['member'][] = $mb;
        }
    }

    $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;
    $list[] = $row;
}

$g5['title'] = '회원 알림톡/푸시 발송';
include_once('./admin.head.php');
?>

<div class="local_desc01 local_desc">
    <p>
        <b>테스트</b>는 등록된 최고관리자의 휴대폰 번호로 테스트 알림톡을 발송합니다.<br>
        현재 등록된 알림톡은 총 <?php echo $total_count ?>건입니다.<br>
    </p>
</div>

<form name="falimtalklist" id="falimtalklist" action="./alimtalk_delete.php" method="post">
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
        <tr>
            <th scope="col"><input type="checkbox" name="chkall" value="1" id="chkall" title="현재 페이지 목록 전체선택" onclick="check_all(this.form)"></th>
            <th scope="col">번호</th>
            <th scope="col">제목</th>
            <th scope="col">작성일시</th>
            <th scope="col">마지막전송일시</th>
            <th scope="col">테스트</th>
            <th scope="col">보내기</th>
            <th scope="col">대상</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($list as $row) { ?>
            <tr>
                <td class="td_chk">
                    <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $row['al_subject']; ?> 알림톡</label>
                    <input type="checkbox" id="chk_<?php echo $i ?>" name="chk[]" value="<?php echo $row['al_id'] ?>">
                </td>
                <td class="td_num_c">
                    <?php echo $row['index']; ?>
                </td>
                <td class="td_left">
                    <a href="./alimtalk_form.php?w=u&amp;al_id=<?php echo $row['al_id'] ?>"><?php echo $row['al_subject'] ?></a>
                </td>
                <td class="td_datetime">
                    <?php echo $row['created_at']; ?>
                </td>
                <td class="td_datetime">
                    <?php echo $row['lastsent_at']; ?>
                </td>
                <td class="td_test">
                    <a href="./alimtalk_send.php?test=1&al_id=<?php echo $row['al_id'] ?>">테스트</a>
                </td>
                <td class="td_send">
                    <a href="./alimtalk_send.php?al_id=<?php echo $row['al_id'] ?>">보내기</a>
                </td>
                <td class="td_name">
                    <?php
                    if($row['al_type'] == 0) {
                        echo '전체사업소';
                    } else {
                        foreach($row['member'] as $mb) {
                            echo $mb['mb_name'].'<br>';
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
    </table>
</div>
<div class="btn_fixed_top">
    <input type="submit" value="선택삭제" class="btn btn_02">
    <a href="./alimtalk_form.php" id="alimtalk_add" class="btn btn_01">알림톡추가</a>
</div>
</form>

<?php
echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page=');
?>

<script>
$(function() {
    $('#falimtalklist').submit(function() {
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
include_once ('./admin.tail.php');
?>