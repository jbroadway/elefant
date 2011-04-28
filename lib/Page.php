<?php

/**
 * Basic document object used to contain the elements sent to
 * the template for rendering. You can add any values you want
 * to the object to shape your page output.
 *
 * The template property sets which template should be rendered
 * for the current request. The default is 'base' which renders
 * views/base.html. To skip a template altogether, simply use:
 *
 *   $page->template = false;
 *
 * The convention is to use the body property for the main body
 * content.
 */
class Page {
	var $head = '';
	var $title = '';
	var $body = '';
	var $template = 'base';

	function render () {
		if ($this->template == false) {
			return $this->body;
		}
		global $tpl;
		return $tpl->render ($this->template, $this);
	}
}

?>