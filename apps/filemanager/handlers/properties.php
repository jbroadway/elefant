<?php

/**
 * Fetches the properties edit form for the file manager.
 */

$page->layout = false;

header ('Content-Type: application/json');

$file = urldecode (join ('/', $this->params));

$out = array (
	'title' => i18n_get ('Properties'),
	'body' => $tpl->render (
		'filemanager/properties',
		array (
			'file' => $file,
			'desc' => FileManager::prop ($file, 'desc')
		)
	)
);

echo json_encode ($out);

?>