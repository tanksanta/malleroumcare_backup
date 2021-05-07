<?php
$url = './thk001_1.html';
exec('/usr/local/bin/wkhtmltopdf --enable-local-file-access --viewport-size 1240 "'.$url.'" "test.pdf"');

header("Content-type:application/pdf");
// It will be called downloaded.pdf
header("Content-Disposition:attachment;filename=test.pdf");
// The PDF source is in original.pdf
readfile("test.pdf");
?>