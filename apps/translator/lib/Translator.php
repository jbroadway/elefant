<?php

/**
 * API for translator app.
 */
class Translator extends Restful {
	public $lang_hash = array ();

	/**
	 * Gets the translations for the specified list of index items.
	 */
	public function translations ($lang, $items) {
		if (file_exists ('lang/' . $lang . '.php')) {
			require ('lang/' . $lang . '.php');
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

	/**
	 * Get a list of all source files from the translation list.
	 */
	public static function get_sources ($items) {
		$sources = array ();
		foreach ($items as $k => $v) {
			$v['src'] = is_array ($v['src']) ? $v['src'] : array ($v['src']);
			foreach ($v['src'] as $src) {
				$sources[$src] = $src;
			}
		}
		asort ($sources);
		return $sources;
	}

	/**
	 * Get all strings in a specific source file.
	 */
	public static function get_by_source ($items, $source) {
		$out = array ();
		foreach ($items as $k => $v) {
			$v['src'] = is_array ($v['src']) ? $v['src'] : array ($v['src']);
			if (in_array ($source, $v['src'])) {
				$out[$k] = $v;
			}
		}
		return $out;
	}

	/**
	 * Get all strings with a specific text string.
	 */
	public static function get_by_search ($items, $contains) {
		$contains = strtolower ($contains);
		$out = array ();
		foreach ($items as $k => $v) {
			if (strpos (strtolower ($k), $contains) !== false || strpos (strtolower ($v['trans']), $contains) !== false) {
				$out[$k] = $v;
			}
		}
		return $out;
	}

	/**
	 * Write the data back to disk.
	 */
	private function write ($lang) {
		asort ($this->lang_hash[$lang]);

		$out = "<?php\n\n\$this->lang_hash['$lang'] = array (\n";
		$sep = '';
		foreach ($this->lang_hash[$lang] as $k => $v) {
			$out .= sprintf (
				"%s\t'%s' => '%s'",
				$sep,
				str_replace ('\'', '\\\'', stripslashes ($k)),
				str_replace ('\'', '&apos;', stripslashes ($v))
			);
			$sep = ",\n";
		}
		$out .= "\n);\n";

		if (! file_put_contents ('lang/' . $lang . '.php', $out)) {
			return false;
		}
		chmod ('lang/' . $lang . '.php', 0666);
		return true;
	}

	/**
	 * Handle save requests from edit screen.
	 */
	public function post_save () {
		if (file_exists ('lang/' . $_POST['lang'] . '.php')) {
			require ('lang/' . $_POST['lang'] . '.php');
		}

		$this->lang_hash[$_POST['lang']][$_POST['orig']] = $_POST['value'];

		if (! $this->write ($_POST['lang'])) {
			return $this->error ('Failed to save translation file.');
		}

		return $_POST;
	}

	/**
	 * Get the percentage that a translation has been completed.
	 */
	public function completed ($lang) {
		static $index = null;
		static $index_count = null;
		if ($index === null) {
			$index = unserialize (file_get_contents ('lang/_index.php'));
			$index_count = count ($index);
		}
		if ($index_count === 0) {
			return 0;
		}

		if (! is_array ($this->lang_hash[$lang]) && file_exists ('lang/' . $lang . '.php')) {
			require ('lang/' . $lang . '.php');
		}

		$count = 0;
		foreach ($index as $k => $v) {
			if (isset ($this->lang_hash[$lang][$k]) && ! empty ($this->lang_hash[$lang][$k])) {
				$count++;
			}
		}

		return ($count / $index_count) * 100;
	}
}
