<?php

/**
 * Clear the cache folder of compiled templates, cached
 * navigation, and cache data files.
 *
 * Leaves thumbnails, HTML code from dynamic objects,
 * and any non-default app-specific cached elements.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

exec ('rm -f cache/*.php cache/datastore/* cache/navigation.json');

?>