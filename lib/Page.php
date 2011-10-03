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
 *     $page->layout = false;
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
	var $head = '';

	/**
	 * Data to place just before the closing `</body>` tag.
	 * To use, add the following tag to your layouts:
	 *
	 *     {{ tail|none }}
	 */
	var $tail = '';

	/**
	 * The title of the page. To use, add something like this
	 * to your layouts:
	 *
	 *     {% if title %}<h1>{{ title }}</h1>{% end %}
	 */
	var $title = '';

	/**
	 * The main body of the page. To use, add the following tag
	 * to your layouts:
	 *
	 *     {{ body|none }}
	 */
	var $body = '';

	/**
	 * The layout template to use to render the page.
	 */
	var $layout = 'default';

	/**
	 * A list of scripts that have been added to the page via
	 * `add_script()`.
	 */
	var $scripts = array ();

	/**
	 * This is set to true when the template is currently rendering
	 * the page.
	 */
	var $is_being_rendered = false;

	/**
	 * This is set to true when Elefant is rendering a preview
	 * request.
	 */
	var $preview = false;

	/**
	 * Render the page in its template and layout.
	 */
	function render () {
		global $tpl;

		$this->menu_title = (! empty ($this->menu_title)) ? $this->menu_title : $this->title;
		$this->window_title = (! empty ($this->window_title)) ? $this->window_title : $this->title;

		if ($this->layout === '') {
			$this->layout = 'default';
		}
		if ($this->layout === 'default') {
			$this->layout = conf('General', 'default_layout');
		}
		if ($this->layout === false) {
			return $this->body;
		}
		if ($this->preview) {
			return $tpl->render_preview ($this->layout, $this);
		}
		return $tpl->render ($this->layout, $this);
	}

	/**
	 * Returns title for menu_title or window_title if they're empty.
	 */
	function __get ($key) {
		if ($key == 'menu_title') {
			return (! empty ($this->menu_title)) ? $this->menu_title : $this->title;
		} elseif ($key == 'window_title') {
			return (! empty ($this->window_title)) ? $this->window_title : $this->title;
		}
	}

	/**
	 * Add a script to the head or tail of a page, or echo immediately if the template
	 * rendering has already begun. Tracks duplicate additions so scripts will only
	 * be added once. This makes it a good replacement for adding script include tags
	 * to view templates.
	 */
	function add_script ($script, $add_to = 'head') {
		if (! in_array ($script, $this->scripts)) {
			$this->scripts[] = $script;
			if ($this->is_being_rendered) {
				echo $script;
			} elseif ($add_to == 'head') {
				$this->head .= $script;
			} elseif ($add_to == 'tail') {
				$this->tail .= $script;
			}
		}
	}
}

?>