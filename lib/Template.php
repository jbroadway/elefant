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
 * Template is a template compiler and rendering engine. It looks
 * for templates via the pattern `apps/{app}/views/{file}.html`
 * where the template is passed as `'app/file'`. Failing that, it
 * looks for `layouts/{file}.html` and finally `layouts/default.html`.
 * It then creates a PHP version of the template  and caches it to
 * `cache/{app}-{template}.php`, so the cache folder must be writeable.
 * Auto-refreshes cached versions when the originals change.
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
 * you inside foreach loops by the template engine, or you can specify
 * your own key and value names:
 *
 *     {% for pages as key, page %}
 *         {{ key }} - {{ page }}
 *     {% end %}
 *
 * You can also test for more complex conditions, for example:
 *
 *     {% if ! some_val %}
 *         No value.
 *     {% end %}
 *
 *     {% if some_val == 'some value' %}
 *         Value: {{ some_val }}
 *     {% end %}
 *
 * Note that `endif` and `endforeach` are valid as well as `end`,
 * if you prefer, for the sake of clarity.
 *
 * Here's one more example of how to loop through an array of arrays:
 *
 *     {% foreach my_list %}
 *         {% foreach loop_value %}
 *             {{ loop_index }}. {{ loop_value }}<br />
 *         {% end %}
 *     {% end %}
 *
 * To break up your template into smaller parts, you can use the `inc`
 * tag to include one template from inside another. For example:
 *
 *     {% inc header %}
 *
 * This will include the contents of `layouts/header.html` into the
 * current template, with the same data passed to it as the main template
 * file.
 *
 * You can also specify subfolders in this way, to better organize your
 * templates into themes. If you have a theme named `layouts/mytheme`
 * then you can include a `header.html` template within it via:
 *
 *     {% inc mytheme/header %}
 *
 * Note that this will first look for `apps/mytheme/views/header.html`,
 * which would be the most frequently desired behaviour, and second it
 * will look for `layouts/mytheme/header.html`, so be sure to name your
 * themes with unique names that do not conflict with the names of apps.
 *
 * ## Usage in PHP
 *
 * To call a template, use:
 *
 *     <?php
 *     
 *     echo $tpl->render ('base', array ('foo' => 'bar'));
 *     
 *     ?>
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
 *     {{ DB::shift ('select * from foo') }}
 *
 * In an if block:
 *
 *     {% if User::is_valid () %}
 *
 *     {% if user.name != '' %}
 *
 * In a foreach:
 *
 *     {% foreach Object::some_method () %}
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
 * Or you can precompile them once when the template is compiled so the
 * output of the handler is hard-coded into the template at compile time
 * like this:
 *
 *     {# app/handler?param=value #}
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
 * This evaluates to:
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
 *     __ ('Text here')
 */
class Template {
	/**
	 * The character encoding.
	 */
	public $charset = 'UTF-8';

	/**
	 * The cache location.
	 */
	public $cache_folder = 'cache';

	/**
	 * The layouts location.
	 */
	public $layouts_folder = 'layouts';
	
	/**
	 * The app view locations.
	 */
	public $view_folders = 'apps/%s/views/%s';
	
	/**
	 * Default layout filename.
	 */
	public $default_layout = 'default';
	
	/**
	 * File extension.
	 */
	public $file_extension = 'html';

	/**
	 * The controller object used to run includes. The controller can be
	 * any object that satisfies the following interface:
	 *
	 *     interface AbstractController {
	 *         public function run ($uri, $data = array ());
	 *     }
	 */
	public $controller = null;

	/**
	 * Constructor method sets the charset and receives a controller object.
	 */
	public function __construct ($charset = 'UTF-8', $controller = false) {
		$this->charset = $charset;
		if ($controller) {
			$this->controller = $controller;
		}
	}

	/**
	 * Render a template with the given data. Generate the PHP template if
	 * necessary.
	 */
	public function render ($template, $data = array ()) {
		if (is_array ($data)) {
			$data = (object) $data;
		}
		$data->is_being_rendered = true;
		$data->template_id = str_replace ('/', '-', $template);

		// Resolve the template to a file name, in one of:
		// `apps/appname/views/filename.html`
		// `layouts/themename/filename.html`
		// `layouts/filename.html`
		// `layouts/filename/filename.html`
		// `layouts/default.html`
		if (strstr ($template, '/')) {
			list ($app, $view) = preg_split ('/\//', $template, 2);
			$file = sprintf ($this->view_folders, $app, $view . '.' . $this->file_extension);
			if (! file_exists ($file)) {
				$file = $this->layouts_folder . '/' . $app . '/' . $view . '.' . $this->file_extension;
				if (! file_exists ($file)) {
					throw new RuntimeException ('Template not found: ' . $template);
				}
			}
		} elseif (file_exists ($this->layouts_folder . '/' . $template . '.' . $this->file_extension)) {
			$file = $this->layouts_folder . '/' . $template . '.' . $this->file_extension;
		} elseif (file_exists ($this->layouts_folder . '/' . $template . '/' . $template . '.' . $this->file_extension)) {
			$file = $this->layouts_folder . '/' . $template . '/' . $template . '.' . $this->file_extension;
		} else {
			$file = $this->layouts_folder . '/' . $this->default_layout . '.' . $this->file_extension;
		}

		// The cache file is named based on the original
		$cache = $this->cache_folder . '/' . str_replace ('/', '-', $template) . '.php';

		if (! file_exists ($cache) || filemtime ($file) > filemtime ($cache)) {
			// Regenerate cached file
			$out = file_get_contents ($file);
			$out = $this->parse_template ($out);
			if (! is_writeable (dirname ($cache))) {
				die ('Cache folder must be writeable to continue. Please check the <a href="http://www.elefantcms.com/wiki/Installing-Elefant" target="_blank">installation instructions</a> and try again.');
			}
			if (! file_put_contents ($cache, $out)) {
				throw new RuntimeException ('Failed to generate cached template: ' . $cache);
			}
		}
		
		ob_start ();
		require ($cache);
		return ob_get_clean ();
	}

	/**
	 * Render a template from a string with the given data. Generates
	 * the PHP and caches it.
	 */
	public function render_string ($template, $data = array (), $file_prefix = '_string_') {
		if (is_array ($data)) {
			$data = (object) $data;
		}
		$data->is_being_rendered = true;
		
		$cache_file = $this->cache_folder . '/' . $file_prefix . '' . md5 ($template) . '.php';
		if (! file_exists ($cache_file)) {
			$out = $this->parse_template ($template);
			if (! file_put_contents ($cache_file, $out)) {
				throw new RuntimeException ('Failed to generate cached template: ' . $cache_file);
			}
		}
		
		// Include the temp file and return the output
		ob_start ();
		require ($cache_file);
		$out = ob_get_clean ();
		return $out;
	}

	/**
	 * Render a template string for preview purposes. Generates a temporary
	 * cached version but unlinks it immediately after use.
	 */
	public function render_preview ($template, $data = array ()) {
		$out = $this->render_string ($template, $data, '_preview_');
		$cache_file = $this->cache_folder . '/_preview_' . md5 ($template) . '.php';
		unlink ($cache_file);
		return $out;
	}

	/**
	 * Replace values from template as string into PHP code equivalents.
	 * Note that this method never receives the original data sent to the
	 * template, so it can't accidentally embed user data into the PHP
	 * code, eliminating the possibility of exposing a security hole.
	 */
	public function parse_template ($val) {
		$val = str_replace (
			array (
				'\\{{', '\\}}', '\\{%', '\\%}', '\\{"', '\\"}', '\\{\'', '\\\'}',
				'\\{!', '\\!}', '\\{#', '\\#}'
			),
			array (
				'#EOBRACE#', '#ECBRACE#', '#EOBLOCK#', '#ECBLOCK#', '#EOQUOTE#', '#ECQUOTE#',
				'#EOQUOTE#', '#ECQUOTE#', '#EOINCLUDE#', '#ECINCLUDE#', '#EOHARDCODE#',
				'#ECHARDCODE#'
			),
			$val
		);
		$val = preg_replace_callback ('/\{\{ ?(.*?) ?\}\}/', array ($this, 'replace_vars'), $val);
		$val = preg_replace_callback ('/\{[\'"] ?(.*?) ?[\'"]\}/', array ($this, 'replace_strings'), $val);
		$val = preg_replace_callback ('/\{\% ?(.*?) ?\%\}/', array ($this, 'replace_blocks'), $val);
		$val = preg_replace_callback ('/\{\! ?(.*?) ?\!\}/s', array ($this, 'replace_includes'), $val);
		$val = preg_replace_callback ('/\{# ?(.*?) ?#\}/s', array ($this, 'hard_codes'), $val);
		$val = str_replace (
			array (
				'#EOBRACE#', '#ECBRACE#', '#EOBLOCK#', '#ECBLOCK#', '#EOQUOTE#', '#ECQUOTE#',
				'#EOINCLUDE#', '#ECINCLUDE#', '#EOHARDCODE#', '#ECHARDCODE#'),
			array (
				'{{', '}}', '{%', '%}', '{"', '"}', '{!', '!}', '{#', '#}'
			),
			$val
		);
		return $val;
	}

	/**
	 * Replace variables of the form:
	 *
	 *     {{ some_var }}
	 *
	 * Also applies filters, which can take the following forms:
	 *
	 *     {{ some_var }}							# defaults to Template::sanitize()
	 *     {{ some_var|none }}						# no filter
	 *     {{ some_var|strtoupper }}				# filters are php functions
	 *     {{ some_var|strrev|strtolower }}			# filters can be chained
	 *     {{ some_var|my_function }}				# calling a custom function
	 *     {{ some_var|date (%s, "F j, Y") }}		# use %s for multiple-parameter functions
	 */
	public function replace_vars ($regs) {
		$val = is_array ($regs) ? $regs[1] : $regs;

		// Get any filters
		$filters = explode ('|', $val);
		$val = array_shift ($filters);

		// Change `$_GLOBAL.value` into `$_GLOBAL['value']`
		if (strstr ($val, '$_')) {
			if (strstr ($val, '.')) {
				$val = preg_replace ('/\.([a-zA-Z0-9_]+)/', '[\'\1\']', $val, 1);
			}

		// Change `object.value` into `$GLOBALS['object']->value`
		} elseif (strstr ($val, '.')) {
			$val = '$GLOBALS[\'' . preg_replace ('/\./', '\']->', $val, 1);

		// Ordinary request for `$data->value`
		} elseif (! strstr ($val, '::') && ! strstr ($val, '(')) {
			$val = '$data->' . $val;
		}

		// Does it have an assignment?
		if (strstr ($val, '=')) {
			return '<?php ' . $val . '; ?>';
		}

		// Apply default filter or none
		if (count ($filters) === 0) {
			return '<?php echo Template::sanitize (' . $val . ', \'' . $this->charset . '\'); ?>';
		} elseif (trim ($filters[0]) === 'none') {
			return '<?php echo ' . $val . '; ?>';
		}

		// Apply specified filters
		$filters = array_reverse ($filters);
		$out = '<?php echo ';
		$end = '; ?>';
		foreach ($filters as $filter) {
			if (strstr ($filter, '%s')) {
				list ($one, $two) = explode ('%s', $filter);
				$out .= $one;
				$end = $two . $end;
			} elseif (trim ($filter) === 'quotes') {
				$out .= 'Template::quotes (';
				$end = ')' . $end;
			} elseif (trim ($filter) === 'sanitize') {
				$out .= 'Template::sanitize (';
				$end = ', \'' . $this->charset . '\')' . $end;
			} elseif (trim ($filter) === 'autolink') {
				$out .= 'Template::autolink (';
				$end = ')' . $end;
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
	public function replace_includes ($regs) {
		$val = is_array ($regs) ? $regs[1] : $regs;

		// remove spaces before ? and & for multiline includes
		$val = preg_replace ('/\n(\t+| {2,})(\?|\&)/', '\2', trim ($val));

		$url = parse_url ($val);
		if (isset ($url['query'])) {
			parse_str (html_entity_decode ($url['query'], ENT_COMPAT, 'UTF-8'), $data);
		} else {
			$data = array ();
		}
		$arr = '';
		$sep = '';
		foreach ($data as $k => $v) {
			if (is_array ($v)) {
				$arr .= sprintf ('%s\'%s\' => array (', $sep, $k);
				$sep2 = '';
				foreach ($v as $a) {
					$arr .= sprintf ('%s\'%s\'', $sep2, $a);
					$sep2 = ', ';
				}
				$arr .= ')';
			} elseif (strpos ($v, '[') === 0 && $v[strlen ($v) - 1] === ']') {
				$v = str_replace (
					array ('<?php echo ', '; ?>'),
					array ('', ''),
					$this->replace_vars (array (null, substr ($v, 1, strlen ($v) - 2)))
				);
				$arr .= sprintf ('%s\'%s\' => %s', $sep, $k, $v);
			} elseif (preg_match ('/\[(.*)\]/', $v, $regs)) {
				$r = str_replace (
					array ('<?php echo ', '; ?>'),
					array ('\' . ', ' . \''),
					$this->replace_vars (array (null, $regs[1]))
				);
				$v = preg_replace ('/\[(.*)\]/', $r, $v);
				$arr .= sprintf ('%s\'%s\' => \'%s\'', $sep, $k, $v);
			} else {
				$arr .= sprintf ('%s\'%s\' => \'%s\'', $sep, $k, $v);
			}
			$sep = ', ';
		}
		return sprintf (
			'<?php echo $this->controller->run (\'%s\', array (%s)); ?>',
			$url['path'],
			$arr
		);
	}

	/**
	 * Replace `{# app/handler?param=value #}` with the hard-coded output from
	 * a call to `Controller::run()`. Note that you cannot use sub-expressions
	 * here like you can with the dynamic `{! app/handler !}` calls.
	 */
	public function hard_codes ($regs) {
		$val = is_array ($regs) ? $regs[1] : $regs;

		// remove spaces before ? and & for multiline includes
		$val = preg_replace ('/\n(\t+| {2,})(\?|\&)/', '\2', trim ($val));

		$url = parse_url ($val);
		if (isset ($url['query'])) {
			parse_str (html_entity_decode ($url['query'], ENT_COMPAT, 'UTF-8'), $data);
		} else {
			$data = array ();
		}
		return $this->controller->run ($url['path'], $data);
	}

	/**
	 * Run any includes and include their output in the return value. Primarily
	 * for page body in the admin app. This only evaluates `{! app/handler !}`
	 * style tags.
	 */
	public function run_includes ($val) {
		// remove spaces before ? and & for multiline includes
		$val = preg_replace ('/\n(\t+| {2,})(\?|\&)/', '\2', trim ($val));

		// normalize <span data-embed> tags to {! tags !} embeds.
		// also remove enclosing <p>, <br> and spacing that may have
		// been added around it by the wysiwyg editor.
		$val = preg_replace ('/(<p>\s*?)?<[^>]*?data-embed="([^"]+)".*?>.*?<\/.*?>(\s*?(<br>)?\s*?<\/p>)?/s', '{! \2 !}', $val);

		$parts = preg_split ('/(\{\! ?.*? ?\!\})/', $val, -1, PREG_SPLIT_DELIM_CAPTURE);
		$out = '';
		foreach ($parts as $part) {
			if (strpos ($part, '{!') === 0) {
				$part = trim ($part, '{! }');
				$url = parse_url ($part);
				if (! isset ($url['host'])) {
					$url['host'] = '';
				}
				if (isset ($url['query'])) {
					parse_str (html_entity_decode ($url['query'], ENT_COMPAT, 'UTF-8'), $data);
				} else {
					$data = array ();
				}
				$out .= $this->controller->run ($url['host'] . $url['path'], $data);
			} else {
				$out .= $part;
			}
		}
		return $out;
	}

	/**
	 * Replace strings with calls to `__ ()` for multilingual sites.
	 * Translatable strings take the following form using either double
	 * or single quotes:
	 *
	 *     {" some text here "}
	 *     {' some text here '}
	 */
	public function replace_strings ($regs) {
		$val = is_array ($regs) ? $regs[1] : $regs;

		return '<?php echo __ (\'' . str_replace ('\'', '\\\'', $val) . '\'); ?>';
	}

	/**
	 * Sanitize a value for safe output, helping to prevent XSS attacks.
	 * Please note that this method can still be insecure if used in an
	 * unquoted portion of an HTML tag, for example:
	 *
	 * Don't do this:
	 *
	 *     <a href="/example" {{ some_var }}>click me</a>
	 *
	 * But this is okay:
	 *
	 *     <a href="/example" id="{{ some_var }}">click me</a>
	 *
	 * And so is this:
	 *
	 *     <span id="some-var">{{ some_var }}</span>
	 *
	 * In the first case, if the string were to contain something like
	 * `onclick=alert(document.cookie)` then your visitors would be
	 * exposed to the malicious JavaScript.
	 *
	 * The key is to know where your data comes from, and to act accordingly.
	 * Not all cases of the first example are necessarily a security hole,
	 * but it should only be used if you know the source and have validated
	 * your data beforehand.
	 */
	public static function sanitize ($val, $charset = 'UTF-8') {
		if (! defined ('ENT_SUBSTITUTE')) {
			define ('ENT_SUBSTITUTE', ENT_IGNORE);
		}
		return htmlspecialchars ($val, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
	}

	/**
	 * Convert quotes to HTML entities for form input values.
	 * Note: This should only be done for *trusted* data, as it does
	 * not prevent XSS attacks.
	 *
	 * Usage as a template filter:
	 *
	 *     {{ text|quotes }}
	 */
	public static function quotes ($val) {
		return str_replace ('"', '&quot;', $val);
	}
	
	/**
	 * Replace links in text with html links. For use as a
	 * template filter via:
	 *
	 *     {{ text|autolink }}
	 *
	 * Note that you will likely want to sanitize your output too:
	 *
	 *     {{ text|sanitize|autolink }}
	 *
	 * Or even add line breaks too:
	 *
	 *     {{ text|sanitize|autolink|nl2br }}
	 *
	 * Based on: http://stackoverflow.com/a/1959073/1092725
	 */
	public static function autolink ($text) {
		$pattern = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';

		$callback = function ($matches) {
			$url = array_shift ($matches);
			$url_parts = parse_url ($url);

			$text = parse_url ($url, PHP_URL_HOST) . parse_url ($url, PHP_URL_PATH);
			$text = preg_replace ("/^www\./", "", $text);
			$text = trim ($text, '/');

			return sprintf ('<a rel="nofollow" href="%s">%s</a>', $url, $text);
		};

		return preg_replace_callback ($pattern, $callback, $text);
	}

	/**
	 * Replace foreach and if blocks. Handles the following forms:
	 *
	 *     {% foreach some_list %}
	 *     {% endforeach %}
	 *
	 *     {% foreach some_list as key, item %}
	 *     {% endforeach %}
	 *
	 *     {% for some_list %}
	 *     {% endfor %}
	 *
	 *     {% for some_list as item %}
	 *     {% endfor %}
	 *
	 *     {% if statement %}
	 *     {% elseif statement %}
	 *     {% else %}
	 *     {% endif %}
	 *
	 * Note: `for` and `endfor` are aliases of `foreach` and `endforeach`.
	 * You can also use `{% end %}` as an alias for `{% endforeach %}`,
	 * `{% endfor %}` or `{% endif %}`.
	 *
	 * If an `as` clause isn't specified, the current loop index is available
	 * via `{{ loop_index }}~ and the current loop value is available via `{{ loop_value }}`.
	 *
	 * If an `as` clause is specified without a key, the current loop index is available
	 * via `{{ loop_index }}`.
	 */
	public function replace_blocks ($regs) {
		$val = is_array ($regs) ? $regs[1] : $regs;

		if ($val === 'end' || $val === 'endif' || $val === 'endforeach' || $val === 'endfor') {
			return '<?php } ?>';
		}
		
		if (strstr ($val, ' ')) {
			list ($block, $extra) = explode (' ', $val, 2);
		} else {
			$block = $val;
			$extra = '';
		}

		if ($block === 'inc' || $block === 'include') {
			if (preg_match ('/\[(.*)\]/', $extra, $regs)) {
				$r = str_replace (
					array ('<?php echo ', '; ?>'),
					array ('\' . ', ' . \''),
					$this->replace_vars (array (null, $regs[1]))
				);
				$extra = preg_replace ('/\[(.*)\]/', $r, $extra);
			}
			return '<?php echo $this->render (\'' . $extra . '\', $data); ?>';
		}

		if (($block === 'if' || $block === 'elseif') && strpos (ltrim ($extra), '!') === 0) {
			$not = '! ';
			$extra = ltrim ($extra, '! ');
		} else {
			$not = '';
		}

		if (($block === 'foreach' || $block === 'for') && strpos ($extra, ' in ') !== false) {
			$extra = preg_replace ('/^(.*) in (.*)$/', '\2 as \1', $extra);
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
		if ($block === 'foreach' || $block === 'for') {
			if (strpos ($extra, ' as ') !== false) {
				if (strpos ($extra, ', ') === false) {
					return '<?php foreach (' . str_replace (' as ', ' as $data->loop_index => $data->', $extra) . ') { ?>';
				}
				return '<?php foreach (' . str_replace (
					array (' as ', ', '),
					array (' as $data->', ' => $data->'),
					$extra
				) . ') { ?>';
			}
			return '<?php foreach (' . $extra . ' as $data->loop_index => $data->loop_value) { ?>';
		} elseif ($block === 'if') {
			return '<?php if (' . $not . $extra . ') { ?>';
		} elseif ($block === 'elseif') {
			return '<?php } elseif (' . $not . $extra . ') { ?>';
		} elseif ($block === 'else') {
			return '<?php } else { ?>';
		}
		throw new LogicException ('Invalid template block: ' . $val);
	}
}
