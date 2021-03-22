<?php


## checkform : _POST 및 _GET 으로 넘어온 값 보기
function checkform($action){
    if($action=="post"){
    while(list($key,$value)= each($_POST)){
    if(is_array($value)){
    while(list($key1,$value1)=each($value)){
    echo $key."[".$key1."]" ." = ". $value1."<br>\n";
    }
    }else{
    echo $key ." = ". $value."<br>\n";
    }
    }
    }elseif($action=="get"){
    while(list($key,$value)= each($_GET)){
    if(is_array($value)){
    while(list($key1,$value1)=each($value)){
    echo $key."[".$key1."]" ." = ". $value1."<br>\n";
    }
    }else{
    echo $key ." = ". $value."<br>\n";
    }
    }
    }else{
    echo "사용방법 오류 : checkform 사용방법 = checkform('get' or 'post');";
    }
}


    include_once('./_common.php');
    // $res = json_decode($_POST, true);
    echo $_POST['formData'];
    return false;
    if($_POST['stoId']&&$_POST['dis_new']=="1"){
        $dis_total_date=G5_TIME_YMDHIS;
        $sql = " insert into `g5_disinfection`
            set `dis_stoId` = '$stoId',
                `dis_detail` = '$dis_detail',
                `dis_perosn` = '$dis_perosn',
                `dis_phone` = '$dis_phone',
                `dis_total_date` = '$dis_total_date';";
        sql_query($sql);
        if(sql_query($sql)){
            echo "S";
        }else{
            echo "N";
        }
    }
?>
