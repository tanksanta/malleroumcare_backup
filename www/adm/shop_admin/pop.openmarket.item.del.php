<?php
include_once('./_common.php');
include_once(G5_ADMIN_PATH.'/apms_admin/apms.admin.lib.php');

  $sql = " delete from g5_shop_matching where oit_id = '{$oit_id}'";
  sql_query($sql);

  goto_url("pop.openmarket.item.list.php");
?>