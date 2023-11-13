<?php
include_once('./_common.php');

if ($member['mb_type'] !== 'default') {
    alert("사업소 회원만 접근 가능합니다.");
}

$action = 'pop.item.select.php';
$no_option = $_GET['no_option'] ?: '';

$where = " and ";
$sql_search = "";

// 유통/비유통/비급여 제품 검색
if($no_option == 'nonReimbursement'){ // 간편 계약서에서 검색 시 : 유통+비유통+급여
    $sql_search .= " $where (a.ca_id LIKE '10%' OR a.ca_id LIKE '20%') ";
}else { // 이외의 검색(간편 주문서 + 주문 변경 + 간편제안서) : 유통+급여+비급여+보장구
    $sql_search .= " $where prodSupYn = 'Y'  AND it_soldout = '0' ";
    $where = " and ";
    $sql_search .= " $where (a.ca_id LIKE '10%' OR a.ca_id LIKE '20%' OR a.ca_id LIKE '70%' OR a.ca_id LIKE '80%') ";
}

$where = " and ";

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

$sql_search .= " $where (a.ca_id LIKE '10%' OR a.ca_id LIKE '20%' OR a.ca_id LIKE '70%' OR a.ca_id LIKE '80%') ";
$sql_search .= " $where a.it_id NOT IN ('PRO2021072200013', 'PRO2021072200012') "; // 체험상품 제외
$sql_search .= " $where a.it_name NOT LIKE 'test%' "; // 테스트 상품 제외
$sql_search .= " $where a.it_use = 1 "; // 판매 상품

$ca_id = get_search_string($ca_id);
if($ca_id) {
    $sql_search .= " $where a.ca_id LIKE '$ca_id%' ";
}

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

$sql  = " select
           it_id,
           it_name,
           it_model,
           it_price,
           it_cust_price,
           it_rental_price,
           a.ca_id,
           ca_name,
           it_img1 as it_img
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = "no_option={$no_option}&sfl={$sfl}&stx={$stx}&save_stx={$stx}&sca={$sca}&ca_id={$ca_id}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 카테고리 가져오기
$cate_10 = [];
$cate_20 = [];
$cate_70 = [];

// 간편 계약서 검색시 비급여 카테고리 숨기기
$sql = $no_option != 'nonReimbursement'
    ?" select * from g5_shop_category where (ca_id LIKE '10%' OR ca_id LIKE '20%' OR ca_id LIKE '70%' OR ca_id LIKE '80%') and length(ca_id) = 4 order by ca_id asc "
    :" select * from g5_shop_category where (ca_id LIKE '10%' OR ca_id LIKE '20%') and length(ca_id) = 4 order by ca_id asc ";
$cate_result = sql_query($sql);

$cate_text = '전체';
if($ca_id) {
    switch(substr($ca_id, 0, 2)) {
        case '10':
            $cate_text .= ' / 판매품목';
            break;
        case '20':
            $cate_text .= ' / 대여품목';
            break;
        case '70':
            $cate_text .= ' / 비급여품목';
            break;
		 case '80':
            $cate_text .= ' / 보장구품목';
            break;
    }
}

while($cate = sql_fetch_array($cate_result)) {
    if($ca_id == $cate['ca_id']) {
        $cate_text .= ' / ' . $cate['ca_name'];
    }

    switch(substr($cate['ca_id'], 0, 2)) {
        case '10':
            $cate_10[] = [
                'ca_id' => $cate['ca_id'],
                'ca_name' => $cate['ca_name']
            ];
            break;
        case '20':
            $cate_20[] = [
                'ca_id' => $cate['ca_id'],
                'ca_name' => $cate['ca_name']
            ];
            break;
        case '70':
            $cate_70[] = [
                'ca_id' => $cate['ca_id'],
                'ca_name' => $cate['ca_name']
            ];
            break;
		case '80':
            $cate_80[] = [
                'ca_id' => $cate['ca_id'],
                'ca_name' => $cate['ca_name']
            ];
            break;
    }
}

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
#ca_id {
    display: none;
    margin: 0 auto;
    width: calc(100% - 40px);
    height: 40px;
}
.cate_text {
    font-size: 16px;
    padding: 20px;
    padding-top: 10px;
}

#pop_add_item {
    padding-left: 180px;
}
.cate_wr {
    position: absolute;
    top: 0;
    left: 0;
    width: 180px;
    border-right: 1px solid #ccc;
    z-index: 10;
}
.cate_wr .cate {
    padding: 10px;
    border-bottom: 1px solid #ccc;
}
.cate_wr .cate a {
    display: block;
    padding: 4px;
    padding-left: 10px;
    color: #333;
    font-size: 14px;
}
.cate_wr .cate a:first-child {
    font-size: 18px;
    padding-left: 4px;
}
.cate_wr .cate a.active {
    color: #fff;
    background: #f08606;
}

@media (max-width: 960px) {
    #ca_id {
        display: block;
    }
    .cate_wr {
        display: none;
    }
    #pop_add_item {
        padding-left: 0;
    }
    .cate_text {
        padding-top: 20px;
    }
}
</style>
<div class="cate_wr">
    <div class="cate">
        <a href="?<?php echo "no_option={$no_option}"; ?>" <?php if($ca_id == '') echo 'class="active"' ?>>전체</a>
    </div>
    <div class="cate">
        <a href="?<?php echo "no_option={$no_option}"; ?>&ca_id=10" <?php if($ca_id == '10') echo 'class="active"' ?>>판매품목</a>
        <?php
        foreach($cate_10 as $cate) {
            echo "<a href=\"?no_option={$no_option}&ca_id={$cate['ca_id']}\"" . ($ca_id == $cate['ca_id'] ? ' class="active"' : '') . ">{$cate['ca_name']}</a>";
        }
        ?>
    </div>
    <div class="cate">
        <a href="?<?php echo "no_option={$no_option}"; ?>&ca_id=20" <?php if($ca_id == '20') echo 'class="active"' ?>>대여품목</a>
        <?php
        foreach($cate_20 as $cate) {
            echo "<a href=\"?no_option={$no_option}&ca_id={$cate['ca_id']}\"" . ($ca_id == $cate['ca_id'] ? ' class="active"' : '') . ">{$cate['ca_name']}</a>";
        }
        ?>
    </div>

    <?php
    // 간편 계약서 검색시 비급여 카테고리 숨기기
    if($no_option != 'nonReimbursement') {
        echo '<div class="cate">';
        $class = $ca_id == '70' ? 'class="active"' : '';
        echo '<a href="?no_option={$no_option}&ca_id=70"' . $class . '>비급여품목</a>';
        foreach ($cate_70 as $cate) {
            echo "<a href=\"?no_option={$no_option}&ca_id={$cate['ca_id']}\"" . ($ca_id == $cate['ca_id'] ? ' class="active"' : '') . ">{$cate['ca_name']}</a>";
        }
        echo '</div>';
		echo '<div class="cate">';
        $class = $ca_id == '80' ? 'class="active"' : '';
        echo '<a href="?no_option={$no_option}&ca_id=80"' . $class . '>보장구품목</a>';
        foreach ($cate_80 as $cate) {
            echo "<a href=\"?no_option={$no_option}&ca_id={$cate['ca_id']}\"" . ($ca_id == $cate['ca_id'] ? ' class="active"' : '') . ">{$cate['ca_name']}</a>";
        }
        echo '</div>';
    }
    ?>
</div>
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
        <select id="ca_id">
            <option value="">전체</option>
            <option value="10" <?=get_selected($ca_id, '10')?>>판매품목</option>
            <?php
            foreach($cate_10 as $cate) {
                echo "<option value=\"{$cate['ca_id']}\"" . get_selected($ca_id, $cate['ca_id']) . ">&nbsp;&nbsp;&nbsp;{$cate['ca_name']}</option>";
            }
            ?>
            <option value="20" <?=get_selected($ca_id, '20')?>>대여품목</option>
            <?php
            foreach($cate_20 as $cate) {
                echo "<option value=\"{$cate['ca_id']}\"" . get_selected($ca_id, $cate['ca_id']) . ">&nbsp;&nbsp;&nbsp;{$cate['ca_name']}</option>";
            }
            ?>
            <option value="70" <?=get_selected($ca_id, '70')?>>비급여품목</option>
            <?php
            foreach($cate_70 as $cate) {
                echo "<option value=\"{$cate['ca_id']}\"" . get_selected($ca_id, $cate['ca_id']) . ">&nbsp;&nbsp;&nbsp;{$cate['ca_name']}</option>";
            }
            ?>
			<option value="80" <?=get_selected($ca_id, '80')?>>보장구품목</option>
            <?php
            foreach($cate_80 as $cate) {
                echo "<option value=\"{$cate['ca_id']}\"" . get_selected($ca_id, $cate['ca_id']) . ">&nbsp;&nbsp;&nbsp;{$cate['ca_name']}</option>";
            }
            ?>
        </select>
        <div class="cate_text">
            <?php echo $cate_text; ?>
        </div>
    </div>
    <div class="content">
        <form class="form-horizontal popadditemsearch" role="form" name="popadditemsearch" action="<?=$action?>" onsubmit="return true;" method="get" autocomplete="off">
            <input type="hidden" name="no_option" value="<?=$no_option?>">
            <input type="hidden" name="ca_id" value="<?=$ca_id?>">
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
                    <th style="width:80px">선택</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i=0; $row=sql_fetch_array($result); $i++) {
                    $gubun = $cate_gubun_table[substr($row["ca_id"], 0, 2)];
                    $gubun_text = '판매';
                    if($gubun == '01') $gubun_text = '대여';
                    else if($gubun == '02') $gubun_text = '비급여';
					else if($gubun == '03') $gubun_text = '보장구';

                    $row['gubun'] = $gubun_text;
                ?>
                <tr>
                    <td class="td-left image">
                        <a href="./item.php?it_id=<?php echo $row['it_id']; ?>" target="_blank">
                            <?php echo get_it_image($row['it_id'], 50, 50); ?>
                            <?php echo "[{$row['gubun']}] "; ?>
                            <span><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?></span>
                        </a>
                    </td>
                    <td>
                        <?php if(!$no_option) { ?>
                        <a href="./pop.item.select.option.php?ref=select&it_id=<?php echo $row['it_id']; ?>" class="shbtn lineblue">선택</a>
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

<script>
$(function() {
    $('#ca_id').change(function() {
        var ca_id = $(this).val();

        window.location.href = "?<?php echo "no_option={$no_option}" ?>&ca_id=" + ca_id;
    });
});
</script>

</body>
</html>
