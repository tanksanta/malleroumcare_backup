<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

function wzRobotChecker($useragent) {
    $robotPattern = array(
        'Googlebot' => 1,
        'NaverBot' => 1,
        'TechnoratiSnoop' => 1,
        'Allblog.net' => 1,
        'CazoodleBot' => 1,
        'nhn/1noon' => 1,
        'Feedfetcher-Google' => 1,
        'Yahoo! Slurp' => 1,
        'RMOM' => 1,
        'msnbot' => 1,
        'Technoratibot' => 1,
        'sproose' => 1,
        'CazoodleBot' => 1,
        'ONNET-OPENAPI' => 1,
        'UCLA CS Dept' => 1,
        'Snapbot' => 1,
        'DAUM RSS Robot' => 1,
        'RMOM' => 1,
        'S20 Wing' => 1,
        'FeedBurner' => 1,
        'xMind' => 1,
        'openmaru feed aggregator' => 1,
        'ColFeed' => 1,
        'MJ12bot' => 1,
        'Twiceler' => 1,
        'ia_archiver' => 1,
        //'Daumoa' => 1,
        'Mediapartners-Google' => 1
    );

    foreach ($robotPattern as $agentName => $isRobot)

    if ((strpos($useragent, $agentName) !== false) && $isRobot)
        return true;
    else
        return false;
}

function wzLoadModule($service = '') {

    $cache_time   = 60 * 5; // 1초 : 1, 1분 : 60 * 1, 1시간 : 3600 * 1 (시간을 짧게 해도 검색봇이 접근하지 않으면 짧은시간 주기로 실행되지 않습니다.)
    $cache_file   = G5_DATA_PATH.'/cache/web_crontab_'.$service.'.log';
    $cache_fwrite = false;

    if(!file_exists($cache_file)) {
        $cache_fwrite = true;
    } else {
        if($cache_time > 0) {
            $fp = fopen($cache_file,'r');
            $read_time = fread($fp, 100);
            fclose($fp);

            if ($read_time && $read_time < (G5_SERVER_TIME - $cache_time)) {
                $cache_fwrite = true;
            }
        }
        else {
            $cache_fwrite = true;
        }
    }

    if ($cache_fwrite) {

        $handle = fopen($cache_file, 'w');
        $cache_content = G5_SERVER_TIME;
        fwrite($handle, $cache_content);
        fclose($handle);

        // 실행
        if ($service == 'wznaverpay') {
            include_once(G5_PLUGIN_PATH.'/wznaverpay/config.php');
            $aor = new NHNAPIORDER();
            $aor->ordersync_rotation('ordersync');

            $aor = new NHNAPIORDER();
            $aor->PurchaseReviewClassType = 'GENERAL'; // 일반평가져오기
            $aor->customersync_rotation('GetPurchaseReviewList-GENERAL');

            $aor = new NHNAPIORDER();
            $aor->PurchaseReviewClassType = 'PREMIUM'; // 프리미엄평가져오기
            $aor->customersync_rotation('GetPurchaseReviewList-PREMIUM');
        }
    }
}

if (wzRobotChecker($_SERVER['HTTP_USER_AGENT'])) {
    if ($default['de_naverpayorder_AccessLicense'] && $default['de_naverpayorder_SecretKey']) { // 네이버페이를 사용하는 사이트에만 함수 사용
    	wzLoadModule('wznaverpay');
    }
}
?>