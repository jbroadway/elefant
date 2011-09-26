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
 * Convenience functions for validating form submissions and input data.
 * The input validation can be useful not just for form submissions,
 * but it's packaged here to keep things tidy.
 *
 * Usage:
 *
 *     $form = new Form ('post', 'apps/myapp/forms/verify.php');
 *     if ($form->submit ()) {
 *         // handle form
 *     } else {
 *         $page->failed = $form->failed;
 *         $page->head = '<script src="/js/jquery-1.6.2.min.js"></script>
 *             <script src="/js/jquery.verify_values.js"></script>
 *             <script>
 *             $(function () {
 *                 $.verify_values ({
 *                     element: "#myapp-form",
 *                     handler: "myapp/verify",
 *                     callback: function (failed) {
 *                         // highlight the failed elements
 *                     }
 *                 });
 *             });
 *             </script>';
 *         $page->template = 'myapp/form';
 *     }
 */
class Form {
	/**
	 * Fields that failed validation.
	 */
	var $failed = array ();

	/**
	 * The required request method.
	 */
	var $method = 'post';

	/**
	 * Validation rules.
	 */
	var $rules = array ();

	/**
	 * Whether to verify the referrer or not.
	 */
	var $verify_referrer = true;

	/**
	 * The reason `submit()` failed to pass.
	 */
	var $error = false;

	function __construct ($required_method = 'post', $form_rules = false) {
		$this->method = strtolower ($required_method);

		if (! empty ($form_rules)) {
			if (! @file_exists ($form_rules)) {
				list ($app, $form) = explode ('/', $form_rules);
				$form_rules = 'apps/' . $app . '/forms/' . $form . '.php';
			}
			if (@file_exists ($form_rules)) {
				$this->rules = parse_ini_file ($form_rules, true);
			}
		}
	}

	/**
	 * Check if the form is okay to submit. Verifies the request method,
	 * the referrer, and the input data.
	 */
	function submit () {
		$values = ($this->method == 'post') ? $_POST : $_GET;
		
		if (! $this->verify_request_method ()) {
			$this->error = 'Request method must be ' . strtoupper ($this->method);
			return false;
		}
		
		if ($this->verify_referrer && ! $this->verify_referrer ()) {
			$this->error = 'Referrer must match the host name.';
			return false;
		}

		$this->failed = $this->verify_values ($values, $this->rules);
		if (count ($this->failed) > 0) {
			$this->error = 'Validation error.';
			return false;
		}
		return true;
	}

	/**
	 * Merge the values from `$_GET` or `$_POST` onto a data array or
	 * object for re-rendering a form with the latest data entered.
	 */
	function merge_values ($obj) {
		$values = ($this->method == 'post') ? $_POST : $_GET;
		
		foreach ($values as $k => $v) {
			if (is_object ($obj)) {
				$obj->{$k} = $v;
			} else {
				$obj[$k] = $v;
			}
		}

		return $obj;
	}

	/**
	 * Verify the request method is the one specified.
	 */
	function verify_request_method () {
		if (strtolower ($_SERVER['REQUEST_METHOD']) != $this->method) {
			return false;
		}
		return true;
	}

	/**
	 * Verify the referrer came from this site. No remote form submissions,
	 * since those are almost certainly abusive.
	 */
	function verify_referrer () {
		if (strpos ($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
			return false;
		}
		return true;
	}

	/**
	 * Verifies the specified value, useful for input validation.
	 * Pass the value, a type of validation, and a validator.
	 * Types include:
	 *
	 * - `skip_if_empty` - a special verifier that tells `verify_values()` to skip
	 *                   validation on the field if it's been left blank.
	 * - `regex` - calls `preg_match($validator, $value)`
	 * - `type` - calls `is_$validator($value)`
	 * - `callback` - calls `call_user_func($validator, $value)`
	 * - `email` - a valid email address
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
	 */
	function verify_value ($value, $type, $validator = false) {
		if (preg_match ('/^not (.+)$/i', $type, $regs)) {
			return ! Form::verify_value ($value, $regs[1], $validator);
		}
		switch ($type) {
			case 'matches':
				if (preg_match ('/\$(_[a-z]+|GLOBALS)\[[\'"]?([a-z0-9_-]+)[\'"]?\]/i', $validator, $regs)) {
					// Can't dynamically reference superglobals, so instead...
					switch ($regs[1]) {
						case '_POST':
							return ($value == $_POST[$regs[2]]);
						case '_GET':
							return ($value == $_GET[$regs[2]]);
						case '_REQUEST':
							return ($value == $_REQUEST[$regs[2]]);
						case '_SERVER':
							return ($value == $_SERVER[$regs[2]]);
						case '_FILES':
							return ($value == $_FILES[$regs[2]]);
						case '_COOKIE':
							return ($value == $_COOKIE[$regs[2]]);
						case '_SESSION':
							return ($value == $_SESSION[$regs[2]]);
						case '_ENV':
							return ($value == $_ENV[$regs[2]]);
						case 'GLOBALS':
							return ($value == $GLOBALS[$regs[2]]);
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
				return empty ($value);
			case 'length':
				if (preg_match ('/^([0-9]+)([+-]?)([0-9]*)$/', $validator, $regs)) {
					if (! empty ($regs[3])) {
						if (strlen ($value) < $regs[1] || strlen ($value) > $regs[3]) {
							return false;
						}
					} elseif ($regs[2] == '+' && strlen ($value) < $regs[1]) {
						return false;
					} elseif ($regs[2] == '-' && strlen ($value) > $regs[1]) {
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
				if (strpos ($value, '.@') !== false) {
					return false;
				} elseif (preg_match ('/\.$/', $value)) {
					return false;
				} elseif (! preg_match ("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/" , $value)) {
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
				$res = db_shift ('select ' . $column . ' from ' . $table . ' where ' . $column . ' = ?', $value);
				if ($res == $value) {
					return false;
				}
				return true;
			case 'exists':
				if (strpos ($validator, '%s') !== false) {
					return @file_exists (sprintf ($validator, $value));
				}
				return @file_exists ($validator . '/' . $value);
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
	function verify_values ($values, $validations = array ()) {
		if (is_string ($validations) && @file_exists ($validations)) {
			$validations = parse_ini_file ($validations, true);
		}
		$failed = array ();
		foreach ($validations as $name => $validators) {
			foreach ($validators as $type => $validator) {
				if ($type == 'skip_if_empty') {
					if (empty ($values[$name])) {
						break;
					} else {
						continue;
					}
				}
				if (! Form::verify_value ($values[$name], $type, $validator)) {
					$failed[] = $name;
					break;
				}
			}
		}
		return $failed;
	}
}

?>