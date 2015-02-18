<?php

/**
 * Slideshow embed handler. Creates a slideshow of the images from the
 * specified folder. Used by the WYSIWYG editor's dynamic objects menu,
 * or manually via:
 *
 *     {! filemanager/slideshow?path=foldername !}
 *
 * The `foldername` is a folder of images inside `/files/` or the directory set
 * by the 'filemanager_path' option in your config file.
 *
 *  * Alternatively, it can be used via:
 *
 *      {! filemanager/slideshow?files=filelist&name=idsuffix !}
 *
 * The `filelist` is a list of images inside `/files/` or the directory set
 * by the `filemanager_path` option in your config file.
 * `filelist` can either be an array or a string with each file path delimited
 * by `|` or a glob pattern like 'stuff/*_light.jpg'.
 * `idsuffix` will be appended to the CSS id of the slideshow.
 *
* To set the transition speed in milliseconds add a `speed`
 * parameter in the form `SPEED`
 *
 * To set the photo ratio, add a `ratio`
 * parameter in the form `WIDTH:HEIGH`, for example:
 *
 *     {! filemanager/slideshow?path=foldername&ratio=4:3&speed=1500 !}
 *
 * Note that this will also reorient photos that have been uploaded from
 * devices that don't automatically correct the photo orientation, based
 * the EXIF metadata.
 */
$root = trim (conf ('Paths', 'filemanager_path'), '/') . '/';

if (! isset ($data['autoplay']) || $data['autoplay'] === 'yes') {
	$timeout = '6000';
} else {
	$timeout = '0';
}

if (isset ($data['path']) or isset ($_GET['path'])) {

	if (isset ($data['path'])) {
		$path = trim ($data['path'], '/');
	} elseif (isset ($_GET['path'])) {
		$path = trim ($_GET['path'], '/');
	}

	if (strpos ($path, '..') !== false) {
		return;
	}

	if ( ! @is_dir ($root . $path)) {
		return;
	}

	$name = str_replace ('/', '-', $path);

	$files = glob ($root . $path . '/*.{jpg,jpeg,gif,png,JPG,JPEG,GIF,PNG}', GLOB_BRACE);
	$files = is_array ($files) ? $files : array ();
} elseif (isset ($data['files']) or isset ($_GET['files'])) {

	if (isset ($data['files'])) {
		$files_arg = $data['files'];
	} elseif (isset ($_GET['files'])) {
		$files_arg = $_GET['files'];
	}

	$addroot = true;
	if (is_string ($files_arg)) {
		if (strpos($files_arg,'|') !== false) {
			$files = explode ('|', $files_arg);
		} elseif (strpos($files_arg,'*') !== false || strpos($files_arg,'?') !== false) {
			if (strpos ($files_arg, '..') !== false) {
				return;
			}
			$files = glob ($root .  $files_arg, GLOB_BRACE);
			$files = is_array ($files) ? $files : array ();
			$addroot = false;
		} else {
			return;
		}
	} elseif (is_array ($files_arg)) {
		$files = $files_arg;
	} else {
		return;
	}

    if ($addroot) {
        $files = array_map (
            function($var) use ($root) {
                return ($root . trim ($var, '/'));
            }
            , $files);
    }

	if (isset ($data['name'])) {
		$name = $data['name'];
	} elseif (isset ($_GET['name'])) {
		$name = $_GET['name'];
	}
} else {
	return;
}

if (isset ($data['speed'])) {
	$speed = $data['speed'];
} else {
	$speed = "2000";
}


if (isset ($data['ratio'])) {
	$ratio = $data['ratio'];
}

// rewrite if proxy is set
if ($appconf['General']['proxy_handler']) {
	foreach ($files as $k => $file) {
		$files[$k] = str_replace ($root, 'filemanager/proxy/', $file);
	}
}

$page->add_style ('/apps/filemanager/css/cycle2.css');
$page->add_script ('/apps/filemanager/js/jquery.cycle2.js');
echo $tpl->render (
	'filemanager/slideshow', 
	array (
		'files' => $files,
		'name' => $name,
		'timeout' => $timeout,
		'speed' => $speed,
		'ratio' => $ratio
	)
);
