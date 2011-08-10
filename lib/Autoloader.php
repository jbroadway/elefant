<?php

/**
 * Autoloader for classes. Checks in lib and models folders.
 */
function elefant_autoloader ($class) {
	if (strpos ($class, '\\') !== false) {
		list ($app, $class) = explode ('\\', $class, 2);
		if (@file_exists ('apps/' . $app . '/lib/' . $class . '.php')) {
			require_once ('apps/' . $app . '/lib/' . $class . '.php');
			return true;
		} elseif (@file_exists ('apps/' . $app . '/models/' . $class . '.php')) {
			require_once ('apps/' . $app . '/models/' . $class . '.php');
			return true;
		}
	} elseif (file_exists ('lib/' . $class . '.php')) {
		require_once ('lib/' . $class . '.php');
		return true;
	} else {
		$res = glob ('apps/*/{models,lib}/' . $class . '.php', GLOB_BRACE);
		if (is_array ($res) && count ($res) > 0) {
			require_once ($res[0]);
			return true;
		}
	}
	return false;
}

spl_autoload_register ('elefant_autoloader');

?>