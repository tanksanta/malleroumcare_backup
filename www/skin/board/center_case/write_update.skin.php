<?php
if($w == '' || $w == 'u') {
    if($wr_id && $_POST['wr_provider']) {
        $provider = clean_xss_tags($_POST['wr_provider']);
        $sql = " update $write_table set wr_provider = '$provider' where wr_id = '$wr_id' ";
        sql_query($sql);
    }
}
