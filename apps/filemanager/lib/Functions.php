<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Returns a list of folders recursively under the specified
 * folder path.
 */
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

/**
 * Returns a list of sorting order options for the gallery handler.
 */
function filemanager_gallery_order () {
	return array (
		array (
			'key' => 'desc',
			'value' => i18n_get ('Newest first')
		),
		array (
			'key' => 'asc',
			'value' => i18n_get ('Oldest first')
		),
		array (
			'key' => 'alpha',
			'value' => i18n_get ('Alphabetical')
		)
	);
}

/**
 * Returns a yes/no options for the gallery handler.
 */
function filemanager_yes_no () {
	return array (
		array (
			'key' => 'no',
			'value' => i18n_get ('No')
		),
		array (
			'key' => 'yes',
			'value' => i18n_get ('Yes')
		)
	);
}

/**
 * Returns a list of display style options for the gallery handler.
 */
function filemanager_style_list () {
	return array (
		array (
			'key' => 'lightbox',
			'value' => i18n_get ('Lightbox')
		),
		array (
			'key' => 'embedded',
			'value' => i18n_get ('Embedded')
		)
	);
}

/**
 * Sort files by mtime descending. Usage:
 *
 *     usort ($list, 'filemanager_sort_mtime_desc');
 */
function filemanager_sort_mtime_desc ($one, $two) {
	$t_one = filemtime ($one);
	$t_two = filemtime ($two);
	if ($t_one === $t_two) {
		return 0;
	}
	return ($t_one < $t_two) ? 1 : -1;
}

/**
 * Sort files by mtime ascending. Usage:
 *
 *     usort ($list, 'filemanager_sort_mtime_asc');
 */
function filemanager_sort_mtime_asc ($one, $two) {
	$t_one = filemtime ($one);
	$t_two = filemtime ($two);
	if ($t_one === $t_two) {
		return 0;
	}
	return ($t_one > $t_two) ? 1 : -1;
}

/**
 * If it can, it creates a cached thumbnail of the specified
 * image file, saved to `cache/thumbs` with the name
 * `md5($file)` plus `-WIDTHxHEIGHT.jpg`. If it can't create
 * the thumbnail, the original will be returned with one of
 * the following messages added to it as a hashmark:
 *
 *     #gd-missing
 *     #libjpg-missing
 *     #libpng-missing
 *     #libgif-missing
 *     #unsupported-format
 *
 * If the cached version already exists, and its modification time
 * is newer than the original, then the cached version is returned
 * immediately.
 *
 * This makes first requests to a gallery page expensive, but
 * subsequent requests much faster.
 */
function filemanager_get_thumbnail ($file, $width = 140, $height = 105, $style = "cover") {
	$cache_file = 'cache/thumbs/' . md5 ($file) . '-'. $style ."-" . $width . 'x' . $height . '.jpg';
	if (@file_exists ($cache_file) && @filemtime ($cache_file) > @filemtime ($file)) {
		return $cache_file;
	}

	$info = pathinfo ($file);
	$ext = strtolower ($info['extension']);

	if (! extension_loaded ('gd')) {
		return $file . '#gd-missing';
	}
	if ($ext === 'jpg' || $ext === 'jpeg') {
		if (@imagetypes () & IMG_JPG) {
			$orig = @imagecreatefromjpeg ($file);
		} else {
			return $file . '#libjpg-missing';
		}
	} elseif ($ext === 'png') {
		if (@imagetypes () & IMG_PNG) {
			$orig = @imagecreatefrompng ($file);
		} else {
			return $file . '#libpng-missing';
		}
	} elseif ($ext === 'gif') {
		if (@imagetypes () & IMG_GIF) {
			$orig = @imagecreatefromgif ($file);
		} else {
			return $file . '#libgif-missing';
		}
	} else {
		return $file . '#unsupported-format';
	}

	if (! @is_dir ('cache/thumbs')) {
		mkdir ('cache/thumbs');
	}

	list ($w, $h) = getimagesize ($file);

	if($style === "cover"){
		$ratio = max ($width / $w, $height / $h);
		$woffset = ($w - $width / $ratio) / 2;
		$hoffset = ($h - $height / $ratio) / 2;
		$h = $height / $ratio;
		$w = $width / $ratio;	
	} elseif ($style === "stretch"){		
		$woffset = 0;
		$hoffset = 0;
	} elseif ($style === "contain"){		
		$woffset = 0;
		$hoffset = 0;			
		$scale = min($width/$w, $height/$h);	
		$width  = ceil($scale*$w);
		$height = ceil($scale*$h);
	}
	
	$new = @imagecreatetruecolor ($width, $height);
	@imagecopyresampled ($new, $orig, 0, 0, $woffset, $hoffset, $width, $height, $w, $h);
	
	@imagejpeg ($new, $cache_file);
	@imagedestroy ($orig);
	@imagedestroy ($new);
	return $cache_file;
}

?>