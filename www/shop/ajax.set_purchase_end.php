<?php
	include_once("./_common.php");
    if ($od_id && $ct_id) {
      sql_query("update purchase_cart 
                set is_purchase_end = '{$is_purchase_end}' 
                where od_id = '{$od_id}' and ct_id = '{$ct_id}'
                ");
    }
?>