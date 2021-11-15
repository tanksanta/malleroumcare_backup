<?php
include_once('./_common.php');

// 회원이 아닌 경우
if (!$is_member) goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/claim_manage.php'));

$g5['title'] = '내 요양정보 확인';
include_once("./_head.php");

// 요청 내역
$sql = "
  select *
  from macro_request
  where mb_id = '{$member['mb_id']}' ORDER BY id DESC
";
$result = sql_query($sql);

// 최근조회완료
$sql2 = "
  select *
  from macro_request
  where mb_id = '{$member['mb_id']}' AND status = 'D' ORDER BY updated_at DESC LIMIT 1
";
$result2 = sql_fetch($sql2);

// 대기중인 요청
$sql3 = "
  select count(*) as cnt
  from macro_request
  where mb_id = '{$member['mb_id']}' AND status = 'W'
";
$result3 = sql_fetch($sql3);

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/DataTables/datatables.min.css">', 13);
?>

<style>
.btn_se_submit {
  display: inline-block;
  padding: 10px;
  width: 250px;
  text-align: center;
  color: #fff;
  font-size: 16px;
  border-radius: 5px;
  background: #333333;
}
.btn_se_submit.active {
  background: #6e9254;
}
.btn_se_submit:hover, .btn_se_submit:focus {
  color: #fff;
}
.recent_info {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 30px;
}
</style>
<section class="ltcare_wrap">
  <div class="ltcare_tit" >
    <p>내 요양정보 확인</p>
    <p>수급자명, 요양인정번호 입력 후 조회 하시면<br/>내 정보 확인이 가능합니다.</p>
  </div>
  <div class="ltcare_search">
    <form id="form_simple_eform" class="form-horizontal" autocomplete="off" onsubmit="return false;">
        <div class="search_input">
        	<p>
        		<label>
	        		<span>수급자명</span> 
	        		<input type="hidden" name="penId" id="mb_id" value="<?php echo $member['mb_id']; ?>">
		            <input type="hidden" name="penId" id="waiting_cnt" value="<?php echo $result3['cnt']; ?>">
		              <input type="text" name="penNm" id="penName"   value="<?php if($dc) echo $dc['penNm']; ?>" placeholder="수급자명">
        		</label>
        	</p><br>
        	<p>
        		<label>
        			<span>요양인정번호</span>
        			<input type="text" name="penNm" id="penNum"   value="L">
        		</label>
        	</p>
        	</div>
        	
        	<!-- <div class="form-group">
        	            <label for="penNm" class="col-md-2 control-label">
        	              <strong>수급자명</strong>
        	            </label>
        	            <div class="col-md-3" style="max-width: unset;">
        	            <input type="hidden" name="penId" id="mb_id" value="<?php echo $member['mb_id']; ?>">
        	            <input type="hidden" name="penId" id="waiting_cnt" value="<?php echo $result3['cnt']; ?>">
        	              <input type="text" name="penNm" id="penName" class="form-control input-sm pen_id_flexdatalist" value="<?php if($dc) echo $dc['penNm']; ?>" placeholder="수급자명">
        	            </div>
        	          </div>
        	          <div class="form-group">
        	            <label for="penNm" class="col-md-2 control-label">
        	              <strong></strong>
        	            </label>
        	            <div class="col-md-3" style="max-width: unset;">
        	              <input type="text" name="penNm" id="penNum" class="form-control input-sm pen_id_flexdatalist" value="L">
        	            </div>
        	          </div> -->
        
          <button type="submit" id="btn_submit">
            조회요청
          </button>
    </form>
  </div>
</section>
<p style="text-align:right;">최근 업데이트 : <?=$result2['updated_at']?></p>
<section>
    <div class="recent_info">
        <p>· 수급자명 : <?=$result2['recipient_name']?></p>
        <p>· 장기요양정보 : L<?=$result2['recipient_num']?></p>
        <p>· 생년월일 : <?=$result2['birth']?></p>
        <p>· 인정등급 : <?=$result2['grade']?></p>
        <p>· 대상자구분 : <?=$result2['type']?></p>
        <p>· 본인부담율 : <?=$result2['percent']?></p>
    </div>
</section>
<div id="list_wrap" class="list_box">
    <div class="table_box">
        <table id="table_list">
            <thead>
            <tr>
                <th>No.</th>
                <th>수급자 정보</th>
                <th>처리상태</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $query_num = sql_num_rows($result);
                for ($i=0; $row=sql_fetch_array($result); $i++) {
                    $info = $row['recipient_name'] . "(L" . $row['recipient_num'] . ")";
                    $status = $row['status'] == 'D' ? "처리완료" : "<span class='txt_point'>대기중</span>";
            ?>
            <tr>
                <td class="text_c"><?=$query_num?></td>
                <td ><?=$info?></td>
                <td class="text_c"><?=$status?></td>
            </tr>
            <?php $query_num--; } ?>
            </tbody>
        </table>
    </div>
    <div class="list-paging">
      <ul class="pagination ">
        <li> </li>
        <li><a href="#">&lt;</a></li>
        <li class="active"><a href="#">1</a></li>
        <!-- <li><a href="#">2</a></li>
        <li><a href="#">3</a></li> -->
        <li><a href="#">&gt;</a></li>
        <li></li>
      </ul>
    </div>
</div>
<script>
    $("#btn_submit").click(function() {
        var name = $("#penName").val();
        var num = $("#penNum").val();
        if (name.length < 2) {
            alert("수급자명을 정확히 입력하세요.");
            return false;
        }
        if (num.length < 10) {
            alert("수급자 번호를 정확히 입력하세요.");
            return false;
        }
        num = num.substring(1);
        if (/^[0-9]*$/.test(num) == false) {
            alert("수급자 번호는 숫자만 입력하세요.");
            return false;
        }

        if ($("#waiting_cnt").val() > 0) {
            alert("대기중인 조회 요청이 있습니다. 업데이트 완료 후 조회가 가능합니다.");
            return false;
        }

        var mb_id = $("#mb_id").val();
        $.ajax({
            type: 'POST',
            url: './ajax.macro_request.php',
            data: {
                name: name,
                num: num,
                mb_id: mb_id
            },
            dataType: 'json'
        })
        .done(function(result) {
            alert('요청되었습니다.');
            window.location.reload();
        })
        .fail(function($xhr) {
            var data = $xhr.responseJSON;
            alert(data && data.message);
        });
    })
</script>

<?php include_once("./_tail.php"); ?>
