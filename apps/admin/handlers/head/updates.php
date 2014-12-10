<?php

/**
 * Fetches the latest updates from the elefant-updates repository and
 * returns the output with content type application/javascript for use
 * with JSONP callback.
 */

$page->layout = false;

header ('Content-Type: application/javascript');

echo fetch_url ('https://raw.githubusercontent.com/jbroadway/elefant-updates/master/releases/' . $this->params[0] . '.js');
