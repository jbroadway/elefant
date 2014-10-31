<?php

/**
 * Outputs the navigation list in JSON.
 */

$page->layout = false;
header ('Content-Type: application/json');
echo file_exists (conf('Paths','navigation_json')) ? file_get_contents (conf('Paths','navigation_json')) : '[]';
$this->quit ();
