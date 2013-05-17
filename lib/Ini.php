<?php

/**
 * A very simple class that abstracts reading/writing INI data from
 * strings and files.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // Parsing files or strings
 *     $data = Ini::parse ('ini_filename_or_string', true);
 *     
 *     // Write a data structure into an INI-formatted string
 *     $ini = Ini::write ($data);
 *     
 *     // Write a data structure to an INI-formatted file
 *     $res = Ini::write ($data, 'filename.ini');
 *     
 *     ?>
 */
class Ini {
	/**
	 * Parses an INI-formatted string or file. Just an alias
	 * for `parse_ini_file`/`parse_ini_string` that's here for
	 * completeness and consistency.
	 */
	public static function parse ($string, $sections = false, $scanner_mode = INI_SCANNER_NORMAL) {
		if (file_exists ($string)) {
			return parse_ini_file ($string, $sections, $scanner_mode);
		}
		return parse_ini_string ($string, $sections, $scanner_mode);
	}

	/**
	 * Write a data structure to an INI-formatted string or file.
	 * Adds "secure" comments to the start and end of the data so
	 * you can hide your INI data in files using a .php extension.
	 * If a `$header` is provided, it will add that as a comment to
	 * the top of the file.
	 */
	public static function write ($data, $file = false, $header = false) {
		$out = "; <?php /*\n";

		if ($header !== false) {
			$out .= ";\n; $header\n;\n";
		}

		$write_value = function ($value) {
			if (is_bool ($value)) {
				return ($value) ? 'On' : 'Off';
			} elseif ($value === '0' || $value === '') {
				return 'Off';
			} elseif ($value === '1') {
				return 'On';
			} elseif (preg_match ('/[^a-z0-9\/\.@<> _-]/i', $value)) {
				return '"' . str_replace ('"', '\"', $value) . '"';
			}
			return $value;
		};
	
		$sections = is_array ($data[current (array_keys ($data))]) ? true : false;
		if (! $sections) {
			$out .= "\n";
		}
	
		foreach ($data as $key => $value) {
			if (is_array ($value)) {
				$out .= "\n[$key]\n\n";
				foreach ($value as $k => $v) {
					if (is_array ($v)) {
						foreach ($v as $subkey => $val) {
							if (is_int ($subkey)) {
								$out .= str_pad ($k . '[]', 24) . '= ' . $write_value ($val) . "\n";
							} else {
								$out .= str_pad ($k . '[' . $subkey . ']', 24) . '= ' . $write_value ($val) . "\n";
							}
						}
					} else {
						$out .= str_pad ($k, 24) . '= ' . $write_value ($v) . "\n";
					}
				}
			} else {
				if (is_array ($value)) {
					foreach ($value as $subkey => $val) {
						if (is_int ($subkey)) {
							$out .= str_pad ($key . '[' . $subkey . ']', 24) . '= ' . $write_value ($val) . "\n";
						} else {
							$out .= str_pad ($key . '[]', 24) . '= ' . $write_value ($val) . "\n";
						}
					}
				} else {
					$out .= str_pad ($key, 24) . '= ' . $write_value ($value) . "\n";
				}
			}
		}
	
		$out .= "\n; */ ?>";
		if ($file === false) {
			return $out;
		}
		return file_put_contents ($file, $out);
	}
}

?>