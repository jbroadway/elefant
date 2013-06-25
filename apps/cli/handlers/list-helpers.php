<?php

/**
 * List all handlers found in their app's handlers/util folder,
 * which are meant to be reused by other app developers.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

$files = glob ('apps/*/handlers/util/*.php');

foreach ($files as $file) {
	preg_match ('/apps\/(.*)\/handlers\/util\/(.*)\.php$/', $file, $regs);
	printf ("%s/util/%s\n", $regs[1], $regs[2]);
}

?>