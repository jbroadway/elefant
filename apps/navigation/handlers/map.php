<?php

/**
 * Displays a complete site map as a multi-level
 * bulleted list.
 */

$n = new Navigation;

require_once ('apps/navigation/lib/Functions.php');
navigation_print_level ($n->tree);

$this->cache = true;

?>