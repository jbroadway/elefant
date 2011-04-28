<?php

/**
 * Basic template renderer. Looks for templates via the pattern
 * views/{template}.html. Creates a PHP versions of the template
 * and caches it to views/cache/{template}.php, so the views/cache
 * folder must be writeable. Auto-refreshes cached versions when
 * the original changes.
 *
 * As a result, templates can include any PHP, along with tags of
 * the form:
 *
 *   {{ body }}
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
 *
 * To call a template, use:
 *
 *   echo $tpl->render ('base', array ('foo' => 'bar'));
 *
 * Note that arrays passed to templates are converted to objects,
 * and objects are left as-is.
 */
class Template {
	var $charset = 'UTF-8';
	var $quotes = ENT_QUOTES;

	function __construct ($charset = 'UTF-8') {
		$this->charset = $charset;
	}

	function render ($template, $data) {
		if (is_array ($data)) {
			$data = (object) $data;
		}

		if (! file_exists ('views/' . $template . '.html')) {
			$template = 'base';
		}
		$file = 'views/' . $template . '.html';
		$cache = 'views/cache/' . $template . '.php';
		
		if (! file_exists ($cache) || filemtime ($file) > filemtime ($cache)) {
			// regenerate cached file
			$out = file_get_contents ($file);
			$out = preg_replace ('/\{\{ ?(.*?) ?\}\}/e', '$this->replace (\'\\1\', \'\\2\')', $out);

			file_put_contents ($cache, $out);
		}
		
		ob_start ();
		require_once ($cache);
		$out = ob_get_contents ();
		ob_end_clean ();
		return $out;
	}

	function replace ($val) {
		$filters = explode ('|', $val);
		$val = array_shift ($filters);

		if (count ($filters) == 0) {
			return '<?php echo htmlspecialchars ($data->' . $val . ', ENT_QUOTES, \'' . $this->charset . '\'); ?>';
		} else if ($filters[0] == 'none') {
			return '<?php echo $data->' . $val . '; ?>';
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
		return $out . '$data->' . $val . $end;
	}
}

?>