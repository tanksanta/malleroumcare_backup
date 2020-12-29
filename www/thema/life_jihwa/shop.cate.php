<?php

// 카테고리
$category = array();
$sql = "SELECT * FROM g5_shop_category where length(ca_id) = '2' and ca_use = '1' ORDER BY ca_order, ca_id ASC";
$res = sql_query($sql);
while( $row = sql_fetch_array($res) ) {
    $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '4' and ca_id like '{$row['ca_id']}%' and ca_use = '1' ORDER BY ca_order, ca_id ASC";
    $res2 = sql_query($sql);
    while( $row2 = sql_fetch_array($res2) ) {
        $sql = "SELECT * FROM g5_shop_category where  length(ca_id) = '6' and ca_id like '{$row2['ca_id']}%' and ca_use = '1' ORDER BY ca_order, ca_id ASC";
        $res3 = sql_query($sql);
        while( $row3 = sql_fetch_array($res3) ) {
            $row2['sub'][] = $row3;
        }
        $row['sub'][] = $row2;
    }
    $category[] = $row;
}

//print_r2($category);


// 배너
$result = sql_query("SELECT * FROM g5_shop_banner WHERE bn_device = 'both' AND ('" .G5_TIME_YMDHIS . "' between bn_begin_time and bn_end_time" . ") AND bn_position = '메인' ORDER BY bn_order ASC ");
$banners = array();
while($row = sql_fetch_array($result)) {
    $banners[] = $row;
}
?>

<?php
// if ( $is_main || $_SERVER["PHP_SELF"] == '/shop/list.php') { 
if ( $is_main ) { 
?>
<style>
    .rolling_panel { width: <?php echo count($category) * 880; ?>px; }
</style>
<div id="samhwa-cate">
    <div class="samhwa-cate-menu">
        <ul class="menu">
            <?php $count_cate = 0; ?>
            <?php foreach($category as $cate) { ?>
                <li class="<?php echo (substr($ca_id, 0, strlen($cate['ca_id'])) === $cate['ca_id']) ? 'on default_on ': ''; ?>" data-id="<?php echo $cate['ca_id']; ?>">
                    <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' class='title'><?php echo sprintf("%02d", ++$count_cate); ?>.&nbsp;<?php echo $cate['ca_name']; ?></a>
                    <?php if ( $cate['sub'] ) { ?>
                        <ul class='sub'>
                            <?php foreach($cate['sub'] as $sub) { ?>
                                <li class="<?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?> ">
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
    </div>
    <div class="samhwa-cate-banner">
        <a href="javascript:void(0)" id="banner_prev">이전</a>
        <a href="javascript:void(0)" id="banner_next" class='mobile'><img src='<?php echo THEMA_URL; ?>/assets/img/icon_arrow_next.png'/></a>
        <div class="rolling_panel">
            <ul>
                <!--
                <?php foreach($banners as $banner) { ?>
                    <li>
                        <a href="<?php echo $banner['bn_url']; ?>" <?php echo $banner['bn_new_win'] ? 'target="_blank"' : ''; ?> >
                            <div class="contents">
                                <h1><?php echo $banner['bn_title']; ?></h1>
                                <p><?php echo nl2br($banner['bn_content']); ?></p>
                            </div>
                            <img src="<?php echo G5_DATA_URL; ?>/banner/<?php echo $banner['bn_id']; ?>">
                        </a>
                    </li>
                <?php } ?>
                -->
                <?php foreach($category as $c) { ?>
                    <li id="category_<?php echo $c['ca_id']; ?>" class="<?php echo (substr($ca_id, 0, strlen($c['ca_id'])) === $c['ca_id']) ? 'default_pcon ': ''; ?>" onclick="location.href='<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $c['ca_id']; ?>';">
                        <a style="cursor:unset;">
                            <div class="contents">
                                <h1><?php echo $c['ca_title']; ?></h1>
                                <p><?php echo nl2br($c['ca_content']); ?></p>
                            </div>
                            <img src="<?php echo G5_DATA_URL; ?>/category/<?php echo $c['ca_id']; ?>">
                        </a>
                        <img src='<?php echo THEMA_URL; ?>/assets/img/icon_arrow_next.png' class='pc pc-arrow'/>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        // Auto 롤링 아이디
        var rollingId = null;

        $('.samhwa-cate-menu ul.menu>li').mouseover(function(type) {
            $('.samhwa-cate-menu ul.menu>li').removeClass('on');
            $(this).addClass('on');

            $('.rolling_panel ul li').removeClass('pcon');
            var id = $(this).data('id');
            $('#category_' + id).addClass('pcon');
            //console.log(type);
            clearInterval(rollingId);
            rollingId = null;
        });

        $('.samhwa-cate-menu ul.menu').mouseleave(function() {
            //console.log('leave!');
            if ( $('.samhwa-cate-menu ul.menu>li.default_on').length > 0 ) {
                $('.samhwa-cate-menu ul.menu>li').removeClass('on');
                $('.samhwa-cate-menu ul.menu>li.default_on').addClass('on');
            }else{
                auto();
            }
            //console.log($('.samhwa-cate-menu ul.menu>li.default_on'));
        });

        function set_banner_size() {
            //console.log('set_banner_size');

            var windowWidth = $( window ).width();
            if(windowWidth < 991) {
                // console.log('start set banner size');

                var width = $('.samhwa-cate-banner').width();
                $('.rolling_panel ul li').css('width', width);
                $('.rolling_panel ul li').css('height', '300px');
                
                var ea = $('.rolling_panel ul li').length;
                $('.rolling_panel').css('width', width * ea);
                $('.rolling_panel').css('height', '300px');
            }else{
                // console.log('start set banner size2');
                $('.rolling_panel ul li').css('width', '');
                $('.rolling_panel ul li').css('height', '');
                $('.rolling_panel').css('width', '');
                $('.rolling_panel').css('height', '');
            }
        }

        $(window).resize(function(){
            set_banner_size();
        }).resize();



        var $panel = $(".rolling_panel").find("ul");

        var itemWidth = $panel.children().outerWidth(); // 아이템 가로 길이
        var itemLength = $panel.children().length;      // 아이템 수

        // 배너 마우스 오버 이벤트
        $panel.mouseover(function() {
            clearInterval(rollingId);
        });

        // 배너 마우스 아웃 이벤트
        $panel.mouseout(function() {
            auto();
        });

        // 이전 이벤트
        $("#banner_prev").on("click", banner_prev);

        $("#banner_prev").mouseover(function(e) {
            clearInterval(rollingId);
        });

        $("#banner_prev").mouseout(auto);

        // 다음 이벤트
        $("#banner_next").on("click", banner_next);

        $("#banner_next").mouseover(function(e) {
            clearInterval(rollingId);
        });

        $("#banner_next").mouseout(auto);
        
        if ( !$('.samhwa-cate-menu ul.menu>li.default_on').length ) {
            $('.samhwa-cate-menu ul.menu>li:first-child').mouseover();
        }

        function rolled_start() {

            var newobj;

            if ( $('.samhwa-cate-menu ul.menu>li.on').next().length > 0 ) {
                //$('.samhwa-cate-menu ul.menu>li.on').next().mouseover();
                var obj = $('.samhwa-cate-menu ul.menu>li.on');
                newobj = obj.next();
                $('.samhwa-cate-menu ul.menu>li').removeClass('on');
                obj.next().addClass('on');
            }else{
                $('.samhwa-cate-menu ul.menu>li').removeClass('on');
                $('.samhwa-cate-menu ul.menu>li:first-child').addClass('on');
                newobj = $('.samhwa-cate-menu ul.menu>li:first-child');
                //$('.samhwa-cate-menu ul.menu>li:first-child').mouseover();
            }

            $('.rolling_panel ul li').removeClass('pcon');
            var id = $(newobj).data('id');
            $('#category_' + id).addClass('pcon');
        }

        function auto() {
            /*
            if ( rollingId === null ) {
                var windowWidth = $( window ).width();
                if(windowWidth < 991) {
                    rollingId = setInterval(function() {
                        start();
                    }, 4000);
                }else{
                    rollingId = setInterval(function() {
                        rolled_start();
                    }, 4000);
                }
            }
            // console.log(start);
            */
        }


        auto();

        function start() {
            $panel.css("width", itemWidth * itemLength);
            $panel.animate({"left": - itemWidth + "px"}, function() {

                // 첫번째 아이템을 마지막에 추가하기
                $(this).append("<li>" + $(this).find("li:first").html() + "</li>");

                // 첫번째 아이템을 삭제하기
                $(this).find("li:first").remove();

                // 좌측 패널 수치 초기화
                $(this).css("left", 0);
            });
            set_banner_size();
        }

        // 이전 이벤트 실행
        function banner_prev(e) {
            $('#banner_prev').attr('disabled', true);

            $panel.css("left", - itemWidth);
            $panel.prepend("<li>" + $panel.find("li:last").html() + "</li>");

            $panel.animate({"left": "0px"}, function() {
                $(this).find("li:last").remove();
                $('#banner_prev').attr('disabled', false);
            });
            set_banner_size();
        }

        // 다음 이벤트 실행
        function banner_next(e) {
            $panel.animate({"left": - itemWidth + "px"}, function() {
                $(this).append("<li>" + $(this).find("li:first").html() + "</li>");
                $(this).find("li:first").remove();
                $(this).css("left", 0);
            });
            set_banner_size();
        }

        $.each($('.samhwa-cate-menu ul.sub'), function(item, index) {
            //console.log(this);

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
    });
</script>
<?php } ?>

<div id="samhwa-m-menu">
    <div class="wrap">
        <div class="closer">
            <img src="<?php echo THEMA_URL; ?>/assets/img/btn_close.png" />
        </div>
        <div class="scrollable-wrap">
            <ul class="mobile-cate">   
                <?php foreach($category as $cate) { ?>
                    <li class="<?php echo (substr($ca_id, 0, strlen($cate['ca_id'])) === $cate['ca_id']) ? 'on default_on ': ''; ?>" data-id="<?php echo $cate['ca_id']; ?>">
                        <a class='title'><?php echo $cate['ca_name']; ?></a> <?php /*href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$cate['ca_id']; ?>' data-id="<?php echo $cate['ca_id']; ?>"*/?>
                        <?php if ( $cate['sub'] ) { ?>
                            <ul class='sub'>
                                <?php foreach($cate['sub'] as $sub) { ?>
                                    <li class="<?php echo $sub['ca_id'] == $ca_id ? 'on' : ''; ?> ">
                                        <a href='<?php echo G5_SHOP_URL . '/list.php?ca_id=' .$sub['ca_id']; ?>' class='sub-title'><?php echo $sub['ca_name']; ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
            <?php if($is_member) { // 로그인 상태 ?>
                <a href="<?php echo $at_href['logout'];?>">로그아웃</a>
            <?php }else{ ?>
                <a href="<?php echo $at_href['login'];?>" class="green">로그인</a>
            <?php } ?>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.header-hamburger').click(function() {
        // $('#samhwa-m-menu').toggle();
        $('#samhwa-m-menu').show(10);
        $('#samhwa-m-menu .wrap').addClass('active');
    });

    $('#samhwa-m-menu .wrap .closer').click(function() {
        $('#samhwa-m-menu').hide(100);
        $('#samhwa-m-menu .wrap').removeClass('active');

    });

    $('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').click(function() {
        $('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li').removeClass('on');
        $(this).addClass('on');
        //return false;
    });
    $('#samhwa-m-menu .wrap .scrollable-wrap ul.mobile-cate>li>a').dblclick(function() {
        console.log('aaa');
        window.location = this.href;
        //return false;
    });
});
</script>