<?php

/**
 * Displays the top-level navigation as a bulleted list
 * when [I18n][multilingual] is enabled, which will show
 * a list of languages linking to their homepages, with
 * `class="current"` added to the current page's `<li>`
 * element for custom styling.
 */

if (! conf ('I18n', 'multilingual')) {
	return;
}

$n = Link::nav ();

echo '<ul>';
foreach ($n->tree as $item) {
	switch ($i18n->negotiation) {
		case 'http':
		case 'url':
			echo Link::single ($item->attr->id, $item->data);
			break;
		case 'cookie':
			echo Link::single (
				$item->attr->id,
				$item->data,
				'/navigation/cookie/' . $item->attr->id . '?redirect=/' . $item->attr->id
			);
			break;
		case 'subdomain':
			echo Link::single (
				$item->attr->id,
				$item->data,
				$this->is_https ()
					? 'https://' . $item->attr->id . '.' . Link::base_domain () . '/' . $item->attr->id
					: 'http://' . $item->attr->id . '.' . Link::base_domain () . '/' . $item->attr->id
			);
			break;
	}
}
echo '</ul>';

?>