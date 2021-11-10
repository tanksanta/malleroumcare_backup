<?php
$sub_menu = '400300';
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

auth_check($auth[$sub_menu], "w");

$it_id = get_search_string($_GET['it_id']);
$it = sql_fetch(" select * from g5_shop_item WHERE it_id = '$it_id' ");
if(!$it['it_id'])
    alert('존재하지 않는 상품입니다.');

$title = '사업소별 가격지정';
include_once('./pop.head.php');

$qstr = "it_id=$it_id";

$sql_common = " FROM g5_member m WHERE mb_type = 'default' and mb_level in (3, 4) ";

$where = [];
$sel_field = in_array($sel_field, ['mb_name']) ? $sel_field : '';
$search = get_search_string($search);

if($sel_field && $search) {
    $where[] = " $sel_field like '%$search%' ";
    $qstr .= "&amp;$sel_field=".urlencode($search);
}

$sql_where = $where ? ( ' and ' . implode(' and ', $where) ) : '';
$sql_common .= $sql_where;

// 총 개수 구하기
$total_count = sql_fetch(" SELECT count(*) as cnt {$sql_common} ", true)['cnt'];
$page_rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

$sql_limit = " limit {$from_record}, {$page_rows} ";

$result = sql_query("
    SELECT m.*, (select mb_name from g5_member where mb_id = m.mb_manager) as mb_manager_name
    $sql_common
    $sql_limit
", true);

$list = [];
for($i = 0; $row = sql_fetch_array($result); $i++) {
    $row['index'] = $total_count - (($page - 1) * $page_rows) - $i;

    $list[] = $row;
}
?>

<div id="pop_entprice" class="admin_popup admin_popup_padding">
    <h4 class="h4_header"><?php echo $title; ?></h4>
    <div class="sch_wr" style="border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px;">
        <h5 style="font-size: 16px; font-weight: bold;">
            <?php
            echo "{$it['it_name']} (판매가격 : " . number_format($it['it_price']) . "원)";
            ?>
        </h5>
        <form name="fentprice" class="form" role="form" method="GET" action="./pop.item.entprice.php" onsubmit="return formcheck(this);">
            <input type="hidden" name="it_id" value="<?=$it_id?>">
            <label for="sel_field" class="sound_only">검색대상</label>
            <select name="sel_field" id="sel_field">
                <option value="mb_name" <?php echo get_selected($sel_field, 'mb_name'); ?>>사업소명</option>
            </select>
            <input type="text" name="search" value=""class="frm_input">
            <input type="submit" class="shbtn small" value="검색">
        </form>
    </div>

    <form onsubmit="return false;">
    <input type="hidden" id="it_id" value="<?=$it_id?>">
    <table class="tbl_entprice pop_order_add_item_table">
        <colgroup>
            <col width="80px" />
            <col />
            <col width="100px" />
            <col width="100px" />
            <col width="50px" />
        </colgroup>
        <thead>
            <tr>
                <th>
                    No.
                </th>
                <th>
                    사업소명
                </th>
                <th>
                    영업담당자명
                </th>
                <th>
                    판매가격
                </th>
                <th>
                    적용
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $row) { ?>
            <tr>
                <td class="no"><?=$row['index']?></td>
                <td><?="{$row['mb_name']} ({$row['mb_id']})"?></td>
                <td class="it_option"><?=$row['mb_manager_name']?></td>
                <td>
                    <input type="text" class="ipt_entprice">
                </td>
                <td class="no">
                    <button type="button" class="shbtn small lineblue btn_apply">적용</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    </form>

    <?php echo get_paging(5, $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div>

<?php include_once('./pop.tail.php'); ?>