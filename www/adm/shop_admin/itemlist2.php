<?php
$sub_menu = '400310';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");
add_javascript('<script src="'.G5_JS_URL.'/jquery.fileDownload.js"></script>', 0);

$g5['title'] = '직배송 상품관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 검색처리
$select = array();
$where = array();

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sel_field = isset($_REQUEST['sel_field']) && in_array($_REQUEST['sel_field'], array('it_thezone2', 'it_name','it_id','it_admin_memo')) ? $_REQUEST['sel_field'] : '';
$page_rows = ($_REQUEST['page_rows'] != "") ? get_search_string($_REQUEST['page_rows']) : $config['cf_page_rows'];//


$qstr = '';


/////////////////////////////////////////////////
$where = " and ";
$sql_search = "";
if ($search != "") {
        if($sel_field == '') {
            $attrs = ['it_thezone2', 'it_name', 'it_id','it_admin_memo'];
            $sql_search .= " $where ( 1 != 1 ";
            $where = ' or ';
            foreach($attrs as $attr) {
                $sql_search .= " $where $attr like '%$search%' ";
            }
            $sql_search .= ' ) ';
            $where = ' and ';
        } else {
            $sql_search .= " $where $sel_field like '%$search%' ";
            $where = " and ";
        }
	$qstr .="&amp;search=".$search."&amp;sel_field=".$sel_field;
}



if($_REQUEST["prodSupYn"] != ""){//유통구분
	if($_REQUEST["prodSupYn"] != "all"){
		$sql_search .= " AND prodSupYn = '{$_REQUEST["prodSupYn"]}' ";
	}
	$qstr .="&amp;prodSupYn=".$_REQUEST["prodSupYn"];
}else{
	$sql_search .= " AND prodSupYn = 'Y' ";
}

if($_REQUEST["gubun"] != ""){//급여구분
	if($_REQUEST["gubun"] == "70"){//비급여
		$sql_search .= " AND a.ca_id like '70%' ";
	}else{//급여
		$sql_search .= " AND (a.ca_id like '10%' or a.ca_id like '20%') ";
	}
	$qstr .="&amp;gubun=".$_REQUEST["gubun"];
}

if($_REQUEST["it_deadline"] != ""){//마감시간
	switch($_REQUEST["it_deadline"]){
		case 1: $sql_search .= " AND it_deadline between '09:00:00' and '09:59:59' "; break;
		case 2: $sql_search .= " AND it_deadline between '10:00:00' and '10:59:59' "; break;
		case 3: $sql_search .= " AND it_deadline between '11:00:00' and '11:59:59' "; break;
		case 4: $sql_search .= " AND it_deadline between '12:00:00' and '12:59:59' "; break;
		case 5: $sql_search .= " AND it_deadline between '13:00:00' and '13:59:59' "; break;
		case 6: $sql_search .= " AND it_deadline between '14:00:00' and '14:59:59' "; break;
		case 7: $sql_search .= " AND it_deadline between '15:00:00' and '15:59:59' "; break;
		case 8: $sql_search .= " AND it_deadline between '16:00:00' and '16:59:59' "; break;
		case 9: $sql_search .= " AND it_deadline between '17:00:00' and '17:59:59' "; break;
		case 10: $sql_search .= " AND (it_deadline between '18:00:00' and '23:59:59' or it_deadline between '00:00:00' and '08:59:59') "; break;
		default: $sql_search .= " AND it_deadline between '09:00:00' and '09:59:59' "; break;
	}
	$qstr .="&amp;it_deadline=".$_REQUEST["it_deadline"];
}

if($_REQUEST["it_is_direct_delivery"] != ""){//위탁여부
	$sql_search .= " AND it_is_direct_delivery = '{$_REQUEST["it_is_direct_delivery"]}' ";
	$qstr .="&amp;it_is_direct_delivery=".$_REQUEST["it_is_direct_delivery"];
}

if($_REQUEST["it_direct_delivery_partner"] != ""){//파트너
	$sql_search .= " AND it_is_direct_delivery = 'Y' ";
	if($_REQUEST["it_direct_delivery_partner"] == "no_reg"){//미등록
		$sql_search .= " AND it_direct_delivery_partner = '' ";
	}else{
		$sql_search .= " AND it_direct_delivery_partner = '{$_REQUEST["it_direct_delivery_partner"]}' ";
	}
	$qstr .="&amp;it_direct_delivery_partner=".$_REQUEST["it_direct_delivery_partner"];
}

if($_REQUEST["it_sc_type"] != ""){//배송비 유형
	$sql_search .= " AND it_sc_type = '{$_REQUEST["it_sc_type"]}' ";
	$qstr .="&amp;it_sc_type=".$_REQUEST["it_sc_type"];
}

if($_REQUEST["it_default_warehouse"] != ""){//출하창고
	if($_REQUEST["it_default_warehouse"] == "미지정"){//미지정
		$sql_search .= " AND it_default_warehouse = '' ";
	}else{
		$sql_search .= " AND it_default_warehouse = '{$_REQUEST["it_default_warehouse"]}' ";
	}
	$qstr .="&amp;it_default_warehouse=".$_REQUEST["it_default_warehouse"];
}



//if ($sel_field == "")  $sel_field = "all";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
// if ($is_admin != 'super')
//     $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

//$rows = $config['cf_page_rows'];
$page_rows = (int)$page_rows ? (int)$page_rows : $config['cf_page_rows'];
$rows = $page_rows;

$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
	$sst = "it_time";
    $sod = "desc";
}



$sql_order = "order by  $sst $sod ";

$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows "; 
$result = sql_query($sql, true);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr .= '&amp;page='.$page.'&amp;page_rows='.$page_rows;


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// APMS - 2014.07.25
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');
$flist = array();
$flist = apms_form(1,0);
$warehouse_list = get_warehouses();
?>
<style>
  #loading_excel {
    display: none;
    width: 100%;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.3);
  }
  #loading_excel .loading_modal {
    position: absolute;
    width: 400px;
    padding: 30px 20px;
    background: #fff;
    text-align: center;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  #loading_excel .loading_modal p {
    padding: 0;
    font-size: 16px;
  }
  #loading_excel .loading_modal img {
    display: block;
    margin: 20px auto;
  }
  #loading_excel .loading_modal button {
    padding: 10px 30px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }
  .popup_box2 {
		display: none;
		position: fixed;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		z-index: 9999;
		background: rgba(0, 0, 0, 0.8);		
	}

	.popup_box_con {
		padding:20px;
		position: relative;
		background: #ffffff;
		z-index: 99999;
		margin-left:-206px;
	}
	.bg0 {background:#fff}
	.bg1 {background:#f2f5f9}
	.bg1 td {border-color:#e9e9e9}
</style>
<form name="frmsamhwaorderlist" id="frmsamhwaorderlist" style="margin-top:-15px;">
<input type="hidden" name="page_rows" id="page_rows" value="<?=$page_rows?>">
<input type="hidden" name="sst" id="sst" value="<?=$sst?>">
<input type="hidden" name="sod" id="sod" value="<?=$sod?>">
  <div class="new_form">
    <table class="new_form_table" id="search_detail_table">
      <tr>
        <th>검색조건</th>
        <td width="110">
		  유통구분<br>
            <select name="prodSupYn" id="prodSupYn">
            <option value="all" >전체</option>
            <option value="Y" <?php echo ($prodSupYn == "")?"selected":get_selected($prodSupYn, 'Y'); ?>>유통</option>
            <option value="N" <?php echo get_selected($prodSupYn, 'N'); ?>>비유통</option>
          </select>
        </td>
		<td width="110">
		  급여구분<br>
            <select name="gubun" id="gubun">
            <option value="">전체</option>
            <option value="10" <?php echo get_selected($gubun, '10'); ?>>급여</option>
            <option value="70" <?php echo get_selected($gubun, '70'); ?>>비급여</option>
          </select>
        </td>
		<td width="135">
		  마감시간<br>
            <select name="it_deadline" id="it_deadline" style="width:125px;">
            <option value="" >전체</option>
            <option value="1" <?php echo get_selected($it_deadline, '1'); ?>>09:00~10:00</option>
            <option value="2" <?php echo get_selected($it_deadline, '2'); ?>>10:00~11:00</option>
            <option value="3" <?php echo get_selected($it_deadline, '3'); ?>>11:00~12:00</option>
            <option value="4" <?php echo get_selected($it_deadline, '4'); ?>>12:00~13:00</option>
			<option value="5" <?php echo get_selected($it_deadline, '5'); ?>>13:00~14:00</option>
			<option value="6" <?php echo get_selected($it_deadline, '6'); ?>>14:00~15:00</option>
			<option value="7" <?php echo get_selected($it_deadline, '7'); ?>>15:00~16:00</option>
			<option value="8" <?php echo get_selected($it_deadline, '8'); ?>>16:00~17:00</option>
			<option value="9" <?php echo get_selected($it_deadline, '9'); ?>>17:00~18:00</option>
			<option value="10" <?php echo get_selected($it_deadline, '10'); ?>>18:00~익)09:00</option>

          </select>
        </td>
		<td width="110">
		  위탁여부<br>
            <select name="it_is_direct_delivery" id="it_is_direct_delivery">
            <option value="" >전체</option>
            <option value="1" <?php echo get_selected($it_is_direct_delivery, '1'); ?>>Y</option>
            <option value="0" <?php echo get_selected($it_is_direct_delivery, '0'); ?>>N</option>
          </select>
        </td>
		<td width="150">
		  파트너<br>
            <select name="it_direct_delivery_partner" id="it_direct_delivery_partner"  style="width:140px;">
            <option value="" >전체</option>
			<option value="no_reg" >미등록</option>
            <?php
            $partners = get_partner_members();
            foreach($partners as $partner) {
            ?>
            <option value="<?=$partner['mb_id']?>"<?=get_selected($partner['mb_id'], $it_direct_delivery_partner)?>><?=$partner['mb_name']?></option>
            <?php } ?>
          </select>
        </td>
		<td width="130">
		  배송비유형<br>
            <select name="it_sc_type" id="it_sc_type" style="width:120px;">
            <option value="" >전체</option>
            <option value="0" <?php echo get_selected($it_sc_type, '0'); ?>>쇼핑몰 기본 설정</option>
            <option value="1" <?php echo get_selected($it_sc_type, '1'); ?>>무료 배송</option>
            <option value="5" <?php echo get_selected($sel_stat, '5'); ?>>홀수/짝수 배송</option>

          </select>
        </td>
		<td width="145">
		  출하창고<br>
            <select name="it_default_warehouse" id="it_default_warehouse" style="width:135px;">
			<option value="" >전체</option>
            <?php
            foreach($warehouse_list as $warehouse) {
              echo '<option value="'.$warehouse.'" '.get_selected($it_default_warehouse, $warehouse).'>'.$warehouse.'</option>';
            }
            ?>
			</select>
        </td>
		<td>&nbsp;</td>
      </tr>

      <tr>
        <th>검색어</th>
        <td colspan="8">
          <select name="sel_field" id="sel_field">
            <option value="">전체</option>
            <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
            <option value="it_thezone2" <?php echo get_selected($sel_field, 'it_thezone2'); ?>>품목코드</option>
            <option value="it_id" <?php echo get_selected($sel_field, 'it_id'); ?>>상품관리코드</option>
            <option value="it_admin_memo" <?php echo get_selected($sel_field, 'it_admin_memo'); ?>>관리자메모</option>
          </select>
          <input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off" style="width:200px;">          
          <input type="submit" value="검색" class="newbutton" style="background-color:#000000;color:#ffffff;">
        </td>
      </tr>
    </table>
  </div>
</form>

<div style="margin:-5px 0px 0px 20px; width:300px;float:left">
	검색 개수 : <?php echo $total_count; ?> 건 
</div>
<div style="margin:-13px 20px 10px 20px; float:right;right:0px;">
	<select name="page_rows" id="page_rows2" onChange="javascript:$('#page_rows').val(this.value);$('#frmsamhwaorderlist').submit();" style="width:110px;">
        <option value="" >시스템 기본 보기</option>
		<option value="50"  <?=($page_rows =='50')?"selected":"";?>>50개씩보기</option>
        <option value="100" <?=($page_rows=='100')?"selected":"";?>>100개씩보기</option>
        <option value="500" <?=($page_rows=='500')?"selected":"";?>>500개씩보기</option>
    </select>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col" width="125px;">상품관리코드</th>
        <th scope="col" width="50px;">유통구분</th>
		<th scope="col" width="50px;">급여구분</th>
        <th scope="col" width="45px;">품목코드</th>
		<th scope="col" width="120px;"><a href="javascript:;" onClick="sort('a.ca_id')">카테고리</a></th>
        <th scope="col">상품명</th>
		<th scope="col" width="65px;">판매가격</th>
		<th scope="col" width="110px;">출하창고</th>
		<th scope="col" width="110px;">배송비유형</th>
		<th scope="col" width="20px;">위탁<br>사용</th>
		<th scope="col" width="130px;">파트너명</th>
		<th scope="col" width="80px;">회원ID</th>
		<th scope="col" width="100px;">관리자 메모</th>
		<th scope="col" width="50px;">주문마감<br>시간</th>
		<th scope="col" width="75px;"><a href="javascript:;" onClick="sort('it_time')">상품등록일자</a></th>
		<th scope="col" width="125px;"><a href="javascript:;" onClick="sort('it_update_time')">상품수정일자</a></th>
		<th scope="col" width="20px;">위탁<br>수정</th>
		<th scope="col" width="20px;">상품<br>수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $num = $total_count -(($page-1)*$page_rows)- $i ;

        $bg = 'bg'.($i%2);
		switch($row["it_sc_type"]){
			case "0":
			$it_sc_type = "쇼핑몰기본설정";
			break;
			case "1": case "3":
			$it_sc_type = "무료배송";
			break;
			case "2":
			$it_sc_type = "조건부무료배송";
			break;
			case "3":
			$it_sc_type = "유료배송";
			break;
			case "4":
			$it_sc_type = "수량별유료배송";
			break;
			case "5":
			$it_sc_type = "홀수/짝수배송";
			break;
			case "6":
			$it_sc_type = "포장수량무료배송";
			break;
		}
		$mb = get_member($row['it_direct_delivery_partner']);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td align="center"><?=$row["it_id"];//상품ID ?></td>
        <td align="center"><?=($row["prodSupYn"] == "Y") ? "유통" : "비유통";//유통유무?></td>
		<td align="center"><?=(substr($row["ca_id"],0,2) == "70")?"비급여":"급여";//급여유무 ?></a></td>
		<td align="center"><?=$row["it_thezone2"];//상품코드 ?></td>
		<td align="center"><?=$row["ca_name"];//카테고리 ?></td>
   		<td align="center"><?=$row["it_name"];//상품명 ?></td>
		<td align="right"><?=number_format($row["it_price"]);//판매가격 ?></td>
		<td align="center"><?=$row["it_default_warehouse"];//출하창고 ?></td>
		<td align="center"><?=$it_sc_type;//배송비유형 ?></td>
		<td align="center"><?=($row["it_is_direct_delivery"] == 0)?"":"Y";//위탁사용 ?></td>
		<td align="center"><?=$mb["mb_name"];//파트너명?></td>
		<td align="center"><?=$row["it_direct_delivery_partner"];//회원ID ?></td>
		<td align="center"><?=$row["it_admin_memo"];//관리자메모 ?></td>
		<td align="center"><?=($row["it_deadline"] == "00:00:00")?"":substr($row["it_deadline"],0,5);//주문마감시간 ?></td>
		<td align="center"><?=substr($row["it_time"],0,10);//상품등록일자 ?></td>
		<td align="center"><?=$row["it_update_time"];//상품수정일자 ?></td>
		<td align="center"><a href="javascript:;" onClick="go_edit('<?=$row["it_id"];//수정 ?>','<?=($row["prodSupYn"] == "Y") ? "유통" : "비유통";//유통유무?>','<?=(substr($row["ca_id"],0,2) == "70")?"비급여":"급여";//급여유무 ?>','<?=$row["it_name"];//상품명 ?>','<?=$row["it_is_direct_delivery"]?>','<?=$row["it_direct_delivery_partner"];//회원ID ?>','<?=($row["it_deadline"] == "00:00:00")?"":$row["it_deadline"];//주문마감시간 ?>','<?=$row["it_default_warehouse"];//출하창고 ?>','<?=$row["it_admin_memo"];//관리자메모 ?>')"><font color="blue">수정</font></a></td>
		<td align="center"><a href="itemform.php?w=u&it_id=<?=$row["it_id"]?>" target="_blank"><font color="blue">수정</font></a></td>
    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="17" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div id="loading_excel">
  <div class="loading_modal">
    <p>엑셀파일 다운로드 중입니다.</p>
    <p>잠시만 기다려주세요.</p>
    <img src="/shop/img/loading.gif" alt="loading">
    <button onclick="cancelExcelDownload();" class="btn_cancel_excel">취소</button>
  </div>
</div>

<div id="popup_box3" class="popup_box2">
    <div id="" class="popup_box_con" style="height:360px;margin-top:-180px;margin-left:-225px;width:450px;left:50%;top:50%;">
		<div class="form-group">
            <div class="se_sch_hd" style="font-size:20px;margin-bottom:10px;"><b>유통정보 수정</b></div>
        </div>
		<div class="form-group" style="background-color:#eeeeee;border-radius:5px;padding:10px;">
            <ul>
				<li>
					<span style="line-height:18px;">상품관리코드 </span>
					<span id="edit_it_id2" style="width:305px;float:right;line-height:18px;"></span><input type="hidden" name="edit_it_id" id="edit_it_id" value="11">
				</li>
				<li>
					<span style="line-height:18px;">유통여부 </span>
					<span id="edit_prodSupYn" style="width:305px;float:right;line-height:18px;"></span>
				</li>
				<li>
					<span style="line-height:18px;">급여구분 </span>
					<span id="edit_gubun" style="width:305px;float:right;line-height:18px;"></span>
				</li>
				<li>
					<span style="line-height:18px;">상품명 </span>
					<span id="edit_it_name" style="width:305px;float:right;line-height:18px;"></span>
				</li>
            </ul>			
        </div>
		<div class="form-group" style="padding:5px;">
            <ul>
				<li>
					<span style="line-height:30px;">
						* 위탁 여부 
					</span>
					<span id="" style="width:310px;float:right;line-height:30px;">
						<input type="checkbox" name="edit_it_is_direct_delivery" id="edit_it_is_direct_delivery" value="1">&nbsp;&nbsp;체크 시 직배송 상품으로 변경됩니다.
					</span>
				</li>
				<li>
					<span style="line-height:30px;">
						* 파트너 검색 
					</span>
					<span id="" style="width:310px;float:right;line-height:30px;">
						<select name="edit_it_direct_delivery_partner" id="edit_it_direct_delivery_partner"  style="width:315px;background-color:#ffffff;" class="frm_input">
							<option value="no_reg" >미등록</option>
							<?php
							$partners = get_partner_members();
							foreach($partners as $partner) {
							?>
							<option value="<?=$partner['mb_id']?>"<?=get_selected($partner['mb_id'], $it_direct_delivery_partner)?>><?=$partner['mb_name']?></option>
							<?php } ?>
						</select>
					</span>
				</li>
				<li>
					<span style="line-height:30px;">
						* 마감 시간
					</span>
					<span id="" style="width:310px;float:right;line-height:30px;">
						<input type="time" name="edit_it_deadline" id="edit_it_deadline" class="frm_input " style="width:315px;background-color:#ffffff;">
					</span>
				</li>
				<li>
					<span style="line-height:30px;">
						* 창고 선택 
					</span>
					<span id="" style="width:310px;float:right;line-height:30px;">
						<select name="edit_it_default_warehouse" id="edit_it_default_warehouse" style="width:315px;background-color:#ffffff;" class="frm_input">
							<?php
							foreach($warehouse_list as $warehouse) {
							  echo '<option value="'.$warehouse.'" '.get_selected($it_default_warehouse, $warehouse).'>'.$warehouse.'</option>';
							}
							?>
						</select>
					</span>
				</li>
				<li>
					<span style="line-height:30px;">
						* 관리자 메모 
					</span>
					<span id="" style="width:310px;float:right;line-height:30px;">
						<input type="text" name="edit_it_admin_memo" id="edit_it_admin_memo" class="frm_input" style="width:315px;background-color:#ffffff;">
					</span>
				</li>
            </ul>			
        </div>		

		<div style="text-align:right;bottom:0px;width:100%;margin-top:5px;">
			<button type="button" class="btn btn-black btn-sm btn_close" style="margin-right:5px;" onClick="info_close('대리인','popup_box3','contract_sign_type')">돌아가기</button><button type="button" class="btn btn-black btn-sm" style="background:black;color:#ffffff" onClick="item_edit()">수정하기</button><input type="hidden" id="array_id">
		</div>
	</div>
	
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
<div class="btn_fixed_top">
    <a href="javascript:downloadExcel();" class="btn " style="background:#339900;color:#fff;border-radius: 3px;">엑셀다운로드</a>
</div>

<script>
$(function() {
	var EXCEL_DOWNLOADER = null;
    $("#fr_date, #to_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+0d"
    });
});

function sort(a){
	var sst = $("#sst").val();
	if(sst == a){//같은 항목 선택
		if($("#sod").val() == "desc"){
			$("#sod").val("asc");
		}else{
			$("#sod").val("desc");
		}
	}else{
		$("#sst").val(a);
		$("#sod").val("asc");
	}
	$('#frmsamhwaorderlist').submit();	
}

function downloadExcel() {
    var href = './itemlist2.excel.download.php';

    $('#loading_excel').show();
    EXCEL_DOWNLOADER = $.fileDownload(href, {
      httpMethod: "POST",
      data: $("#frmsamhwaorderlist").serialize()
    })
      .always(function() {
        $('#loading_excel').hide();
      });
  }

function cancelExcelDownload() {
    if (EXCEL_DOWNLOADER != null) {
      EXCEL_DOWNLOADER.abort();
    }
    $('#loading_excel').hide();
  }


function go_edit(a,b,c,d,e,f,g,h,i){//a:상품관리코드,b:유통,c:급여,d:상품명,e:위탁,f:파트너,g:마감시간,h:창고,i:메모
	$('#edit_it_id2').text(": "+a);
	$('#edit_it_id').val(a);
	$('#edit_prodSupYn').text(": "+b);
	$('#edit_gubun').text(": "+c);
	$('#edit_it_name').text(": "+d);
	(e == 1)?$('#edit_it_is_direct_delivery').attr("checked",true):$('#edit_it_is_direct_delivery').attr("checked",false);
	if(f==""){$('#edit_it_direct_delivery_partner').val("no_reg");}else{$('#edit_it_direct_delivery_partner').val(f);}
	$('#edit_it_deadline').val(g);
	if(h == ""){$('#edit_it_default_warehouse').val("미지정");}else{$('#edit_it_default_warehouse').val(h);}
	$('#edit_it_admin_memo').val(i);	
	
	$('body').addClass('modal-open');
	$('#popup_box3').show();
}

function info_close(){
	$('#popup_box3').hide();
	$('body').removeClass('modal-open');
	$('#edit_it_id2').text("");
	$('#edit_it_id').val("");
	$('#edit_prodSupYn').text("");
	$('#edit_gubun').text("");
	$('#edit_it_name').text("");
	$('#edit_it_is_direct_delivery').attr("checked",false);
	$('#edit_it_direct_delivery_partner').val("");
	$('#edit_it_deadline').val("");
	$('#edit_it_default_warehouse').val("no_reg");
	$('#edit_it_admin_memo').val("");
}

function item_edit(){
	if(confirm("정말 수정하시겠습니까?")){
        var direct_delivery = 0;
		if($('#edit_it_is_direct_delivery').is(':checked')){
			direct_delivery = 1;
		}

		var params = {
            it_id : $('#edit_it_id').val()
		    , it_is_direct_delivery : direct_delivery
            , it_direct_delivery_partner : $("#edit_it_direct_delivery_partner").val()
            , it_deadline : $("#edit_it_deadline").val()
			, it_default_warehouse : $("#edit_it_default_warehouse").val()
			, it_admin_memo : $("#edit_it_admin_memo").val()
        }                
        // ajax 통신
        $.ajax({
            type : "POST",            // HTTP method type(GET, POST) 형식이다.
            url : "./ajax.item_edit.php",      // 컨트롤러에서 대기중인 URL 주소이다.
            data : params,            // Json 형식의 데이터이다.
			dataType: "json",
            success : function(res){ // 비동기통신의 성공일경우 success콜백으로 들어옵니다. 'res'는 응답받은 데이터이다.
                // 응답코드 > 0000
				if(res == true){
					alert("상품이 정상적으로 수정되었습니다.");
					location.reload();
				}else{
					alert("유통정보 수정에 실패 했습니다.\n다시 시도해 주세요.");
				}
            },
            error : function(XMLHttpRequest, textStatus, errorThrown){ // 비동기 통신이 실패할경우 error 콜백으로 들어옵니다.
                alert("통신 실패.");
            }
        });
	}else{
		return false;
	}
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
