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
		for ($i = 1; $i < count ($trace); $i++) {
			echo Debugger::show_trace_step ($trace[$i]);
		}
	}

	static function handle_error ($errno, $errstr, $errfile, $errline) {
		throw new ErrorException ($errstr, 0, $errno, $errfile, $errline);
	}

	static function show_trace_step ($trace) {
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
			} else {
				info ($arg, true);
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
}

?>