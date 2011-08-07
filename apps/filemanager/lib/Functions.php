<?php

function filemanager_list_folders ($path = '') {
	$folders = array ();

	if (! empty ($path)) {
		$rpath = 'files/' . $path;
		$epath = $path . '/';
	} else {
		$rpath = 'files';
		$epath = '';
	}
	$d = dir ($rpath);
	if (! $d) {
		return array ();
	}
	while (false !== ($file = $d->read ())) {
		$files[] = $file;
	}
	$d->close ();

	foreach ($files as $file) {
		if (strpos ($file, '.') === 0 || ! @is_dir ($rpath . '/' . $file)) {
			continue;
		}
		$folders[] = $epath . $file;
		$subs = filemanager_list_folders ($epath . $file);
		foreach ($subs as $sub) {
			$folders[] = $sub;
		}
	}
	return $folders;
}

function filemanager_get_thumbnail ($file) {
	$cache_file = 'cache/thumbs/' . md5 ($file) . '.jpg';
	if (@file_exists ($cache_file) && @filemtime ($cache_file) > @filemtime ($file)) {
		return $cache_file;
	}

	$info = pathinfo ($file);
	$ext = strtolower ($info['extension']);

	if ($ext != 'jpg' && $ext != 'jpeg') {
		return $file . '#not-a-jpg';
	}
	if (! extension_loaded ('gd')) {
		return $file . '#gd-missing';
	}
	if (@imagetypes () & IMG_JPG) {
		if (! @is_dir ('cache/thumbs')) {
			mkdir ('cache/thumbs');
		}

		list ($w, $h) = getimagesize ($file);
		$width = 140;
		$height = 105;
		if ($h > $w) {
			// cropping the height
			$hoffset = ($h - $w) / 2;
			$woffset = 0;
			$h -= $hoffset * 2;
		} else {
			// cropping the width
			$woffset = ($w - $h) / 2;
			$hoffset = 0;
			$w -= $woffset * 2;
		}
		$jpg = @imagecreatefromjpeg ($file);
		$new = @imagecreatetruecolor ($width, $height);
		@imagecopyresampled ($new, $jpg, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
		@imagejpeg ($new, $cache_file);
		@imagedestroy ($jpg);
		@imagedestroy ($new);
		return $cache_file;
	}
	return $file . '#libjpg-missing';
}

?>