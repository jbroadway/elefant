<?php

if (isset ($data['path'])) {
	$path = trim ($data['path'], '/');
} elseif (isset ($_GET['path'])) {
	$path = trim ($_GET['path'], '/');
} else {
	return;
}

if (strpos ($path, '..') !== false) {
	return;
}

if (! @is_dir ('files/' . $path)) {
	return;
}

$files = glob ('files/' . $path . '/*.{jpg,jpeg,gif,png}', GLOB_BRACE);

echo $tpl->render (
	'filemanager/gallery',
	array (
		'files' => $files,
		'gallery' => str_replace ('/', '-', $path)
	)
);

?>