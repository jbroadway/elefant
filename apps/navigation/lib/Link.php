<?php

/**
 * Generate correct links for site navigation, based on the
 * various combinations of `page_url_style`, `multilingual`,
 * `negotiation_method`, and the currently active page.
 *
 * Usage:
 *
 *     <?php
 *     
 *     $nav = Link::nav ();
 *     
 *     echo '<ul>';
 *     foreach ($nav->tree as $item) {
 *         echo Link::single ($item->attr->id, $item->data);
 *     }
 *     echo '</ul>';
 *     
 *     ?>
 */
class Link {
	protected static $url_style = null;

	protected static $multilingual = null;

	protected static $negotiation_method = null;

	protected static $nav = null;

	protected static $i18n = null;

	protected static $current = null;

	protected static $active = null;

	/**
	 * Reset the internal state of the class.
	 */
	public static function reset () {
		self::$url_style = null;
		self::$multilingual = null;
		self::$negotiation_method = null;
		self::$nav = null;
		self::$i18n = null;
		self::$current = null;
		self::$active = null;
	}

	/**
	 * Gets or sets the URL style (nested or flat).
	 */
	public static function url_style ($url_style = null) {
		if ($url_style !== null) {
			self::$url_style = $url_style;
		}
		if (self::$url_style === null) {
			self::$url_style = conf ('General', 'page_url_style');
		}
		return self::$url_style;
	}

	/**
	 * Gets or sets whether the site is multilingual.
	 */
	public static function multilingual ($multilingual = null) {
		if ($multilingual !== null) {
			self::$multilingual = $multilingual;
		}
		if (self::$multilingual === null) {
			self::$multilingual = conf ('I18n', 'multilingual');
		}

		return self::$multilingual;
	}

	/**
	 * Gets or sets the language negotiation method.
	 */
	public static function negotiation_method ($negotiation_method = null) {
		if ($negotiation_method !== null) {
			self::$negotiation_method = $negotiation_method;
		}
		if (self::$negotiation_method === null) {
			self::$negotiation_method = conf ('I18n', 'negotiation_method');
		}
		return self::$negotiation_method;
	}

	/**
	 * Gets or sets the Navigation object.
	 */
	public static function nav ($nav = null) {
		if ($nav !== null) {
			self::$nav = $nav;
		}
		if (self::$nav === null) {
			self::$nav = new Navigation ();
		}
		return self::$nav;
	}

	/**
	 * Gets or sets the I18n object.
	 */
	public static function i18n ($i18n = null) {
		if ($i18n !== null) {
			self::$i18n = $i18n;
		}
		if (self::$i18n === null) {
			self::$i18n = $GLOBALS['i18n'];
		}
		return self::$i18n;
	}

	/**
	 * Gets or sets the current page ID. Also resets $active if it changes.
	 */
	public static function current ($current = null) {
		if ($current !== null) {
			self::$current = $current;
			self::$active = null;
		}
		if (self::$current === null) {
			self::$current = $GLOBALS['page']->id;
			self::$active = null;
		}
		return self::$current;
	}

	/**
	 * Gets a list of parent page IDs in order to add class="active" to them.
	 */
	public static function active () {
		if (self::$active === null) {
			$nav = self::nav ();
			self::$active = $nav->path (self::current ());
			if (! is_array (self::$active)) {
				self::$active = array ();
			}
		}
		return self::$active;
	}

	/**
	 * Generates a URL for a page ID.
	 */
	public static function href ($id) {
		// Use $prefix only if negotiation_method is 'url' and
		// page_url_style is not 'nested', otherwise it's not needed
		if (self::negotiation_method () === 'url' && self::url_style () !== 'nested') {
			$i18n = self::i18n ();
			$prefix = $i18n->prefix;
			if (! empty ($prefix) && in_array ($id, array_keys ($i18n->languages))) {
				// Prevent /en/en or /en/fr links
				$prefix = '';
			}
		} else {
			$prefix = '';
		}

		// Replace $id with nested URL if page_url_style is 'nested'
		if (self::url_style () === 'nested') {
			$nav = self::nav ();
			$path = $nav->path ($id);
			if (is_array ($path)) {
				$id = join ('/', $path);
			}
		}

		// Render and return
		return $prefix . '/' . $id;		
	}

	/**
	 * Generates a link tag for the specified page ID and title.
	 */
	public static function make ($id, $title) {
		return sprintf (
			'<a href="%s">%s</a>',
			self::href ($id),
			$title
		);
	}

	/**
	 * Generates a single navigation link as a list item.
	 */
	public static function single ($id, $title) {
		// Set class="current" if page is current
		$current = (self::current () === $id) ? ' class="current"' : '';

		// Set class="active" if page is parent of current
		if (empty ($current)) {
			$current = in_array ($id, self::active ()) ? ' class="active"' : $current;
		}

		return sprintf (
			"<li%s>%s</li>\n",
			$current,
			self::make ($id, $title)
		);
	}
}

?>