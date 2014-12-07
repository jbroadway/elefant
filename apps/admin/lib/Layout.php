<?php

namespace admin;

/**
 * Layout-related methods.
 */
class Layout {
	public static $layouts = null;

	public static $sources = array (
		'layouts/*.html',
		'layouts/*/*.html'
	);

	/**
	 * Get a list of installed layouts/themes.
	 */
	public static function all () {
		if (self::$layouts !== null) {
			return self::$layouts;
		}

		foreach (self::$sources as $source) {
			$files = glob ($source);
			if ($files) {
				foreach ($files as $file) {
					if (preg_match ('/\/([^\/]+)\/([^\/]+)\.html$/', $file, $regs)) {
						if ($regs[1] === $regs[2]) {
							self::$layouts[] = $regs[1];
						} else {
							self::$layouts[] = $regs[1] . '/' . $regs[2];
						}
					} elseif (preg_match ('/\/([^\/]+)\.html$/', $file, $regs)) {
						self::$layouts[] = $regs[1];
					}
				}
			}
		}
		sort (self::$layouts);
		return self::$layouts;
	}

	/**
	 * Get the name of a theme from its elefant.json file,
	 * or default to `ucfirst()` of its folder name.
	 */
	public static function theme_name ($theme = false) {
		$theme = $theme ? $theme : conf ('General', 'default_layout');
		
		if (file_exists ('layouts/' . $theme . '/elefant.json')) {
			$info = json_decode (file_get_contents ('layouts/' . $theme . '/elefant.json'));
			if (isset ($info->name)) {
				return $info->name;
			}
		}
		return ucfirst ($theme);
	}

	/**
	 * Get a list of layout options and their names. Useful for
	 * layout selection in forms.
	 */
	public static function options () {
		$layout = conf ('General', 'default_layout');
		
		if ($layout === 'default') {
			$layouts = self::all ();
			$out = array ();
			foreach ($layouts as $layout) {
				$out[$layout] = ucfirst ($layout);
			}
			return $out;
		}

		if (file_exists ('layouts/' . $layout . '/elefant.json')) {
			$info = json_decode (file_get_contents ('layouts/' . $layout . '/elefant.json'));
			if (isset ($info->layouts) && is_object ($info->layouts)) {
				return array_merge (array ('default' => __ ('Default')), (array) $info->layouts);
			}
		}

		$files = glob ('layouts/' . $layout . '/*.html');
		$layouts = array ('default' => __ ('Default'));
		if ($files) {
			foreach ($files as $file) {
				$name = basename ($file, '.html');
				if ($name !== $layout) {
					$layouts[$layout . '/' . $name] = ucfirst ($name);
				}
			}
		}
		return $layouts;
	}

	/**
	 * Check whether a layout exists.
	 */
	public static function exists ($name) {
		if ($name === 'default') {
			return true;
		}
		return (file_exists ('layouts/' . $name . '.html') || file_exists ('layouts/' . $name . '/' . $name . '.html'));
	}
}
