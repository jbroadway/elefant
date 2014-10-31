<?php

/**
 * Displays a multi-level bulleted list for the app/navigation/admin.
 */

$n = new Navigation;

require_once ('apps/navigation/lib/Functions.php');
navigation_print_admin_tree ($n->tree);

//$this->cache = true;
