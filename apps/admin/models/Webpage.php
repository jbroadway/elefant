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
	 * Override the getter for head to include the description
	 * and keywords fields as meta tags.
	 */
	public function __get ($key) {
		if ($key == 'head') {
			$head = '';
			if (isset ($this->data['description'])) {
				$head .= '<meta name="description" content="' . Template::sanitize ($this->data['description']) . "\" />\n";
			}
			if (isset ($this->data['keywords'])) {
				$head .= '<meta name="keywords" content="' . Template::sanitize ($this->data['keywords']) . "\" />\n";
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
