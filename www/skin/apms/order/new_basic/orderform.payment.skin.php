<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!-- <?php if (!$default['de_card_point']) { ?>
	<div class="well" id="sod_frm_pt_alert">
		<strong>무통장입금</strong> 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.
	</div>
<?php } ?> -->

<style>
	#typereceipt2_view {
		display:none;
	}
	#typereceipt1_view {
		display:none;
	}
	#typereceipt2_view {

	}
	#typereceipt1_view ul,
	#typereceipt2_view ul {
	}
	#typereceipt1_view ul li,
	#typereceipt2_view ul li {
	}
	#typereceipt1_view input[readonly], 
	#typereceipt1_view input[readonly="readonly"] {
		color:#909090;
	}
</style>

<script>
$(function() {
	$("input[name='od_settle_case']").change(function(){
		var val = $("input[name='od_settle_case']:checked").val();

		switch(val){
			case "월 마감 정산" :
				$("#settle_bank").hide();
				break;
		}
	});

	function no_login() {
		<?php if ( !$member['mb_id'] ) { ?>
		//$('.typereceipt-form').hide();
		//$('#typereceipt1_view').hide();
		//$('#typereceipt2_view').hide();
		$('#typereceipt1').hide();
		$('#typereceipt1_label').hide();
		<?php } ?>
	}

	$('#typereceipt2').click(function() {
		if ( $(this).is(':checked') ) {
			$('#typereceipt2_view').show();
			$('#typereceipt1_view').hide();
		}
		no_login();
	});
	$('#typereceipt1').click(function() {
		if ( $(this).is(':checked') ) {
			$('#typereceipt1_view').show();
			$('#typereceipt2_view').hide();
		}
		no_login();
	});
	$('#typereceipt0').click(function() {
		if ( $(this).is(':checked') ) {
			$('#typereceipt1_view').hide();
			$('#typereceipt2_view').hide();
		}
		// no_login();
	});

	$('.typereceipt_cuse').click(function() {
		var val = $(this).val();

		if ( val == 1 ) {
			$('.personallay').show();
			$('.businesslay').hide();
		}else{
			$('.personallay').hide();
			$('.businesslay').show();
		}
		no_login();
	});

	$('input[name="od_settle_case"]').click(function() {
		var val = $(this).val();

		if (val === '신용카드') {
			$('#typereceipt0').click();
			$('.typereceipt-form').hide();
			$('#typereceipt1_view').hide();
			$('#typereceipt2_view').hide();
		}else{
			$('.typereceipt-form').show();
		}
		no_login();
	});

	// 사용자 화면에서 비회원일때와 회원인경우 신용카드를 선택한 경우 매출증빙을 선택 못하도록 안보이도록 설정
	no_login();
	$('#typereceipt1').click();
	$("#typereceipt1_view").show();

	$('input[name="typereceipt_bnum"], input[name="p_typereceipt_bnum"]').on('keyup', function(){
		var num = $(this).val();
		num.trim();
		this.value = auto_saup_hypen(num) ;
	});
});
</script>