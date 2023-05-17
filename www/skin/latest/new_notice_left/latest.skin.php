<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

    <ul class="list">

    <?php for ($i=0; $i<count($list); $i++) {  ?>
        <li>
            <?php
            
                if ($list[$i]['icon_new']) echo "<img src=\"/thema/eroumcare/assets/img/boardNew.png\"  >";

                echo "<img src=\"".G5_IMG_URL."/new_common/thkc_ico_notice.svg\"><a href=\"".$list[$i]['href']."\">";

                if ($list[$i]['is_notice'])
                    echo "<strong>".$list[$i]['subject']."</strong>";
                else
                    echo $list[$i]['subject'];

                echo "</a>";

                if ($list[$i]['comment_cnt'])  echo "<span class=\"lt_cmt\">+ ".$list[$i]['comment_cnt']."</span>";
            ?>

        </li>
    <?php }  ?>
    
    <?php if (count($list) == 0) { //게시물이 없을 때  ?>
        <li class="empty_li">게시물이 없습니다.</li>
    <?php }  ?>

    </ul>

