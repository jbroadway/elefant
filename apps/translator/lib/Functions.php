<?php

/**
 * Checks if a language exists.
 */
function translator_lang_exists ($lang) {
	global $i18n;
	
	if (! empty ($_POST['locale'])) {
		$lang = $_POST['code'] . '_' . $_POST['locale'];
	} else {
		$lang = $_POST['code'];
	}
	
	if (isset ($i18n->languages[$lang])) {
		return false;
	}
	return true;
}

/**
 * Sorts languages by name.
 */
function translator_sort_languages ($a, $b) {
	if ($a['name'] === $b['name']) {
		return 0;
	}
	return ($a['name'] < $b['name']) ? -1 : 1;
}

/**
 * Generates an id for the edit page HTML elements
 * from a translatable string.
 */
function translator_field_id ($text) {
	return URLify::filter ($text);
}

/**
 * Filter wrapper around Translator::completed().
 */
function translator_completed ($lang) {
	$t = new Translator;
	return $t->completed ($lang);
}

?>