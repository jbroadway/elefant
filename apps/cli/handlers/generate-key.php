<?php

/**
 * Generates an MD5 key for use in the site_key setting.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

echo md5 (uniqid (rand (), true)) . "\n";
