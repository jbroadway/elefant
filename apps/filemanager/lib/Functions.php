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
 * Alias of `Image::resize()` for backward compatibility.
 */
function filemanager_get_thumbnail ($file, $width = 140, $height = 105, $style = "cover") {
	return Image::resize ($file, $width, $height, $style);
}

?>