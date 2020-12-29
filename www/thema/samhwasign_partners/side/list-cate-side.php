<style>
    #samhwa-list-cate {
        display:none;
    }

	@media (min-width: 991px) {
		.at-content {
			margin-left:220px;
        }                
        #samhwa-list-cate {
            width:190px;
            border-bottom:2px solid #666666;
            background-color:white;
            position:fixed;
            box-shadow: 2px 0px 15px #ddd;
        }
        #samhwa-list-cate.on {
            width:340px;
        }
        #samhwa-list-cate>h2 {
            background-color:#666666;
            padding:10px 10px;
            font-size:14px;
            color:white;
            margin:0;
        }
    }
    @media (max-width: 991px) {
        #samhwa-list-cate {
            display:none !important;
        }
    }


    #samhwa-list-cate ul {
        list-style:none;
        margin:0;
        padding:0;
    }
    #samhwa-list-cate ul.menu {
        min-height:300px;
    }
    #samhwa-list-cate ul.menu>li {
        position: relative;
        width: 140px;
        padding-left: 5px;
    }
    #samhwa-list-cate ul.menu>li .title {
        color:#999999;
        display:block;
        font-size:14px;
        line-height:40px;
        /*font-weight:bold;*/
    }
    #samhwa-list-cate ul.menu>li.on .title,
    #samhwa-list-cate ul.menu>li .title:hover {
        color:#094;
    }
    #samhwa-list-cate ul.sub {
    width: 180px;
    /*display:none;*/
    visibility: hidden;
    position: absolute;
    left: 160px;
    top: 6px;
    border-left: 1px solid #094;
    line-height: 27px;
    font-size:13px;
    padding:0px 0px 0px 15px;
}
#samhwa-list-cate ul.menu li.on ul.sub {
    /*display:block;*/
    /*visibility: visible;*/
    visibility: visible;
}
#samhwa-list-cate ul.menu li.on ul.sub>li {
    position:relative;
}
#samhwa-list-cate ul.sub a {
    color:#999;
}
#samhwa-list-cate ul.sub>li.on>a,
#samhwa-list-cate ul.sub>li:hover>a {
    color:#094;
}
#samhwa-list-cate ul.sub>li:hover .sub2 {
    display:block;
} 
#samhwa-list-cate ul.sub .sub2 {
    display: none;
    position: absolute;
    top: 0;
    left: 110px;
    box-shadow: 2px 0px 15px #a7a7a7;
    background-color: white;
    width: 100px;
    z-index: 999;
}
#samhwa-list-cate ul.sub .sub2 a {
    display:block;
    padding:5px 10px;
}
#samhwa-list-cate ul.sub .sub2 li.on a, 
#samhwa-list-cate ul.sub .sub2 a:hover {
    background-color:#f3f3f3;
}
#samhwa-list-cate ul.menu li.on ul.sub {
    display:none !important;
}
#samhwa-list-cate.on ul.menu li.on ul.sub {
    display:block !important;
}
#samhwa-list-cate .scroller-btn {
    width:190px;
}
#samhwa-list-cate .scroller-btn:after {
    display:block;
    clear:both;
    content: '';
}
#samhwa-list-cate .scroller-btn a {
    display:block;
    float:left;
    width:47%;
    text-align:center;
    border:1px solid #ececec;
    margin-left:3px;
    margin-bottom:5px;
}
#samhwa-list-cate .scroller-btn a:last-child {
    float:right;
    margin-left:0px;
    margin-right:3px;
}
</style>

<div id="samhwa-list-cate">
    <h2>Menu</h2>
    <ul class="menu">
        <?php $count_cate = 0; ?>
        <?php foreach($category as $cate) { ?>
            <li class="<?php echo (substr($ca_id, 0, strlen($cate['ca_id'])) === $cate['ca_id']) ? 'on default_on ': ''; ?>" data-id="<?php echo $cate['ca_id']; ?>">
                <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo sprintf("%02d", ++$count_cate); ?>.&nbsp;<?php echo $cate['ca_name']; ?></a>
                <?php if ( $cate['sub'] ) { ?>
                    <ul class='sub'>
                        <?php foreach($cate['sub'] as $sub) { ?>
                            <li class="<?php echo (substr($ca_id, 0, strlen($sub['ca_id'])) === $sub['ca_id']) ? 'on' : ''; ?> ">
                                <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
                                <?php if ( $sub['sub'] ) { ?>
                                    <ul class='sub2'>
                                    <?php foreach($sub['sub'] as $sub2) { ?>
                                        <li class="<?php echo $sub2['ca_id'] == $ca_id ? 'on' : ''; ?> ">
                                            <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub2['ca_id']; ?>' class='sub-title'><?php echo $sub2['ca_name']; ?></a>
                                        </li>
                                    <?php } ?>
                                    </ul>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
    <div class="scroller-btn">
        <a id="homeup"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_scroll_up.png" /></a>
        <a id="homedown"><img src="<?php echo THEMA_URL; ?>/assets/img/btn_scroll_down.png" /></a>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    //console.log(screen.width);

    var cate_el = $('#samhwa-list-cate');

    function reposition_list_cate() {
        var windowWidth = $( window ).width();
        var windowHeight = $( window ).height();
        var documentHeight = $( document ).height();
        var scrollValue = $(document).scrollTop();

        //console.log(scrollValue)
        //console.log(documentHeight);

        $(cate_el).css('left', ( windowWidth - 1200 ) / 2);
        if ( scrollValue > 331 ) {
            // console.log(documentHeight - scrollValue);
            if ( documentHeight - scrollValue < 1000) {
                $(cate_el).css('top', 'auto');
                $(cate_el).css('bottom', '700px');
            }else{
                $(cate_el).css('bottom', 'auto');
                $(cate_el).css('top', '5px');
            }
        }else{
            $(cate_el).css('bottom', 'auto');
            $(cate_el).css('top', 357 - scrollValue );
        }
    }

    reposition_list_cate();
    $(cate_el).show();

    $( window ).resize(function() {
        reposition_list_cate();
    });
    $( window ).scroll(function() {
        reposition_list_cate();
    });

    $('#samhwa-list-cate').mouseover(function() {
        $(cate_el).addClass('on')
    });
    $('#samhwa-list-cate').mouseout(function() {
        $(cate_el).removeClass('on')
    })

    $.each($('#samhwa-list-cate ul.sub'), function(item, index) {

        var theight = $(this).height();
        var ttop = $(this).offset().top;

        var parent = $(this).closest('.menu')[0];
        var pheight = $(parent).height();
        var ptop = $(parent).offset().top;

        if ( theight + ttop > pheight + ptop ) {
            var top = (theight + ttop) - (pheight + ptop);
            $(this).css('top', '-' + top + 'px');
        }
    })

    $(function () {
		$('#homeup').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});

		$('#homedown').click(function () {
			$('body,html').animate({
				scrollTop: $(document).height()
			}, 800);
			return false;
		});
	});
});
</script>