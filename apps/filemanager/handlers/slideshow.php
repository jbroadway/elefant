<?php

/**
 * Slideshow embed handler. Creates a slideshow of the images from the
 * specified folder. Used by the WYSIWYG editor's dynamic objects menu,
 * or manually via:
 *
 *     {! filemanager/slideshow?path=foldername !}
 *
 * The `foldername` is a folder of images inside `/files/`.
 */

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

echo $tpl->render ('filemanager/slideshow', array ('files' => $files));

?>