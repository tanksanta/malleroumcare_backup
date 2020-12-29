<?php
include_once('./_common.php');

// Page ID
$pid = ($pid) ? $pid : 'partner_excel';
$at = apms_page_thema($pid);
include_once(G5_LIB_PATH.'/apms.thema.lib.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$skin_row = array();
$skin_row = apms_rows('cz_'.MOBILE_.'skin, cz_'.MOBILE_.'set');
$skin_name = $skin_row['cz_'.MOBILE_.'skin'];

// 스킨설정
$wset = array();
if($skin_row['cz_'.MOBILE_.'set']) {
	$wset = apms_unpack($skin_row['cz_'.MOBILE_.'set']);
}

// 데모
if($is_demo) {
	@include ($demo_setup_file);
}


if (!$is_samhwa_partner && !$is_admin) {
    alert('파트너 회원만 접속하실 수 있습니다.');
}

include_once('./_head.php');

?>

<style>
    #frm-export-product {
        margin: 0 50px;
    }

    .export-table {
        border-top: 1px #cecece solid;
        border-right: 1px #cecece solid;
        width:100%;
    }
    .export-table tr td,
    .export-table tr th {
        border: 0;
        border-bottom: 1px #cecece solid;
        border-left: 1px #cecece solid;
        padding: 15px;
    }

    .export-table tr th {
        background: #f3f3f3;
    }

    .export-button {
        text-align: center;
        margin: 20px 0;
    }

    .export-button button {
        border: 1px #333 solid;
        padding: 10px 50px;
    }

    .date-selection > div {
        display: inline-block;
    }

    .date-selector {
        margin-left: 20px;
    }
    
    .date-selector input {
        display: inline-block;
        padding:15px 10px;
        margin: 0 5px;
        width: 100px;
        font-size:12px;
    }

    .category-selector {
        padding: 5px 10px;
        margin: 5px 0;
        border-top: 1px #eee solid;
    }

    .category-selector .child {
        margin-left: 20px;
    }

    .date-selector, .category-selector {
        display: none;
    }
</style>

<?php
    $sql = "SELECT `ca_id`, `ca_name` FROM {$g5['g5_shop_category_table']} WHERE length(`ca_id`) = '2' ORDER BY ca_order, ca_id ";
    $categories = sql_query($sql);

    function getChildCategories($ca_id, $length)
    {
        global $g5;
        $sql = "SELECT `ca_id`, `ca_name` FROM {$g5['g5_shop_category_table']} 
                    WHERE length(`ca_id`) = '{$length}' AND `ca_id` LIKE '{$ca_id}%'
                    ORDER BY ca_order, ca_id ";
        return sql_query($sql);
    }
?>
<form name="frm-export-product" id="frm-export-product" action="./partner_excel_result.php" method="POST" onsubmit="return checkExcelForm()">
    <table class="export-table">
        <tbody>
            <tr>
                <th style="width: 400px;">상품목록</th>
                <td>
                    <div class="selection-item">
                        <div class="radio">
                            <label for="radio-all">
                                <input type="radio" class="item-radio" name="type" value="all" id="radio-all" checked>
                                <span>전체상품</span>
                            </label>
                        </div>
                    </div>
                    <div class="selection-item date-selection">
                        <div class="radio">
                            <label for="radio-date">
                                <input type="radio" class="item-radio" name="type" value="date" id="radio-date">
                                <span>등록기간</span>
                            </label>
                        </div>
                        <div>
                            <div class="date-selector">
                                <input type="text" id="fr_date" class="date frm_input" name="fr_date" value="" size="10" maxlength="10" autocomplete="off"> ~
                                <input type="text" id="to_date" class="date frm_input" name="to_date" value="" size="10" maxlength="10" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="selection-item">
                        <div class="radio">
                            <label for="radio-category">
                                <input type="radio" class="item-radio" name="type" value="category" id="radio-category">
                                <span>분류별</span>
                            </label>
                        </div>
                        <div class="category-selector">
                            <?php while($cat = sql_fetch_array($categories)): ?>
                                <div class="checkbox">
                                    <label for="category-<?php echo $cat['ca_id']; ?>">
                                        <input type="checkbox" name="category[]" class="category-check" value="<?php echo $cat['ca_id']; ?>" id="category-<?php echo $cat['ca_id']; ?>">
                                        <span><?php echo $cat['ca_name']; ?></span>
                                    </label>
                                </div>
                                <?php
                                    $childCategories = getChildCategories($cat['ca_id'], 4);
                                    while($child = sql_fetch_array($childCategories)): 
                                ?>
                                    <div class="checkbox child">
                                        <label for="category-<?php echo $child['ca_id']; ?>">
                                            <input type="checkbox" name="category[]" class="category-check" value="<?php echo $child['ca_id']; ?>" id="category-<?php echo $child['ca_id']; ?>">
                                            <span><?php echo $child['ca_name']; ?></span>
                                        </label>
                                    </div>
                                <?php
                                    endwhile;
                                ?>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>다운 받는 내용</th>
                <td>
                    <ol>
                        <li>모델명 - 제품명</li>
                        <li>간략설명</li>
                        <li>소비자가(옵션가)</li>
                        <li>상품컷</li>
                        <li>상품상세설명</li>
                    </ol>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="export-button">
        <button type="submit" id="excel-btn" class="btn btn_01"><span>엑셀 다운로드</span></button>
    </div>
</form>
<script>
    jQuery(function($) {
        $(".item-radio").change(function() {
            $(".date-selector, .category-selector").hide()
            if( $(this).val() == "date" ) $(".date-selector").show()
            if( $(this).val() == "category" ) $(".category-selector").show()
        })

        $(".date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    })

    function checkExcelForm()
    {
        var item = $(".item-radio:checked").val()
        if( item == "date" )
        {
            if( !$("#fr_date").val() && !$("#to_date").val() )
            {
                alert("등록기간을 선택하세요!")
                return false
            }
        }
        else if( item == "category" )
        {
            if( $(".category-check:checked").length == 0 )
            {
                alert("카테고리를 선택하세요!")
                return false
            }
        }

        return true
    }
</script>

<?php

include_once('./_tail.php');
?>