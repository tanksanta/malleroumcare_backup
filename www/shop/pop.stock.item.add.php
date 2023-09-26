<?php
include_once('./_common.php');

if (!$member['mb_id']) {
    alert("로그인이 필요합니다.");
}

// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_category_table']} ";
$sql .= " order by ca_order, ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;
    $nbsp = '';
    for ($i=0; $i<$len; $i++) {
        $nbsp .= '&nbsp;&nbsp;&nbsp;';
    }

	if($row['as_line']) {
		$ca_list .= "<option value=\"\">".$nbsp."------------</option>\n";
	}

    $ca_list .= '<option value="'.$row['ca_id'].'">'.$nbsp.$row['ca_name'].'</option>'.PHP_EOL;
}

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
// $sql_search .= " $where a.it_use = 1 "; // 판매 상품 // 230503 판매 불가 제품이더라고 재고는 추가할 수 있도록 수정

if ($sfl == "")  $sfl = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a
                JOIN {$g5['g5_shop_category_table']} b ON a.ca_id = b.ca_id and b.ca_use='1'
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

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0,user-scalable=yes,maximum-scale=2,width=device-width" /><meta http-equiv="imagetoolbar" content="no">
<title>보유재고 등록 > 상품선택</title>
<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/popup.css?v=<?php echo time(); ?>">
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
            <li class="on">상품선택</li>
            <li class="arrow">
                <img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/icon_arrow_next.png" />
            </li>
            <li class="">옵션선택</li>
        </ul>
    </div>
    <div class="content">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="./pop.stock.item.add.php" onsubmit="return true;" method="get" autocomplete="off">
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
        <form class="form-horizontal popadditem" role="form" name="popadditem" action="./pop.stock.item.add.option.php" onsubmit="return true;" method="post" autocomplete="off">
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
                    ?>
                    <tr>
                        <td class="td-left image">
                            <a href="./item.php?it_id=<?php echo $row['it_id']; ?>" target="_blank">
                                <?php echo get_it_image($row['it_id'], 50, 50); ?>
                                <?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>
                            </a>
                        </td>
                        <td>
                            <a href="./pop.stock.item.add.option.php?it_id=<?php echo $row['it_id']; ?>" class="shbtn lineblue">선택</a>
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
            <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;od_id='.$od_id.'&amp;page='); ?>
        </form>
    </div>
</div>

</body>
</html>
