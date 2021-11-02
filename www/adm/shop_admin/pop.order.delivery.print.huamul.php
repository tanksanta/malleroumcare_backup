<link rel="stylesheet" href="<?php echo G5_ADMIN_URL; ?>/css/samhwa_admin_common.css?v=<?php echo time(); ?>">
<div id="wrap">

<style>
.pbreak {page-break-after: always;}
#pbreak {page-break-after: always;}


/* 기본 정보 테이블 스타일 */
table.info-table-style {border-collapse:collapse; border-top:1px solid #aaa; border-right:1px solid #dadada;}
table.info-table-style .its-section {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 5px 8px 5px; text-align:center; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-section-bg {border-left:1px solid #dadada; border-bottom:1px solid #dadada; height:30px; padding:0px 5px 0px 5px; background:url('/admin/skin/default/images/common/th_bg_lightblue.gif') repeat-x; font-weight:normal;}
table.info-table-style .its-th {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 15px; text-align:left; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 15px; line-height:180%; letter-spacing:0px;}
table.info-table-style .its-th-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 0; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-th-align-dashed {border-left:1px solid #dadada; border-bottom:1px dashed #dadada; padding:8px 0px 8px 0; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-th-sub-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:8px 0px 8px 0; background-color:#f9f9f9; font-weight:normal;}
table.info-table-style .its-td-align {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:5px 0 5px 0; line-height:180%; letter-spacing:0px;}
table.info-table-style textarea {background-color:#f0f0f0;}
table.info-table-style textarea.input-box-default-text {color:#a5a5a5 !important}
table.info-table-style .its-th-align-package {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:0px 0px 0px 0; background-color:#f1f1f1; font-weight:normal;}
table.info-table-style .its-td-align-package {border-left:1px solid #dadada; border-bottom:1px solid #dadada; padding:0px 0 0px 0; line-height:180%; letter-spacing:0px;}
</style>

<form name="frm_shipping_region" method="post" action="../order_process/shipping?seq=2018062710450017551&international=domestic" target="actionFrame">

<div class="pbreak pd20" id="pbreak" style="border:solid 5px #CCC; width:800px;">
<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="7%" />
		<col width="32%" />
		<col width="7%" />
		<col width="49%" />
	</colgroup>
	<tbody>
    <?php if ( $delivery['type'] == 'gdhuamul' ) { ?>
	<tr>
		<th class="its-th-align center" height="70">도착지점</th>
		<td colspan="4" class="its-td fx60 blue bold center">
        <?php echo $od['od_delivery_text']; ?>
		</td>
	</tr>
    <?php } ?>
	<tr>
		<th class="its-th-align center" height="70">화물</th>
		<td class="its-td fx50 blue bold center"><?php echo $delivery['name']; ?></td>
		<th class="its-th-align center">지불<br />방법</th>
		<td class="its-td fx50 blue bold center"><?php echo $delivery['freight']; ?></td> 
	</tr>
	</tbody>
</table>
	
<div class="hx20"></div>
	
<table class="info-table-style" style="width:100%">
	<colgroup>
		<col width="10%" />
		<col width="10%" />
		<col width="80%" />
	</colgroup>
	<tbody>
	<tr>
		<th class="its-th-align center" height="90">수신<br />(담당자)</th>
		<td colspan="4" class="its-td fx60 blue bold center" style="line-height:80px;"><?php echo $od['od_b_name']; ?>
 

	</tr>
	<?php if($od['od_b_addr1']) { ?>
	<tr>
		<th class="its-th-align center" height="70">받는분<br />주소</th>
		<td colspan="4" class="its-td fx50 blue bold"  style="line-height:70px;">
        <?php echo $od['od_b_addr1']; ?> <?php echo $od['od_b_addr2']; ?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th rowspan="2" class="its-th-align center">연락처</th>
		<td class="its-th-align center" height="70">일반전화</td><td colspan="3" class="its-td fx50 blue bold center"><?php echo $od['od_b_tel']; ?></td>
	</tr>
	<tr>
		<td  class="its-th-align center" height="70">핸드폰</td><td colspan="3" class="its-td fx50 blue bold center"><?php echo $od['od_b_hp']; ?></td>
	</tr>
	<tr>
		<th class="its-th-align center">배송시<br />주의사항</th>
		<td colspan="4" class="its-td fx30 blue bold" height="100"><?php echo $od['od_memo']; ?></td>
	</tr>
	</tbody>
</table>
	
<div class="hx20"></div>
	
	<table class="info-table-style" style="width:100%">
		<colgroup>
			<col width="10%" />
			<col width="40%" />
			<col width="10%" />
			<col width="40%" />
		</colgroup>
		<tbody>
		<tr>
			<th class="its-th-align center" height="60">발신</th>
			<td class="its-td fx36 black bold center"><?php echo $od['sndCustNm']; ?></td>
			<th class="its-th-align center">담당자</th>
			<td class="its-td fx36 black bold center"><?php echo $od['chulgo_member']; ?></td>
		</tr>
		<tr>
			<th class="its-th-align center" height="60">제품</th>
			<td class="its-td fx36 black bold center" style="line-height:36px;"><?php echo $od['goodsName']; ?></td>
			<th class="its-th-align center">전화</th>
			<td class="its-td fx36 black bold center"><?php echo $od['sndHandNo']; ?></td>
		</tr>
		</tbody>
	</table>

</div>

</form>
<script type="text/javascript">
$(document).ready(function() {
	samhwaprint($('html').html());
});
</script>