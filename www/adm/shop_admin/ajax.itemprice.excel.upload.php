<?php
$sub_menu = '400300';
include_once('./_common.php');

$auth_check = auth_check($auth[$sub_menu], "w", true);
if($auth_check)
  json_response(400, $auth_check);

// 담당 사업소 가져오기
$sql = "
    SELECT * FROM g5_member
    WHERE mb_manager = '{$member['mb_id']}'
    ORDER BY mb_id ASC
";
$result = sql_query($sql);

$ents = [];
while($ent = sql_fetch_array($result)) {
    $ents[] = $ent['mb_id'];
}

$file = $_FILES['datafile']['tmp_name'];
if(!$file)
  json_response(400, '파일을 선택해주세요.');

include_once(G5_LIB_PATH."/PHPExcel.php");
$reader = PHPExcel_IOFactory::createReader('Excel2007');
$excel = $reader->load($file);
$sheet = $excel->getActiveSheet();

$last_col = $sheet->getHighestDataColumn();
$num_cols = PHPExcel_Cell::columnIndexFromString($last_col);
$num_rows = $sheet->getHighestDataRow();

for($row = 3; $row <= $num_rows; $row++) {
    $it_id = trim($sheet->getCell("A{$row}")->getValue());
    $it_id = get_search_string($it_id);
    for($idx = 4; $idx <= $num_cols; $idx++) {
        $col = PHPExcel_Cell::stringFromColumnIndex($idx);

        $it_price = preg_replace('/[^0-9]/', '', $sheet->getCell("{$col}{$row}")->getValue());

        // 가격이 없으면 continue
        if(!$it_price)
            continue;

        $mb_id = trim($sheet->getCell("{$col}1")->getValue());
        $mb_id = get_search_string($mb_id);

        // 담당 사업소가 아니면 continue
        if(!in_array($mb_id, $ents))
            continue;

        $sql = " select * from g5_shop_item_entprice where it_id = '{$it_id}' and mb_id = '{$mb_id}' ";
        $result = sql_fetch($sql);

        if($result['mb_id']) {
            $sql = "
                update
                    g5_shop_item_entprice
                set
                    it_price = '$it_price',
                    updated_by = '{$member['mb_id']}',
                    updated_at = NOW()
                WHERE
                    it_id = '$it_id' and
                    mb_id = '$mb_id'
            ";

            sql_query($sql);
        } else {
            $sql = "
                insert into
                    g5_shop_item_entprice
                set
                    it_id = '$it_id',
                    mb_id = '$mb_id',
                    it_price = '$it_price',
                    created_by = '{$member['mb_id']}',
                    created_at = NOW(),
                    updated_by = '{$member['mb_id']}',
                    updated_at = NOW()
            ";

            sql_query($sql);
        }
    }
}

json_response(200, 'OK');
