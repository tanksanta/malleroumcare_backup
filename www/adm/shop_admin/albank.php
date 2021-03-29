<?php
$sub_menu = '500600';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '무통장(알뱅킹)';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$colspan = 7;
// $listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'">처음</a>'; //페이지 처음으로 (초기화용도)

?>
<style>
.tbl_albank_memo_list td {
    height:50px;
}
.btn_albank_memo {
    position:relative;
    width: 100%;
    height: 100%;
    line-height:35px;
    text-align:left;
}
.btn_albank_memo input[type="button"] {
    cursor:pointer;
}
.btn_albank_memo .btn_albank_memo_edit {
    position:absolute;
    top: 5px;
    right: 5px;
}
.btn_albank_memo_after {
    display:none;
}
</style>

<form name="falbanklist" id="falbanklist" method="get" onsubmit="return falbanklist_submit_function(this);">

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <strong>입금일</strong>
            <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
            <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
            <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
            <button type="button" onclick="javascript:set_date('어제');">어제</button>
            <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
            <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
            <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
            <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
            <button type="button" onclick="javascript:set_date('전체');">전체</button>
        </div>
    </div>

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <strong>입금액</strong>
            <input type="text" name="price_s" value="<?php echo $price_s; ?>" class="line frm_input" maxlength="10" style="width:80px">
            원 ~
            <input type="text" name="price_e" value="<?php echo $price_e; ?>" class="line frm_input" maxlength="10" style="width:80px">
            원
        </div>
    </div>

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <strong>검색</strong>
            <select name="bankcode">
                <option value="">전체은행</option>
                <?php foreach($albank_bank_codes as $bank) { ?>
                    <?php if (!$bank['val']) continue; ?>
                    <option value="<?php echo $bank['val']; ?>" <?php echo $bank['val'] && $bank['val'] == $bankcode ? 'selected' : ''; ?>><?php echo $bank['name']; ?></option>
                <?php } ?>
            </select>
            <select name="sfl" id="bn_position">
                <option value="all" <?php echo ($sfl=="all")?"selected":""?>>전체</option>
                <option value="account" <?php echo ($sfl=="account")?"selected":""?>>계좌번호</option>
                <option value="name" <?php echo ($sfl=="name")?"selected":""?>>거래내역</option>
                <option value="memo" <?php echo ($sfl=="memo")?"selected":""?>>메모</option>
            </select>
            <label for="sch_word" class="sound_only"></label>
            <input type="text" name="stx" size="20" value="<?php echo stripslashes($stx); ?>" id="sch_word" class="frm_input" placeholder="">
        </div>
    </div>

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <input type="submit" value="검색" class="btn_submit" id="falbanklist_submit" style="width:100px;height:30px;">
            <input type="button" value="엑셀다운로드" class="btn btn_02" id="albankexcel" style="width:100px;height:30px;font-size:12px;cursor:pointer;">
        </div>
    </div>
</form>

<div class="tbl_wrap tbl_head01 tbl_albank_memo_list">
    <table>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">일자</th>
        <th scope="col">은행명</th>
        <th scope="col">내계좌</th>
        <th scope="col">거래내역</th>
        <th scope="col">입금액</th>
        <!--<th scope="col">잔액</th>-->
        <th scope="col">메모</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql_common = " from tb_log_bank ";
    if ($bankcode) {
        $sql_search = " where bankcode like '$bankcode' ";
    }
    // if ($stx) {
    //     if ($sql_search) {
    //         $sql_search .= " AND ";
    //     }else{
    //         $sql_search .= " WHERE ";
    //     }

    //     $sql_search .= " name LIKE '%{$stx}%' ";
    // }

    if ($fr_date && $to_date) {
        $where[] = " paydt between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    }

    if ( $price_s && $price_e ) {
        $where[] = " price BETWEEN '{$price_s}' AND '{$price_e}' ";
    }

    if ($sfl && $stx) {
        if ($sfl == 'all') {
            $where[] = " ( `account` LIKE '%{$stx}%' OR `name` LIKE '%{$stx}%' OR `memo` LIKE '%{$stx}%' ) ";
        } else {
            $where[] = " {$sfl} LIKE '%{$stx}%' ";
        }
    }

    if(!$sfl) $sfl = 'all';

    if ($where) {
        if ($sql_search) {
            $sql_search .= " AND ";
        }else{
            $sql_search .= " WHERE ";
        }

        $sql_search .= implode(' and ', $where);
    }

    $sql = " select count(*) as cnt
                {$sql_common}
                {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];

    $rows = $config['cf_page_rows'];
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $sql = " select *
                {$sql_common}
                {$sql_search}
                order by no desc
                limit {$from_record}, {$rows} ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {

        if ($is_admin == 'super')
            $ip = $row['vi_ip'];
        else
            $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $row['vi_ip']);

        $bg = 'bg'.($i%2);

        $bank = get_albank_bank_step($row['bankcode']);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id" style="text-align:center;width:50px;"><?php echo $row['no']; ?></td>
        <td class="td_datetime" style="max-width:200px;"><?php echo $row['paydt']; ?></td>
        <td class="td_idsmall td_left"><?php echo $bank['name']; ?></td>
        <td class="td_idsmall td_left"><?php echo $row['account']; ?></td>
        <td class="td_idsmall td_left"><?php echo $row['name']; ?> <?php echo $row['price'] > 0 ? '입금' : '출금'; ?></td>
        <td class="td_idsmall"><?php echo number_format($row['price']); ?>원</td>
        <td class="td_idsmall" style="width:20%;text-align:center;" data-no="<?php echo $row['no']; ?>">
            <div class="btn_albank_memo btn_albank_memo_before">
                <span style="display:inline-block;"><?php echo $row['memo']; ?></span>
                <input type="button" value="수정" class="btn btn_02 btn_albank_memo_edit">
            </div>
            <div class="btn_albank_memo btn_albank_memo_after">
                <input type="text" name="memo" class="frm_input" data-no="<?php echo $row['no']; ?>" value="<?php echo $row['memo']; ?>" style="width:100px;">
                <input type="button" value="저장" class="btn btn_01 btn_albank_memo_edit_submit">
            </div>
        </td>
    </tr>
    <?php } ?>
    <?php if ($i == 0) echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>'; ?>
    </tbody>
    </table>
</div>

<?php
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;domain='.$domain.'&amp;page=');
echo $pagelist;
?>

<script>
$(function(){
    $("#sch_sort").change(function(){ // select #sch_sort의 옵션이 바뀔때
        if($(this).val()=="vi_date"){ // 해당 value 값이 vi_date이면
            $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
        }else{ // 아니라면
            $("#sch_word").datepicker("destroy"); // datepicker 미실행
        }
    });

    if($("#sch_sort option:selected").val()=="vi_date"){ // select #sch_sort 의 옵션중 selected 된것의 값이 vi_date라면
        $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
    }
});

function falbanklist_submit_function(f)
{
    return true;
}

$(document).ready(function() {

    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    $('.btn_albank_memo_edit').click(function() {
        var parent = $(this).closest('td');

        $(parent).find('.btn_albank_memo_before').hide();
        $(parent).find('.btn_albank_memo_after').show();
    });

    $( document ).on( "click", '.btn_albank_memo_edit_submit', function() {
        var parent = $(this).closest('td');

        var no = $(parent).data('no');
        var memo = $(parent).find('input[name="memo"]').val();

        $.ajax({
                method: "POST",
                url : "./ajax.albank.memo.edit.php",
                data : {
                    no: no,
                    memo: memo,
                },
            })
            .done(function(data) {

                if ( data.msg ) {
                    alert(data.msg);
                }

                if ( data.result === 'success' ) {
                    location.reload();
                }
            });
    });

    $("#falbanklist_submit").on("click",function(event){
        event.preventDefault();
        $('#falbanklist').attr("action", "./albank.php");
        $('#falbanklist').submit();
    });

    $('#albankexcel').click(function() {
        $('#falbanklist').attr("action", "./excel_ab.php");
        $('#falbanklist').submit();
    });
});

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
