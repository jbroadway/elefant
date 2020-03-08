<?php

/**
 * Invalidate the specified API token so that it stops working.
 */

if (! $this->cli) die ("Must be run from the command line.\n");

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant api/expire-token <token-id>', 'info');
	return;
}

$a = new api\Api ($_SERVER['argv'][2]);

if ($a->error) {
	Cli::out ('Error: Token not found.', 'error');
	return;
}

$a->valid = 'no';

if (! $a->put ()) {
	Cli::out ('Error: ' . $a->error, 'error');
	return;
}

Cli::out ('Token expired.');
