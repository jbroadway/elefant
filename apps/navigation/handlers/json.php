<?php

$page->layout = false;
header ('Content-Type: application/json');
echo file_get_contents ('conf/navigation.json');
exit;

?>