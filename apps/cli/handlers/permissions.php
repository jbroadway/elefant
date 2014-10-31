<?php

/**
 * Set the filesystem permissions for your Elefant install.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

// set the necessary folder permissions
system ('chmod -R 777 cache conf css files lang layouts');
system ('chmod 777 apps');
