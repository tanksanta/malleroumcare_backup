<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$popular_skin_url.'/style.css">', 0);
?>

            <!-- 인기검색어 시작 { -->
            <div class="hash_tagWrap"> <!--해시태그-->
                <?php 
                    if( isset($list) && is_array($list) ) {
                        for ($i=0; $i<count($list); $i++) { 
                ?>
                <p class="ht"><a href="<?=G5_SHOP_URL;?>/search.php?sfl=wr_subject&amp;sop=and&amp;stx=<?php echo urlencode($list[$i]['pp_word']) ?>">#<?=get_text($list[$i]['pp_word']); ?></a></p>            
                <?php
                        }   //end for
                    }   //end if
                ?>
            </div>
            <!-- } 인기검색어 끝 -->