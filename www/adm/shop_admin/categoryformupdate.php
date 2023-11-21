<?php
$sub_menu = '400200';
include_once('./_common.php');


@mkdir(G5_DATA_PATH."/category", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/category", G5_DIR_PERMISSION);

$ca_bimg      = $_FILES['ca_bimg']['tmp_name'];
$ca_bimg_name = $_FILES['ca_bimg']['name'];

if ($ca_bimg_del)  @unlink(G5_DATA_PATH."/category/$ca_id");

//파일이 이미지인지 체크합니다.
if( $ca_bimg || $ca_bimg_name ){

    @unlink(G5_DATA_PATH."/category/$ca_id");

    if( !preg_match('/\.(gif|jpe?g|bmp|png)$/i', $ca_bimg_name) ){
        alert("이미지 파일만 업로드 할수 있습니다.");
    }

    $timg = @getimagesize($ca_bimg);
    if ($timg['2'] < 1 || $timg['2'] > 16){
        alert("이미지 파일만 업로드 할수 있습니다.");
    }
}

if ($file = $_POST['ca_include_head']) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("상단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if ($file = $_POST['ca_include_tail']) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);

    if (! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || !preg_match("/\.(php|htm[l]?)$/i", $file)) {
        alert("하단 파일 경로가 php, html 파일이 아닙니다.");
    }
}

if( isset($_POST['ca_id']) ){
    $ca_id = preg_replace('/[^0-9a-z]/i', '', $ca_id);
    $sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);

    if (($ca['ca_include_head'] !== $_POST['ca_include_head'] || $ca['ca_include_tail'] !== $_POST['ca_include_tail']) && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

if(!is_include_path_check($_POST['ca_include_head'], 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if(!is_include_path_check($_POST['ca_include_tail'], 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

$check_keys = array('ca_skin_dir', 'ca_mobile_skin_dir', 'ca_skin', 'ca_mobile_skin'); 

foreach( $check_keys as $key ){
    if( isset($$key) && preg_match('#\.+(\/|\\\)#', $$key) ){
        alert('스킨명 또는 경로에 포함시킬수 없는 문자열이 있습니다.');
    }
}

$check_str_keys = array('ca_name', 'ca_mb_id', 'ca_sell_email');
foreach( $check_str_keys as $key ){
    $$key = $_POST[$key] = strip_tags(clean_xss_attributes($_POST[$key]));
}

$ca_include_head = $_POST['ca_include_head'];
$ca_include_tail = $_POST['ca_include_tail'];

if( function_exists('filter_input_include_path') ){
    $ca_include_head = filter_input_include_path($ca_include_head);
    $ca_include_tail = filter_input_include_path($ca_include_tail);
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check($auth[$sub_menu], "d");

check_admin_token();

if ($w == 'd' && $is_admin != 'super')
    alert("최고관리자만 분류를 삭제할 수 있습니다.");

if ($w == "" || $w == "u")
{
    if ($ca_mb_id)
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '$ca_mb_id' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'$ca_mb_id\' 은(는) 존재하는 회원아이디가 아닙니다.");
    }
}

if( $ca_skin && ! is_include_path_check($ca_skin) ){
    alert("오류 : 데이터폴더가 포함된 path 를 포함할수 없습니다.");
}

$sql_common = " ca_order                = '$ca_order',
                ca_skin_dir             = '$ca_skin_dir',
                ca_mobile_skin_dir      = '$ca_mobile_skin_dir',
                ca_skin                 = '$ca_skin',
                ca_mobile_skin          = '$ca_mobile_skin',
				ca_img_width            = '$ca_img_width',
                ca_img_height           = '$ca_img_height',
				ca_list_mod             = '$ca_list_mod',
				ca_list_row             = '$ca_list_row',
                ca_mobile_img_width     = '$ca_mobile_img_width',
                ca_mobile_img_height    = '$ca_mobile_img_height',
				ca_mobile_list_mod      = '$ca_mobile_list_mod',
                ca_mobile_list_row      = '$ca_mobile_list_row',
				ca_sell_email           = '$ca_sell_email',
                ca_use                  = '$ca_use',
                ca_stock_qty            = '$ca_stock_qty',
                ca_use_limit            = '$ca_use_limit',
                ca_limit_month          = '$ca_limit_month',
                ca_limit_num            = '$ca_limit_num',
                ca_explan_html          = '$ca_explan_html',
                ca_head_html            = '$ca_head_html',
                ca_tail_html            = '$ca_tail_html',
                ca_mobile_head_html     = '$ca_mobile_head_html',
                ca_mobile_tail_html     = '$ca_mobile_tail_html',
                ca_include_head         = '$ca_include_head',
                ca_include_tail         = '$ca_include_tail',
                ca_mb_id                = '$ca_mb_id',
                ca_cert_use             = '$ca_cert_use',
                ca_adult_use            = '$ca_adult_use',
                ca_nocoupon             = '$ca_nocoupon',
                ca_1_subj               = '$ca_1_subj',
                ca_2_subj               = '$ca_2_subj',
                ca_3_subj               = '$ca_3_subj',
                ca_4_subj               = '$ca_4_subj',
                ca_5_subj               = '$ca_5_subj',
                ca_6_subj               = '$ca_6_subj',
                ca_7_subj               = '$ca_7_subj',
                ca_8_subj               = '$ca_8_subj',
                ca_9_subj               = '$ca_9_subj',
                ca_10_subj              = '$ca_10_subj',
                ca_1                    = '$ca_1',
                ca_2                    = '$ca_2',
                ca_3                    = '$ca_3',
                ca_4                    = '$ca_4',
                ca_5                    = '$ca_5',
                ca_6                    = '$ca_6',
                ca_7                    = '$ca_7',
                ca_8                    = '$ca_8',
                ca_9                    = '$ca_9',
                ca_10                   = '$ca_10',
				pt_use			        = '$pt_use',
				pt_cate			        = '$pt_cate',
				pt_limit		        = '$pt_limit',
                pt_item			        = '$pt_item',
				pt_point		        = '$pt_point',
                pt_form			        = '$pt_form',
                ca_title                = '$ca_title',
                ca_content              = '$ca_content',
                ca_head_use             = '$ca_head_use',
                ca_main_use             = '$ca_main_use',
                ca_main_item_1          = '$ca_main_item_1',
                ca_main_item_2          = '$ca_main_item_2',
                ca_main_item_3          = '$ca_main_item_3',
                ca_main_item_4          = '$ca_main_item_4',
                itemId          = '$itemId'
                 ";

if ($w == "")
{
    if (!trim($ca_id))
        alert("분류 코드가 없으므로 분류를 추가하실 수 없습니다.");

    // 소문자로 변환
    $ca_id = strtolower($ca_id);

    $sql = " insert {$g5['g5_shop_category_table']}
                set ca_id   = '$ca_id',
                    ca_name = '$ca_name',
                    $sql_common ";
    sql_query($sql);

    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.04 : 서원 - eroumAPI신규추가 [ 카테고리 정보 추가 부분 시작 ]
    //                   EROUMCARE_API_PROD_INSERTCATEGORY - /api/prod/insertCategory
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    $_itemId = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['itemId'] ) ) );
    $_cnt = sql_fetch(" SELECT COUNT(ca_id) AS CNT FROM {$g5['g5_shop_category_table']} WHERE itemId = '$_itemId' ", false);
    if( $_cnt['CNT'] == 1 ) {
        $sendData = [];
        $sendData["ca_name"]        = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_name'] ) ) );
        $sendData["gubun"]          = $cate_gubun_table[substr($ca_id, 0, 2)];
        $sendData["ca_limit_month"] = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_limit_month'] ) ) );
        $sendData["ca_limit_num"]   = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_limit_num'] ) ) );
        $sendData["ca_use"]         = ($_POST['ca_use']?"01":"02") ;
        $sendData["usrId"]          = $member['mb_id'];
        $sendData["itemId"]         = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['itemId'] ) ) );
        $res = get_eroumcare(EROUMCARE_API_PROD_INSERTCATEGORY, $sendData);
    }    
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.04 : 서원 - eroumAPI신규추가 [ 카테고리 정보 추가 부분 종료 ]
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*

}
else if ($w == "u")
{
    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name = '$ca_name',
                    ca_skin             = '$ca_skin',
                    ca_mobile_skin      = '$ca_mobile_skin',
                    ca_skin_dir         = '$ca_skin_dir',
                    ca_mobile_skin_dir  = '$ca_mobile_skin_dir',
					$sql_common
              where ca_id = '$ca_id' ";
    sql_query($sql);

    // 하위분류를 똑같은 설정으로 반영
    if ($sub_category) {
		//리스트 설정값
		$ca = sql_fetch(" select as_list_set, as_mobile_list_set, as_item_set, as_mobile_item_set from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ", false);
		$as_list_set = addslashes($ca['as_list_set']);
		$as_mobile_list_set = addslashes($ca['as_mobile_list_set']);
		$as_item_set = addslashes($ca['as_item_set']);
		$as_mobile_item_set = addslashes($ca['as_mobile_item_set']);

        $len = strlen($ca_id);
        $sql = " update {$g5['g5_shop_category_table']}
					set ca_skin             = '$ca_skin',
						ca_mobile_skin      = '$ca_mobile_skin',
						ca_skin_dir         = '$ca_skin_dir',
						ca_mobile_skin_dir  = '$ca_mobile_skin_dir',
						as_list_set			= '$as_list_set',
						as_mobile_list_set	= '$as_mobile_list_set',
						as_item_set			= '$as_item_set',
						as_mobile_item_set	= '$as_mobile_item_set',
						$sql_common
                  where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
        if ($is_admin != 'super')
            $sql .= " and ca_mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    } 


    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.05 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 시작 ]
    //                   EROUMCARE_API_PROD_UPDATECATEGORY - /api/prod/updateCategory
    //                   카테고리 수정은 1차 카테고리인 ca_id 값이 4자리를 가지고 있는 카테고리만 수정이 가능하며, 하위 카테고리는 WMDS에 처리하지 않는다.
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    if( ($cate_gubun_table[substr($ca_id, 0, 2)]) && (mb_strlen($ca_id)<=4) && (mb_strlen($ca_id)>2) ) {
        $sendData = [];
        $sendData["ca_name"]        = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_name'] ) ) );
        $sendData["gubun"]          = $cate_gubun_table[substr($ca_id, 0, 2)];
        $sendData["ca_limit_month"] = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_limit_month'] ) ) );
        $sendData["ca_limit_num"]   = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['ca_limit_num'] ) ) );
        $sendData["ca_use"]         = ($_POST['ca_use']?"01":"02") ;
        $sendData["usrId"]          = $member['mb_id'];
        $sendData["itemId"]         = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['itemId'] ) ) );
        $res = get_eroumcare(EROUMCARE_API_PROD_UPDATECATEGORY, $sendData);
    }        
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.05 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 종료 ]
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*

}
else if ($w == "d")
{
    // 분류의 길이
    $len = strlen($ca_id);

    $sql = " select COUNT(*) as cnt from {$g5['g5_shop_category_table']}
              where SUBSTRING(ca_id,1,$len) = '$ca_id'
                and ca_id <> '$ca_id' ";
    $row = sql_fetch($sql);
    if ($row['cnt'] > 0)
        alert("이 분류에 속한 하위 분류가 있으므로 삭제 할 수 없습니다.\\n\\n하위분류를 우선 삭제하여 주십시오.");

    $str = $comma = "";
    $sql = " select it_id from {$g5['g5_shop_item_table']} where ca_id = '$ca_id' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma{$row['it_id']}";
        $comma = " , ";
    }

    if ($str)
        alert("이 분류와 관련된 상품이 총 {$i} 건 존재하므로 상품을 삭제한 후 분류를 삭제하여 주십시오.\\n\\n$str");

    // 분류 삭제
    $sql = " delete from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    sql_query($sql);


    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.05 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 시작 ]
    //                   EROUMCARE_API_PROD_UPDATECATEGORY - /api/prod/updateCategory
    //                   프론트단에서 삭제 처리 되지만 WMDS에서는 해당 Key값을 다른 테이블에서 외래키 참조하고 있음으로 삭제하지 않으며, 미사용으로 처리 해놓음.
    //                   단, 이럴 경우 동일 최상위 대분류에서 이전의 품목코드가 프론트(이로움Care)에서 입력될 경우 문제의 소지가 있음.
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    $_itemId = clean_xss_attributes( clean_xss_tags( get_search_string( $_GET['itemId'] ) ) );
    $_cnt = sql_fetch(" SELECT COUNT(ca_id) AS CNT FROM {$g5['g5_shop_category_table']} WHERE itemId = '$_itemId' AND ca_id LIKE '".substr($ca_id, 0, 4)."%'; ", false);
    if( $_cnt['CNT'] == 0 ) {
        $sendData = [];
        $sendData["ca_use"]         = "02" ;
        $sendData["usrId"]          = $member['mb_id'];
        $sendData["itemId"]         = $_itemId;
        $res = get_eroumcare(EROUMCARE_API_PROD_UPDATECATEGORY, $sendData);
    }    
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.05 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 종료 ]
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{

    if ($_FILES['ca_bimg']['name']) upload_file($_FILES['ca_bimg']['tmp_name'], $ca_id, G5_DATA_PATH."/category"); 

    goto_url("./categoryform.php?w=u&amp;ca_id=$ca_id&amp;$qstr");
} else {
    goto_url("./categorylist.php?$qstr");
}
?>
