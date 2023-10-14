<?php

/**
 * Displays a single section of the navigation as a
 * bulleted list, with `class="current"` added to
 * the current page's `<li>` element for custom styling.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/section', array ('section' => 'page-id'));
 *
 * In a template, call it like this:
 *
 *     {! navigation/languages?section=page-id !}
 *
 * To pass a dynamic value to `section` in a template:
 *
 *     {! navigation/languages?section=[id] !}
 *
 * This passes the template's `$data->id` as the `section` value.
 *
 * Also available in the dynamic objects menu as "Navigation: Section".
 */

$n = Link::nav ();
$section = $n->node ($data->section);

if (isset ($section->children) && is_array ($section->children)) {
	echo '<ul>';
	foreach ($section->children as $item) {
		echo Link::single (Tree::attr_id ($item), $item->data);
	}
	echo '</ul>';
}
