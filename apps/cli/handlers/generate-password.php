<?php

/**
 * Generates a random password of the specified length
 * (default is 8 characters), using random lower- and
 * upper-case letters, numbers, and symbols.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

require_once 'apps/cli/lib/Functions.php';

if (isset ($_SERVER['argv'][2])) {
	if (! is_numeric ($_SERVER['argv'][2])) {
		Cli::out ('Usage: elefant generate-password <length|8>', 'info');
		die;
	}
	$length = $_SERVER['argv'][2];
} else {
	$length = 8;
}

echo generate_password ($length) . "\n";

?>