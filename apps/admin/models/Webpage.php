<?php

/**
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
 */
class Webpage extends Model {
	function __get ($key) {
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
}

?>