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
 * This is the debugging handler. It converts errors into ErrorException
 * exceptions, and handles exceptions by printing a trace including
 * highlighted source code.
 *
 * Usage:
 *
 *     <?php
 *     
 *     if (conf ('General', 'debug')) {
 *         require_once ('lib/Debugger.php');
 *         Debugger::start ();
 *     }
 *     
 *     ?>
 */
class Debugger {
	/**
	 * This is set to true if an error is converted into an exception
	 * in `Debugger::handle_error()`.
	 */
	public static $is_error = false;

	/**
	 * Set the error and exception handlers.
	 */
	public static function start ($on = true) {
		if ($on) {
			error_reporting (E_ALL | E_STRICT);
			set_error_handler (array ('Debugger', 'handle_error'));
			set_exception_handler (array ('Debugger', 'handle_exception'));
		} else {
			set_error_handler (array ('Debugger', 'handle_error'));
			set_exception_handler (array ('Debugger', 'log_exception'));
		}
	}

	/**
	 * Handles exceptions.
	 */
	public static function handle_exception ($e) {
		while (ob_get_level () > 0) {
			ob_end_clean ();
		}
		printf (
			"<link rel='stylesheet' href='/apps/admin/css/debugger.css' /><h1>%s: %s</h1>\n",
			get_class ($e),
			$e->getMessage ()
		);
		$trace = $e->getTrace ();
		if (! Debugger::$is_error) {
			array_unshift ($trace, array (
				'file' => $e->getFile (),
				'line' => $e->getLine (),
				'function' => get_class ($e),
				'args' => array ($e->getMessage ())
			));
		}
		Debugger::show_trace ($trace);
	}
	
	/**
	 * Handles exceptions by logging them.
	 */
	public static function log_exception ($e) {
		error_log (
			sprintf (
				'Error in %s on line %d: %s',
				$e->getFile (),
				$e->getLine (),
				$e->getMessage ()
			)
		);
		throw $e;
	}

	/**
	 * Shows the trace output.
	 */
	public static function show_trace ($trace) {
		$start = 0;
		if (! isset ($trace[0]['line'])) {
			$trace[0]['line'] = $trace[0]['args'][3];
		}
		if (! isset ($trace[0]['file'])) {
			$trace[0]['file'] = $trace[0]['args'][2];
		}
		if ($trace[0]['file'] === $trace[1]['file'] && $trace[0]['line'] === $trace[1]['line']) {
			$start++;
		}

		for ($i = $start, $count = count ($trace); $i < $count; $i++) {
			echo Debugger::show_trace_step ($trace[$i]);
		}
		$context = array (
			'_COOKIE' => isset ($_COOKIE) ? $_COOKIE : array (),
			'_SERVER' => $_SERVER
		);
		if (isset ($trace[0]['args']) && is_array ($trace[0]['args'][4])) {
			$context = array_merge ($context, $trace[0]['args'][4]);
		}
		Debugger::show_context ($context);
	}

	/**
	 * Converts errors to ErrorException exceptions.
	 */
	public static function handle_error ($errno, $errstr, $errfile, $errline) {
		if ($errno === 8) {
			return;
		}
		Debugger::$is_error = true;
		throw new ErrorException ($errstr, 0, $errno, $errfile, $errline);
	}

	/**
	 * Shows a step in the trace.
	 */
	public static function show_trace_step ($trace) {
		if (! isset ($trace['line'])) {
			$trace['line'] = $trace['args'][3];
		}
		if (! isset ($trace['file'])) {
			$trace['file'] = $trace['args'][2];
			if (empty ($trace['file'])) {
				return;
			}
		}
		if (isset ($trace['class'])) {
			printf (
				'<div class="step"><h3>%s%s%s (%s)</h3><p class="file"><span class="line">%d</span>%s</p><p class="code">%s</p></div>',
				$trace['class'],
				$trace['type'],
				$trace['function'],
				Debugger::join_arguments ($trace['args']),
				$trace['line'],
				$trace['file'],
				Debugger::get_code ($trace['file'], $trace['line'])
			);
		} else {
			printf (
				'<div class="step"><h3>%s (%s)</h3><p class="file"><span class="line">%d</span>%s</p><p class="code">%s</p></div>',
				$trace['function'],
				Debugger::join_arguments ($trace['args']),
				$trace['line'],
				$trace['file'],
				Debugger::get_code ($trace['file'], $trace['line'])
			);
		}
		echo "\n";
	}

	/**
	 * Joins arguments for displaying the relevant code in a trace step.
	 */
	public static function join_arguments ($args) {
		if (! is_array ($args)) {
			return '';
		}
		$out = '';
		$sep = '';
		foreach ($args as $arg) {
			if (is_numeric ($arg)) {
				$out .= $sep . $arg;
			} elseif (is_string ($arg)) {
				$out .= $sep . '"' . $arg . '"';
			} elseif (is_bool ($arg)) {
				if ($arg) {
					$out .= $sep . 'true';
				} else {
					$out .= $sep . 'false';
				}
			} elseif (is_object ($arg)) {
				$out .= $sep . get_class ($arg);
			} elseif (is_array ($arg)) {
				$out .= $sep . 'array(' . count ($arg) . ')';
			} else {
				//$out .= $sep . $arg;
				info ($arg);
			}
			$sep = ', ';
		}
		return $out;
	}

	/**
	 * Get the code for a step in the trace.
	 */
	public static function get_code ($file, $line) {
		$lines = file ($file);
		$count = count ($lines);
		$out = '';
		for ($i = $line - 3; $i < $line + 2; $i++) {
			if (isset ($lines[$i])) {
				$out .= '<span class="line-number">' . ($i + 1) . '.</span><span class="code">' . Debugger::highlight ($lines[$i]) . "</span>\n";
			}
		}
		return $out;
	}

	/**
	 * Highlight code for a trace step.
	 */
	public static function highlight ($line) {
		if (strpos ($line, '<?php') !== false) {
			return highlight_string ($line, true);
		}
		return preg_replace (
			'/&lt;\?php&nbsp;/',
			'',
			highlight_string ('<?php ' . $line, true)
		);
	}

	/**
	 * Show the context of a trace step.
	 */
	public static function show_context ($context) {
		echo '<h2>Error Context</h2>';
		foreach ($context as $name => $value) {
			echo '<p class="code"><span class="code">';
			ob_start ();
			echo '$' . $name . ' = ';
			Debugger::show_variable ($value);
			echo ';';
			$code = ob_get_clean ();
			echo Debugger::highlight ($code);
			echo '</span></p>';
		}
	}

	/**
	 * Show a variable for the debug output.
	 */
	public static function show_variable ($value, $tabs = 0) {
		if (is_numeric ($value)) {
			// Render a numeric value
			echo $value;

		} elseif (is_bool ($value)) {
			// Render a boolean value
			if ($value) {
				echo 'true';
			} else {
				echo 'false';
			}

		} elseif (is_string ($value)) {
			// Render a string value
			echo '"' . Template::sanitize ($value) . '"';

		} elseif (is_array ($value)) {
			// Render an array
			echo 'array (';
			if (empty ($value)) {
				echo ")";
				return;
			}

			if (Debugger::is_assoc ($value)) {
				// Associative array
				$first = true;
				foreach ($value as $key => $val) {
					if (! $first) {
						echo ",";
						$first = false;
					}
					echo "\n";
					echo str_pad ('', ($tabs + 1) * 4);
					printf ("\"%s\" => ", $key);
					Debugger::show_variable ($val, $tabs + 1);
				}

			} else {
				// Ordinary array
				$first = true;
				foreach ($value as $val) {
					if (! $first) {
						echo ",";
						$first = false;
					}
					print "\n";
					echo str_pad ('', ($tabs + 1) * 4);
					Debugger::show_variable ($val, $tabs + 1);
				}
			}
			echo "\n";
			echo str_pad ('', ($tabs) * 4);
			echo ")";

		} elseif (is_object ($value)) {
			// Render an object
			$vars = get_object_vars ($value);
			if (count ($vars) === 0) {
				echo get_class ($value) . ' ()';
				return;
			}
			echo get_class ($value) . " (\n";
			foreach (get_object_vars ($value) as $key => $val) {
				echo str_pad ('', ($tabs + 1) * 4);
				printf ("$%s = ", $key);
				Debugger::show_variable ($val, $tabs + 1);
				echo ";\n";
			}
			echo ")";

		} else {
			// Render unknown values as-is
			echo $value;
		}
	}

	/**
	 * Checks if an array is associative.
	 */
	public static function is_assoc ($array) {
		if (! is_array ($array) || empty ($array)) {
			return false;
		}

		$i = 0;
		foreach (array_keys ($array) as $k) {
			if ($k !== $i) {
				return true;
			}
			$i++;
		}
		return false;
	}
}

?>