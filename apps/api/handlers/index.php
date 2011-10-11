<?php

/**
 * Default handler, simply forwards to the current version of the API.
 */

$page->layout = false;

header ('Location: /api/' . $appconf['Api']['current_version']);
exit;

?>