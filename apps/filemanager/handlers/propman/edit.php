<?php
 
/**
 * Fetches the properties edit form for the property manager.
 */
 
$page->layout = false;
 
header ('Content-Type: application/json');
 
$res = PropManager::all();
$prop = array();
 
foreach ($res as $row) {
  if ($_GET['id'] == $row->id) {
    $prop['id'] = $row->id;
    $prop['type'] = $row->type;
    $prop['label'] = $row->label;
  }
}
 
$out = array (
  'title' => __ ('Edit Property'),
	'body' => $tpl->render ('filemanager/propman/edit', $prop)
);
echo json_encode ($out);
 
?>