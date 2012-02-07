<?php

/**
 * Default handler, simply forwards to the current version of the API.
 */

$this->redirect ('/api/' . $appconf['Api']['current_version']);

?>