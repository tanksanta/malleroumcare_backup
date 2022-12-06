<?php
// $sub_menu = '400400';
include_once('./_common.php');

// auth_check($auth[$sub_menu], "r");
$where = array();
$where_sql = '';

$sel_field = get_search_string($sel_field);
$sel_field_arr = array('bc_barcode');
if (!in_array($sel_field, $sel_field_arr)) { // 검색할 필드 대상이 아니면 값을 제거
  $sel_field = '';
}
$search_text = get_search_string($search_text);

if ($sel_field && $search_text) {
  $where_sql .= " AND {$sel_field} like '%{$search_text}%' ";
}

if ($only_not_deleted_barcode == 'true') {
  $where_sql .= " AND bc_del_yn = 'N' ";
}

$sql = "
  SELECT count(*) AS cnt
  FROM g5_cart_barcode
  WHERE 
    bc_del_yn = 'N' 
    AND it_id = '{$it_id}'
    AND io_id = '{$io_id}'
";

//$total_count = sql_fetch($sql)['cnt'];
////$rows = $config['cf_page_rows'];
//$rows = 50;
//$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
//if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
//$from_record = ($page - 1) * $rows; // 시작 열을 구함

//$sql = "
//	SELECT
//		a.bc_id,
//		a.bc_barcode,
//		a.bc_status,
//		a.bc_del_yn,
//		b.created_by AS checked_by,
//		DATE_FORMAT(b.created_at, '%m/%d') AS checked_at,
//		b.created_at AS checked_at_full
//  FROM g5_cart_barcode a
//  LEFT JOIN (
//    SELECT * FROM stock_barcode_check_log
//    WHERE id IN (SELECT MAX(id) FROM stock_barcode_check_log GROUP BY barcode)
//    ) AS b
//    ON a.bc_barcode = b.barcode
//  WHERE
//    a.it_id = '{$it_id}'
//    AND a.io_id = '{$io_id}'
//    {$where_sql}
//  ORDER BY
//      bc_barcode ASC
//";

$sql = "
	SELECT 
		bc_id, 
		bc_barcode, 
		bc_status,
		bc_is_check_yn,
		bc_del_yn,
		bc_del_yn AS origin_del_yn,
		checked_by,
		bc_memo,
		DATE_FORMAT(checked_at, '%m/%d') AS checked_at,
		DATE_FORMAT(rentaled_at, '%m/%d') AS rentaled_at,
		DATE_FORMAT(released_at, '%m/%d') AS released_at,
		checked_at AS checked_at_full
  FROM g5_cart_barcode
  WHERE 
    it_id = '{$it_id}'
    AND io_id = '{$io_id}'
    AND bc_status NOT IN ('출고', '관리자승인대기', '관리자승인완료')
    {$where_sql}
  ORDER BY
    bc_barcode ASC
";
if($page != ""){
	$page = ($page != "")?$page: 1;
	$sql .= " limit ".(($page-1)*1000).",1000";
}
$result = sql_query($sql);

$data = [];
while ($row = sql_fetch_array($result)) {
  $data[] = $row;
}

json_response(200, 'OK', $data);


