<?php

/**
 * Output the Elefant version number.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

echo ELEFANT_VERSION . "\n";
