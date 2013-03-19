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

$name = str_replace ('/', '-', $path);

$files = glob ('files/' . $path . '/*.{jpg,jpeg,gif,png,JPG,JPEG,GIF,PNG}', GLOB_BRACE);

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	foreach ($files as $k => $file) {
		$files[$k] = str_replace ('files/', 'filemanager/proxy/', $file);
	}
}

$page->add_script ('/apps/filemanager/js/jquery.cycle.all.min.js');
echo $tpl->render ('filemanager/slideshow', array ('files' => $files, 'name' => $name));

?>