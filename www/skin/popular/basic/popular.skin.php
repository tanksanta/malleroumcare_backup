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
						if($list[$i]['type']){//검색태그 사용 시
							if($list[$i]['type'] == 1){//검색으로 ?>					
							<p class="ht"><a href="javascript:search_tag('<?=G5_SHOP_URL;?>/search.php?sfl=wr_subject&amp;sop=and&amp;stx=<?php echo urlencode($list[$i]['pp_word'])?>','<?=$list[$i]['st_id']?>');">#<?=get_text($list[$i]['pp_word']); ?></a></p> 
							<?php }else{//링크로?>
							<p class="ht"><a href="javascript:search_tag('https://www.<?=$list[$i]['link']?>','<?=$list[$i]['st_id']?>')">#<?=get_text($list[$i]['pp_word']); ?></a></p> 
							<?php }?>
						<?php }else{?>
						<p class="ht"><a href="<?=G5_SHOP_URL;?>/search.php?sfl=wr_subject&amp;sop=and&amp;stx=<?php echo urlencode($list[$i]['pp_word']) ?>">#<?=get_text($list[$i]['pp_word']); ?></a></p>            
						<?php }
                        }   //end for
                    }   //end if
                ?>
            </div>
			<?php if($list[0]['type'] != ""){?>
			<script>
				function search_tag(link,st_id){
					$.ajax({
					method: "POST",
					url: "/shop/ajax.search_tag_count.php",
					async:false,
					data: {
						'st_id': st_id
					},
					}).done(function (data) {
						console.log(data);
						if (data.message == 'OK') {
							location.href=link;
						} else {
							alert(data.message);			
						}
					});
					
				}
			</script>
			<?php }?>
            <!-- } 인기검색어 끝 -->
