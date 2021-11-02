<?php
$sub_menu = '400400';
include_once('./_common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

auth_check($auth[$sub_menu], "w");

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}

if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];
    include_once(G5_LIB_PATH.'/Excel/reader.php');

    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('UTF-8');

    /***
    * if you want you can change 'iconv' to mb_convert_encoding:
    * $data->setUTFEncoder('mb');
    *
    **/

    /***
    * By default rows & cols indeces start with 1
    * For change initial index use:
    * $data->setRowColOffset(0);
    *
    **/



    /***
    *  Some function for formatting output.
    * $data->setDefaultFormat('%.2f');
    * setDefaultFormat - set format for columns with unknown formatting
    *
    * $data->setColumnFormat(4, '%.3f');
    * setColumnFormat - set format for column (apply only to number fields)
    *
    **/

    $data->read($file);

    /*


     $data->sheets[0]['numRows'] - count rows
     $data->sheets[0]['numCols'] - count columns
     $data->sheets[0]['cells'][$i][$j] - data from $i-row $j-column

     $data->sheets[0]['cellsInfo'][$i][$j] - extended info about cell

        $data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
            if 'type' == "unknown" - use 'raw' value, because  cell contain value with format '0.00';
        $data->sheets[0]['cellsInfo'][$i][$j]['raw'] = value if cell without format
        $data->sheets[0]['cellsInfo'][$i][$j]['colspan']
        $data->sheets[0]['cellsInfo'][$i][$j]['rowspan']
    */

    error_reporting(E_ALL ^ E_NOTICE);

    $dup_it_id = array();
    $fail_it_id = array();
    $dup_count = 0;
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;
	$succDataList = [];

    $timestamp = time();
    $datetime = date('Y-m-d H:i:s', $timestamp);

    for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;

        $j = 1;

        $mb_entNm              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $mb_id              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $mb_manager              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $ent_sales              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $ent_balance              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $lc_amount              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $lc_base_date              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $lc_base_date = date('Y-m-d', strtotime($lc_base_date));
        
        if(!$mb_id){
            $fail_count++;
            continue;
        }
		
        sql_query("
            INSERT INTO
                ledger_content
            SET
                mb_id = '{$mb_id}',
                lc_type = '1',
                lc_amount = '{$lc_amount}',
                lc_memo = '',
                lc_created_at = '{$datetime}',
                lc_created_by = '{$member['mb_id']}',
                lc_base_date = '{$lc_base_date}'
        ");

        $succ_count++;
	}
    alert_close("등록되었습니다.", false, true);
} else {
    alert_close('파일을 읽을 수 없습니다.');
}
?>