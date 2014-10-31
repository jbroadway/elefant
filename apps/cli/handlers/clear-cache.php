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

if (isset ($_SERVER['argv'][2])) {
	$cache->delete ($_SERVER['argv'][2]);
	return;
}

exec ('rm -f cache/*.php cache/datastore/* cache/navigation.json');

// Also remove datastore dot-files
$d = dir ('cache/datastore');
while (false !== ($f = $d->read ())) {
	if ($f === '.' || $f === '..') {
		continue;
	}
	unlink ('cache/datastore/' . $f);
}
