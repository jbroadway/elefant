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
				str_replace ('\'', '\\\'', $k),
				str_replace ('\'', '\\\'', $v)
			);
			$sep = ",\r";
		}
		$out .= "\n);\n\n?>";

		if (! file_put_contents ('lang/' . $lang . '.php', $out)) {
			return false;
		}
		chmod ('lang/' . $lang . '.php', 0777);
		return true;
	}

	/**
	 * Handle save requests from edit screen.
	 */
	public function post_save () {
		error_log ($_POST['lang']);
		error_log ($_POST['orig']);
		error_log ($_POST['value']);
		if (file_exists ('lang/' . $_POST['lang'] . '.php')) {
			require_once ('lang/' . $_POST['lang'] . '.php');
		}

		$this->lang_hash[$_POST['lang']][$_POST['orig']] = $_POST['value'];

		if (! $this->write ($_POST['lang'])) {
			return $this->error ('Failed to save translation file.');
		}

		return $_POST;
	}
}

?>