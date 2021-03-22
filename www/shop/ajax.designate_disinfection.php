<?php
    include_once('./_common.php');
    // $res = json_decode($_POST, true);
    $disId="dis".round(microtime(true));
    if($_POST['stoId']&&$_POST['dis_new']=="1"){
        $dis_total_date=G5_TIME_YMDHIS;
        $sql = " insert into `g5_disinfection`
            set 
                `disId` = '$disId',
                `stoId` = '$stoId',
                `dis_detail` = '$dis_detail',
                `dis_perosn` = '$dis_perosn',
                `dis_phone` = '$dis_phone',
                `dis_total_date` = '$dis_total_date';";
        if(sql_query($sql)){
            echo "S";
        }else{
            echo "N";
        }
    }
?>
