<?php

/**
 * Provides the JSON API for the admin file manager/browser.
 */

$this->require_admin ();
$this->restful (new FileManager ());

?>