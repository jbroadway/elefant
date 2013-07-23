<?php
 
/**
 * Fetches the properties edit form for the file manager.
 */
 
$page->layout = false;
 
header ('Content-Type: application/json');
 
$file = urldecode (join ('/', $this->params));
 
$res = PropManager::all();
$props = array();
 
foreach($res as $k => $row) {
  $props[$k]['id'] = $row->id;
  $props[$k]['value'] = FileManager::prop ($file, $row->id);  
  $props[$k]['type'] = $row->type;
  $props[$k]['label'] = $row->label;
}
 
$out = array (
	'title' => __ ('Properties'),
	'body' => $tpl->render (
		'filemanager/properties',
		array (
			'file' => $file,
			'props' => $props
		)
	)
);
 
echo json_encode ($out);
 
?>