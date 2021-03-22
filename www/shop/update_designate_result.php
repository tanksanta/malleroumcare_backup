<?php
    include_once('./_common.php');
    $max_file_size = 2097152;
    // 변수 정리
    $uploads_dir = G5_DATA_PATH.'/file/disinfection';
    $error = $_FILES['dis_file']['error'];
    $name = $_FILES['dis_file']['name'];
    $allowed_ext = array('jpg','jpeg','png','gif');
    $ext = array_pop(explode('.', $name));
    $temp = explode(".", $_FILES["dis_file"]["name"]);
    $newfilename = $_POST['member'].'_'.round(microtime(true)) . '.' . end($temp);

    // 오류 확인
    if( $error != UPLOAD_ERR_OK ) {
        switch( $error ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo "파일이 너무 큽니다. ($error)";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "파일이 첨부되지 않았습니다. ($error)";
                break;
            default:
                echo "파일이 제대로 업로드되지 않았습니다. ($error)";
        }
        exit;
    }
    if($file['size'] >= $max_file_size) {
        alert("2MB 까지만 업로드 가능합니다.");
        return false;
    }
    // 확장자 확인
    if( !in_array($ext, $allowed_ext) ) {
        echo "허용되지 않는 확장자입니다.";
        exit;
    }

    //최신 소독로그 조회
    $sql = "SELECT * FROM `g5_disinfection` WHERE `stoId`= '{$stoId}' ORDER BY `dis_total_date` DESC LIMIT 1";
    // echo $sql;
    $row = sql_fetch($sql);
    $last_date = $row['dis_total_date'];
    //최신 소독로그에 update
    $sql_update=" update `g5_disinfection` 
        set dis_date = '{$dis_date}',
            `dis_chemical` = '{$dis_chemical}',
            `dis_chemical_history` = '{$dis_chemical_history}',
            `dis_file` = '{$newfilename}'
            where `dis_total_date` = '{$last_date}' and `stoId`='{$stoId}'";
    sql_query($sql_update);
    //파일저장
    move_uploaded_file( $_FILES['dis_file']['tmp_name'], "$uploads_dir/$newfilename");




    //대여가능 변경
    $sendData=[];
    $prodsSendData = [];
    $prodsData =[];
    $prodsData["stoId"] = $stoId;
    $prodsData["stateCd"] = "01";

    array_push($prodsSendData,$prodsData);

    $sendData['usrId']= $_POST['member'];
    $sendData["prods"]=$prodsSendData;

    // echo json_encode($sendData);
	$oCurl = curl_init();
	curl_setopt($oCurl, CURLOPT_PORT, 9001);
	curl_setopt($oCurl, CURLOPT_URL, "https://eroumcare.com/api/stock/update");
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($sendData, JSON_UNESCAPED_UNICODE));
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $res = curl_exec($oCurl);
    $res = json_decode($res, true);
    curl_close($oCurl);
    if($res["errorYN"] == "Y"){
       alert($res["message"]);
    } else {
        alert('완료되었습니다.');
    }
    
    
?>
