<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/samhwa_admin_common.css?v=<?php echo time(); ?>">
<div id="wrap">

<style>
.pbreak {page-break-after: always;}
#pbreak {page-break-after: always;}
</style>



<div class="pbreak" id="pbreak" style="border:solid 5px #CCC;">

<table width="100%"><tr><td colspan="7"><div style="text-align:center;font-size:40px;letter-spacing:-1px;font-weight:bold;padding-top:40px;margin-bottom: 20px;">
	차량 및 오토바이 발송</div></td></tr></table>

<div class="hx50"></div>

<table style="width:95%" class="search-form-table">
	<tr>
		<td class="pdl20 fx26 bold" style="width:200px;">배송 및 운임 :</td>
		<td class="fx26 blue bold">			택배-선불

</td>
		<td class="right"><img src="<?php echo G5_ADMIN_URL; ?>/shop_admin/img/print_logo.gif"></td>
	</tr>
</table>

<div class="hx40"></div>

<div class="fx18 bold pdl20 hx40">보내는 사람</div>
<table style="width:95%" class="lh25">
	<tr>
		<td class="pdl20 fx16 bold" style="width:120px;">상&nbsp;&nbsp;&nbsp;&nbsp;호 :</td>
		<td class="fx16 bold pd2">삼화에스앤디㈜</td>
	</tr>
	<tr>
		<td class="pdl20 fx16 bold" style="width:120px;">성&nbsp;&nbsp;&nbsp;&nbsp;명 :</td>
		<td class="fx16 blue bold pd2"><?php echo $od['sndCustNm']; ?></td>
	</tr>
	<tr>
		<td class="pdl20 fx16 bold" style="width:120px;">연&nbsp;락&nbsp;처 :</td>
		<td class="fx16 blue bold pd2">핸드폰 : <?php echo $od['sndTelNo']; ?><br>
		일반전화 : <?php echo $od['sndHandNo']; ?></td>
	</tr>
	<tr>
		<td class="pdl20 fx16 bold" style="width:120px;">제&nbsp;품&nbsp;명 :</td>
		<td class="fx16 blue bold pd2"><?php echo $od['goodsName']; ?> </td>
	</tr>
	<tr>
		<td class="pdl20 fx16 bold" style="width:120px;">배송기사 :</td>
		<td class="fx16 blue bold pd2">&nbsp;</td>
	</tr>
</table>

<div class="hx30"></div>

<div class="fx18 bold pdl100 hx40">받는 사람</div>

<table style="width:95%" class="lh25">
    <?php if ( $receipt_member['mb_giup_bname'] ) { ?>
	<tr>
		<td class="pdl20 fx16 bold pdl100"  style="width:200px;">상&nbsp;&nbsp;&nbsp;&nbsp;호 :</td>
		<td class="fx20 blue bold pd5">			<font size="3"><b><?php echo $receipt_member['mb_giup_bname']; ?></b></font>
        </td>
	</tr>
    <?php } ?>
	<tr>
		<td class="pdl20 fx16 bold pdl100" >성&nbsp;&nbsp;&nbsp;&nbsp;명 :</td>
		<td class="fx20 blue bold pd5"><?php echo $od['od_b_name']; ?></td>
	</tr>
	<tr>
		<td valign="top" class="pdl20 fx16 bold pdl100" >주&nbsp;&nbsp;&nbsp;&nbsp;소 :</td>
		<td class="fx20 blue bold pd5"><?php echo $od['od_b_addr1']; ?> <?php echo $od['od_b_addr2']; ?></td>
	</tr>
	<tr>
		<td valign="top" class="pdl20 fx16 bold pdl100" >연&nbsp;락&nbsp;처 :</td>
		<td class="fx20 blue bold pd5"><?php echo $od['od_delivery_text']; ?><br><!--핸드폰 : <?php echo $od['od_b_hp']; ?><br>
		일반전화 : <?php echo $od['od_b_tel']; ?>-->
        </td>
	</tr>
	<tr>
		<td class="pdl20 fx16 bold pdl100">요청사항<span style="font-size:4px;"> </span>:</td>
		<td class="fx20 blue bold pd5"><?php echo $od['od_memo']; ?></td>
	</tr>
</table>

<table style="width:95%" class="lh25">
	<tr>
		<td class="pdl20 fx16 bold pdl100" style="width:260px;">배송요청시간 :</td>
		<td class="fx24 red bold pd5">
				<?php echo $od['od_ex_date']; ?>
		</td>
	</tr>
</table>


<div class="hx30 center fx16 pdt50 pdb10 darkgray bold">안전하고 빠른 배송 부탁드리겠습니다~</div>

<div style="padding:20px;"></div>

</div>
<script type="text/javascript">
$(document).ready(function() {
	samhwaprint($('html').html());
});
</script>