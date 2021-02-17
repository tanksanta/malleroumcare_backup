<?php
$sub_menu = '400300';
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
        $it_explan2         = strip_tags(trim($it_explan));

        if(!$it_id || !$ca_id || !$it_name) {
            $fail_count++;
            continue;
        }

        // it_id 중복체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        $row2 = sql_fetch($sql2);
        if($row2['cnt']) {
            $fail_it_id[] = $it_id;
            $dup_it_id[] = $it_id;
            $dup_count++;
            $fail_count++;
            continue;
        }

        // 기본분류체크
        $sql2 = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
        $row2 = sql_fetch($sql2);
        if(!$row2['cnt']) {
            $fail_it_id[] = $it_id;
            $fail_count++;
            continue;
        }
		
		$itemId = sql_fetch("SELECT itemId FROM g5_shop_category WHERE ca_id = '{$ca_id}'")["itemId"];

        $sql = " INSERT INTO {$g5['g5_shop_item_table']}
                     SET it_id = '$it_id',
                         ca_id = '$ca_id',
                         ca_id2 = '$ca_id2',
                         ca_id3 = '$ca_id3',
                         it_name = '$it_name',
                         it_maker = '$it_maker',
                         it_origin = '$it_origin',
                         it_brand = '$it_brand',
                         it_model = '$it_model',
                         it_type1 = '$it_type1',
                         it_type2 = '$it_type2',
                         it_type3 = '$it_type3',
                         it_type4 = '$it_type4',
                         it_type5 = '$it_type5',
                         it_basic = '$it_basic',
                         it_explan = '$it_explan',
                         it_explan2 = '$it_explan2',
                         it_mobile_explan = '$it_mobile_explan',
                         it_cust_price = '$it_cust_price',
                         it_price = '$it_price',
                         it_point = '$it_point',
                         it_point_type = '$it_point_type',
                         it_stock_qty = '$it_stock_qty',
                         it_noti_qty = '$it_noti_qty',
                         it_buy_min_qty = '$it_buy_min_qty',
                         it_buy_max_qty = '$it_buy_max_qty',
                         it_notax = '$it_notax',
                         it_use = '$it_use',
                         it_time = '".G5_TIME_YMDHIS."',
                         pt_num = '".time()."',
						 it_ip = '{$_SERVER['REMOTE_ADDR']}',
                         it_order = '$it_order',
                         it_tel_inq = '$it_tel_inq',
                         prodSym = '$prodSym',
                         prodWeig = '$prodWeig',
                         entId = '$entId',
                         prodSupYn = '$prodSupYn',
                         supId = '$supId',
                         ProdPayCode = '$prodPayCode',
						 	it_thezone = '$itemId',
							it_option_subject = '색상,사이즈',
                         it_img1 = '$it_img1',
                         it_img2 = '$it_img2',
                         it_img3 = '$it_img3',
                         it_img4 = '$it_img4',
                         it_img5 = '$it_img5',
                         it_img6 = '$it_img6',
                         it_img7 = '$it_img7',
                         it_img8 = '$it_img8',
                         it_img9 = '$it_img9',
                         it_img10 = '$it_img10' ";
        sql_query($sql);

        $succ_count++;
		
		$prodSizeList = explode("|", $prodSize);
		$prodColorList = explode("|", $prodColor);
		foreach($prodColorList as $thisColor){
			foreach($prodSizeList as $thisSize){
				if($thisColor && $thisSize){
					$thisItem = $thisColor.chr(30).$thisSize;

					sql_query("
						INSERT INTO g5_shop_item_option
							( io_id, io_type, it_id, io_price, io_price_partner, io_price_dealer, io_price_dealer2, io_stock_qty, io_noti_qty, io_use, io_thezone )
						VALUES
							( '{$thisItem}', 0, '{$it_id}', 0, 0, 0, 0, 9999, 100, 1, '' )
					");
				}
			}
		}

		$imgList = [];
		for($ii = 1; $ii < 11; $ii++){
			if(${"it_img{$ii}"}){
				array_push($imgList, "/data/item/{${"it_img{$ii}"}}");
			}
		}

		$gubun = "00";
		switch(substr($ca_id, 0, 2)){
			case "10" :
				$gubun = "00";
				break;
			case "20" :
				$gubun = "01";
				break;
		}

		$thisDataList = [];
		$thisDataList["usrId"] = $member["mb_id"];
		$thisDataList["entId"] = $entId;
		$thisDataList["prodNm"] = $it_name;
		$thisDataList["prodSym"] = $prodSym;
		$thisDataList["prodWeig"] = $prodWeig;
		$thisDataList["prodColor"] = $prodColor;
		$thisDataList["prodSize"] = $prodSize;
		$thisDataList["prodDetail"] = $it_explan;
		$thisDataList["prodPayCode"] = $prodPayCode;
		$thisDataList["prodSupYn"] = $prodSupYn;
		$thisDataList["prodSupPrice"] = $it_cust_price;
		$thisDataList["prodOflPrice"] = $it_price;
		$thisDataList["prodStateCode"] = "03";
		$thisDataList["supId"] = $supId;
		$thisDataList["itemId"] = $itemId;
		$thisDataList["subItem"] = "";
		$thisDataList["gubun"] = $gubun;
		$thisDataList["imgList"] = $imgList;

		$succDataList[$it_id] = $thisDataList;
}
}

$g5['title'] = '상품 엑셀일괄등록 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>상품등록을 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>총상품수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt>완료건수</dt>
        <dd id="successCnt"><?php echo $succ_count; ?></dd>
        <dt>실패건수</dt>
        <dd id="failCnt"><?php echo $fail_count; ?></dd>
        <?php if($fail_count > 0) { ?>
        <dt>실패상품코드</dt>
        <dd><?php echo implode(', ', $fail_it_id); ?></dd>
        <?php } ?>
        <?php if($dup_count > 0) { ?>
        <dt>상품코드중복건수</dt>
        <dd><?php echo number_format($dup_count); ?></dd>
        <dt>중복상품코드</dt>
        <dd><?php echo implode(', ', $dup_it_id); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
    
    <script type="text/javascript">
		$(function(){

			function dataURItoBlob(dataURI) {
				// convert base64/URLEncoded data component to raw binary data held in a string
				var byteString;
				if (dataURI.split(',')[0].indexOf('base64') >= 0)
					byteString = atob(dataURI.split(',')[1]);
				else
					byteString = unescape(dataURI.split(',')[1]);

				// separate out the mime component
				var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

				// write the bytes of the string to a typed array
				var ia = new Uint8Array(byteString.length);
				for (var i = 0; i < byteString.length; i++) {
					ia[i] = byteString.charCodeAt(i);
				}

				return new Blob([ia], {type:mimeString});
			}

			var nowToDataURLResult = "";
			function toDataURL(url) {
				return new Promise(function(resolve, reject){
				  var xhr = new XMLHttpRequest();
				  xhr.onload = function() {
					var reader = new FileReader();
					reader.onloadend = function() {
						nowToDataURLResult = reader.result;
						resolve();
					}
					reader.readAsDataURL(xhr.response);
				  };
				  xhr.open('GET', url);
				  xhr.responseType = 'blob';
				  xhr.send();
				});
			}

			function dataURLtoFile(dataurl, filename) {
				var arr = dataurl.split(','),
					mime = arr[0].match(/:(.*?);/)[1],
					bstr = atob(arr[1]), 
					n = bstr.length, 
					u8arr = new Uint8Array(n);

				while(n--){
					u8arr[n] = bstr.charCodeAt(n);
				}

				return new File([u8arr], filename, {type:mime});
			}
			
			var succList = <?=json_encode($succDataList)?>;
			
			async function frmUpdate(){
				var dataCnt = 0;
				$.each(succList, async function(it_id, data){
					dataCnt++;
					var sendData = new FormData();
					
					$.each(data, function(key, value){
						sendData.append(key, value);
					});
					
					var imgList = data.imgList;
					for(var i = 0; i < imgList.length; i++){
						nowToDataURLResult = "";
						if(imgList[i]){
							await toDataURL(imgList[i]);
							var blob = dataURItoBlob(nowToDataURLResult);
							var ext = blob.type.split("/")[1];
							var file = dataURLtoFile(nowToDataURLResult, "file_" + i + "." + ext);
							sendData.append("file" + (i + 1), file);
						}
					}
					
					$.ajax({
						url : "https://eroumcare.com:9001/api/prod/insert",
						type : "POST",
						async : false,
						cache : false,
						processData : false,
						contentType : false,
						data : sendData,
						success : function(result){
							if(result.errorYN == "Y"){
								$.ajax({
									url : "./ajax.item.excel.delete.php",
									type : "POST",
									data : {
										it_id : it_id
									}
								});
								
								$("#successCnt").text(Number($("#successCnt").text()) - 1);
								$("#failCnt").text(Number($("#failCnt").text()) + 1);
							} else {
								$.ajax({
									url : "./ajax.item.excel.change.php",
									type : "POST",
									data : {
										it_id : it_id,
										prodId : result.data.prodId
									}
								});
							}
						},
						error : function(result){
							$.ajax({
								url : "./ajax.item.excel.delete.php",
								type : "POST",
								data : {
									it_id : it_id
								}
							});
							
							$("#successCnt").text(Number($("#successCnt").text()) - 1);
							$("#failCnt").text(Number($("#failCnt").text()) + 1);
						}
					});
				});
			}
			
			frmUpdate();
			
		})
	</script>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>