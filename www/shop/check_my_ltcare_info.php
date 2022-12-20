<?php
// 강제로 못 열도록 막음
header("Not Found");
exit();

include_once('./_common.php');

// 회원이 아닌 경우
if (!$is_member) goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/claim_manage.php'));

$g5['title'] = '내 요양정보 확인';
include_once("./_head.php");

// 요청 내역
$sql_result = "
  select *
  from macro_request
  where mb_id = '{$member['mb_id']}' and status = 'U' GROUP BY recipient_name,recipient_num ORDER BY id DESC
";
$result = sql_query($sql_result);

$sql_result_list = "
  select *
  from macro_request
  where mb_id = '{$member['mb_id']}' ORDER BY id DESC
";
$result_list = sql_query($sql_result_list);
$recipient_list = [];
for ($i=0; $row=sql_fetch_array($result_list); $i++) {
  $list_item = $row;
  array_push($recipient_list, $list_item);
}


// 최근조회완료
$sql_recent = "
  select *
  from macro_request
  where mb_id = '{$member['mb_id']}' ORDER BY updated_at DESC LIMIT 1
";
$recent_result = sql_fetch($sql_recent);

/*
// 최근조회 사진 가져오기
$sql_img = "
  select *
  from macro_request_image
  where id = '{$recent_result['id']}'
";
$recent_result_images = sql_query($sql_img);
$query_num = sql_num_rows($recent_result_images);
$images = [];
for ($i=0; $row=sql_fetch_array($recent_result_images); $i++) {
  $image = G5_URL . "/data/person/img/" . $row['image_url'];
  array_push($images, $image);
}

// 대기중인 요청
$sql_result3 = "
  select count(*) as cnt
  from macro_request
  where mb_id = '{$member['mb_id']}' AND status = 'W'
";
$result3 = sql_fetch($sql_result3);
*/

/**
* 기존에 있던  macro_request 테이블을 재사용하기 위한 작업
* 새로이 필요한 컬럼이 존재하는지 확인 후, 없으면 새로 추가하는 작업 진행
*/
$sql_check = "
  show columns from macro_request where field in ('rem_amount','penExpiDtm','penApplyDtm','bathingChair','safetyHandGrip','sliveryPreventSocks','safetyPreventSlivery','simpleToilet','cane','cushionPreventMatriss','postureChangeTool','bedsorePreventMatriss','adultWalker','runway','movingToilet','incontinencePanty','mWheelChair','eBed','mBed','lendBedsorePreventionMatriss','portableBath','bathLift','loiteringDetection','lendRunway');
";
$res_check = sql_query($sql_check);
if(sql_num_rows($res_check) == 0){
  $append_col = "alter table macro_request ".
                "add column bathingChair varchar(10) default '1' after percent,".
                "add column safetyHandGrip varchar(10) default '10' after percent,".
                "add column sliveryPreventSocks varchar(10) default '6' after percent,".
                "add column safetyPreventSlivery varchar(10) default '5' after percent,".
                "add column simpleToilet varchar(10) default '2' after percent,".
                "add column cane varchar(10) default '1' after percent,".
                "add column cushionPreventMatriss varchar(10) default '1' after percent,".
                "add column postureChangeTool varchar(10) default '5' after percent,".
                "add column bedsorePreventMatriss varchar(10) default '1' after percent,".
                "add column adultWalker varchar(10) default '2' after percent,".
                "add column runway varchar(10) default '6' after percent,".
                "add column movingToilet varchar(10) default '1' after percent,".
                "add column incontinencePanty varchar(10) default '4' after percent,".
                "add column mWheelChair varchar(10) default '1' after percent,".
                "add column eBed varchar(10) default '1' after percent,".
                "add column mBed varchar(10) default '1' after percent,".
                "add column lendBedsorePreventionMatriss varchar(10) default '1' after percent,".
                "add column portableBath varchar(10) default '1' after percent,".
                "add column bathLift varchar(10) default '1' after percent,".
                "add column loiteringDetection varchar(10) default '1' after percent,".
                "add column lendRunway varchar(10) default '1' after percent,".
                "add column rem_amount varchar(30) default '1600000' after percent, ".
                "add column penExpiDtm varchar(30) default null after percent, ".
                "add column penApplyDtm varchar(30) default null after percent";
  sql_query($append_col);
}

$sql_write = "
select recipient_name,recipient_num,birth,grade,type,percent,penApplyDtm,penExpiDtm,rem_amount from macro_request where mb_id = '{$member['mb_id']}' and status = 'U';
";

$arr_write = [];
$res_write = sql_query($sql_write);
for ($i=0; $row=sql_fetch_array($res_write); $i++) {
  array_push($arr_write, $row);
}

$result_arr = [];
$res_table = sql_query($sql_result);
for ($i=0; $row=sql_fetch_array($res_table); $i++) {            
  $result_arr[] = $row;
}

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/DataTables/datatables.min.css">', 13);
?>

<style>
.btn_se_submit {
  display: inline-block;
  padding: 5px;
  width: 100px;
  text-align: center;
  color: #333;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ddd;
  background: #fff;
}
.btn_write {
  display: inline-block;
  padding: 5px;
  width: 100px;
  text-align: center;
  color: #333;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ddd;
  background: #f5f5f5;
}
#btn_already {
  display: inline-block;
  padding: 5px;
  width: 100px;
  text-align: center;
  color: #ddd;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ddd;
  background: #f5f5f5;
}
.recent_info {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 30px;
}
#table_result tr {
  text-align:center;
}
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.ltcare_wrap .ltcare_tit{position: relative; width:100%; height: fit-content; text-align: left; padding:10px 5px; line-height: 50px;}
.ltcare_wrap .ltcare_tit p:first-child{font-size:30px;font-weight:bold;margin-bottom:50px; color: #000}
.ltcare_wrap .ltcare_search {padding:40px;background: #f5f5f5;margin-bottom:20px;text-align:center;}
.ltcare_wrap .ltcare_search .search_input{display:inline-block;text-align:left;margin-right:20px;}
.ltcare_wrap .ltcare_search .search_input p{display:inline-block;width:400px;}
.ltcare_wrap .ltcare_search .search_input p label{width:100%;}
.ltcare_wrap .ltcare_search .search_input .head{float: left;width:fit-content;margin-right:10px;font-size:20px;display:inline-block;line-height: 40px;}
.ltcare_wrap .ltcare_search .search_input p span{float: left;width:150px;font-size:20px;display:inline-block;line-height: 40px;}
.ltcare_wrap .ltcare_search .search_input .penName{width:calc(100% - 180px);font-size:16px;border:0;background: #fff;padding:10px 10px;}
.ltcare_wrap .ltcare_search .search_input .penNum{width:calc(100% - 200px);font-size:16px;border:0;background: #fff;padding:10px 10px;}
.ltcare_wrap .ltcare_search button{display:inline-block;background: #333;color:#fff;position:relative;height:100px;width:100px;border-radius: 3px;top:-30px;font-size:16px;}
.txt_point{color:#ef8505;}
.infomation_txt p {color:#000; line-height:1.6;}

@media only screen and (max-width:960px) {
	 .ltcare_wrap .ltcare_tit p:first-child{font-size:30px;margin-bottom: 20px;margin-top:20px;}
	 .ltcare_wrap .ltcare_tit p:last-child{font-size:20px;}
	 .ltcare_wrap .ltcare_search{padding:20px;}
	 .ltcare_wrap .ltcare_search .search_input{margin-right:0;width:100%;}
	 .ltcare_wrap .ltcare_search .search_input p{width:100%;}
	 .ltcare_wrap .ltcare_search .search_input p span{font-size:16px;width:120px;}
	 .ltcare_wrap .ltcare_search .search_input .penName{    width: calc(100% - 150px);}
	 .ltcare_wrap .ltcare_search .search_input .penNum{    width: calc(100% - 170px);}
	 .ltcare_wrap .ltcare_search button{top:0;width:100%;height:40px;}
}

/* 품목찾기 팝업 */
#item_popup_box {
  display: none;
  position: fixed;
  width: 100%;
  height: 100%;
  left: 0;
  top: 0;
  z-index:9999;
  background: rgba(0, 0, 0, 0.8);
}
#item_popup_box iframe {
  width:1000px;
  height:700px;
  max-height: 80%;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
}
.popup_box_close {
  position:absolute;
  top:15px;
  right: 15px;
  color: white;
  font-size: 2.5em;
  cursor:pointer;
}
@media (max-width: 1020px) {
  #item_popup_box iframe {
    width: 100%;
    height: 90%;
    max-height:100%;
    transform: none;
    top: auto;
    left: 0px;
    bottom:0px;
  }
}

</style>
<section class="ltcare_wrap">
  <div class="ltcare_tit">
    <p>요양정보 간편조회</p>
  </div>
  <div class="ltcare_search">
    <form id="form_simple_eform" class="form-horizontal" autocomplete="off" onsubmit="return false;">
        <div class="search_input">
        	<p>
        		<label>
	        		<span>수급자명</span> 
	        		<input type="hidden" name="penId" id="mb_id" value="<?php echo $member['mb_id']; ?>">
		            <input type="hidden" name="penId" id="waiting_cnt" value="<?php echo $result3['cnt']; ?>">
		              <input type="text" name="penNm" class = "penName" id="penName"   value="<?php if($dc) echo $dc['penNm']; ?>" placeholder="수급자명">
        		</label>
        	</p><br>
        	<p>
        		<label>
        			<span>요양인정번호</span>
        			<span class = "head">L</span>
        			<input type="number" name="penNm" class = "penNum" id="penNum"   value="">
        		</label>
        	</p>
        	</div>

          <button type="submit" id="btn_submit">
            조회요청
          </button>
    </form>
  </div>
</section>
<p style="width: 100%; background: #f5f5f5; height: 1px; display: inline-block; content: ' '"/>
<p id = "rep_cnt" style="float:left; display: inline-block; line-height: 20px; font-weight:bold; font-size:18px; margin : 5px 0px;">조회결과 <?php if($result_arr == null){echo "0";}else{echo sizeof($result_arr);}?>명</p>

<div id="list_wrap" class="list_box" style="margin-top:20px;">
    <div class="table_box">
        <table id="table_list">
            <colgroup>
              <col width="5%"/>
              <col width="15%"/>
              <col width="40%"/>
              <col width="20%"/>
              <col width="20%"/>
            </colgroup>
            <thead>
            <tr>
                <th>No.</th>
                <th>수급자명</th>
                <th>요양정보</th>
                <th>수급자 등록</th>
                <th>조회일</th>
            </tr>
            </thead>
            <tbody id = "table_result">
              <tr>
                  <td  colspan="5" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                    조회된 수급자 정보가 없습니다.
                  </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="infomation_txt">	
      <p>*간편조회 한 수급자 정보를 등록하면, 최신 데이터로 관리할 수 있습니다.</p>	
    </div>
    <div class="list-paging">
      <ul class="pagination ">
        <li> </li>
        <li><a href="#">&lt;</a></li>
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">&gt;</a></li>
        <li></li>
      </ul>
    </div>
</div>

<div id="item_popup_box">
  <div class="popup_box_close">
    <i class="fa fa-times"></i>
  </div>
  <iframe name="iframe" src="" scrolling="yes" frameborder="0" allowTransparency="false"></iframe>
</div>

<script>
    var recipient_list = <?=json_encode($recipient_list)?>;
    var table_result = <?=json_encode($result_arr)?>;
    buildTable(table_result);
    
    $('#item_popup_box').click(function() {
      $('body').removeClass('modal-open');
      $('#item_popup_box').hide();
    });

    $('#table_result').on('click', '.search_rep_info', function(){
      var checkBtn = $(this);
      var tr = checkBtn.parent().parent();
      var td = tr.children('input');
      var penNm = tr.children('input.penNm')[0].value;
      var penLtmNum = 'L'+tr.children('input.penLtmNum')[0].value;
      var url = 'pop.recipient_info.php?page=search&penNm='+penNm+'&penLtmNum=L'+penLtmNum;

      $('#item_popup_box iframe').attr('src', url);
      $('body').addClass('modal-open');
      $('#item_popup_box').show();
    });

    let arr_wirte = <?=json_encode($arr_write);?>;

    $('#table_result').on('click', '.btn_write', function(){
      var checkBtn = $(this);
      var tr = checkBtn.parent().parent();
      var link = 'my_recipient_write.php';
            
      var write_data;
      if(arr_wirte.length > 0){
        for(var ind=0; ind < arr_wirte.length; ind++){
          if(arr_wirte[ind]['recipient_name'] == tr.children('input.penNm')[0].value && arr_wirte[ind]['recipient_num'] == tr.children('input.penLtmNum')[0].value){
            write_data = arr_wirte[ind];
            break;
          }
        }
      }
      $.redirectPost(link, write_data);
    });

    $("#btn_submit").click(function() {
        var btn_submit = document.getElementById('btn_submit');
        btn_submit.disabled = true;

        var name = $("#penName").val();
        var num = 'L'+$("#penNum").val();
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

        var penNm = '';
        var penNm_list = [];
        for(var i = 0; i < recipient_list.length; i++)
          penNm_list[i] = recipient_list[i]['recipient_name'];
        
        if(penNm_list.indexOf(name) > -1){
            var search_result = [];
            for(var i = 0; i < recipient_list.length; i++){
              if(name == recipient_list[i]['recipient_name']){
                search_result[0] = recipient_list[i];
                break;
              }
            }

            // 이미 등록이 완료된 수급자 정보를 검색한 경우
            if(search_result[0]['status'] == 'R'){
              alert("이미 등록된 수급자입니다.\n 운영관리>수급자 관리 메뉴에서 확인하실 수 있습니다.");
              location.href = './my_recipient_list.php';
              return false;
            }

            $("#rep_cnt").text('조회결과 '+search_result.length+'명');
            buildTable(search_result);

            alert("조회가 완료되었습니다.");            
            btn_submit.disabled = false;
        } else {
          $.ajax('ajax.recipient.inquiry.php', {
            type: 'POST',  // http method
            data: { id : num,rn : name },  // data to submit
            success: function (data, status, xhr) {
                let rep_list = data['data']['recipientContractDetail']['Result'];                
                let rep_info = rep_list['ds_welToolTgtList'][0];
                let penPayRate = rep_info['REDUCE_NM'] == '일반' ? '15%': rep_info['REDUCE_NM'] == '기초' ? '0%' : rep_info['REDUCE_NM'] == '의료급여' ? '6%'
                                                              : (rep_info['SBA_CD'].split('(')[1].substr(0, rep_info['SBA_CD'].split('(')[1].length-1));
								
                let rem_amount = 1600000;
                let today = new Date();
                let st_date, ed_date;
                if(rep_list['ds_toolPayLmtList'] != null && rep_list['ds_toolPayLmtList'].length>0){
                  for(var i =0; i< rep_list['ds_toolPayLmtList'].length;i++){                    
                    st_date = new Date(setDate(rep_list['ds_toolPayLmtList'][i]['APDT_FR_DT']));
                    ed_date = new Date(setDate(rep_list['ds_toolPayLmtList'][i]['APDT_TO_DT']));
                    if(st_date < today && ed_date > today){
                      rem_amount = rep_list['ds_toolPayLmtList'][i]['REMN_AMT'];
                      break;
                    }
                  }
                }

                $.post('./ajax.inquiry_log.php', {
                  data: { ent_id : "<?=$member['mb_id']?>",ent_nm : "<?=$member['mb_name']?>",pen_id : num,pen_nm : name,resultMsg : status,occur_page : "check_my_ltcare_info.php" }
                }, 'json')
                .fail(function($xhr) {
                  var data = $xhr.responseJSON;
                  alert("로그 저장에 실패했습니다!");
                });

                $.post('./ajax.my.recipient.hist.php', {
                  data: data['data'],
                  status: false
                }, 'json')
                .fail(function($xhr) {
                  var data = $xhr.responseJSON;
                  alert("계약정보 업데이트에 실패했습니다!");
                });

                $.ajax({
                    type: 'POST',
                    url: './ajax.macro_request.php',
                    data: {
                        status: "U",
                        mb_id: "<?=$member['mb_id']?>",
                        name: name,
                        num: num,
                        birth: setDate(rep_info['BDAY']),
                        grade: rep_info['LTC_RCGT_GRADE_CD']+"등급",
                        type: rep_info['REDUCE_NM'],
                        percent: penPayRate,
                        penApplyDtm: st_date.toISOString().split('T')[0]+' ~ '+ed_date.toISOString().split('T')[0],
                        penExpiDtm: rep_info['RCGT_EDA_DT'],
                        rem_amount: rem_amount,
                        item_data:  JSON.parse(data['data']['recipientPurchaseRecord'])
                    },
                    dataType: 'json'
                })
                .done(function(result) {
                alert(data['message']);
                    window.location.reload(true);
                })
                .fail(function($xhr) {
                    var data = $xhr.responseJSON;
                    alert(data && data.message);
                });
                
                btn_submit.disabled = false;
            },
            error: function (jqXhr, textStatus, errorMessage) {
                var errMSG = typeof(jqXhr['responseJSON']) == "undefined"? "수급자명 / 장기요양인정번호 확인 후, 조회하시기 바랍니다.":jqXhr['responseJSON']['message'];
                alert(errMSG);
                btn_submit.disabled = false;
                return false;
            }
          });
        }
    })

    function buildTable(data) {
        $("#table_result").empty();
        var table = document.getElementById('table_result');
        
        if(data.length == 0){
            var row = `<tr>
                            <td  colspan="5" style="padding: 8% 0%; border-left-style:none; border-right-style:none;">
                            조회된 수급자 정보가 없습니다.
                            </td>
                        </tr>`;
            table.innerHTML += row;
        } else {
            for (var i=0; i < data.length; i++) {
                var disabled = data[i]['status'] == 'R' ? '<td colspan="1"><button disabled id="btn_already">등록완료</button></td>' : '<td colspan="1"><button class="btn_write">등록하기</button></td>'; // U : unregister / R : register
                var dtm = data[i]['ORD_STATUS'] == '판매'? data[i]['ORD_STR_DTM']: data[i]['ORD_STR_DTM']+'~</br>'+data[i]['ORD_END_DTM'];
                // var update = data[i]['updated_at']?data[i]['updated_at'].split(' ')[0]:data[i]['regdt'].split(' ')[0];
                var update = data[i]['regdt'].split(' ')[0];
                var row = `<tr>
                            <td colspan="1">${i+1}</td>
                            <td colspan="1">${data[i]['recipient_name'].substr(0,1)+'*'+data[i]['recipient_name'].substr(2,1)}</td>
                            <input type = "hidden" name="penNm" class = "penNm" value="${data[i]['recipient_name']}" />
                            <td colspan="1"><a class = "search_rep_info" style = "text-decoration-line: underline;">${'L'+data[i]['recipient_num'].substr(0,5)+'*****'}</a></br>${'계약가능금액 : '+makeComma(data[i]['rem_amount'])+'원'}</td>
                            <input type = "hidden" name="penLtmNum" class = "penLtmNum" value="${data[i]['recipient_num']}" />`+
                            disabled+
                            `<td colspan="1" style="border-right-style:none;">${update}</td>
                        </tr>`;
                table.innerHTML += row;
            }
        }        
    }

    function setDate(str_date){ return str_date.substr(0,4)+'-'+str_date.substr(4,2)+'-'+str_date.substr(6,2);}

    function makeComma(str) {str = String(str);return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');}

    $.extend({
        redirectPost: function (location, args) {
            var form = $('<form></form>');
            form.attr("method", "post");
            form.attr("action", location);
            
            var key_list = Object.keys(args);
            var value_list = Object.values(args);

            for(var i = 0; i < key_list.length; i++){
                var field = $('<input></input>');
                field.attr("type", "hidden");
                field.attr("name", key_list[i]);
                field.attr("value", value_list[i]);

                form.append(field);
            }

            // 위에서 생성된 폼을 제출 한다
            $(form).appendTo('body').submit();
        }
    });
</script>

<?php include_once("./_tail.php"); ?>
