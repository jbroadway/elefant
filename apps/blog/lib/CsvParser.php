<?php

namespace blog;

/**
 * Parses comma or tab-delimited files or strings. Can handle
 * quoted strings with commas inside them, and will remove quotes
 * from quoted strings as well as escaped quotes inside them.
 */
class CsvParser {
	/**
	 * The delimiter used, set by `determine_delimiter()`.
	 */
	public static $delimiter = ',';

	/**
	 * The error message if an error occurred.
	 */
	public static $error = false;

	/**
	 * Parses a string or file into an array.
	 */
	public static function parse ($str) {
		if (file_exists ($str)) {
			$data = file_get_contents ($str, FILE_IGNORE_NEW_LINES);
			if (strpos ($data, "\r\n") !== false) {
				// Windows
				$lines = explode ("\r\n", $data);
			} else {
				// Unix
				$lines = explode ("\n", $data);
			}
		} else {
			$lines = explode ("\n", $str);
		}

		if (count ($lines) === 0) {
			self::$error = 'Data is empty';
			return false;
		}

		self::determine_delimiter ($lines[0]);

		foreach ($lines as $n => $line) {
			$line = self::parse_line ($line);
			if ($line === false) {
				self::$error = 'Parsing error on line ' . $n;
				return false;
			}
			$lines[$n] = $line;
		}

		return $lines;
	}

	/**
	 * Determines the delimiter used, either comma or tab.
	 */
	public static function determine_delimiter ($line) {
		if (strpos ($line, "\t") !== false && strpos ($line, ',') === false) {
			self::$delimiter = "\t";
		} else {
			self::$delimiter = ',';
		}
		return self::$delimiter;
	}

	/**
	 * Parse a single line into an array. Uses the delimiter set in
	 * `determine_delimiter()`. If there is an error parsing the line,
	 * it will return false.
	 */
	public static function parse_line ($str) {
		$tmp = explode (self::$delimiter, $str);
		$fields = array ();

		$last = count ($tmp) - 1;
		for ($i = 0; $i <= $last; $i++) {
			if ($i > $last) {
				break;

			} elseif (strpos ($tmp[$i], '"') === false) {
				// ordinary field, no quotes
				$fields[] = $tmp[$i];

			} elseif (strpos ($tmp[$i], '"') === 0 && substr ($tmp[$i], -1, 1) === '"') {
				// remove escaped and outer quotes
				$tmp[$i] = str_replace ('""', '"', $tmp[$i]);
				$fields[] = substr ($tmp[$i], 1, -1);

			} elseif ($i < $last && substr ($tmp[$i + 1], -1, 1) === '"') {
				// field was split with an inner delimiter
				$fields[] = substr ($tmp[$i], 1) . self::$delimiter . substr ($tmp[$i + 1], 0, -1);

				// remove escaped quotes
				$fields[count ($fields) - 1] = str_replace ('""', '"', $fields[count ($fields) - 1]);
				$i++;

			} else {
				return false;
			}
		}
		return $fields;
	}
}

?>