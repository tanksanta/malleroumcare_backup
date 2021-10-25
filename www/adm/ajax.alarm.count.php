<?php
include_once('./_common.php');

header('Content-Type: application/json');

// 게시판
$sql = "select * from {$g5['board_table']}";
$result = sql_query($sql);

$bbs = array();
$bbs_count = 0;
while( $row = sql_fetch_array($result) ) {
    $sql = "SELECT count(*) as cnt FROM g5_write_{$row['bo_table']} WHERE adm_read = '0' AND wr_is_comment = '0'";
    $r = sql_fetch($sql);

    $bbs[$row['bo_table']] = (int)$r['cnt'];

    $bbs_count += $r['cnt'];
}

// 상품문의
$sql = "  select * from {$g5['g5_shop_item_qa_table']} a
                 left join {$g5['g5_shop_item_table']} b on (a.it_id = b.it_id)
                 left join {$g5['member_table']} c on (a.mb_id = c.mb_id) 
          where a.iq_answer = ''
        ";
$result = sql_query($sql);

$shop_qa = array();
$shop_qa_count = 0;
while( $row = sql_fetch_array($result) ) {
    $shop_qa[] = $row;
    $shop_qa_count++;
}

// 사용후기
$sql = "  select * from {$g5['g5_shop_item_use_table']} a
                 left join {$g5['g5_shop_item_table']} b on (a.it_id = b.it_id)
                 left join {$g5['member_table']} c on (a.mb_id = c.mb_id) 
                where is_confirm = 0";
$result = sql_query($sql);

$shop_use = array();
$shop_use_count = 0;
while( $row = sql_fetch_array($result) ) {
    $shop_use[] = $row;
    $shop_use_count++;
}

// 1:1문의
$sql = " select count(*) as cnt from {$g5['qa_content_table']} where qa_type = 0 and qa_status = 0 ";
$result = sql_fetch($sql);
$qa_count = $result['cnt'] ? (int) $result['cnt'] : 0;


$ret = array(
    'result' => 'success',
    'bbs' => array(
        'data' => $bbs,
        'total_count' => $bbs_count,
    ),
    'shop_qa' => array(
        'data' => $shop_qa,
        'total_count' => $shop_qa_count,
    ),
    'shop_use' => array(
        'data' => $shop_use,
        'total_count' => $shop_use_count,
    ),
    'qa' => array(
        'total_count' => $qa_count
    )
);

$json = json_encode($ret);
echo $json;
?>