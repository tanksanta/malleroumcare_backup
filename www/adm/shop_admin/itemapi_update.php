<?php
$sub_menu = "200200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

check_admin_token();

$it_id = strip_tags(clean_xss_attributes($_POST['it_id']));

if (!$it_id)
    alert('상품관리코드가 없습니다.', './itemlist.php?'.$qstr);

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

$sql_common = "	prodId				= '$prodId',
                gubun               = '$gubun',
                prodNm				= '$prodNm',
                itemId				= '$itemId',
                subItem				= '$subItem',
                prodSupPrice		= '$prodSupPrice',
                prodOflPrice		= '$prodOflPrice',
                ProdPayCode			= '$ProdPayCode',
                supId               = '$supId',
                prodColor			= '$prodColor',
                prodSym				= '$prodSym',
                prodWeig			= '$prodWeig',
                prodSize			= '$prodSize',
                prodQty				= '$prodQty',
                prodDetail			= '$prodDetail',
                regDtm				= '$regDtm',
                regUsrId			= '$regUsrId',
                regUsrIp			= '$regUsrIp',
                supNm               = '$supNm',
                prodImgAttr			= '$prodImgAttr'
				";

$sql_common .= " , it_update_time = '".G5_TIME_YMDHIS."' ";
$sql = " update {$g5['g5_shop_item_table']}
           set $sql_common
         where it_id = '$it_id' ";
sql_query($sql);

$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;page_rows='.$page_rows.'&amp;save_stx='.$stx;
if($it_id) $qstr  .= '&amp;api_it_id='.$it_id;

goto_url('./itemlist.php?'.$qstr);
?>
