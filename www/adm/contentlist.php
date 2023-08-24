<?php
$sub_menu = '300600';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

if( !isset($g5['content_table']) ){
    die('<meta charset="utf-8">/data/dbconfig.php 파일에 <strong>$g5[\'content_table\'] = G5_TABLE_PREFIX.\'content\';</strong> 를 추가해 주세요.');
}
//내용(컨텐츠)정보 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['content_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_content_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_content_table']} RENAME TO `{$g5['content_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['content_table']}` (
                      `co_id` varchar(20) NOT NULL DEFAULT '',
                      `co_html` tinyint(4) NOT NULL DEFAULT '0',
                      `co_subject` varchar(255) NOT NULL DEFAULT '',
                      `co_content` longtext NOT NULL,
                      `co_hit` int(11) NOT NULL DEFAULT '0',
                      `co_include_head` varchar(255) NOT NULL,
                      `co_include_tail` varchar(255) NOT NULL,
                      PRIMARY KEY (`co_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);

        // 내용관리 생성
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'company', co_html = '1', co_subject = '회사소개', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b></p>' ", false );
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'privacy', co_html = '1', co_subject = '개인정보 처리방침', co_content= '<p align=center><b>개인정보 처리방침에 대한 내용을 입력하십시오.</b></p>' ", false );
        sql_query(" insert into `{$g5['content_table']}` set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.</b></p>' ", false );
    }
}

$g5['title'] = '내용관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g5['content_table']} where co_id not like 'privacy_%' and co_id not like 'provision_%'";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>
<style>
	.popup_box2 {
		display: none;
		position: fixed;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		z-index: 9999;
		background: rgba(0, 0, 0, 0.5);		
	}
	.popup_box_con {
		padding:20px;
		position: relative;
		background: #ffffff;
		z-index: 99999;
	}

</style>
<div class="local_ov01 local_ov">
    <?php if ($page > 1) {?><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">처음으로</a><?php } ?>
    <span class="btn_ov01"><span class="ov_txt">전체 내용</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<div class="btn_fixed_top">
    <a href="./contentform.php" class="btn btn_01">내용 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_id"><?php echo $row['co_id']; ?></td>
        <td class="td_left"><?php echo htmlspecialchars2($row['co_subject']); ?></td>
        <td class="td_mng td_mng_l">
		<?php if(strpos($row['co_id'],"privacy") === false && strpos($row['co_id'],"provision") === false){?>
            <a href="./contentform.php?w=u&amp;co_id=<?php echo $row['co_id']; ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>수정</a>
            <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=<?php echo $row['co_id']; ?>" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span> 보기</a>
            <a href="./contentformupdate.php?w=d&amp;co_id=<?php echo $row['co_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span>삭제</a>
		<?php }else{?>			
            <a href="javascript:show_hist('<?=$row['co_id']?>','<?=$row['co_subject']?>');" class="btn btn_02"><span class="sound_only"><?php echo htmlspecialchars2($row['co_subject']); ?> </span> 보기</a>            
		<?php }?>
        </td>
    </tr>
    <?php
    }
    if ($i == 0) {
        echo '<tr><td colspan="3" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>
<div id="popup_box2" class="popup_box2 list_box">
    
	<div id="" class="popup_box_con" style="height:700px;margin-top:-350px;width:50%;left:50%;top:50%;margin-left:-25%;">
	
	<header>
        <div style="width:100%;border-bottom:1px solid #a5a5a5;font-weight:bold;font-size:18px;padding:0px 0px 10px 0px;margin-bottom:5px;"><span id="co_id"></span>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<span id="co_subject"></span></div>
    </header>

    <div id="ctt_con" style="height:580px;overflow:auto; text-align:center;">
        
    </div>
	<div style="text-align:center;top:0px;width:100%;border-top:1px solid #a5a5a5;padding:13px 0px 0px 0px;">
		<button type="button" class="btn btn_01 btn_close" onClick="add_content();">추가</button>
		<button type="button" class="btn btn-black btn_close">닫기</button>
		<input type="hidden" id="co_id2" value="">
		<input type="hidden" id="co_subject2" value="">
	</div>
	</div>
</div>
<script>
	$(function(){
		<?php if($_REQUEST["co_id"] != ""){?>
			//show_hist('<?=$_REQUEST["co_id"]?>','');
		<?php }?>
	});
	function show_hist(id,subject){
		$("#ctt_con").html("");
		$("#co_id").text(id);
		$("#co_id2").val(id);
		$("#co_subject2").val(subject);
		$("#co_subject").text(subject);
		$.ajax({
		  method: 'POST',
		  url: './ajax.content_hist.php',
		  data: {
			co_id: id
		  },
		}).done(function (data) {
		  // return false;
		  if (data.msg != "") {
			alert(data.msg);
		  }
		  if (data.result === 'success') {
			if(data.co_subject != ""){
				$("#co_subject2").val(data.co_subject);
				$("#co_subject").text(data.co_subject);
			}
			var cont_html = "<table style='border:1px solid #ddd'>";
			cont_html += "<thead>";
			cont_html += "<tr>";
			cont_html += "	<th scope='col'>ID</th>";
			cont_html += "	<th scope='col'>제목</th>";
			cont_html += "	<th scope='col'>관리</th>";
			cont_html += "</tr>";
			cont_html += "</thead>";
			var button = "";
			for(var i = 0; i < data.rows.length; i++){
				if(i == 0 && data.rows.length > 1){
					button = "<a href='./contentform.php?w=u&amp;co_id="+data.rows[i]["co_id"]+"' class='btn btn_03 btn-sm'>수정</a> <a href='<?php echo G5_BBS_URL; ?>/content.php?co_id="+data.rows[i]["co_id"]+"' target='_blank' class='btn btn_02 btn-sm'>보기</a> <a href='./contentformupdate.php?w=d&amp;co_id="+data.rows[i]["co_id"]+"' onclick='return delete_confirm(this);'  class='btn btn_02 btn-sm'>삭제</a>";
				}else{
					button = "<a href='./contentform.php?w=u&amp;co_id="+data.rows[i]["co_id"]+"' class='btn btn_03 btn-sm'>수정</a> <a href='<?php echo G5_BBS_URL; ?>/content.php?co_id="+data.rows[i]["co_id"]+"' target='_blank' class='btn btn_02 btn-sm'>보기</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				cont_html += "<tr><td class='td_left'>"+data.rows[i]["co_id"]+"</td><td class='td_left'>"+ data.rows[i]["co_subject"]+"</td><td class='td_mng td_mng_l'>"+button+"</td></tr>";
			}
			cont_html += "</table>";
			$("#ctt_con").html(cont_html);
		  }
		});
		
		$('body').addClass('modal-open');		
		$('#popup_box2').show();
	}
	$('.btn_close').click(function() {			
		$('body').removeClass('modal-open');
		$('#popup_box2').hide();
	});
	function add_content(){
		location.href = "./contentform.php?co_id2="+$("#co_id2").val()+"&co_subject2="+$("#co_subject2").val();
	}
</script>
<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
