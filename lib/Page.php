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
	 * If set, add the specified value as a `?v=' to the end of scripts and stylesheets
	 * that use a relative absolute path and end in `.js` or `.css` such as:
	 *
	 *     /apps/myapp/js/myscript.js -> /apps/myapp/js/myscript.js?v=123
	 *     /apps/myapp/css/style.css -> /apps/myapp/css/style.css?v=123
	 *
	 * Should not affect scripts or stylesheets of these forms:
	 *
	 *     <script>...</script>
	 *     <link rel="stylesheet" href="..." />
	 *     https://www.google.com/script.js
	 *     //www.google.com/script.js?abc=123
	 *
	 * This prevents it from altering external links including protocol-relative links,
	 * and links which are being passed parameters already.
	 */
	public static $assets_version = false;

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
	 * Set this to true if you wish to globally bypass all templates.
	 * Primarily used by the async utility.
	 */
	public static $bypass_layout = false;

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
		if ($this->layout === false || self::$bypass_layout) {
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
		} elseif ($this->layout === 'admin') {
			$admin_layout = Product::admin_layout ();
			$this->layout = $admin_layout ? $admin_layout : 'admin';
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
	public function add_script ($script, $add_to = 'head', $type = '') {
		$script = self::wrap_script ($script, $type);

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
	public function add_style ($script, $add_to = 'head', $type = '') {
		return $this->add_script ($script, $add_to, $type);
	}

	/**
	 * Add a meta tag to the page. Example usage:
	 *
	 *     $page->add_meta ('keywords', 'One, Two, Three');
	 *     $page->add_meta ('UTF-8', '', 'charset');
	 *     $page->add_meta ('og:image', 'http://example.com/foo.jpg', 'property');
	 *     $page->add_meta ('refresh', '30;url=http://example.com/', 'http-equiv');
	 *     $page->add_meta ('<meta charset="utf-8" />');
	 */
	public function add_meta ($name, $content = '', $attr = 'name') {
		if (strpos ($name, '<') === 0) {
			$script = trim ($name) . "\n";
		} else {
			$script = '<meta ' . $attr . '="' . $name . '"'
				. (($content !== '') ? ' content="' . Template::quotes ($content) . '"' : '')
				. " />\n";
		}
		
		if (! in_array ($script, $this->scripts)) {
			$this->scripts[] = $script;
			
			if (! $this->is_being_rendered) {
				$this->head .= $script;
			}
		}
	}

	/**
	 * Wrap scripts that are simply URLs in the correct HTML tags,
	 * including `<link>` tags for CSS files. Will pass through
	 * on scripts that are already HTML.
	 */
	public static function wrap_script ($script, $type = '') {
		if (strpos ($script, '<') === 0) {
			return $script;
		}
		if ($type !== '') {
			$type = ' type="' . $type . '"';
		}
		if (preg_match ('/\.css$/i', $script) || strpos ($script, '.css?') !== false) {
			return '<link rel="stylesheet"' . $type . ' href="' . self::assets_version ($script) . "\" />\n";
		}
		return '<script' . $type . ' src="' . self::assets_version ($script) . "\"></script>\n";
	}

	/**
	 * Returns a script with `'?v='.Page::$assets_version` appended if
	 * `Page::$assets_version` is set and the script is a relative
	 * absolute path ending in `.js` or `.css`, otherwise it returns the script
	 * unchanged. If the script is empty, it will return 
	 */	
	public static function assets_version ($script = '') {
		if (! self::$assets_version) {
			return $script;
		}

		if ($script !== '') {
			if (! preg_match ('/\.(js|css)$/i', $script)) {
				return $script; // make sure script ends in .js or .css
			}

			if (strpos ($script, '/') !== 0) {
				return $script; // make sure script begins with /
			}
		
			if (preg_match ('/^\/\//', $script)) {
				return $script; // ignore protocol-relative URLs beginning with //
			}
		}

		return $script . '?v=' . self::$assets_version;
	}
}
