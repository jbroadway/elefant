<?php

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
 *   $page->layout = false;
 *
 * To skip a template altogether for things like JSON, use:
 *
 *   $page->template = false;
 *
 * This will skip both the template and the layout and simply
 * return the page body to the user.
 *
 * The convention is to use the body property for the main body
 * content.
 */
class Page {
	var $head = '';
	var $title = '';
	var $body = '';
	var $template = '';
	var $layout = 'default';
	var $scripts = array ();

	function render () {
		global $tpl;

		if ($this->layout === '') {
			$this->layout = 'default';
		}
		if ($this->template === false) {
			return $this->body;
		} elseif (! empty ($this->template)) {
			$this->body = $tpl->render ($this->template, $this);
		}
		if ($this->layout) {
			return $tpl->render ($this->layout, $this);
		}
		return $this->body;
	}

	function __get ($key) {
		if ($key == 'menu_title') {
			return (! empty ($this->menu_title)) ? $this->menu_title : $this->title;
		} elseif ($key == 'window_title') {
			return (! empty ($this->window_title)) ? $this->window_title : $this->title;
		}
	}

	/**
	 * Add a script to the header. Tracks duplicate additions so scripts will only
	 * be added once.
	 */
	function add_script ($script) {
		if (! in_array ($script, $this->scripts)) {
			$this->scripts[] = $script;
			$this->head .= $script;
		}
	}
}

?>