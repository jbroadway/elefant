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
 * If it can, it creates a cached thumbnail of the specified
 * image file (JPG only at this time), saved to `cache/thumbs`
 * with the name `md5($file)` plus `.jpg`. If it can't create
 * the thumbnail, the original will be returned with one of
 * the following messages added to it as a hashmark:
 *
 *     #not-a-jpg
 *     #gd-missing
 *     #libjpg-missing
 *
 * If the cached version already exists, and its modification time
 * is newer than the original, then the cached version is returned
 * immediately.
 *
 * This makes first requests to a gallery page expensive, but
 * subsequent requests much faster.
 */
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