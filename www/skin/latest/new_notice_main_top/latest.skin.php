<?php
if (!defined('_GNUBOARD_')) exit;
add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
?>

<div class="main_latest">
	<div class="main_latest_div">

		<div class="li01">
			<div class="li01_1">
                <img src="<?=G5_IMG_URL;?>/new_main_eroum/thkc_ico_bell.svg" alt="공지사항" style="width:16px; display: revert;">&nbsp;
				<a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $bo_table ?>"><?php echo $bo_subject; ?></a>
			</div>

			<div class="li01_txt">
				<div class="block">
					<ul id="ticker">			
                    <?php for ($i=0; $i<count($list); $i++) {  ?>
                    <li><a href="<?php echo $list[$i]['href']?>" title="<?php echo $list[$i]['subject']?>"><?php if($list[$i]['is_notice']) { ?><?="<strong>".$list[$i]['subject']."</strong>";?><?php } else { ?><?=$list[$i]['subject'];?></a></li>
                    <?php }  } ?>
                    <?php if (count($list) == 0) { ?><li>등록된 게시물이 없습니다.</li><?php } ?>
					</ul>
				</div>
			</div>
			<div class="li01_2 navi">      
				<img class="prev" src="<?=G5_IMG_URL;?>/new_common/thkc_btn_arrow_down.svg" style="cursor:pointer; display: revert;">
				<img class="next" src="<?=G5_IMG_URL;?>/new_common/thkc_btn_arrow_above.svg" style="cursor:pointer; display: revert; margin-left: 8px;">         
			</div>
		</div>
	</div>
</div>

<script>
jQuery(function($) {

    var ticker = function() {
        timer = setTimeout(function() {
            $('#ticker li:first').animate( {marginTop: '-30px'}, 400, function() {
                $(this).detach().appendTo('ul#ticker').removeAttr('style');
            });
            
            ticker();

        }, 12000);
    };


    $(document).on('click','.prev',function() {
        $('#ticker li:last').hide().prependTo($('#ticker')).slideDown();
        clearTimeout(timer);
        ticker();
        
        if($('.pause').text() == 'Unpause') {
            tickerUnpause();
        };
    });


    $(document).on('click','.next',function() {
        $('#ticker li:first').animate( {marginTop: '-30px'}, 400, function() {
            $(this).detach().appendTo('ul#ticker').removeAttr('style');
        });

        clearTimeout(timer);
        ticker();

        if($('.pause').text() == 'Unpause') {
            tickerUnpause();
        };
    });


    var tickerUnpause = function() {
        $('.pause').text('Pause');
    };


    var tickerpause = function() {
        $('.pause').click(function() {
            $this = $(this);
            if($this.text() == 'Pause') {
                $this.text('Unpause');
                clearTimeout(timer);
            } else {
                tickerUnpause();
            }
        });
    };

    tickerpause();
    
    var tickerover = function(event) {
        $('#ticker').mouseover(function() {
            clearTimeout(timer);
        });

        $('#ticker').mouseout(function() {
            ticker();
        });
    };

    tickerover();
    ticker();

});
</script>
