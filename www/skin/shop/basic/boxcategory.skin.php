<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>
<style type="text/css" media="screen">

#gnb {margin-bottom:15px;background:#333;border:0px solid #e8e8e8;border-top:0;width: 200px;height: 500px;display: inline-block}
#gnb h2 {position:absolute;font-size:0;line-height:0;overflow:hidden}
.gnb_1dli {word-wrap:break-word}
.gnb_1dli_on {color:#fff;text-decoration:none}
.gnb_1da {display:block;padding:0 20px;line-height:48px;color:#FFF;text-decoration:none;font-size:14px}
.gnb_1da i {position:absolute;right:0;top:0;display:inline-block;color:#c4c4c4;padding:15px;font-size:1.45em}

.gnb_1dam {background:url('img/gnb_bg.png') center right no-repeat}
.gnb_1dli_on .gnb_1da {    background: #fafaf9;
    color: #333;font-weight:bold;text-decoration:none}
.gnb_1dli_on .gnb_1da:after {position:absolute;left:-1px;top:0;content:"";background:#3a8afd;width:0px;height:100%}
#gnb_1dul{background-color: #333;position: relative}
.gnb_1dli_on .gnb_1dam {text-decoration:none}
.gnb_2dul {display:none;z-index:1000;position:absolute;border:0px solid #e8e8e8;padding:10px;height: 500px;left:0;top:0;bottom: 0;}
.gnb_1dli_over .gnb_2dul, .gnb_1dli_over2 .gnb_2dul {display:inline-block;top:0;left:200px;width:200px;background:#fff}
.gnb_2dli a{color:#333;}
.gnb_2da {}
.gnb_1dli_over .gnb_2da {display:block;padding:5px 10px;line-height:20px;font-size:1.083em}
.gnb_2da:focus, .gnb_2da:hover {text-decoration:none;color:#3a8afd}
</style>
<div class="menu_wrap bgsec">
	<!-- 쇼핑몰 카테고리 시작 { -->
<nav id="gnb">
    <!--<button type="button" id="menu_open"><i class="fa fa-bars" aria-hidden="true"></i> 카테고리</button>-->
    <ul id="gnb_1dul">
        <?php
        // 1단계 분류 판매 가능한 것만
        $hsql = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where length(ca_id) = '2' and ca_use = '1' order by ca_order, ca_id ";
        $hresult = sql_query($hsql);
        $gnb_zindex = 999; // gnb_1dli z-index 값 설정용
        for ($i=0; $row=sql_fetch_array($hresult); $i++)
        {
            $gnb_zindex -= 1; // html 구조에서 앞선 gnb_1dli 에 더 높은 z-index 값 부여
            // 2단계 분류 판매 가능한 것만
            $sql2 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where LENGTH(ca_id) = '4' and SUBSTRING(ca_id,1,2) = '{$row['ca_id']}' and ca_use = '1' order by ca_order, ca_id ";
            $result2 = sql_query($sql2);
            $count = sql_num_rows($result2);
        ?>
        <li class="gnb_1dli" style="z-index:<?php echo $gnb_zindex; ?>">
            <a href="<?php echo G5_SHOP_URL.'/list.php?ca_id='.$row['ca_id']; ?>" class="gnb_1da<?php if ($count) echo ' gnb_1dam'; ?>"><?php echo $row['ca_name']; ?></a>
            <?php
            for ($j=0; $row2=sql_fetch_array($result2); $j++)
            {
            if ($j==0) echo '<ul class="gnb_2dul" style="z-index:'.$gnb_zindex.'">';
            ?>
                <li class="gnb_2dli"><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $row2['ca_id']; ?>" class="gnb_2da"><?php echo $row2['ca_name']; ?></a></li>
            <?php }
            if ($j>0) echo '</ul>';
            ?>
        </li>
        <?php } ?>
    </ul>
</nav>
<!-- } 쇼핑몰 카테고리 끝 -->
</div>
<div class="notice">
	<a href="#"> <img src="<?php echo THEMA_URL; ?>/assets/img/icon_notice.png"> <p>공지사항</p></a>
	<div class="new">2</div>
</div>


