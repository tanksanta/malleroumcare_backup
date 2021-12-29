<?php
$options = array(
  'enable-javascript',
  'no-stop-slow-scripts',
  'javascript-delay' => 500,
  'page-size' => 'A4',  
  'no-outline',
  'encoding' => 'UTF-8',
  'margin-top'    => 15,
  'margin-right'  => 0,
  'margin-bottom' => 0,
  'margin-left'   => 0,
  'viewport-size' => 1240
);

$args = '';
foreach($options as $key => $val) {
  if(is_int($key)) {
    $key = $val;
    $val = null;
  }

  $args .= ' --'.$key;
  if($val !== null) $args .= ' '.$val;
}

$G5_URL = G5_URL;

exec("wkhtmltopdf{$args} \"{$G5_URL}/shop/eform/install_report_render.php?od_id={$od_id}&mb_id={$member['mb_id']}\" \"{$pdfdir}\"");

?>
