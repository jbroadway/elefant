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
	return preg_replace ('/[^a-z0-9_-]+/', '-', strtolower ($text));
}

/**
 * Writes the INI output, for modifying the lang/languages.php file.
 */
function translator_ini_write ($data) {
	$out = "; <?php /*\n";

	$write_value = function ($value) {
		if (is_bool ($value)) {
			return ($value) ? 'On' : 'Off';
		} elseif ($value === '0' || $value === '') {
			return 'Off';
		} elseif ($value === '1') {
			return 'On';
		} elseif (preg_match ('/[^a-z0-9\/\.@<> _-]/i', $value)) {
			return '"' . $value . '"';
		}
		return $value;
	};

	$sections = is_array ($data[array_shift (array_keys ($data))]) ? true : false;

	foreach ($data as $key => $value) {
		if (is_array ($value)) {
			$out .= "\n[$key]\n\n";
			foreach ($value as $k => $v) {
				$out .= str_pad ($k, 24) . '= ' . $write_value ($v) . "\n";
			}
		} else {
			$out .= str_pad ($key, 24) . '= ' . $write_value ($value) . "\n";
		}
	}

	return $out . "\n; */ ?>";
}

?>