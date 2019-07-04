<?php

/**
 * Get a value from the cache.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant get-cache <key>', 'info');
	die;
}

var_dump ($cache->get ($_SERVER['argv'][2]));
