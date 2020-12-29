<?php

$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
header('Content-Type: application/json');

@mkdir(G5_DATA_PATH."/order_cart", G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH."/order_cart", G5_DIR_PERMISSION);

$file_tmp_name      = $_FILES['file']['tmp_name'];
$file_name          = $_FILES['file']['name'];

if( $file_tmp_name || $file_name ){

    if( !preg_match('/\.(gif|jpe?g|bmp|png|pdf|psd|xls|csv|xlsx|ppt?x|doc|docx|zip|ai|hwp)$/i', $file_name) ){
        $ret = array(
            'result' => 'fail',
            'msg' => '허용된 확장자가 아닙니다.',
        );
        echo json_encode($ret);
        exit;
    }
}else{
    $ret = array(
        'result' => 'fail',
        'msg' => '파일을 업로드 해주세요.',
    );
    echo json_encode($ret);
    exit;
}

// 확장자 구하기
$tmp = strpos(strrev($file_name), '.');
$temp = strlen($file_name) - $tmp;

if ($tmp) {
    $strName = substr($file_name, 0, $temp-1);
    $strExt = substr($file_name, strlen($strName) + 1, strlen($file_name));
    //if (preg_match('/htm|php|inc|phtm|shtm|cgi|dot|asp|ztx|pl/i', $strExt)) $strExt .= '.txt';
} else {
    $strName = $file_name;
    $strExt = 'unknown';
}

$strExt = strtolower($strExt);

$new_name = get_uniqid() . '_' . $file_name;

upload_file($_FILES['file']['tmp_name'], $new_name, G5_DATA_PATH."/order_cart");

$sql = "INSERT INTO g5_shop_order_cart_file SET
            od_id = '{$od_id}',
            it_id = '{$it_id}',
            ctf_uid = '{$uid}',
            ctf_name = '{$new_name}',
            ctf_real_name = '{$file_name}',
            ctf_type = '{$type}'
        ";
sql_query($sql);

$ret_files = array();

$sql = "SELECT ctf_no as no, ctf_name as file_name, ctf_real_name as real_name, it_id, od_id FROM g5_shop_order_cart_file WHERE od_id = '{$od_id}' AND it_id = '{$it_id}' AND ctf_uid = '{$uid}' AND ctf_type = '{$type}'";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $ret_files[] = $row;
}

$ret = array(
    'result' => 'success',
    'msg' => '완료되었습니다.',
    'data' => $ret_files,
);
echo json_encode($ret);
exit;

?>