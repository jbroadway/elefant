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
 * This is a simple form handling class. Provides validation of the form
 * referrer, request method, cross-site request forgery (CSRF) prevention,
 * and numerous convenience functions for validating form submissions and
 * input data. Also integrates with the `/js/jquery.verify_values.js`
 * jQuery plugin to provide matching client-side validation based on the
 * same set of rules.
 *
 * The input validation can be useful not just for form submissions,
 * but it's packaged here to keep things tidy.
 *
 * Simplest usage:
 *
 *     <?php
 *     
 *     $form = new Form ('post', $this);
 *     
 *     echo $form->handle (function ($form) {
 *         // Create some data...
 *         $foo = new MyModel ($_POST);
 *         $foo->put ();
 *     
 *         // Refer them to a thank you page
 *         $form->controller->redirect ('/thank/you');
 *     });
 *     
 *     ?>
 *
 * Long form usage:
 *
 *     <?php
 *     
 *     $form = new Form ('post', 'apps/myapp/forms/verify.php');
 *
 *     if ($form->submit ()) {
 *         // handle form
 *         info ($_POST);
 *
 *     } else {
 *         // set some default values
 *         $obj = new StdClass;
 *         $obj->foo = 'bar';
 *
 *         // merge with user input
 *         $obj = $f->merge_values ($obj);
 *
 *         // get failed fields
 *         $obj->failed = $form->failed;
 *
 *         // add scripts for client-side validation
 *         $page->add_script ('<script src="/js/jquery.verify_values.js"></script>');
 *         $page->add_script ('<script>
 *             $(function () {
 *                 $.verify_values ({
 *                     element: "#myapp-form",
 *                     handler: "myapp/verify",
 *                     callback: function (failed) {
 *                         // highlight the failed elements
 *                     }
 *                 });
 *             });
 *             </script>');
 *
 *         // output your form template
 *         echo $tpl->render ('myapp/form', $obj);
 *     }
 *     
 *     ?>
 */
class Form {
	/**
	 * Fields that failed validation.
	 */
	public $failed = array ();

	/**
	 * The required request method.
	 */
	public $method = 'post';

	/**
	 * Validation rules.
	 */
	public $rules = array ();

	/**
	 * The original `$form_rules` passed to the constructor, parsed
	 * down to the `appname/rules` short form.
	 */
	private $_rules;

	/**
	 * A view to render the form with. Used by `handle()`.
	 */
	public $view = false;

	/**
	 * A data object with the initial form values. Used by `handle()`.
	 */
	public $data = false;

	/**
	 * An optional copy of the controller object.
	 */
	public $controller = false;

	/**
	 * Whether to verify the referrer or not.
	 */
	public $verify_referrer = true;

	/**
	 * Whether to verify with a CSRF token or not.
	 */
	public $verify_csrf = true;

	/**
	 * Token generated for CSRF prevention.
	 */
	public $csrf_token;

	/**
	 * The name of the token form field.
	 */
	public $csrf_field_name = '_token_';

	/**
	 * The reason `submit()` failed to pass.
	 */
	public $error = false;

	/**
	 * Whether `handle()` should include the default JavaScript validation
	 * or just the `/js/jquery.verify_values.js` script so you can write
	 * your own custom validation display.
	 */
	public $js_validation = true;

	/**
	 * Constructor method. Parameters are:
	 *
	 * - Required request method (default is 'post')
	 * - A reference to the form rules file, or a Controller object
	 * - If param 2 is a file reference, this can be the Controller object
	 *
	 * Usage:
	 *
	 *     $f = new Form (); // defaults and no Controller or rules set
	 *     $f = new Form ('post'); // POST requests but no Controller or rules
	 *     $f = new Form ('post', $this); // POST and Controller set
	 *     $f = new Form ('post', 'myapp/rules'); // POST and rules set
	 *     $f = new Form ('post', 'myapp/rules, $this); // Everything set
	 *
	 * Note that if the rules are not set but the Controller is passed,
	 * the rules file will be assumed to match the appname/handlername of
	 * the currently active handler, and the view will be set to match as
	 * well. This is the most handy scenario, since if you match your
	 * rules file, handler, and view names, you can simply say:
	 *
	 *     $f = new Form ('post', $this);
	 *
	 * And it will set everything up correctly based on `$this->uri` in the
	 * Controller.
	 */
	public function __construct ($required_method = 'post', $form_rules = false, $controller = false) {
		// Normalize the request method to lowercase
		$this->method = strtolower ($required_method);

		if ($form_rules instanceof Controller) {
			// Controller was passed as the second param
			$this->controller = $form_rules;
			$form_rules = $this->controller->uri;
		} else {
			// Controller was third param
			$this->controller = $controller;
		}

		// Fetch any form validation rules
		if (! empty ($form_rules)) {
			if (! file_exists ($form_rules)) {
				list ($app, $form) = explode ('/', $form_rules);
				$form_rules = 'apps/' . $app . '/forms/' . $form . '.php';
			}
			if (file_exists ($form_rules)) {
				$this->rules = parse_ini_file ($form_rules, true);
			}
			// Set the view by default based on the form rules (can be changed later)
			$this->_rules = preg_replace ('|apps/(.+)/forms/(.+)\.php$|', '\1/\2', $form_rules);
			$this->view = $this->_rules;
		}
	}

	/**
	 * Accepts an anonymous function (aka closure) that handles the
	 * form submission, and abstracts away the rendering of the form
	 * based on the provided rules and view, helping eliminate much
	 * of the boilerplate code of form creation.
	 *
	 * Note that `handle()` will also include `/js/jquery.verify_values.js`
	 * for you, but you need to provide your own initialization code
	 * to perform the client-side validations. There is a page in the
	 * public wiki on www.elefantcms.com describing the steps for this.
	 *
	 * Also note that if the anonymous function returns false, there
	 * will be no output and the false status will be passed to the
	 * handler to catch.
	 */
	public function handle ($func) {
		if ($this->submit ()) {
			// Form can be handled, capture output and return it
			// If the function returns false, simply pass that
			// to the handler with no output
			ob_start ();
			$res = $func ($this);
			if ($res !== false) {
				return ob_get_clean ();
			}
			ob_end_clean ();
		}

		if (! $this->view) {
			// No view so we simply return false so the handler
			// can take over rendering the form
			return false;
		}

		// Render the view and return its output
		global $page, $tpl;

		// Take the initial form data from $this->data or a new StdClass
		if ($this->data === false) {
			$o = new StdClass;
		} else {
			$o = is_object ($this->data) ? $this->data : (object) $this->data;
		}

		// Determine the default values
		foreach ($this->rules as $field => $rules) {
			foreach ($rules as $key => $value) {
				if ($key === 'default') {
					$o->{$field} = $value;
					break;
				}
			}
		}

		// Set some views to go to the template
		$o = $this->merge_values ($o);
		$o->_form = str_replace ('/', '-', $this->view) . '-form';
		$o->_failed = $this->failed;
		$o->_rules = $this->_rules;
		$page->add_script ('/js/jquery.verify_values.js');
		if ($this->js_validation) {
			return $tpl->render ('admin/default-validation', $o) . $tpl->render ($this->view, $o);
		}
		return $tpl->render ($this->view, $o);
	}

	/**
	 * Check if the form is okay to submit. Verifies the request method,
	 * the referrer, and the input data.
	 */
	public function submit () {
		$values = ($this->method === 'post') ? $_POST : $_GET;

		$this->initialize_csrf ();
		
		if (! $this->verify_request_method ()) {
			// form hasn't been submitted yet, or request method doesn't match
			$this->error = 'Request method must be ' . strtoupper ($this->method);
			return false;
		}
		
		if ($this->verify_referrer && ! $this->verify_referrer ()) {
			$this->error = 'Referrer must match the host name.';
			return false;
		}

		if ($this->verify_csrf && ! $this->verify_csrf ()) {
			$this->error = 'Cross-site request forgery detected.';
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
	public function merge_values ($obj) {
		$values = ($this->method === 'post') ? $_POST : $_GET;
		
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
	public function verify_request_method () {
		if (strtolower ($_SERVER['REQUEST_METHOD']) !== $this->method) {
			return false;
		}
		return true;
	}

	/**
	 * Verify the referrer came from this site. No remote form submissions,
	 * since those are almost certainly abusive.
	 */
	public function verify_referrer () {
		if (strpos ($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
			return false;
		}
		return true;
	}

	/**
	 * Initialize the CSRF token.
	 */
	public function initialize_csrf () {
		if ($this->verify_csrf) {
			// Start a session
			@session_set_cookie_params (time () + 2592000);
			@session_start ();

			if (isset ($_SESSION['csrf_token']) && $_SESSION['csrf_expires'] > time ()) {
				// Get an existing token
				$this->csrf_token = $_SESSION['csrf_token'];

				// Reset the timer on the request so it doesn't expire on the
				// user if time is running short
				$_SESSION['csrf_expires'] = time () + 7200;
			} else {
				// Generate a random token
				$this->csrf_token = md5 (uniqid (rand (), true));

				// Set the token and expiry time (2 hours)
				$_SESSION['csrf_token'] = $this->csrf_token;
				$_SESSION['csrf_expires'] = time () + 7200;
			}

			// Append the CSRF token Javascript if there is a page object
			if (isset ($GLOBALS['page'])) {
				$GLOBALS['page']->add_script (
					$this->generate_csrf_script (),
					'tail'
				);
			}
		}
	}

	/**
	 * Generate the script that will append the token to forms in the page.
	 * You do not need to call this directly as long as you have `{{ tail|none }}`
	 * in your layout template, since `initialize_csrf()` will automatically
	 * add this to the tail if it can.
	 */
	function generate_csrf_script () {
		return sprintf (
			'<script>$(function(){$("form").append("<input type=\'hidden\' name=\'%s\' value=\'%s\'/>");});</script>',
			$this->csrf_field_name,
			$this->csrf_token
		);
	}

	/**
	 * Verify the CSRF token is present, matches the stored value in the session
	 * data, and has not expired (2 hour limit).
	 */
	public function verify_csrf () {
		if (! isset ($_SESSION['csrf_token']) || ! isset ($_SESSION['csrf_expires'])) {
			// No token in session
			return false;
		}

		$values = ($this->method === 'post') ? $_POST : $_GET;

		if (! isset ($values[$this->csrf_field_name])) {
			// No token provided
			return false;
		}

		if ($_SESSION['csrf_token'] !== $values[$this->csrf_field_name]) {
			// Token doesn't match
			return false;
		}
		if ($_SESSION['csrf_expires'] < time ()) {
			// Timed out
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
	 * - `file` - a special verifier that tells `verify_values()` to check if it's
	 *          a valid uploaded file.
	 * - `filetype` - a special verifier that tells `verify_values()` to check if the
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
	 */
	public static function verify_value ($value, $type, $validator = false) {
		if ($type === 'default') {
			return true;
		}
		if (preg_match ('/^not (.+)$/i', $type, $regs)) {
			return ! Form::verify_value ($value, $regs[1], $validator);
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
				return empty ($value);

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
				$res = db_shift ('select ' . $column . ' from ' . $table . ' where ' . $column . ' = ?', $value);
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
	public function verify_values ($values, $validations = array ()) {
		if (is_string ($validations) && file_exists ($validations)) {
			$validations = parse_ini_file ($validations, true);
		}
		$failed = array ();
		foreach ($validations as $name => $validators) {
			foreach ($validators as $type => $validator) {
				if ($type === 'file') {
					if (! is_uploaded_file ($_FILES[$name]['tmp_name'])) {
						$failed[] = $name;
						break;
					} else {
						continue;
					}
				}
				if ($type === 'filetype') {
					$extensions = preg_split ('/, ?', trim (strtolower ($validator)));
					if ($extensions === false) {
						$extensions = array ($validator);
					}
					$extension = strtolower (pathinfo ($_FILES[$name]['name'], PATHINFO_EXTENSION));
					if (! in_array ($extension, $extensions)) {
						$failed[] = $name;
						break;
					} else {
						continue;
					}
				}
				if ($type === 'skip_if_empty') {
					if (empty ($values[$name]) && (! isset ($_FILES[$name]) || $_FILES[$name]['error'] === 4)) {
						break;
					} else {
						continue;
					}
				}
				if (! isset ($values[$name]) || ! Form::verify_value ($values[$name], $type, $validator)) {
					$failed[] = $name;
					break;
				}
			}
		}
		return $failed;
	}
}

?>