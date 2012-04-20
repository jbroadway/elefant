<?php

/**
 * The default error handler. Takes an error `code` (e.g., `404`),
 * `title` string, and `message` string.
 *
 * If the `install` directory is present, and the file
 * `install/installed` does not exist, then it instead
 * forwards to `/install` to run the web installer.
 */

if ($this->data['code'] == 404 && @file_exists ('install') && (! @file_exists ('conf/installed') && ! @file_exists ('install/installed'))) {
	$this->redirect ('install/');
}

header ('HTTP/1.1 ' . $this->data['code'] . ' ' . $this->data['title']);

$page->title = $this->data['title'];

if (! empty ($this->data['message'])) {
	echo $this->data['message'];
}

?>