
<?php
    include_once("./_common.php");
    if($_POST['page']){$page=$_POST['page'];}else{$page=1;}
    $load=($page-1)*5;
    $sql = "select * from `g5_rental_log` where `stoId` = '{$stoId}' order by `strdate` DESC  limit {$load}, 5";
    $result = sql_query($sql);

    $sql = "select count(*) as count from `g5_rental_log` where `stoId` = '{$stoId}'";
    $row = sql_fetch($sql);
    # 페이징
    $totalCnt = $row['count'];

    $list="";
    for($i = 0; $row = sql_fetch_array($result); $i++){
        $number = $totalCnt-(($page-1)*5)-$i;
        if($row['strdate']){
            $strdate=substr($row['strdate'],5,2).'/'.substr($row['strdate'],8,2);
            $enddate=substr($row['enddate'],5,2).'/'.substr($row['enddate'],8,2);
            $date=$strdate.'~'.$enddate;
        }else{
            $date="미등록";
        }
        if($row['rental_log_division']=="1"){
            $content=$row['dis_detail'];
            if($row['dis_file']){
                $url=G5_URL.'/data/file/disinfection/'.$row['dis_file'];
                $document='<a href="'.$url.'" download target="_blank">소독 확인서</a>';
            }else{
                $document='';
            }
        }else{
            $content=$row['ren_person'].' 대여';
            $document='<a href="'.$row['ren_eformUrl'].'"  target="_blank">계약서</a>';
        }
        $list=$list.'<tr>
            <td>'.$number.'</td>
                <td>'.$content.'</td>
                <td>'.$date.'</td>
                <td>'.$document.'</td>
            </tr>';
     } 
     echo $list;
?>
      