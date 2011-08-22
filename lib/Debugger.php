<?php

class Debugger {
	static function start () {
		set_error_handler (array ('Debugger', 'handle_error'));
		set_exception_handler (array ('Debugger', 'handle_exception'));
	}

	static function handle_exception ($e) {
		while (ob_get_level () > 0) {
			ob_end_clean ();
		}
		printf (
			"<link rel='stylesheet' href='/apps/admin/css/debugger.css' /><h1>%s: %s</h1>\n",
			get_class ($e),
			$e->getMessage ()
		);
		Debugger::show_trace ($e->getTrace ());
	}

	static function show_trace ($trace) {
		$start = 0;
		if (! isset ($trace[0]['line'])) {
			$trace[0]['line'] = $trace[0]['args'][3];
		}
		if (! isset ($trace[0]['file'])) {
			$trace[0]['file'] = $trace[0]['args'][2];
		}
		if ($trace[0]['file'] == $trace[1]['file'] && $trace[0]['line'] == $trace[1]['line']) {
			$start++;
		}

		for ($i = $start; $i < count ($trace); $i++) {
			echo Debugger::show_trace_step ($trace[$i]);
		}
		if (isset ($trace[0]['args']) && is_array ($trace[0]['args'][4])) {
			Debugger::show_context ($trace[0]['args'][4]);
		}
	}

	static function handle_error ($errno, $errstr, $errfile, $errline) {
		if ($errno == 8) {
			return;
		}
		throw new ErrorException ($errstr, 0, $errno, $errfile, $errline);
	}

	static function show_trace_step ($trace) {
		if (! isset ($trace['line'])) {
			$trace['line'] = $trace['args'][3];
		}
		if (! isset ($trace['file'])) {
			$trace['file'] = $trace['args'][2];
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

	static function join_arguments ($args) {
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

	static function get_code ($file, $line) {
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

	static function highlight ($line) {
		if (strpos ($line, '<?php') !== false) {
			return highlight_string ($line, true);
		}
		return preg_replace (
			'/&lt;\?php&nbsp;/',
			'',
			highlight_string ('<?php ' . $line, true)
		);
	}

	static function show_context ($context) {
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

	static function show_variable ($value, $tabs = 0) {
		if (is_numeric ($value)) {
			echo $value;
		} elseif (is_bool ($value)) {
			if ($value) {
				echo 'true';
			} else {
				echo 'false';
			}
		} elseif (is_string ($value)) {
			echo '"' . Template::sanitize ($value) . '"';
		} elseif (is_array ($value)) {
			echo 'array (';
			if (empty ($value)) {
				echo ")";
				return;
			}
			if (Debugger::is_assoc ($value)) {
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
			$vars = get_object_vars ($value);
			if (count ($vars) == 0) {
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
			echo $value;
		}
	}

	static function is_assoc ($array) {
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