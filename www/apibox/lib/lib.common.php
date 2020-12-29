<?php

### 배열/클래스 출력 함수
if (!function_exists('debug')){
	function debug($data){
		print "<div style='background:#000000;color:#00ff00;padding:10px;text-align:left'><xmp style=\"font:8pt 'Courier New'\">";
		print_r($data);
		print "</xmp></div>";
	}
}

?>