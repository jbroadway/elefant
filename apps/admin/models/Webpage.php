<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Model for web pages.
 *
 * Fields:
 *
 * id
 * title
 * menu_title
 * window_title
 * weight
 * head (virtual, for description/keyword inclusion)
 * layout
 * description
 * keywords
 * body
 * extra
 */
class Webpage extends ExtendedModel {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#webpage';

	/**
	 * The `extra` field can contain an arbitrary number of additional
	 * user-defined properties.
	 */
	public $_extended_field = 'extra';
	
	/**
	 * Display name for this model type.
	 */
	public static $display_name = 'Web Page';
	
	/**
	 * Plural display name for this model type.
	 */
	public static $plural_name = 'Web Pages';
	
	/**
	 * Link format for version history.
	 */
	public static $versions_link = '/admin/edit?page={{id}}';

	/**
	 * Fields to display as links in version history.
	 */
	public static $versions_display_fields = [
		'title' => 'Title'
	];

	/**
	 * Override the getter for head to include the description
	 * and keywords fields as meta tags.
	 */
	public function __get ($key) {
		if ($key == 'head') {
			$head = '';
			
			$twitter_id = Appconf::user ('Twitter', 'twitter_id');
			if (is_string ($twitter_id) && $twitter_id !== '') {
				$head .= '<meta property="twitter:site" content="@' . $twitter_id . "\" />\n";
			}
			
			$head .= '<meta property="og:url" content="' . $GLOBALS['controller']->absolutize ($this->data['id']) . "\" />\n";
			$head .= '<meta property="og:site_name" content="' . Template::sanitize (conf ('General', 'site_name')) . "\" />\n";
			$head .= '<meta property="og:type" content="article" />' . "\n";
			
			if (isset ($this->data['window_title']) && $this->data['window_title'] !== '') {
				$head .= '<meta property="og:title" content="' . Template::sanitize ($this->data['window_title']) . "\" />\n";
				$head .= '<meta property="twitter:title" content="' . Template::sanitize ($this->data['window_title']) . "\" />\n";
			} else {
				$head .= '<meta property="og:title" content="' . Template::sanitize ($this->data['title']) . "\" />\n";
				$head .= '<meta property="twitter:title" content="' . Template::sanitize ($this->data['title']) . "\" />\n";
			}

			if (isset ($this->data['description'])) {
				$head .= '<meta name="description" content="' . Template::sanitize ($this->data['description']) . "\" />\n";
				$head .= '<meta property="og:description" content="' . Template::sanitize ($this->data['description']) . "\" />\n";
				$head .= '<meta property="twitter:description" content="' . Template::sanitize ($this->data['description']) . "\" />\n";
			}

			if (isset ($this->data['keywords'])) {
				$head .= '<meta name="keywords" content="' . Template::sanitize ($this->data['keywords']) . "\" />\n";
			}
			
			if (! isset ($this->data['thumbnail']) || $this->data['thumbnail'] == '') {
				$this->data['thumbnail'] = conf ('General', 'default_thumbnail');
			}

			if (isset ($this->data['thumbnail']) && $this->data['thumbnail'] != '') {
	
				list ($width, $height) = getimagesize (substr ($this->data['thumbnail'], 1));
	
				$head .= '<meta property="og:image:width" content="' . $width . "\" />\n";
				$head .= '<meta property="og:image:height" content="' . $height . "\" />\n";

				$link = $GLOBALS['controller']->absolutize (str_replace (' ', '%20', $this->data['thumbnail']));

				$head .= '<meta property="twitter:card" content="summary_large_image" />' . "\n";
				$head .= '<meta property="og:image" content="' . Template::sanitize ($link) . "\" />\n";
				$head .= '<meta property="twitter:image" content="' . Template::sanitize ($link) . "\" />\n";
			}
			return $head;
		}
		return parent::__get ($key);
	}

	/**
	 * Generate a list of pages for the sitemaps app.
	 */
	public static function sitemap () {
		$pages = self::query ()
			->where ('access', 'public')
			->fetch_orig ();
		
		$urls = array ();
		foreach ($pages as $page) {
			$urls[] = Link::href ($page->id);
		}
		return $urls;
	}

	/**
	 * Generate a list of pages for the search app,
	 * and add them directly via `Search::add()`.
	 */
	public static function search () {
		$pages = self::query ()
			->where ('access', 'public')
			->fetch_orig ();
		
		foreach ($pages as $i => $page) {
			if (! Search::add (
				$page->id,
				array (
					'title' => $page->title,
					'text' => $page->body,
					'url' => Link::href ($page->id)
				)
			)) {
				return array (false, $i);
			}
		}
		return array (true, count ($pages));
	}
}
