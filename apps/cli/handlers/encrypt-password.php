<?php

/**
 * Encrypts the specified password in a compatible format
 * for storage in the Elefant user table.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	echo "Usage: elefant encrypt-password <password>\n";
	return;
}

echo User::encrypt_pass ($_SERVER['argv'][2]) . "\n";

?>