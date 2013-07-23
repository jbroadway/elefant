<?php
 
/**
 * Fetches the properties add form for the property manager.
 */
 
$page->layout = false;
 
header ('Content-Type: application/json');
 
$out = array (
  'title' => __ ('Add Property'),
	'body' => $tpl->render ('filemanager/propman/add')
);
 
echo json_encode ($out);
 
?>