<?php
include_once('./_common.php');

if ($member['mb_type'] !== 'default') {
    alert("사업소 회원만 접근 가능합니다.");
}

$action = 'pop.item.select.php';
$no_option = $_GET['no_option'] ?: '';

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "" && $sfl != 'all') {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($sfl == 'all') {
        $sql_search .= " $where ( it_model like '%$stx%' OR it_name like '%$stx%' OR it_id like '%$stx%' OR pt_id like '%$stx%' ) ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

$sql_search .= " $where (a.ca_id LIKE '10%' OR a.ca_id LIKE '20%') ";
$sql_search .= " $where a.it_id NOT IN ('PRO2021072200013', 'PRO2021072200012') "; // 체험상품 제외
$sql_search .= " $where a.it_name NOT LIKE 'test%' "; // 테스트 상품 제외
$sql_search .= " $where a.it_use = 1 "; // 판매 상품

if ($sfl == "")  $sfl = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a
                JOIN {$g5['g5_shop_category_table']} b ON a.ca_id = b.ca_id
               where 1=1 ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
	$sst = "it_id";
    $sod = "desc";
}

if($sst == 'it_id') {
	$pth = "a.pt_num desc,";
	$ptt = "";
} else {
	$pth = "";
	$ptt = ", a.pt_num desc";
}

$sql_order = "order by $pth $sst $sod $ptt";

$sql  = " select
           it_id,
           it_name,
           it_model,
           it_price,
           it_cust_price,
           a. ca_id,
           it_img1 as it_img
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = "no_option={$no_option}&sfl={$sfl}&stx={$stx}&save_stx={$stx}&sca={$sca}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=2,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<title>상품선택</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
<script src="<?php echo G5_JS_URL ?>/jquery-1.11.3.min.js"></script>
</head>
<style>
#pop_add_item .popadditemsearch input[type="text"] {
    min-width: 30px;
    width: calc(100% - 162px);
}
.pg_page, .pg_current {
    padding: 0 8px;
}
</style>
<div id="pop_add_item" class="admin_popup">
    <div class="header">
        <ul class="add_item_header">
            <li class="on" <?php if($no_option) echo 'style="width: 100%"'; ?>>상품선택</li>
            <?php if(!$no_option) { ?>
            <li class="arrow">
                <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_next.png" />
            </li>
            <li class="">옵션선택</li>
            <?php } ?>
        </ul>
    </div>
    <div class="content">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="<?=$action?>" onsubmit="return true;" method="get" autocomplete="off">
            <input type="hidden" name="no_option" value="<?=$no_option?>">
            <label for="sca" class="sound_only">분류선택</label>

            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <option value="all" <?php echo get_selected($sfl, 'all'); ?>>전체</option>
                <option value="it_model" <?php echo get_selected($sfl, 'it_model'); ?>>모델명</option>
                <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
                <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
            </select>

            <label for="stx" class="sound_only">검색어</label>
            <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
            <input type="submit" value="검색" class="btn_submit shbtn">

        </form>
        <table class="popup_table">
            <thead>
                <tr>
                    <th>상품명</th>
                    <th>선택</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array($result); $i++) {
                    $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
                    $gubun_text = '판매';
                    if($gubun == '01') $gubun_text = '대여';
                    else if($gubun == '02') $gubun_text = '비급여';

                    $row['gubun'] = $gubun_text;
                ?>
                <tr>
                    <td class="td-left image">
                        <a href="./item.php?it_id=<?php echo $row['it_id']; ?>" target="_blank">
                            <?php echo get_it_image($row['it_id'], 50, 50); ?>
                            <?php echo "[{$row['gubun']}] "; ?>
                            <?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>
                        </a>
                    </td>
                    <td>
                        <?php if(!$no_option) { ?>
                        <a href="./pop.stock.item.add.option.php?it_id=<?php echo $row['it_id']; ?>" class="shbtn lineblue">선택</a>
                        <?php } else { ?>
                        <a href="javascript:void(0);" data-item="<?php echo get_text(json_encode($row)); ?>" class="shbtn lineblue" onclick="select_item(this);">선택</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                }
                ?>
                <?php if ( $i == 0 ) { ?>
                <tr>
                    <td colspan="6" class="no_item">
                        검색된 상품이 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
    </div>
</div>

<?php if($no_option) { ?>
<script>
function select_item(target) {
    var $this = $(target);

    window.parent.select_item($this.data('item'));
}
</script>
<?php } ?>

</body>
</html>