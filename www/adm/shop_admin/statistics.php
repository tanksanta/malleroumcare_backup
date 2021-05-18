<?php
$sub_menu = '500010';
include_once('./_common.php');
// auth_check($auth[$sub_menu], "r");

$g5['title'] = '운영관리통계';
include_once (G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
?>

<!-- 퍼블섹션 -->

<style type="text/css" media="screen">
	.statistics_wrap{padding:20px;}
	.statistics_wrap th,.statistics_wrap td{padding:10px;text-align: center;}
	.statistics_wrap th{background: #333;color:#fff;}
	.statistics_wrap a{font-weight:bold;color:#000;text-decoration: underline !important;}
	.statistics_wrap .bg_gray{background: #f5f5f5;}
	.statistics_wrap p{margin:20px 0;}
</style>


<section class="statistics_wrap">
	<table >
		<tr>
			<th>기준기간</th>
			<th>분류</th>
			<th>최재훈(jhc)</th>
			<th>윤희동(hdy)</th>
			<th>정연부(thkc_jyb)</th>
			<th>송준석(jss)</th>
			<th>권성민(smkwon)</th>
			<th>권준혁(kkt7740)</th>
			<th>합계</th>
		</tr>
		<tr class="bg_gray">
			<td>합계</td>
			<td>관리사업소</td>
			<td><a href="#">2</a></td>
			<td><a href="#">2</a></td>
			<td><a href="#">3</a></td>
			<td><a href="#">1</a></td>
			<td><a href="#">2</a></td>
			<td><a href="#">2</a></td>
			<td>25</td>
		</tr>
		<tr class="bg_gray">
			<td>구매(주문완료)</td>
			<td>관리사업소</td>
			<td>3</td>
			<td>1</td>
			<td>2</td>
			<td>2</td>
			<td>3</td>
			<td>1</td>
			<td>10</td>
		</tr>
		<tr class="bg_gray">
			<td>수급자수</td>
			<td>관리사업소 수급자</td>
			<td>311</td>
			<td>122</td>
			<td>233</td>
			<td>21</td>
			<td>322</td>
			<td>133</td>
			<td>1022</td>
		</tr>
		<tr>
			<td>2021-05-17 ~ 2021-05-23</td>
			<td>신규사업소</td>
			<td>3</td>
			<td>1</td>
			<td>2</td>
			<td>2</td>
			<td>3</td>
			<td>1</td>
			<td>10</td>
		</tr>
		<tr>
			<td>2021-05-10 ~ 2021-05-16</td>
			<td>신규사업소</td>
			<td>3</td>
			<td>1</td>
			<td>2</td>
			<td>2</td>
			<td>3</td>
			<td>1</td>
			<td>10</td>
		</tr>
	</table>
	<p>*기준은 매주 월요일부터 일요일까지 신규 가입된 사업소 중 담당자가 지정된 사업소만 보여집니다.<br>
	*담당자를 지정하는 일시와 상관없이 사업소 가입일 기준으로 보여집니다. <br>
	*구매(주문완료) 사업소는 신규 가입 후 1회 이상 주문한 사업소를 말합니다. <br>
	*통계는 영업팀 6명에 한하여 측정합니다. 영업팀 외 직원은 통계확인이 안됩니다.
	</p>
</section>


<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
