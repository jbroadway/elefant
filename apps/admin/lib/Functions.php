<?php

/**
 * The filter to save dynamic HTML embeds and get an ID value for them.
 * Saves to `cache/html/${md5($html)}.html`.
 *
 * If `$reverse` is set to true, it will call `admin_embed_lookup()`
 * instead to retrieve the original value, as dynamic embed filters
 * are supposed to do.
 */
function admin_embed_filter ($html, $reverse = false) {
	if ($reverse) {
		return admin_embed_lookup ($html);
	}

	if (! @file_exists ('cache/html')) {
		mkdir ('cache/html', 0777);
	}

	$id = md5 ($html);
	file_put_contents ('cache/html/' . $id . '.html', $html);
	return $id;
}

/**
 * The reverse lookup that returns HTML for a given ID value.
 */
function admin_embed_lookup ($id) {
	return file_get_contents ('cache/html/' . $id . '.html');
}

/**
 * Get a list of installed layouts/themes.
 */
function admin_get_layouts () {
	$layouts = array ();
	$sources = array (
		'layouts/*.html',
		'layouts/*/*.html'
	);
	foreach ($sources as $source) {
		$files = glob ($source);
		if ($files) {
			foreach ($files as $file) {
				if (preg_match ('/\/([^\/]+)\/([^\/]+)\.html$/', $file, $regs)) {
					if ($regs[1] === $regs[2]) {
						$layouts[] = $regs[1];
					} else {
						$layouts[] = $regs[1] . '/' . $regs[2];
					}
				} elseif (preg_match ('/\/([^\/]+)\.html$/', $file, $regs)) {
					$layouts[] = $regs[1];
				}
			}
		}
	}
	sort ($layouts);
	return $layouts;
}

/**
 * Check whether a layout exists.
 */
function admin_layout_exists ($name) {
	return (file_exists ('layouts/' . $name . '.html') || file_exists ('layouts/' . $name . '/' . $name . '.html'));
}

?>