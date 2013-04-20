<?php

/**
 * Provides the JSON API for the admin file manager/browser.
 */

$this->require_acl ('admin', 'filemanager');
$this->restful (new filemanager\API ());

?>