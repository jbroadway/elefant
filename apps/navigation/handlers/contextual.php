<?php

$n = new Navigation;
$path = $n->path ($page->id);
$path = ($path) ? $path : array ();

require_once ('apps/navigation/lib/Functions.php');
navigation_print_context ($n->tree, $path);

?>