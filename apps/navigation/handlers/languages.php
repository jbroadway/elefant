<?php

/**
 * Displays the top-level navigation as a bulleted list
 * when `[I18n][multilingual]` is enabled, which will show
 * a list of languages linking to their homepages, with
 * `class="current"` added to the current page's `<li>`
 * element for custom styling.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/languages');
 *
 * In a template, call it like this:
 *
 *     {! navigation/languages !}
 *
 * Also available in the dynamic objects menu as "Navigation: Languages".
 */

if (! conf ('I18n', 'multilingual')) {
	return;
}

$n = Link::nav ();

echo '<ul>';
foreach ($n->tree as $item) {
	$_id = Tree::attr_id ($item);
	switch ($i18n->negotiation) {
		case 'http':
		case 'url':
			echo Link::single ($_id, $item->data ?? '');
			break;
		case 'cookie':
			echo Link::single (
				$_id,
				$item['data'] ?? '',
				'/navigation/cookie/' . $_id . '?redirect=/' . $_id
			);
			break;
		case 'subdomain':
			echo Link::single (
				$_id,
				$item['data'] ?? '',
				$this->is_https ()
					? 'https://' . $_id . '.' . Link::base_domain () . '/' . $_id
					: 'http://' . $_id . '.' . Link::base_domain () . '/' . $_id
			);
			break;
	}
}
echo '</ul>';
