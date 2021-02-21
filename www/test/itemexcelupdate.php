<?php
$sub_menu = '400300';
include_once('../common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}

if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once('../lib/Excel/reader.php');

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
	
    for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++) {
        $total_count++;

        $j = 1;

        $it_id              = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id              = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id2             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $ca_id3             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_name            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_maker           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_origin          = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_brand           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_model           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type1           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type2           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type3           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type4           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_type5           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_basic           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_explan          = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_mobile_explan   = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_cust_price      = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_price           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_tel_inq         = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_point           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_point_type      = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_sell_email      = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_use             = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_stock_qty       = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_noti_qty        = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_buy_min_qty     = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_buy_max_qty     = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_notax           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_order           = addslashes(only_number($data->sheets[0]['cells'][$i][$j++]));
        $it_img1            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img2            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img3            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img4            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img5            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img6            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img7            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img8            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img9            = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_img10           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodSym           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodWeig           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $entId           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodSupYn           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $supId           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodPayCode           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodColor           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodSize           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $prodSizeDetail           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_taxInfo           = addslashes($data->sheets[0]['cells'][$i][$j++]);
        $it_explan2         = strip_tags(trim($it_explan));
		
        $sql = "
			UPDATE {$g5['g5_shop_item_table']} SET
				prodSizeDetail = '$prodSizeDetail',
				it_taxInfo = '$it_taxInfo'
			WHERE prodPayCode = '$prodPayCode'
		";
        sql_query($sql);

        $succ_count++;
}
}
?>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>