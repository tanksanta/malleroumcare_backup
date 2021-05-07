<?php
$url = './thk001_2.html';
exec('/usr/local/bin/wkhtmltopdf -L 0 -R 0 -T 0 -B 0 --enable-local-file-access --viewport-size 1240 "'.$url.'" "test.pdf"');

header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=test.pdf");

@readfile("test.pdf");
?>