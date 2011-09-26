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
 * the template for rendering. You can add any values you want
 * to the object to shape your page output.
 *
 * The template property sets which template should be rendered
 * for the current request. The default is empty, which simply
 * outputs the page body, which is then passed to
 * layouts/default.html to render the overall layout, unless you
 * also specify:
 *
 *     $page->layout = false;
 *
 * To skip a template altogether for things like JSON, use:
 *
 *     $page->template = false;
 *
 * This will skip both the template and the layout and simply
 * return the page body to the user.
 *
 * The convention is to use the body property for the main body
 * content.
 */
class Page {
	var $head = '';
	var $tail = '';
	var $title = '';
	var $body = '';
	var $template = '';
	var $layout = 'default';
	var $scripts = array ();
	var $is_being_rendered = false;
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
		if ($this->template === false) {
			return $this->body;
		} elseif (! empty ($this->template)) {
			$this->body = $tpl->render ($this->template, $this);
		}
		if ($this->layout) {
			if ($this->preview) {
				return $tpl->render_preview ($this->layout, $this);
			}
			return $tpl->render ($this->layout, $this);
		}
		return $this->body;
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