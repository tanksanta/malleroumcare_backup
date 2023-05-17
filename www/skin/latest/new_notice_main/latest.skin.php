<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

    <?php for ($i=0; $i<count($list); $i++) {  ?>

        <ul class="noti_con">
            <li class="noti_text">· 

                <?php
                
                    if ($list[$i]['icon_new']) echo "<img src=\"/thema/eroumcare/assets/img/boardNew.png\" >";

                    echo "<a href=\"".$list[$i]['href']."\">";

                    if ($list[$i]['is_notice'])
                        echo "<strong>".$list[$i]['subject']."</strong>";
                    else
                        echo $list[$i]['subject'];

                    echo "</a>";

                    if ($list[$i]['comment_cnt'])  echo "<span class=\"lt_cmt\">+ ".$list[$i]['comment_cnt']."</span>";
                    
                ?>

            </li>
            <li class="noti_data"><?=mb_substr($list[$i]['wr_datetime'],0,10);?></li>
        </ul>
        <hr>



    <?php }  ?>

    <?php if(count($list) == 0) { //게시물이 없을 때  ?>
        <ul class="noti_con">
            <li class="noti_con">게시물이 없습니다.</li>
            <li class="noti_data">0000.00.00</li>
        </ul>
        <hr>
    <?php }  ?>