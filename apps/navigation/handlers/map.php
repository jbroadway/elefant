<?php

/**
 * Displays a complete site map as a multi-level
 * bulleted list.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/map');
 *
 * In a template, call it like this:
 *
 *     {! navigation/map !}
 *
 * Also available in the dynamic objects menu as "Navigation: Site Map".
 */

$n = Link::nav ();

require_once ('apps/navigation/lib/Functions.php');

if (conf ('I18n', 'multilingual')) {
	$section = $n->node ($i18n->language);
	if (is_array ($section['children'])) {
		navigation_print_level ($section['children']);
	}
} else {
	navigation_print_level ($n->tree);
}
