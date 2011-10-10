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

?>