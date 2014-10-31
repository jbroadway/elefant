<?php

/**
 * Provides the JSON API for the admin file manager/browser.
 */

if (strpos ($_SERVER['REQUEST_URI'], '/filemanager/api/ls') === 0) {
	// do nothing
} elseif (strpos ($_SERVER['REQUEST_URI'], '/filemanager/api/dirs') === 0) {
	// do nothing
} elseif (! User::require_acl ('admin', 'filemanager')) {
	echo $this->restful_error (__ ('Forbidden'), 403);
	return;
}
$this->restful (new filemanager\API ());
