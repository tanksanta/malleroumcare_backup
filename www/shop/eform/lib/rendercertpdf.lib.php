<?php
$timestamp = time();
$datetime = date('Y-m-d H:i:s', $timestamp);

$G5_URL = G5_URL;

$options = array(
  'page-size' => 'A4',  
  'no-outline',
  'encoding' => 'UTF-8',
  'margin-top'    => 12,
  'margin-right'  => 0,
  'margin-bottom' => 20,
  'margin-left'   => 0,
  'viewport-size' => 1240,
  'header-html' => "'{$G5_URL}/shop/eform/document/certificate_head.php'",
  'replace' => "'datetime' '{$datetime}'"
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

echo $args;

exec("wkhtmltopdf{$args} \"{$G5_URL}/shop/eform/renderCertificate.php?od_id={$eform['od_id']}&uuid={$uuid}&entId={$eform['entId']}&penId={$eform['penId']}\" \"{$certdir}\"");

?>
