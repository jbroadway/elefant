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
 * Basic document object used to contain the elements sent to
 * the layout template for rendering. You can add any values
 * you want to the object to shape your page output.
 *
 * The layout property sets which template should be used to
 * render the design of the page, unless you specify:
 *
 *     <?php
 *     
 *     $page->layout = false;
 *     
 *     ?>
 *
 * This will skip the layout and simply return the page body
 * to the user.
 *
 * The convention is to use the body property for the main body
 * content.
 */
class Page {
	/**
	 * Data to place in the `<head>` of the document. To use,
	 * add the following tag to your layouts:
	 *
	 *     {{ head|none }}
	 */
	public $head = '';

	/**
	 * Data to place just before the closing `</body>` tag.
	 * To use, add the following tag to your layouts:
	 *
	 *     {{ tail|none }}
	 */
	public $tail = '';

	/**
	 * The title of the page. To use, add something like this
	 * to your layouts:
	 *
	 *     {% if title %}<h1>{{ title }}</h1>{% end %}
	 */
	public $title = '';

	/**
	 * An optional separate title to be used in navigation.
	 */
	public $_menu_title = '';

	/**
	 * An optional separate title to be used in the page's
	 * `<title>` tag.
	 */
	public $_window_title = '';

	/**
	 * The main body of the page. To use, add the following tag
	 * to your layouts:
	 *
	 *     {{ body|none }}
	 */
	public $body = '';

	/**
	 * The layout template to use to render the page.
	 */
	public $layout = 'default';

	/**
	 * A list of scripts that have been added to the page via
	 * `add_script()`.
	 */
	public $scripts = array ();

	/**
	 * This is set to true when the template is currently rendering
	 * the page.
	 */
	public $is_being_rendered = false;

	/**
	 * Control caching policy for this page
	 */
	public $cache_control = false;

	/**
	 * This is set to true when Elefant is rendering a preview
	 * request.
	 */
	public $preview = false;

	/**
	 * Constructor method
	 */
	public function __construct () {
		$method = isset ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : $_SERVER['REQUEST_METHOD'];
		$cacheable = array ('GET', 'HEAD');

		if (in_array ($method, $cacheable) ) {
			// Enable cache control only for GET and HEAD methods
			$this->cache_control = true;
		}
	}

	/**
	 * Render the page in its template and layout. Uses a Template
	 * object for rendering. Determines whether to render with a
	 * layout template at all, and if so, which one. Also determines
	 * whether to render as a preview or as a real page.
	 */
	public function render ($tpl, $controller) {
		if ($this->layout === false) {
			// No layout, return the body as-is
			return $this->body;
		}

		// Set default menu and window titles if they're empty
		$this->_menu_title = (! empty ($this->_menu_title)) ? $this->_menu_title : $this->title;
		$this->_window_title = (! empty ($this->_window_title)) ? $this->_window_title : $this->title;

		// No layout, use default
		if ($this->layout === '') {
			$this->layout = 'default';
		}

		// Fetch the default layout setting
		if ($this->layout === 'default') {
			$this->layout = conf ('General', 'default_layout');
		}

		// Determine render method (preview or real)
		if ($this->preview) {
			$out = $tpl->render_preview ($this->layout, $this);
			$res = $controller->hook ('page/render', array ('html' => $out));
			return ($res) ? $res : $out;
		}
		$out = $tpl->render ($this->layout, $this);
		$res = $controller->hook ('page/render', array ('html' => $out));
		return ($res) ? $res : $out;
	}

	/**
	 * Returns title for menu_title or window_title if they're empty.
	 */
	public function __get ($key) {
		if ($key === 'menu_title') {
			return (! empty ($this->_menu_title)) ? $this->_menu_title : $this->title;
		} elseif ($key === 'window_title') {
			return (! empty ($this->_window_title)) ? $this->_window_title : $this->title;
		}
	}

	/**
	 * Add a script to the head or tail of a page, or echo immediately if the template
	 * rendering has already begun. Tracks duplicate additions so scripts will only
	 * be added once. This makes it a good replacement for adding script include tags
	 * to view templates.
	 */
	public function add_script ($script, $add_to = 'head') {
		$script = Page::wrap_script ($script);

		if (! in_array ($script, $this->scripts)) {
			$this->scripts[] = $script;

			if ($this->is_being_rendered) {
				echo $script;
			} elseif ($add_to === 'head') {
				$this->head .= $script;
			} elseif ($add_to === 'tail') {
				$this->tail .= $script;
			}
		}
	}

	/**
	 * Add a style to the page. Simply an alias of `add_script()` for the sake of
	 * referring to stylesheets correctly as styles and not scripts, but functionally
	 * they both do the same thing, and `add_script()` always handled stylesheets
	 * as well.
	 */
	public function add_style ($script, $add_to = 'head') {
		return $this->add_script ($script, $add_to);
	}

	/**
	 * Wrap scripts that are simply URLs in the correct HTML tags,
	 * including `<link>` tags for CSS files. Will pass through
	 * on scripts that are already HTML.
	 */
	public static function wrap_script ($script) {
		if (strpos ($script, '<') === 0) {
			return $script;
		}
		if (preg_match ('/\.css$/i', $script)) {
			return '<link rel="stylesheet" href="' . $script . "\" />\n";
		}
		return '<script src="' . $script . "\"></script>\n";
	}
}

?>