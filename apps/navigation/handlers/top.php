<?php

/**
 * Displays the top-level navigation as a bulleted list,
 * with `class="current"` added to the current page's
 * `<li>` element for custom styling.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/breadcrumb');
 *
 * In a template, call it like this:
 *
 *     {! navigation/breadcrumb !}
 *
 * Also available in the dynamic objects menu as "Navigation: Top Level".
 */

if (conf ('I18n', 'multilingual')) {
	echo $this->run ('navigation/section', array ('section' => $i18n->language));
	return;
}

$n = Link::nav ();

echo '<ul>';
foreach ($n->tree as $item) {
	echo Link::single ($item->attr->id, $item->data);
}
echo '</ul>';
