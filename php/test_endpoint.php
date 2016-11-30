<?php
ob_start();
echo "GET " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n";
var_dump($_GET);
$handle = fopen("postback.log", "w");
fwrite($handle, ob_get_contents());
fclose($handle);
ob_end_clean();
?>