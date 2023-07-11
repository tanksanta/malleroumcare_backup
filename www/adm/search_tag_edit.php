<?php
$sub_menu = '300410';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '검색태그 관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// 검색처리
$select = array();
$where = array();

$st_id = isset($_GET['st_id']) ? get_search_string($_GET['st_id']) : '';

//$select[] = ' m.mb_id ';

// select 배열 처리
$select[] = "*";
$sql_select = implode(', ', $select);

// where 배열 처리
$where[] = "st_id = '".$st_id."'";
$sql_where = " WHERE 1 ";//" WHERE E.entId = '{$entId}' ";
if($where) {
  $sql_where .= ' AND '.implode(' AND ', $where);
}

$sql_from = " FROM `g5_search_tag` E";
$result = sql_query("SELECT " . $sql_select . $sql_from . $sql_where );
//echo "SELECT " . $sql_select . $sql_from . $sql_where;

$row=sql_fetch_array($result);

$fr_date2 = $row["fr_date"];
$to_date2 = $row["to_date"];
if ($st_id == "") {
    $to_date2 = date("Y-m-d", strtotime("+1 month"));
    $fr_date2 = date("Y-m-d");
}

?>
<form method="post" name="form_search_tag">
<input type="hidden" name="st_id" id='st_id' value="<?=$st_id?>">
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;margin-top:20px;">
    <div style="float:left;margin-top:5px;width:120px;">검색태그 <font color="red">*</font></div>
	<div id="" class="" style="display:inline;">
		<input type="text" name="st_text" id="st_text" class="frm_input" style="width:550px;padding-left:5px;<?=($st_id != "")?"color:#aaaaaa":"background:#ffffff;"?>" maxlength="15" value="<?=$row["st_text"]?>" <?=($st_id != "")?"readonly":""?>>&nbsp;&nbsp;&nbsp;※ 글자 수 가이드 : 1개당 8글자(16byte)  이하 권장, 노출하는 전체 검색태그 글자 수의 총합이 40글자 이하
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">유형 <font color="red">*</font></div>
	<div id="" class="" style="display:inline;">
		<input type="radio" name="type" id="type1" value="1" <?=($row["type"] == "" || $row["type"] == "1")?"checked":"";?>><label for='type1'> 검색</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="type" id="type2" value="2" <?=($row["type"] == "2")?"checked":"";?> onClick="$('#link').focus();"><label for='type2' onClick="$('#link').focus();"> 링크이동&nbsp;&nbsp;&nbsp;</label><input type="text" name="link" id="link" class="frm_input" style="width:422px;padding-left:5px;background:#ffffff;"  value="<?=$row["link"]?>">
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">메모</div>
	<div id="" class="" style="display:inline;">
		<input type="text" name="memo" id="memo" class="frm_input" style="width:550px;padding-left:5px;background:#ffffff;" value="<?=$row["memo"]?>">
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">시작일자 <font color="red">*</font></div>
	<div id="" class="" style="display:inline;">
		<input type="text" id="fr_date2" name="fr_date2" value="<?php echo $fr_date2; ?>" class="frm_input" maxlength="10" autocomplete='off' readonly style="text-align:center;background:#ffffff;">
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">종료일자 <font color="red">*</font></div>
	<div id="" class="" style="display:inline;">
		<input type="text" id="to_date2" name="to_date2" value="<?php echo $to_date2; ?>" class="frm_input" maxlength="10" autocomplete='off' readonly style="text-align:center;background:#ffffff;">
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">우선순위</div>
	<div id="" class="" style="display:inline;">
		<input type="text" name="order_num" id="order_num" class="frm_input" min="1" max="99" maxlength="2" style="width:140.67px;text-align:center;background:#ffffff;" value="<?=$row["order_num"]?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
	</div>
</div>
<div class="local_desc01 local_desc" style="background:#fff;border:#fff;border-bottom:1px solid #dddddd;padding-bottom:20px;">
    <div style="float:left;margin-top:5px;width:120px;">사용여부 <font color="red">*</font></div>
	<div id="" class="" style="display:inline;">
		<input type="radio" name="useYN" id="useYN1" value="Y" <?=($row["useYN"] == "" || $row["useYN"] == "Y")?"checked":"";?>><label for='useYN1'> 사용</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="useYN" id="useYN2" value="N" <?=($row["useYN"] == "N")?"checked":"";?>><label for='useYN2'> 미사용</label>
	</div>
</div>

<div class="btn_fixed_top" style="text-align:center;">
    <input type="button" onClick="$(location).attr('href','./search_tag_list.php?<?=explode("?",$_SERVER["REQUEST_URI"])[1]?>')" class="btn" style="background:#666666;color:#ffffff;width:80px;cursor:pointer;" value="목록"> <input type="button" value="<?=($st_id == "")?"저장":"수정";?>" class="btn " style="background:#ff3399;color:#fff;width:80px;cursor:pointer;" onClick="search_tag_edit();">
</div>

</form>
<script>
$(function() {
    $("#fr_date2, #to_date2").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        maxDate: "+2y"
    });

});

function search_tag_edit(){
	//검색태그 글자수 체크
	if(maxLengthCheck('st_text', "검색태그", 30,2) == false){
		return false;
	}

	if($("#type2").is(':checked') && $("#link").val() == ""){
		alert("필수항목을 모두 입력하시기 바랍니다.");
		$("#link").focus();
		return false;
	}
		
	if($("#st_id").val() == ""){//등록 시
		$("#page").val("");
	}
	var queryString = $("form[name=form_search_tag]").serialize() ;
	$.ajax({
		method: "POST",
		url: "./ajax.search_tag_edit.php",
		async:false,
		data: queryString,
	}).done(function (data) {
		console.log(data);
		if (data.message == 'OK') {
			location.href="./search_tag_list.php?<?=explode("?",$_SERVER["REQUEST_URI"])[1]?>";
		} else {
			alert(data.message);			
		}
	});

}

/**
 * 바이트 문자 입력가능 문자수 체크
 * 
 * @param id : tag id 
 * @param title : tag title
 * @param maxLength : 최대 입력가능 수 (byte)
 * @returns {Boolean}
 */
function maxLengthCheck(id, title, maxLength,minLength){
     var obj = $("#"+id);
     if(maxLength == null) {
         maxLength = obj.attr("maxLength") != null ? obj.attr("maxLength") : 1000;
     }
     if(Number(byteCheck(obj)) < Number(minLength)){
		alert("검색태그는 최소 2byte 이상 입력해야 합니다.");
		//alert(title + "이(가) 입력최소문자에 미달하였습니다.\n2글자 이상 입력하시기 바랍니다..");
         obj.focus();
         return false;
	 }else if(Number(byteCheck(obj)) > Number(maxLength)) {
         alert("검색태그는 최대 30byte까지 입력 가능합니다.");
		 //alert(title + "이(가) 입력가능문자수를 초과하였습니다.\n(영문, 숫자, 일반 특수문자 : " + maxLength + " / 한글, 한자, 기타 특수문자 : " + parseInt(maxLength/2, 10) + ")");
         obj.focus();
         return false;
     } else  {
         return true;
    }
}
 
/**
 * 바이트수 반환  
 * 
 * @param el : tag jquery object
 * @returns {Number}
 */
function byteCheck(el){
    var codeByte = 0;
    for (var idx = 0; idx < el.val().length; idx++) {
        var oneChar = escape(el.val().charAt(idx));
        if ( oneChar.length == 1 ) {
            codeByte ++;
        } else if (oneChar.indexOf("%u") != -1) {
            codeByte += 2;
        } else if (oneChar.indexOf("%") != -1) {
            codeByte ++;
        }
    }
    return codeByte;
}

</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
