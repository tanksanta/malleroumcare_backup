<?php
include_once('./_common.php');

$it_id = get_search_string($_POST['it_id']);

if(!$it_id || !$member['mb_entId'])
    json_response(200, 'OK', []);

$result = api_post_call(EROUMCARE_API_STOCK_SELECT_DETAIL_LIST, [
    'usrId' => $member['mb_id'],
    'entId' => $member['mb_entId'],
    'stateCd' => ['01'],
    'prodId' => $it_id
]);

$data = $result['data'] ?: [];

json_response(200, 'OK', $data);
