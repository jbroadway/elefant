<?php

/**
 * Basic template renderer. Looks for templates via the pattern
 * `apps/{app}/views/{file}.html` where the template is passed as
 * `'app/file'`. Failing that, it looks for `layouts/{file}.html`
 * and finally `layouts/default.html`. It then creates a PHP version
 * of the template  and caches it to `cache/{app}-{template}.php`,
 * so the cache folder must be writeable. Auto-refreshes cached
 * versions when the originals change.
 *
 * As a result, templates can include any PHP, along with tags of
 * the form:
 *
 *     {{ body }}
 *
 * And blocks of the form:
 *
 *     {% foreach pages %}
 *         {{ loop_index }} - {{ loop_value }}
 *     {% end %}
 *
 *     {% if some_val %}
 *         {{ some_val }}
 *     {% end %}
 *
 * Note the use of loop_index and loop_value, which are defined for
 * you inside foreach loops by the template engine.
 *
 * You can also test for more complex conditions, but make sure the
 * value being tested for is not preceeded by anything. For example,
 * no false checks via `{% if !some_val %}`, instead use:
 *
 *     {% if some_val == false %}
 *         {{ some_val }}
 *     {% end %}
 *
 * Note that `'endif'` and `'endforeach'` are valid as well as `'end'`,
 * if you prefer, for the sake of clarity.
 *
 * ## Usage in PHP
 *
 * To call a template, use:
 *
 *     echo $tpl->render ('base', array ('foo' => 'bar'));
 *
 * Note that arrays passed to templates are converted to objects,
 * and objects are left as-is.
 *
 * ## Globals
 *
 * In addition to the fields in the data array passed to `render()`,
 * you can also call global objects and class methods as follows
 * from within if and foreach blocks as well as variable
 * substitutions:
 *
 * Call User::constant_value:
 *
 *     {{ User::constant_value }}
 *
 * Call $GLOBALS['user']->name:
 *
 *     {{ user.name }}
 *
 * Call a function:
 *
 *     {{ db_shift ('select * from foo') }}
 *
 * In an if block:
 *
 *     {% if User::is_valid () %}
 *
 *     {% if user.name != '' %}
 *
 * In a foreach:
 *
 *     {% foreach Oject::some_method () %}
 *
 * Calling a superglobal:
 *
 *     {{ $_POST.value }}
 *
 * Note that these must come at the beginning of a statement, not
 * anywhere else within it. The replacement mechanism is very
 * simplistic.
 *
 * ## Embedding handlers
 *
 * You can use special `{! app/handler !}` tags to embed handlers
 * directly into your templates. These are the equivalent of calling:
 *
 *     {{ controller.run ('app/handler') }}
 *
 * You can also pass them an array of data using a shorthand like a url:
 *
 *     {! app/handler?param=value&another=value2 !}
 *
 * ## Filters
 *
 * Filtering is supported, and `htmlspecialchars()` is the default
 * filter unless another is specified or 'none' is supplied via:
 *
 *     {{ body|none }}
 *
 * Any valid function can be a filter, and filters can be chained,
 * executing in the following order:
 *
 *     {{ body|strtoupper|strtolower }}
 *
 *     <?php echo strtolower (strtoupper ($data->body)); ?>
 *
 * You can also set additional parameters to a filter as follows:
 *
 *     {{ timestamp|date ('F j', %s) }}
 *
 * ## String translations
 *
 * You can use the following tag format to mark strings for translation
 * into the current visitor's language:
 *
 *     {" Text here "}
 *
 * This will be replaced with a call to:
 *
 *     i18n_get('Text here')
 */
class Template {
	var $charset = 'UTF-8';
	var $quotes = ENT_QUOTES;

	function __construct ($charset = 'UTF-8') {
		$this->charset = $charset;
	}

	/**
	 * Render a template with the given data. Generate the PHP template if
	 * necessary.
	 */
	function render ($template, $data = array ()) {
		if (is_array ($data)) {
			$data = (object) $data;
		}
		$data->is_being_rendered = true;

		if (strstr ($template, '/')) {
			list ($app, $file) = preg_split ('/\//', $template, 2);
			$file = 'apps/' . $app . '/views/' . $file . '.html';
			if (! @file_exists ($file)) {
				die ('Template not found: ' . $template);
			}
		} elseif (@file_exists ('layouts/' . $template . '.html')) {
			$file = 'layouts/' . $template . '.html';
		} else {
			$file = 'layouts/default.html';
		}
		$cache = 'cache/' . str_replace ('/', '-', $template) . '.php';

		if (! file_exists ($cache) || filemtime ($file) > filemtime ($cache)) {
			// Regenerate cached file
			$out = file_get_contents ($file);
			$out = $this->parse_template ($out);
			if (! file_put_contents ($cache, $out)) {
				die ('Failed to generate cached template: ' . $cache);
			}
		}
		
		ob_start ();
		require ($cache);
		return ob_get_clean ();
	}

	/**
	 * Render a template string for preview purposes. Generates a temporary
	 * cached version but unlinks it immediately after use.
	 */
	function render_preview ($template, $data = array ()) {
		if (is_array ($data)) {
			$data = (object) $data;
		}
		$data->is_being_rendered = true;

		$cache_file = 'cache/_preview_' . md5 ($template) . '.php';
		$out = $this->parse_template ($template);
		if (! file_put_contents ($cache_file, $out)) {
			die ('Failed to generate cached template: ' . $cache_file);
		}

		ob_start ();
		require ($cache_file);
		$out = ob_get_clean ();
		unlink ($cache_file);
		return $out;
	}

	/**
	 * Replace values from template as string.
	 */
	function parse_template ($val) {
		$val = preg_replace ('/\{\{ ?(.*?) ?\}\}/e', '$this->replace_vars (\'\\1\')', $val);
		$val = preg_replace ('/\{[\'"] ?(.*?) ?[\'"]\}/e', '$this->replace_strings (\'\\1\')', $val);
		$val = preg_replace ('/\{\% ?(.*?) ?\%\}/e', '$this->replace_blocks (\'\\1\')', $val);
		$val = preg_replace ('/\{\! ?(.*?) ?\!\}/e', '$this->replace_includes (\'\\1\')', $val);
		return $val;
	}

	/**
	 * Replace variables.
	 */
	function replace_vars ($val) {
		$filters = explode ('|', $val);
		$val = array_shift ($filters);

		if (strstr ($val, '$_')) {
			if (strstr ($val, '.')) {
				$val = preg_replace ('/\.([a-zA-Z0-9_]+)/', '[\'\1\']', $val, 1);
			}
		} elseif (strstr ($val, '.')) {
			$val = '$GLOBALS[\'' . preg_replace ('/\./', '\']->', $val, 1);
		} elseif (! strstr ($val, '::') && ! strstr ($val, '(')) {
			$val = '$data->' . $val;
		}

		if (count ($filters) == 0) {
			return '<?php echo Template::sanitize (' . $val . ', \'' . $this->charset . '\'); ?>';
		} else if ($filters[0] == 'none') {
			return '<?php echo ' . $val . '; ?>';
		}

		$filters = array_reverse ($filters);
		$out = '<?php echo ';
		$end = '; ?>';
		foreach ($filters as $filter) {
			if (strstr ($filter, '%s')) {
				list ($one, $two) = explode ('%s', $filter);
				$out .= $one;
				$end = $two . $end;
			} else {
				$out .= $filter . ' (';
				$end = ')' . $end;
			}
		}
		return $out . $val . $end;
	}

	/**
	 * Replace `{! app/handler?param=value !}` with calls to `Controller::run()`.
	 * You can also substitute sub-expressions for values using `[]` tags, like
	 * this: `{! app/handler?param=[varname] !}`
	 */
	function replace_includes ($val) {
		$url = parse_url ($val);
		if (isset ($url['query'])) {
			parse_str (html_entity_decode ($url['query'], ENT_COMPAT, 'UTF-8'), $data);
		} else {
			$data = array ();
		}
		$arr = '';
		$sep = '';
		foreach ($data as $k => $v) {
			if (strpos ($v, '[') === 0 && $v[strlen ($v) - 1] == ']') {
				$v = str_replace (
					array ('<?php echo ', '; ?>'),
					array ('', ''),
					$this->replace_vars (substr ($v, 1, strlen ($v) - 2))
				);
				$arr .= sprintf ('%s\'%s\' => %s', $sep, $k, $v);
			} else {
				$arr .= sprintf ('%s\'%s\' => \'%s\'', $sep, $k, $v);
			}
			$sep = ', ';
		}
		return sprintf (
			'<?php echo $GLOBALS[\'controller\']->run (\'%s\', array (%s)); ?>',
			$url['path'],
			$arr
		);
	}

	/**
	 * Run any includes and include their output in the return value. Primarily
	 * for page body in the admin app.
	 */
	function run_includes ($val) {
		$parts = preg_split ('/(\{\! ?.*? ?\!\})/e', $val, -1, PREG_SPLIT_DELIM_CAPTURE);
		$out = '';
		foreach ($parts as $part) {
			if (strpos ($part, '{!') === 0) {
				$part = trim ($part, '{! }');
				$url = parse_url ($part);
				if (isset ($url['query'])) {
					parse_str (html_entity_decode ($url['query'], ENT_COMPAT, 'UTF-8'), $data);
				} else {
					$data = array ();
				}
				$out .= $GLOBALS['controller']->run ($url['host'] . $url['path'], $data);
			} else {
				$out .= $part;
			}
		}
		return $out;
	}

	/**
	 * Replace strings with calls to `i18n_get()` for multilingual sites.
	 */
	function replace_strings ($val) {
		return '<?php echo i18n_get (\'' . str_replace ('\'', '\\\'', $val) . '\'); ?>';
	}

	/**
	 * Sanitize a value for safe output, helping to prevent XSS attacks.
	 */
	function sanitize ($val, $charset = 'UTF-8') {
		return htmlspecialchars ($val, ENT_QUOTES | ENT_IGNORE, $charset);
	}

	/**
	 * Replace foreach and if blocks.
	 */
	function replace_blocks ($val) {
		if ($val == 'end' || $val == 'endif' || $val == 'endforeach') {
			return '<?php } ?>';
		}
		
		if (strstr ($val, ' ')) {
			list ($block, $extra) = explode (' ', $val, 2);
		} else {
			$block = $val;
			$extra = '';
		}
		if (strstr ($extra, '$_')) {
			if (strstr ($val, '.')) {
				$extra = preg_replace ('/\.([a-zA-Z0-9_]+)/', '[\'\1\']', $extra, 1);
			}
		} elseif (strstr ($extra, '.')) {
			$extra = '$GLOBALS[\'' . preg_replace ('/\./', '\']->', $extra, 1);
		} elseif (! strstr ($extra, '::') && ! strstr ($extra, '(')) {
			$extra = '$data->' . $extra;
		}
		if ($block == 'foreach') {
			return '<?php foreach (' . $extra . ' as $data->loop_index => $data->loop_value) { ?>';
		} elseif ($block == 'if') {
			return '<?php if (' . $extra . ') { ?>';
		} elseif ($block == 'elseif') {
			return '<?php } elseif (' . $extra . ') { ?>';
		} elseif ($block == 'else') {
			return '<?php } else { ?>';
		}
		die ('Invalid template block: ' . $val);
	}
}

?>