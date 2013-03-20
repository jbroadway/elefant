<?php

/**
 * Outputs the navigation list in JSON.
 */

$page->layout = false;
header ('Content-Type: application/json');
echo file_exists ('conf/navigation.json') ? file_get_contents ('conf/navigation.json') : '[]';
$this->quit ();

?>