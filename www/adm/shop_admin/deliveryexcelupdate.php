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

    for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;

        $j = 1;

        $ct_id              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $od_id              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $od_time              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $it_name              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $ct_delivery_company              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $ct_delivery_num              = addslashes(trim($data->sheets[0]['cells'][$i][$j++]));
        $ct_delivery_cnt              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));
        $ct_delivery_price              = addslashes(only_number(trim($data->sheets[0]['cells'][$i][$j++])));

        if(!$ct_id || !$od_id){
            $fail_count++;
            continue;
        }
		
		foreach($delivery_companys as $companyInfo){
			if($companyInfo["name"] == $ct_delivery_company){
				$ct_delivery_company = $companyInfo["val"];
			}
		}

        $sql = "
			UPDATE g5_shop_cart SET
				ct_delivery_company = '{$ct_delivery_company}',
				ct_delivery_num = '{$ct_delivery_num}',
				ct_delivery_cnt = '{$ct_delivery_cnt}',
				ct_delivery_price = '{$ct_delivery_price}'
			WHERE ct_id = '{$ct_id}'
			AND od_id = '{$od_id}'
		";
        sql_query($sql);
		
		sql_query("
			UPDATE g5_shop_order SET
				od_delivery_insert = ( SELECT COUNT(*) FROM g5_shop_cart WHERE od_id = '{$od_id}' AND ct_delivery_num != '' AND ct_delivery_num IS NOT NULL )
			WHERE od_id = '{$od_id}'
		");

        $succ_count++;
	}
}

$g5['title'] = '배송정보 엑셀일괄업로드 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>배송정보업로드를 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>총배송정보수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt>완료건수</dt>
        <dd id="successCnt"><?php echo $succ_count; ?></dd>
        <dt>실패건수</dt>
        <dd id="failCnt"><?php echo $fail_count; ?></dd>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>