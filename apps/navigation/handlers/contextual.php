<?php

/**
 * Displays contextual navigation, opening and closing
 * sections based on the currently active page. Shows
 * All parents and children of the current page.
 */

$n = new Navigation;
$path = $n->path ($page->id);
$path = ($path) ? $path : array ();

require_once ('apps/navigation/lib/Functions.php');
navigation_print_context ($n->tree, $path);

?>