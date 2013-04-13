<?php

/**
 * Outputs the navigation list in JSON.
 */

$page->layout = false;
header ('Content-Type: application/json');
echo file_exists (conf('Navigation','json_file')) ? file_get_contents (conf('Navigation','json_file')) : '[]';
$this->quit ();

?>