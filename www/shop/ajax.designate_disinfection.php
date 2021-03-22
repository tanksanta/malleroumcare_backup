<?php
    include_once('./_common.php');
    // $res = json_decode($_POST, true);
    $rental_log_Id="rental_log".round(microtime(true));
    if($_POST['stoId']&&$_POST['dis_new']=="1"){
        $dis_total_date=G5_TIME_YMDHIS;
        $sql = " insert into `g5_rental_log`
            set `rental_log_Id` = '$rental_log_Id',
                `stoId` = '$stoId',
                `dis_detail` = '$dis_detail',
                `dis_perosn` = '$dis_perosn',
                `dis_phone` = '$dis_phone',
                `dis_total_date` = '$dis_total_date',
                `rental_log_division` = '1';";
        if(sql_query($sql)){
            echo "S";
        }else{
            echo $sql;
        }
    }
?>
