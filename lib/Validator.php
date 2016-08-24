<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Provides flexible input validation for form and model input. Also see
 * the `Form` class for examples on implementing matching client-side
 * validation through Elefant's `jquery.verify_values.js` jQuery plugin.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // single value
 *     if (! Validator::validate ($_POST['email'], 'email')) {
 *         // failed
 *     }
 *
 *     // list of values
 *     $failed = Validator::validate_list (
 *         $_POST,
 *         'apps/myapp/forms/myform.php'
 *     );
 *
 *     if (count ($failed) > 0) {
 *         // failed
 *     }
 */
class Validator {
	/**
	 * The full details of which rules failed in a call to
	 * `Validator::validate_list()`.
	 */
	public static $invalid = array ();
	
	/**
	 * Verifies the specified value, useful for input validation.
	 * Pass the value, a type of validation, and a validator.
	 * Types include:
	 *
	 * - `skip_if_empty` - a special verifier that tells `validate_list()` to skip
	 *                   validation on the field if it's been left blank.
	 * - `file` - a special verifier that tells `validate_list()` to check if it's
	 *          a valid uploaded file.
	 * - `filetype` - a special verifier that tells `validate_list()` to check if the
	 *              file name contains one of a list of comma-separated extensions.
	 * - `regex` - calls `preg_match($validator, $value)`
	 * - `type` - calls `is_$validator($value)`
	 * - `callback` - calls `call_user_func($validator, $value)`
	 * - `email` - a valid email address
	 * - `url` - a valid url
	 * - `range` - number within a range e.g., `123-456`
	 * - `length` - string of length, $verifier examples: `6, 6+, 6-12, 12-`
	 * - `gt` - greater than
	 * - `gte` - greater than or equal to
	 * - `lt` - less than
	 * - `lte` - less than or equal to
	 * - `empty` - value is empty
	 * - `not empty` - value is not empty
	 * - `contains` - `stristr($value, $validator)`
	 * - `equals` - equality test
	 * - `date` - date value (`YYYY-MM-DD`)
	 * - `time` - time value (`HH:MM:SS`)
	 * - `datetime` - date and time value (`YYYY-MM-DD HH:MM:SS`)
	 * - `header` - verifies there are no newlines so spammers can't pass headers to `mail()`
	 * - `unique` - verifies it's unique to a table and column in the database,
	 *            `$verifier` should be `'table_name.column_name'`
	 * - `exists` - verifies that a file exists in the specified directory,
	 *            `$verifier` should be a directory path with no trailing /,
	 *            or optionally a file path with `%s` in it for the form value.
	 * - `matches` - Matches another variable, e.g., `"$_POST['name']"`, must be a
	 *             global or superglobal.
	 *
	 * Functions must accept only the value of the variable and return
	 * a boolean value.
	 *
	 * You can also specify 'not' in front of any rule to check for its
	 * opposite, for example "not empty".
	 *
	 * For array elements (e.g., `<input name="name[]" />`), you can also specify
	 * 'each' in front of any rule and the rule will be applied to each element
	 * of the array instead of the array itself. Note that the 'each' must come
	 * before 'not', for example "each email" would make sure each is a valid
	 * email address, and "each not empty" would make sure each is not empty.
	 */
	public static function validate ($value, $type, $validator = false) {
		if ($type === 'default') {
			return true;
		}
		if (preg_match ('/^each (.+)$/i', $type, $regs)) {
			if (! is_array ($value)) {
				// can't specify each on non-array value
				return false;
			}
			foreach ($value as $val) {
				if (! Validator::validate ($val, $regs[1], $validator)) {
					return false;
				}
			}
			return true;
		}
		if (preg_match ('/^not (.+)$/i', $type, $regs)) {
			return ! Validator::validate ($value, $regs[1], $validator);
		}
		switch ($type) {
			case 'matches':
				if (preg_match ('/\$(_[a-z]+|GLOBALS)\[[\'"]?([a-z0-9_-]+)[\'"]?\]/i', $validator, $regs)) {
					// Can't dynamically reference superglobals, so instead...
					switch ($regs[1]) {
						case '_POST':
							return ($value === $_POST[$regs[2]]);
						case '_GET':
							return ($value === $_GET[$regs[2]]);
						case '_REQUEST':
							return ($value === $_REQUEST[$regs[2]]);
						case '_SERVER':
							return ($value === $_SERVER[$regs[2]]);
						case '_FILES':
							return ($value === $_FILES[$regs[2]]);
						case '_COOKIE':
							return ($value === $_COOKIE[$regs[2]]);
						case '_SESSION':
							return ($value === $_SESSION[$regs[2]]);
						case '_ENV':
							return ($value === $_ENV[$regs[2]]);
						case 'GLOBALS':
							return ($value === $GLOBALS[$regs[2]]);
					}
				}
				return false;

			case 'regex':
				return (bool) preg_match ($validator, $value);

			case 'type':
				return call_user_func ('is_' . $validator, $value);

			case 'callback':
				return call_user_func ($validator, $value);

			case 'range':
				list ($min, $max) = explode ('-', $validator);
				return (($min <= $value) && ($value <= $max));

			case 'empty':
				if (is_array ($value)) {
					if (count ($value) === 0) {
						return false;
					}

					foreach ($value as $_v) {
						if ($_v === '0') {
							return true;
						}
						
						if (empty (trim ($_v))) {
							return true;
						}
					}
					
					return false;

				} elseif ($value === '0') {
					return false;
				}

				return empty (trim ($value));

			case 'length':
				if (preg_match ('/^([0-9]+)([+-]?)([0-9]*)$/', $validator, $regs)) {
					if (! empty ($regs[3])) {
						if (strlen ($value) < $regs[1] || strlen ($value) > $regs[3]) {
							return false;
						}
					} elseif ($regs[2] === '+' && strlen ($value) < $regs[1]) {
						return false;
					} elseif ($regs[2] === '-' && strlen ($value) > $regs[1]) {
						return false;
					} elseif (empty ($regs[2]) && strlen ($value) != $regs[1]) {
						return false;
					}
				}
				return true;

			case 'contains':
				return (bool) stristr ($value, $validator);

			case 'equals':
				return ($value == $validator);

			case 'gt':
				return ($value > $validator);

			case 'gte':
				return ($value >= $validator);

			case 'lt':
				return ($value < $validator);

			case 'lte':
				return ($value <= $validator);

			case 'email':
				if (! filter_var ($value, FILTER_VALIDATE_EMAIL)) {
					return false;
				}

				// also verify that the domain isn't just 'localhost'
				// which would allow garbage in
				list ($one, $two) = explode ('@', $value);
				if (strpos ($two, '.') === false) {
					return false;
				}
				return true;

			case 'url':
				if (! filter_var ($value, FILTER_VALIDATE_URL)) {
					return false;
				}
				if (strpos ($value, '.') === false) {
					return false;
				}
				return true;

			case 'header':
				return ! (bool) preg_match ('/[\r\n]/s', $value);

			case 'date':
				return (bool) preg_match ('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value);

			case 'time':
				return (bool) preg_match ('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value);

			case 'datetime':
				return (bool) preg_match ('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value);

			case 'unique':
				list ($table, $column) = preg_split ('/[\.:\/]/', $validator);
				$res = DB::shift ('select ' . $column . ' from ' . $table . ' where ' . $column . ' = ?', $value);
				if ($res == $value) {
					return false;
				}
				return true;

			case 'exists':
				if (strpos ($validator, '%s') !== false) {
					return file_exists (sprintf ($validator, $value));
				}
				return file_exists ($validator . '/' . $value);
		}
		// If tests fail, be safe and fail by default
		return false;
	}
	
	/**
	 * Validate a list of values, such as `$_GET` or `$_POST` data against
	 * a list of validation rules. If the rules are a string, it will
	 * look for a file and parse it using `parse_ini_file()` for the rules.
	 * The format is as follows:
	 *
	 *     [field1]
	 *     email = 1
	 *     
	 *     [field2]
	 *     type = string
	 *     regex = "/^[a-z]+$/i"
	 *     
	 *     [field3]
	 *     skip_if_empty = 1
	 *     unique = "table.column"
	 *
	 * Returns an array of failed fields. If the array is empty, everything
	 * passed.
	 */
	public static function validate_list ($values, $validations = array ()) {
		if (is_string ($validations) && file_exists ($validations)) {
			$validations = parse_ini_file ($validations, true);
		}
		$failed = array ();
		self::$invalid = array ();
		foreach ($validations as $n => $validators) {
			if (strpos ($n, ':') !== false) {
				list ($name, $rule) = explode (':', $n);
			} else {
				$name = $n;
				$rule = $n;
			}
			
			if (in_array ($name, $failed)) {
				continue;
			}

			foreach ($validators as $type => $validator) {
				if ($type === 'file') {
					if (! is_uploaded_file ($_FILES[$name]['tmp_name'])) {
						$failed[] = $rule;
						self::$invalid[$name] = array (
							'field' => $name,
							'type'  => $type,
							'validator' => $validator,
							'value' => $_FILES[$name]['name']
						);
						break;
					} else {
						continue;
					}
				}
				if ($type === 'filetype') {
					$extensions = preg_split ('/, ?/', trim (strtolower ($validator)));
					if ($extensions === false) {
						$extensions = array ($validator);
					}
					$extension = strtolower (pathinfo ($_FILES[$name]['name'], PATHINFO_EXTENSION));
					if (! in_array ($extension, $extensions)) {
						$failed[] = $rule;
						self::$invalid[$name] = array (
							'field' => $name,
							'type'  => $type,
							'validator' => $validator,
							'value' => $_FILES[$name]['name']
						);
						break;
					} else {
						continue;
					}
				}
				if ($type === 'skip_if_empty') {
					if (is_array ($values[$name])) {
						foreach ($values[$name] as $k => $v) {
							if (empty ($v)) {
								// Unset empty array values so they're not checked against the other rules
								unset ($values[$name][$k]);
							}
						}
						continue;
					} elseif (empty ($values[$name]) && (! isset ($_FILES[$name]) || $_FILES[$name]['error'] === 4)) {
						break;
					} else {
						continue;
					}
				}
				if ($type === 'validate_on_change') {
					continue;
				}
				if (! isset ($values[$name]) || ! Validator::validate ($values[$name], $type, $validator)) {
					$failed[] = $rule;
					self::$invalid[$name] = array (
						'field' => $name,
						'type'  => $type,
						'validator' => $validator,
						'value' => isset ($values[$name]) ? $values[$name] : null
					);
					break;
				}
			}
		}
		return $failed;
	}
}
