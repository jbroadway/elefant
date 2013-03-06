<?php

/**
 * Displays the top-level navigation as a bulleted list,
 * with `class="current"` added to the current page's
 * `<li>` element for custom styling.
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

?>