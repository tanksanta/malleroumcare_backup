<?php
$sub_menu = '500700';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '외부발주 목록';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$colspan = 8;
// $listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'">처음</a>'; //페이지 처음으로 (초기화용도)

?>

<form name="outsourcinglist" id="outsourcinglist" method="get" onsubmit="return outsourcing_submit_function(this);">

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
            <strong>입고</strong>
            <input type="radio" id="oo_important_all" name="oo_important" value="" <?php echo option_array_checked('', $oo_important); ?>><label for="oo_important_all"> 전체</label>
            <input type="radio" id="oo_important_0" name="oo_important" value="0" <?php echo option_array_checked('0', $oo_important); ?>><label for="oo_important_0">미입고</label>
            <input type="radio" id="oo_important_1" name="oo_important" value="1" <?php echo option_array_checked('1', $oo_important); ?>><label for="oo_important_1">입고</label>
        </div>
    </div>

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <strong>검색</strong>
            <select name="sfl" id="bn_position">
                <option value="all" <?php echo ($sfl=="all")?"selected":""?>>전체</option>
                <option value="oo_id" <?php echo ($sfl=="account")?"selected":""?>>발주번호</option>
                <option value="mb_name" <?php echo ($sfl=="name")?"selected":""?>>거래처명</option>
                <option value="od_id" <?php echo ($sfl=="memo")?"selected":""?>>주문번호</option>
            </select>
            <label for="sch_word" class="sound_only"></label>
            <input type="text" name="stx" size="20" value="<?php echo stripslashes($stx); ?>" id="sch_word" class="frm_input" placeholder="">
        </div>
    </div>

    <div class="local_sch03 local_sch">
        <div class="sch_last">
            <input type="submit" value="검색" class="btn_submit" id="oursourcing_submit" style="width:100px;height:30px;">
        </div>
    </div>
</form>

<div class="tbl_wrap tbl_head01 tbl_albank_memo_list">
    <table>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">거래처</th>
        <th scope="col">주문번호</th>
        <th scope="col">담당자</th>
        <th scope="col">입고확인</th>
        <th scope="col">희망납기일</th>
        <th scope="col">전송일시</th>
        <th scope="col">보기</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql_common = " from 
        g5_shop_order_outsourcing as a 
        LEFT JOIN g5_member as b ON a.oo_outsourcing_id = b.mb_id 
        LEFT JOIN g5_shop_order c ON a.od_id = c.od_id
    ";

    // if ($stx) {
    //     if ($sql_search) {
    //         $sql_search .= " AND ";
    //     }else{
    //         $sql_search .= " WHERE ";
    //     }

    //     $sql_search .= " name LIKE '%{$stx}%' ";
    // }

    if ($fr_date && $to_date) {
        $where[] = " oo_created_at between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    }

    if (gettype($oo_important) == 'string' && $oo_important !== '') {
        $oo_important = (int)$oo_important;
        $where[] = " oo_important = '$oo_important' ";
    }

    if ($sfl && $stx) {
        if ($sfl == 'all') {
            $where[] = " ( a.oo_id LIKE '%{$stx}%' OR b.mb_name LIKE '%{$stx}%' OR a.od_id LIKE '%{$stx}%' ) ";
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

    $sql = " select a.*, b.mb_nick, b.mb_email, b.mb_name, c.od_ex_date, c.od_sales_manager
                {$sql_common}
                {$sql_search}
                order by oo_id desc
                limit {$from_record}, {$rows} ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++) {

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id" style="text-align:center;width:30px;"><?php echo $row['oo_id']; ?></td>
        <td class="td_idsmall td_left" style="width:20%">
            <?php
            $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'] ? $row['mb_nick'] : $row['mb_name'], $row['mb_email'], $row['mb_homepage']);
            ?>
            <?php echo $mb_nick; ?>
            <?php echo $row['mb_email'] ? '(' . $row['mb_email'] . ')' : ''; ?>
        </td>
        <td class="td_idsmall td_center"><?php echo $row['od_id']; ?></td>
        <td class="td_idsmall td_center">
            <?php
            $od_sales_manager = get_member($row['od_sales_manager']);
            if ($od_sales_manager) {
                $od_sales_manager_show = get_sideview($od_sales_manager['mb_id'], $od_sales_manager['mb_nick'], $od_sales_manager['mb_email'], $od_sales_manager['mb_homepage']);
                echo $od_sales_manager_show;
            }else{
                echo '없음';
            }
            ?>
        </td>
        <td class="td_idsmall td_center" style="width:10%">
            <span class="icon-star-gray hand list-important important-25 <?php echo $row['oo_important'] ? 'on' : ''; ?> " data-oo-id="<?php echo $row['oo_id']; ?>"></span>
        </td>
        <td class="td_idsmall td_center"><?php echo $row['od_ex_date']; ?></td>
        <td class="td_idsmall td_center">
            <?php echo substr($row['oo_created_at'], 5, 5); ?>
            (<?php echo substr($row['oo_created_at'], 11, 5); ?>)
        </td>
        <td class="td_idsmall td_center ">
            <a href="./samhwa_orderform.php?od_id=<?php echo $row['od_id']; ?>" class="btn btn_01">보기</a>
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

function outsourcing_submit_function(f)
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

    $( document ).on( "click", '.important-25', function() {
        var oo_id = $(this).data('oo-id');

        var data = {
            oo_id: oo_id,
            val: $(this).hasClass('on')
        }
        var obj = this;

        var ajax = $.ajax({
            method: "POST",
            url: "./ajax.outsourcing.important.php",
            data: data,
        })
        .done(function(data) {
            if ( data.msg ) {
                alert(data.msg);
            }
            if (data.result === 'success') {
                if (data.value === true) {
                    $(obj).addClass('on');
                }else{
                    $(obj).removeClass('on');
                }
            }
        });
    });
});

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
