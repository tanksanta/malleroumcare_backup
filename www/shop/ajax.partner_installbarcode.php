<?php
include_once('./_common.php');

/*

설치결과보고서 팝업(popup.partner_installreport.php)
바코드 업데이트 (설치결과보고서 서명 전, 설치결과보고서 저장 시 호출)

*/

if($member['mb_type'] !== 'partner' && !$is_admin)
    json_response(400, '먼저 로그인하세요.');

$barcodes = $_POST['barcode'];

$od_id = get_search_string($_POST['od_id']);
if(!$od_id)
    json_response(400, '유효하지 않은 요청입니다.');

$sql = "
    SELECT * FROM
        g5_shop_cart
    WHERE
        od_id = '$od_id' and
        ct_direct_delivery_partner = '{$member['mb_id']}' and
        ct_status IN('준비', '출고준비', '배송', '완료')
    ORDER BY
        ct_id ASC
";
$result = sql_query($sql);

while($ct = sql_fetch_array($result)) {

    // 기존 바코드 정보 가져오기
    $barcodes_orig = [];
    $stock_result = api_post_call(EROUMCARE_API_SELECT_PROD_INFO_AJAX_BY_SHOP, [
        'stoId' => implode('|', array_filter(explode('|', $ct['stoId'])))
    ]);
    foreach($stock_result['data'] as $stock) {
        $barcodes_orig[$stock['stoId']] = $stock['prodBarNum'];
    }

    $count = 0;
    $prods = [];
    foreach(array_filter(explode('|', $ct['stoId'])) as $stoId) {
        $prodBarNum = get_search_string($barcodes[$stoId]);

        if($prodBarNum) $count++;
        
        // 바코드가 기존 바코드값에서 변경된 경우에만 적용
        if($barcodes_orig[$stoId] != $prodBarNum) {
            $prods[] = [
                'stoId' => $stoId,
                'prodBarNum' => $prodBarNum
            ];
        }
    }

    if($prods) {
        $ent_id = get_member($ct['mb_id'], 'mb_entId')['mb_entId'];
        $api_result = api_post_call(EROUMCARE_API_STOCK_UPDATE, [
            'usrId' => $ct['mb_id'],
            'entId' => $ent_id,
            'prods' => $prods
        ]);

        if($api_result['errorYN'] != 'N')
            json_response(500, $api_result['message']);
    }

    // 바코드 로그
    $it_name = $ct['it_name'];
    if($ct['ct_option'] && $ct['ct_option'] != $ct['it_name']) $it_name .= "({$ct['ct_option']})";
    foreach($prods as $prod) {
        $content = "파트너 바코드입력 : {$it_name}[ {$prod['prodBarNum']} ]";
        sql_query("
            INSERT INTO
                g5_barcode_log
            SET
                od_id = '$od_id',
                mb_id = '{$member['mb_id']}',
                stoId = '{$prod['stoId']}',
                barcode = '{$prod['prodBarNum']}',
                b_content = '{$content}',
                b_date = NOW()
        ");
    }

    // ct_barcode_insert update
    sql_query("
        UPDATE
            {$g5['g5_shop_cart_table']}
        SET
            ct_barcode_insert = '{$count}'
        WHERE
            ct_id = '{$ct['ct_id']}'
    ");
}

json_response(200, 'OK');
