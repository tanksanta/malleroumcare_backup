<?php
$sub_menu = '400200';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

for ($i=0; $i<count($_POST['ca_id']); $i++)
{
    $str_ca_mb_id = isset($_POST['ca_mb_id'][$i]) ? strip_tags(clean_xss_attributes($_POST['ca_mb_id'][$i])) : '';

    if ($str_ca_mb_id)
    {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '".sql_real_escape_string($str_ca_mb_id)."' ";
        $row = sql_fetch($sql);
        if (!$row['mb_id'])
            alert("\'{$str_ca_mb_id}\' 은(는) 존재하는 회원아이디가 아닙니다.", "./categorylist.php?$qstr");
	}

    $check_files =  array();
    
    if( !empty($_POST['ca_skin'][$i]) ){
        $check_files[] = $_POST['ca_skin'][$i];
    }

    if( !empty($_POST['ca_mobile_skin'][$i]) ){
        $check_files[] = $_POST['ca_mobile_skin'][$i];
    }

    if( !empty($_POST['ca_skin_dir'][$i]) ){
        if( preg_match('#\.+(\/|\\\)#', $_POST['ca_skin_dir'][$i]) ){
            alert('PC 스킨폴더명에 포함될수 없는 문자가 들어있습니다.');
        }
    }

    if( !empty($_POST['ca_mobile_skin_dir'][$i]) ){
        if( preg_match('#\.+(\/|\\\)#', $_POST['ca_mobile_skin_dir'][$i]) ){
            alert('모바일 스킨폴더명에 포함될수 없는 문자가 들어있습니다.');
        }
    }

	$is_gun_theme = (isset($config['as_gnu']) && $config['as_gnu']) ? true : false;

    foreach( $check_files as $file ){
        if( empty($file) ) continue;

        if( preg_match('#\.+(\/|\\\)#', $file) ){
            alert('스킨파일명에 포함될수 없는 문자가 들어있습니다.');
        }

        if( ! is_include_path_check($file) ){
            alert('오류 : 데이터폴더가 포함된 path 또는 잘못된 path 를 포함할수 없습니다.');
        }

		if($is_gun_theme) {
	        $file_ext = pathinfo($file, PATHINFO_EXTENSION);

		    if( ! $file_ext || ! in_array($file_ext, array('php', 'htm', 'html')) || ! preg_match('/^.*\.(php|htm|html)$/i', $file) ) {
			    alert('스킨 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
	        }
		}
    }

    $p_ca_name = is_array($_POST['ca_name']) ? strip_tags(clean_xss_attributes($_POST['ca_name'][$i])) : '';

    $sql = " update {$g5['g5_shop_category_table']}
                set ca_name             = '".$p_ca_name."',
                    ca_order            = '".sql_real_escape_string(strip_tags($_POST['ca_order'][$i]))."',
					ca_mb_id            = '".sql_real_escape_string(strip_tags(clean_xss_attributes($_POST['ca_mb_id'][$i])))."',
					ca_use              = '".sql_real_escape_string(strip_tags($_POST['ca_use'][$i]))."',
                    ca_img_width        = '".sql_real_escape_string(strip_tags($_POST['ca_img_width'][$i]))."',
                    ca_img_height       = '".sql_real_escape_string(strip_tags($_POST['ca_img_height'][$i]))."',
                    ca_mobile_img_width  = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_img_width'][$i]))."',
                    ca_mobile_img_height = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_img_height'][$i]))."',
                    ca_skin             = '".sql_real_escape_string(strip_tags($_POST['ca_skin'][$i]))."',
                    ca_mobile_skin      = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_skin'][$i]))."',
                    ca_skin_dir         = '".sql_real_escape_string(strip_tags($_POST['ca_skin_dir'][$i]))."',
                    ca_mobile_skin_dir  = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_skin_dir'][$i]))."',
					ca_list_mod         = '".sql_real_escape_string(strip_tags($_POST['ca_list_mod'][$i]))."',
                    ca_list_row         = '".sql_real_escape_string(strip_tags($_POST['ca_list_row'][$i]))."',
                    ca_mobile_list_mod  = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_list_mod'][$i]))."',
                    ca_mobile_list_row  = '".sql_real_escape_string(strip_tags($_POST['ca_mobile_list_row'][$i]))."',
					pt_use			    = '".sql_real_escape_string(strip_tags($_POST['pt_use'][$i]))."',
                    pt_limit		    = '".sql_real_escape_string(strip_tags($_POST['pt_limit'][$i]))."',
                    pt_item			    = '".sql_real_escape_string(strip_tags($_POST['pt_item'][$i]))."',
					pt_point		    = '".sql_real_escape_string(strip_tags($_POST['pt_point'][$i]))."',
                    pt_form			    = '".sql_real_escape_string(strip_tags($_POST['pt_form'][$i]))."'
              where ca_id = '".sql_real_escape_string(strip_tags($_POST['ca_id'][$i]))."' "; // APMS : 2014.07.23
    sql_query($sql);


    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.04 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 시작 ]
    //                   EROUMCARE_API_PROD_UPDATECATEGORY - /api/prod/updateCategory
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    $_ca_id = sql_real_escape_string(strip_tags($_POST['ca_id'][$i]));
    $_ca_use = sql_real_escape_string(strip_tags($_POST['ca_use'][$i]));
    if( ($cate_gubun_table[substr($_ca_id, 0, 2)]) && (mb_strlen($_ca_id)<=4) && (mb_strlen($_ca_id)>2) ) {
        $sendData = [];
        $sendData["ca_name"]        = $p_ca_name;
        $sendData["gubun"]          = $cate_gubun_table[substr($_ca_id, 0, 2)];
        $sendData["ca_use"]         = ($_ca_use?"01":"02") ;
        $sendData["usrId"]          = $member['mb_id'];
        $sendData["itemId"]         = clean_xss_attributes( clean_xss_tags( get_search_string( $_POST['itemId'][$i] ) ) );
        $res = get_eroumcare(EROUMCARE_API_PROD_UPDATECATEGORY, $sendData);
    }        
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*
    // 23.10.04 : 서원 - eroumAPI신규추가 [ 카테고리 정보 수정 부분 종료 ]
    // *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-* *-*

}

goto_url("./categorylist.php?$qstr");
?>
