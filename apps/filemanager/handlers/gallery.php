<?php

/**
 * Photo gallery embed handler. Creates a gallery of the images
 * from the specified folder. Used by the WYSIWYG editor's dynamic
 * objects menu, or manually via:
 *
 *     {! filemanager/gallery?path=foldername !}
 *
 * The `foldername` is a folder of images inside `/files/` or the directory set
 * by the 'filemanager_path' option in your config file.
 */

require_once ('apps/filemanager/lib/Functions.php');

$root = trim (conf ('Paths','filemanager_path'), '/') . '/';

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

if (! @is_dir ($root . $path)) {
	return;
}

// fetch the files
$files = glob ($root . $path . '/*.{jpg,jpeg,gif,png,JPG,JPEG,GIF,PNG}', GLOB_BRACE);
$files = is_array ($files) ? $files : array ();

// sorting order
if ($data['order'] === 'desc') {
	usort ($files, 'filemanager_sort_mtime_desc');
} elseif ($data['order'] === 'asc') {
	usort ($files, 'filemanager_sort_mtime_asc');
} elseif ($data['order'] === 'alpha') {
	sort ($files);
}

// remove 'files/' from paths and create output list
$list = array ();
foreach ($files as $key => $file) {
	$list[preg_replace ('/^files\//', '', $file)] = (object) array (
		'path' => $file,
		'desc' => ''
	);
}

// fetch descriptions
if ($data['desc'] === 'yes') {
	if (!empty ($list)) {
		$descriptions = FileManager::prop (array_keys ($list), 'desc');
		foreach ($descriptions as $file => $desc) {
			$list[$file]->desc = $desc;
		}
	}
}

// display style
if ($data['style'] === 'lightbox') {
	$page->add_style ('/apps/filemanager/css/gallery.css');
	$page->add_style ('/apps/filemanager/css/colorbox/colorbox.css');
	$page->add_script ('/apps/filemanager/js/jquery.colorbox.min.js');

	$template = 'filemanager/gallery';
} else {
	$template = 'filemanager/gallery/embedded';
    if (User::require_admin ()) {
        $page->add_script ('/apps/filemanager/js/jquery.filemanager.js');
    }
}

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	foreach ($list as $k => $file) {
		$list[$k]->path = str_replace ('files/', 'filemanager/proxy/', $file->path);
	}
}

echo $tpl->render (
	$template,
	array (
		'files' => $list,
		'gallery' => str_replace (array ('/', '.', ' '), array ('-', '-', '-'), $path),
		'desc' => $data['desc']
	)
);

?>
