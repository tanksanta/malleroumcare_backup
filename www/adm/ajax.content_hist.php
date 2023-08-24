<?php
    include_once('./_common.php');
    header('Content-Type: application/json');
    $ret = array();
	if($_POST['co_id']){
        $rows = array();
		$sql = "SELECT co_id,co_subject FROM {$g5['content_table']} WHERE co_id LIKE '".$_POST['co_id']."'";
		$row = sql_fetch($sql, true);
		$subject = $row["co_subject"];
		$rows[] = $row;
		$sql2 = "SELECT co_id,co_subject FROM {$g5['content_table']} WHERE co_id LIKE '".$_POST['co_id']."_%' ORDER BY co_id DESC";
		$result = sql_query($sql2);
		while($row2 = sql_fetch_array($result)){
			$rows[] = $row2;
		}
        
		if(count($rows)>0){
			$ret = array(
				'result' => 'success',
				'rows' =>  $rows,
				'co_subject' => $subject,
				'msg' => ''
			);
		}else{
			$ret = array(
            'result' => 'fail',
			'msg' => '조회 된 내용이 없습니다.'
        );
		}
        $json = json_encode($ret);
        echo $json;
    }else{
        $ret = array(
            'result' => 'fail',
			'msg' => 'co_id가 존재 하지 않습니다.'
        );
        $json = json_encode($ret);
        echo $json;
    }

?>