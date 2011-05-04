<?php

/**
 * Basic template renderer. Looks for templates via the pattern
 * apps/{app}/views/{file}.html where the template is passed as
 * 'app/file'. Failing that, it looks for layouts/{file}.html
 * and finally layouts/default.html. It then creates a PHP version
 * of the template  and caches it to cache/{app}-{template}.php,
 * so the cache  folder must be writeable. Auto-refreshes cached
 * versions when  the originals change.
 *
 * As a result, templates can include any PHP, along with tags of
 * the form:
 *
 *   {{ body }}
 *
 * And blocks of the form:
 *
 *   {% foreach pages %}
 *     {{ loop_index }} - {{ loop_value }}
 *   {% end %}
 *
 *   {% if some_val %}
 *     {{ some_val }}
 *   {% end %}
 *
 * Note the use of loop_index and loop_value, which are defined for
 * you inside foreach loops by the template engine.
 *
 * You can also test for more complex conditions, but make sure the
 * value being tested for is not preceeded by anything. For example,
 * no false checks via {% if !some_val %}, instead use:
 *
 *   {% if some_val == false %}
 *     {{ some_val }}
 *   {% end %}
 *
 * ## Usage in PHP
 *
 * To call a template, use:
 *
 *   echo $tpl->render ('base', array ('foo' => 'bar'));
 *
 * Note that arrays passed to templates are converted to objects,
 * and objects are left as-is.
 *
 * ## Globals
 *
 * In addition to the fields in the data array passed to render(),
 * you can also call global objects and class methods as follows
 * from within if and foreach blocks as well as variable
 * substitutions:
 *
 * Call User::constant_value:
 *
 *   {{ User::constant_value }}
 *
 * Call $GLOBALS['user']->name:
 *
 *   {{ user.name }}
 *
 * Call a function:
 *
 *   {{ db_shift ('select * from foo') }}
 *
 * In an if block:
 *
 *   {% if User::is_valid () %}
 *
 *   {% if user.name != '' %}
 *
 * In a foreach:
 *
 *   {% foreach Oject::some_method () %}
 *
 * Calling a superglobal:
 *
 *   {{ $_POST.value }}
 *
 * Note that these must come at the beginning of a statement, not
 * anywhere else within it. The replacement mechanism is very
 * simplistic.
 *
 * ## Filters
 *
 * Filtering is supported, and htmlspecialchars() is the default
 * filter unless another is specified or 'none' is supplied via:
 *
 *   {{ body|none }}
 *
 * Any valid function can be a filter, and filters can be chained,
 * executing in the following order:
 *
 *   {{ body|strtoupper|strtolower }}
 *
 *   <?php echo strtolower (strtoupper ($data->body)); ?>
 *
 * You can also set additional parameters to a filter as follows:
 *
 *   {{ timestamp|date ('F j', %s) }}
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
	function render ($template, $data) {
		if (is_array ($data)) {
			$data = (object) $data;
		}

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
			// regenerate cached file
			$out = file_get_contents ($file);
			$out = $this->parse_template ($out);
			if (! file_put_contents ($cache, $out)) {
				die ('Failed to generate cached template: ' . $cache);
			}
		}
		
		ob_start ();
		require_once ($cache);
		$out = ob_get_contents ();
		ob_end_clean ();
		return $out;
	}

	/**
	 * Replace values from template as string.
	 */
	function parse_template ($val) {
		$val = preg_replace ('/\{\{ ?(.*?) ?\}\}/e', '$this->replace_vars (\'\\1\')', $val);
		$val = preg_replace ('/\{\% ?(.*?) ?\%\}/e', '$this->replace_blocks (\'\\1\')', $val);
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
			return '<?php echo htmlspecialchars (' . $val . ', ENT_QUOTES, \'' . $this->charset . '\'); ?>';
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
	 * Replace foreach and if blocks.
	 */
	function replace_blocks ($val) {
		if ($val == 'end') {
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