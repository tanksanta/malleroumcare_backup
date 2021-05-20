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

<?php
    //관리사업소
    function get_customer($mb_manager){
        if($mb_manager == "all"){
            $sql = "select count(*) as cnt from g5_member where mb_manager in ('jhc','hdy','thkc_jyb','jss','smkwon','kkt7740')";
        }else{
            $sql = "select count(*) as cnt from g5_member where mb_manager = '".$mb_manager."'";
        }
        $result = sql_fetch($sql);
        echo $result['cnt'];
    }
    //관리사업소 판매
    function get_customer_complete($mb_manager){
        if($mb_manager == "all"){
            $sql = "
            select count(*) AS cnt from (
                select count(A.mb_id) from g5_member A
                INNER JOIN g5_shop_order B ON A.mb_id = B.mb_id
                where mb_manager in ('jhc','hdy','thkc_jyb','jss','smkwon','kkt7740')"
                ." GROUP BY A.mb_id ".
            ") t";
        }else{
            $sql = "
            select count(*) AS cnt from (
                select count(A.mb_id) from g5_member A
                INNER JOIN g5_shop_order B ON A.mb_id = B.mb_id
                where A.mb_manager = '".$mb_manager
                ."' GROUP BY A.mb_id ".
            ") t";
        }
        $result = sql_fetch($sql);
        echo $result['cnt'];
    }
    //신규사업소
    function get_customer_new($mb_manager,$start_day,$last_day){

        if($mb_manager == "all"){
            $sql = "select count(*) as cnt from g5_member where mb_manager in ('jhc','hdy','thkc_jyb','jss','smkwon','kkt7740')"
            ." and ( mb_datetime between '$start_day 00:00:00' and '$last_day 23:59:59' )";
        }
        else{
            $sql = "select count(*) as cnt from g5_member where mb_manager = '".$mb_manager."'"
            ." and ( mb_datetime between '$start_day 00:00:00' and '$last_day 23:59:59' )";
        }
        $result = sql_fetch($sql);
        echo $result['cnt'];
    }


?>
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
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=최재훈"><?php echo get_customer('jhc'); ?></a></td>
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=윤희동"><?php echo get_customer('hdy'); ?></a></td>
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=정연부"><?php echo get_customer('thkc_jyb'); ?></a></td>
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=송준석"><?php echo get_customer('jss'); ?></a></td>
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=권성민"><?php echo get_customer('smkwon'); ?></a></td>
			<td><a href="<?=G5_URL?>/adm/member_list.php?sfl=mb_manager&stx=권준혁"><?php echo get_customer('kkt7740'); ?></a></td>
			<td><?php echo get_customer('all'); ?></td>
		</tr>
		<tr class="bg_gray">
			<td>구매(주문완료)</td>
			<td>관리사업소</td>
			<td><?php echo get_customer_complete('jhc'); ?></td>
			<td><?php echo get_customer_complete('hdy'); ?></td>
			<td><?php echo get_customer_complete('thkc_jyb'); ?></td>
			<td><?php echo get_customer_complete('jss'); ?></td>
			<td><?php echo get_customer_complete('smkwon'); ?></td>
			<td><?php echo get_customer_complete('kkt7740'); ?></td>
			<td><?php echo get_customer_complete('all'); ?></td>
		</tr>
		<!-- <tr class="bg_gray">
			<td>수급자수</td>
			<td>관리사업소 수급자</td>
			<td>131</td>
			<td>122</td>
			<td>233</td>
			<td>21</td>
			<td>322</td>
			<td>133</td>
			<td>1022</td>
		</tr> -->
        <?php 
        $today_date = date('Y-m-d'); 
        $day_of_the_week = date('w');
        $day_of_the_week=$day_of_the_week-1;
        $start_day=strtotime($date." -".$day_of_the_week."days");
        $last_day = strtotime("Now");
        for($i =0; $i <999999 ; $i ++){ 
            if($i){
                $last_day = $start_day;
                $start_day = strtotime("-1 week", $start_day);
                $last_day = strtotime("-1 day", $last_day);
            }
        ?>
		<tr>
			<td><?php echo date("Y-m-d",$start_day)."~".date("Y-m-d",$last_day)?></td>
			<td>신규사업소</td>
			<td><?php echo get_customer_new('jhc',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('hdy',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('thkc_jyb',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('jss',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('smkwon',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('kkt7740',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
			<td><?php echo get_customer_new('all',date("Y-m-d",$start_day),date("Y-m-d",$last_day)); ?></td>
		</tr>
        <?php 
            if(date("Y-m-d",$start_day) == "2021-05-17")break;
        } ?>
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
