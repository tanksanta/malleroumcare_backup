<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($ca_id)
{
    $navigation = $bar = "";
    $len = strlen($ca_id) / 2;
    for ($i=1; $i<=$len; $i++)
    {
        $code = substr($ca_id,0,$i*2);

        $sql = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$code' ";
        $row = sql_fetch($sql);

        $sct_here = '';
        if ($ca_id == $code) // 현재 분류와 일치하면
            $sct_here = 'sct_here';

        if ($i != $len) // 현재 위치의 마지막 단계가 아니라면
            $sct_bg = 'sct_bg';
        else $sct_bg = '';

        $navigation .= $bar.'<a href="./list.php?ca_id='.$code.'" class="'.$sct_here.' '.$sct_bg.'">'.$row['ca_name'].'</a>';
    }
}
else
    $navigation = $g5['title'];

//if ($it_id) $navigation .= " > $it[it_name]";

?>

<div id="sct_location">
    <a href='<?php echo G5_SHOP_URL; ?>/' class="sct_bg">Home</a>
    <?php echo $navigation; ?>



<?
$sql_chl="SELECT * FROM `g5_shop_category` ";
    $result_chl = sql_query($sql_chl);
    $k = 0;
?>
<select name="ca_id" onChange="op_y(this)">  

	<option value="" selected>선택하세요

<?
while ($row_chl = sql_fetch_array($result_chl)){
//if(strlen($row_chl[ca_id])==2){
//selected
if($row_chl[ca_id]==$ca_id){$s_ed="selected";}
echo "<option value='".$row_chl[ca_id]."' ".$s_ed.">".$row_chl[ca_name];


}
	
//	}
?>
</select>

</div>
<script type="text/javascript">
function op_y(val_chl){
    tmp1 = val_chl.options[val_chl.selectedIndex].text; //text값을 가져오기 
    tmp2= val_chl.options[val_chl.selectedIndex].value; // value값을 가져오기
//	alert(tmp2);
location.replace("list.php?ca_id="+tmp2);         // 이동전 주소가 안보임. 

//location.href("이동할 주소");              // 이동전 주소가 보임

//history.go(-1);                             // 이전페이지가기. ()안의 값이 현재페이지에 대한 상대좌표

//location.reload();                             // 새로고침
}
</script>
