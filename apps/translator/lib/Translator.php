<?php

/**
 * Gets the translations for the specified list of index items.
 */
class Translator {
	public $lang_hash = array ();

	function getTranslations ($lang, $items) {
		if (file_exists ('lang/' . $lang . '.php')) {
			require_once ('lang/' . $lang . '.php');
		}

		foreach ($items as $k => $v) {
			if (isset ($this->lang_hash[$lang][$k])) {
				$items[$k]['trans'] = $this->lang_hash[$lang][$k];
			} else {
				$items[$k]['trans'] = '';
			}
		}
		return $items;
	}
}

?>