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
 * Controller provides the request marshalling to Elefant handlers.
 * It evaluates `$_SERVER['REQUEST_URI']` against files in
 * a `apps/{appname}/handlers/` folder, defaulting to the handler
 * specified in `conf ('General', 'default_handler')` if no others match.
 *
 * Matching is done by reducing the URL folder-by-folder until a file
 * matches. Here are some examples:
 *
 *     / -> conf(General, default_handler)
 *
 *     /foo -> apps/foo/handlers/index.php,
 *             conf(General, default_handler)
 *
 *     /user/login -> apps/user/handlers/login.php,
 *                    apps/user/handlers/index.php,
 *                    conf(General, default_handler)
 *
 *     /user/info/123 -> apps/user/handlers/info/123.php,
 *                       apps/user/handlers/info.php,
 *                       apps/user/handlers/index.php,
 *                       conf(General, default_handler)
 *
 * The controller simply returns the matching URL so you can include
 * it via the following code:
 *
 *     <?php
 *
 *     $handler = $controller->route ($_SERVER['REQUEST_URI']);
 *     ob_start ();
 *     require_once ($handler);
 *     $page->body = ob_get_clean ();
 *
 *     ?>
 *
 * Or more simply (but in practice the same):
 *
 *     <?php
 *
 *     $handler = $controller->route ($_SERVER['REQUEST_URI']);
 *     $page->body = $controller->handle ($handler);
 *
 *     ?>
 *
 * In this way, there is less scaffolding code for individual controllers,
 * they can simply begin executing just like an ordinary PHP script, and
 * the output is simply echoed like an ordinary PHP script too.
 *
 * The remaining elements of the URL are accessible in the array
 * `$this->params`, so for `/user/123` handled by `handlers/user.php`,
 * you could get the value `'123'` via `$this->params[0]`.
 *
 * To use named parameters, you simply say:
 *
 *     <?php
 *
 *     list ($id, $title) = $this->params;
 *
 *     ?>
 *
 * You can also call one handler from within another and get its results
 * like this:
 *
 *     <?php
 *
 *     $res = $this->run ('/user/123');
 *
 *     ?>
 *
 * Sometimes you might want to pass values to another handler for internal
 * processing, which you can do like this:
 *
 *     <?php
 *
 *     $res = $this->run ('/user/123', array ('foo' => 'bar'));
 *
 *     ?>
 *
 * You can then access the array via:
 *
 *     <?php
 *
 *     echo $this->data['foo'];
 *
 *     ?>
 *
 * In addition to running one handler from another, you can configure
 * hooks with one or more handlers to be run for you when you trigger
 * the hook. This is a 3-step process:
 *
 * 1\. Add your hook and its handler to `conf/config.php`:
 *
 *     myapp/somehandler[] = otherapp/handler
 *
 * 2\. In `myapp/somehandler`, add the hook call and pass it some data:
 *
 *     <?php
 *
 *     $this->hook ('myapp/somehandler', array('id' => 123));
 *
 *     ?>
 *
 * 3\. In `otherapp/handler`, verify the request and do something
 * interesting with the id:
 *
 *     <?php
 *
 *     if (! $this->internal) {
 *         die ('Cannot call me from a browser.');
 *     }
 *
 *     if (! Form::verify_value ($this->data['id'], 'type', 'numeric')) {
 *         die ('Invalid id value');
 *     }
 *
 *     // do something with $this->data['id']
 *
 *     ?>
 */
class Controller {
	/**
	 * Extra parameters from the end of the URL.
	 */
	public $params = array ();

	/**
	 * Whether the request originated internally or externally.
	 */
	public $internal = true;

	/**
	 * Data sent from another handler to the current one.
	 */
	public $data = array ();

	/**
	 * Set to true if the request came from the command line.
	 */
	public $cli = false;

	/**
	 * A list of handlers defined to be called for each type of hook.
	 * Similar to the idea of webhooks, this provides a means of triggering
	 * handlers from each other without hard-coding the specific handlers in
	 * the triggering code. See `conf/config.php`'s `[Hooks]` section for examples.
	 */
	public static $hooks = array ();

	/**
	 * Keeps track of the number of times each handler has been called in
	 * this request. You can check `self::$called['handler']` for the
	 * number.
	 */
	public static $called = array ();

	/**
	 * Cached PUT data from get_put_data() so it only reads it the first
	 * time.
	 */
	public $put_data = null;

	/**
	 * The app that is being called. Set by `route()`.
	 */
	public $app;

	/**
	 * The uri that was last called, as parsed by `route()`. This will have
	 * preceding slashes trimmed, and if it resolves to a default like
	 * `app -> app/index`, then it will have the `/index` added.
	 */
	public $uri;

	/**
	 * Tracks which apps have been loaded.
	 */
	public static $loaded = array ();

	/**
	 * This will be set the first time `chunked()` is called, so the controller
	 * knows it's already started sending the response with
	 * `Transfer-Encoding: chunked`.
	 */
	public $chunked = false;

	/**
	 * Whether the current handler's output should be cached automatically
	 * when it returns. Set to `true` to cache indefinitely, and a number
	 * to set a timeout in seconds. The cache key will be the app and handler
	 * name, with slashes converted to underscores, e.g., `myapp_handler`.
	 *
	 * Usage:
	 *
	 *     <?php
	 *
	 *     // cache indefinitely
	 *     $this->cache = true;
	 *
	 *     // cache for 5 minutes
	 *     $this->cache = 300;
	 *
	 *     ?>
	 *
	 * To clear a cached handler before its time, which you would have to
	 * do from a separate handler since the original won't be called while
	 * cached, you can use:
	 *
	 *     <?php
	 *
	 *     $cache->delete ('myapp_handler');
	 *
	 *     ?>
	 */
	public $cache = false;

	/**
	 * The HTTP status code that's been set.
	 */
	public $status_code = false;

	/**
	 * Page object.
	 */
	private $_page;

	/**
	 * I18n object.
	 */
	private $_i18n;

	/**
	 * Cache object.
	 */
	private $_cache;

	/**
	 * Template object.
	 */
	private $_tpl;

	/**
	 * Constructor method. Receives a list of hooks as well
	 * as a Page and I18n object.
	 */
	public function __construct ($hooks = array ()) {
		if (defined ('STDIN')) {
			$this->cli = true;
		}
		self::$hooks = $hooks;
	}

	/**
	 * Get or set the template object.
	 */
	public function template ($tpl = false) {
		if ($tpl) {
			$this->_tpl = $tpl;
		}
		return $this->_tpl;
	}

	/**
	 * Get or set the cache object.
	 */
	public function cache ($cache = false) {
		if ($cache) {
			$this->_cache = $cache;
		}
		return $this->_cache;
	}

	/**
	 * Get or set the page object.
	 */
	public function page ($page = false) {
		if ($page) {
			$this->_page = $page;
		}
		return $this->_page;
	}

	/**
	 * Get or set the i18n object.
	 */
	public function i18n ($i18n = false) {
		if ($i18n) {
			$this->_i18n = $i18n;
		}
		return $this->_i18n;
	}

	/**
	 * Run an internal request from one handler to another.
	 */
	public function run ($uri, $data = array (), $internal = true) {
		$c = new Controller (conf ('Hooks'));
		$c->page ($this->_page);
		$c->i18n ($this->_i18n);
		$c->template ($this->_tpl);
		$c->cache ($this->_cache);
		$handler = $c->route ($uri);

		if (! isset (self::$called[$uri])) {
			self::$called[$uri] = 1;
		} else {
			self::$called[$uri]++;
		}

		return $c->handle ($handler, $internal, $data);
	}

	/**
	 * Trigger the default error handler. Note that you must echo the
	 * output from your handler before returning, for example:
	 *
	 *     <?php
	 *
	 *     echo $this->error ();
	 *     return;
	 *
	 *     ?>
	 *
	 * Not like this:
	 *
	 *     <?php
	 *
	 *     return $this->error ();
	 *
	 *     ?>
	 */
	public function error ($code = 404, $title = 'Page not found', $message = '') {
		// Erase any existing output up to this point
		ob_clean ();

		// Call the error handler
		return $this->run (conf ('General', 'error_handler'), array (
			'code' => $code,
			'title' => $title,
			'message' => $message
		));
	}

	/**
	 * Run any handlers for the specified hook type. Note that the
	 * output for hooks is ignored.
	 */
	public function hook ($type, $data = array ()) {
		if (! isset (self::$hooks[$type])) {
			return false;
		}
		$out = '';
		foreach (self::$hooks[$type] as $handler) {
			$out .= $this->run ($handler, $data);
		}
		return $out;
	}

	/**
	 * Takes a list of names as arguments and returns an associative
	 * array of the handler parameters using the names as keys. Note
	 * that it will return false if the number of names is different
	 * than the number of parameters. Handy for using named parameters
	 * via:
	 *
	 *     <?php
	 *
	 *     extract ($this->params ('id', 'title'));
	 *
	 *     ?>
	 *
	 * Note that you can also achieve the same thing via:
	 *
	 *     <?php
	 *
	 *     list ($id, $title) = $this->params;
	 *
	 *     ?>
	 */
	public function params () {
		$keys = func_get_args ();
		return array_combine ($keys, $this->params);
	}

	/**
	 * Execute the request handler. $internal determines whether the
	 * request originated internally from another handler or template,
	 * or externally from a browser request.
	 */
	public function handle ($handler, $internal = true, $data = array ()) {
		// Create local references to the page, template, cache, and i18n objects
		// for easier reference in handlers (e.g., simply `$tpl->render()`).
		$page = $this->_page;
		$tpl = $this->_tpl;
		$cache = $this->_cache;
		$i18n = $this->_i18n;

		// Check for a cached copy of this handler's output
		$cache_uri = '_c_' . str_replace ('/', '_', $this->uri);
		$out = $cache->get ($cache_uri);
		if ($out) {
			return $out;
		}

		// Set the handler data
		$this->internal = $internal;
		$data = (array) $data;
		$this->data = $data;

		if (! in_array ($this->app, self::$loaded)) {
			// Load app-specific language files on first call to app
			$i18n->initApp ($this->app);
		}
		// Load the app's configuration settings
		$appconf = Appconf::get ($this->app);

		// Run the handler and get its output
		ob_start ();
		$res = require ($handler);
		$out = ob_get_clean ();
		if (is_string ($res) && strlen ($res) > 0) {
			$out = $res;
		}

		// If this is a chunked request, flush and exit
		if ($this->chunked) {
			$this->flush ($out);
			$this->flush (NULL);
		}

		// If the handler is cacheable, cache the results before returning
		if ($this->cache !== FALSE) {
			$timeout = is_numeric ($this->cache) ? $this->cache : 0;
			$res = $cache->replace ($cache_uri, $out, 0, $timeout);
			if ($res === FALSE) {
				$cache->set ($cache_uri, $out, 0, $timeout);
			}
		}
		return $out;
	}

	/**
	 * Route a request URI to a file.
	 */
	public function route ($uri) {
		$exp = explode ('/', conf ('General', 'default_handler'));
		$this->app = array_shift ($exp);
		$this->params = array ();

		// Remove queries and hash from uri
		$uri = preg_replace ('/(\?|#).*$/', '', $uri);

		if (! $this->clean ($uri) || $uri === '/') {
			$uri = conf ('General', 'default_handler');
		}

		// Remove leading /
		$uri = ltrim ($uri, '/');

		// If no / and doesn't match an app's name with an index.php
		// handler, then use the default handler.
		if (! strstr ($uri, '/')) {
			if (file_exists ('apps/' . $uri . '/handlers/index.php')) {
				$uri .= '/index';
			} else {
				$this->add_param ($uri);
				$uri = conf ('General', 'default_handler');
			}
		}

		// Determine the handler by cascading through potential file names
		// until one matches.
		list ($app, $handler) = explode ('/', $uri, 2);
		$route = 'apps/' . $app . '/handlers/' . $handler . '.php';
		while (! file_exists ($route)) {
			$route = preg_replace ('/\/([^\/]*)\.php$/e', '$this->add_param (\'\\1\')', $route);
			if ($route === 'apps/' . $app . '/handlers.php') {
				if (file_exists ('apps/' . $app . '/handlers/index.php')) {
					$this->app = $app;
					$this->uri = $app . '/index';
					return 'apps/' . $app . '/handlers/index.php';
				}
				$this->uri = conf ('General', 'default_handler');
				$this->add_param ($app);
				list ($this->app, $handler) = explode ('/', $this->uri);
				return sprintf ('apps/%s/handlers/%s.php', $this->app, $handler);
			}
		}
		$this->app = $app;
		$this->uri = $uri;
		return $route;
	}

	/**
	 * Looks for an override of the current handler in the app
	 * configuration in a `[Custom Handlers]` section. Overrides
	 * are handlers that should be called transparently in place
	 * of the current handler, overriding its behaviour without
	 * modifying the original handler.
	 *
	 * An override setting's key should be the app/handler name,
	 * and the value can be either the same app/handler name
	 * (meaning no override), another app/handler name (meaning
	 * override with that handler), or Off (meaning disable the
	 * handler). A handler that has been disabled will return a
	 * 404 error.
	 *
	 * If the response is false, there was no override or disabling,
	 * and the handler should continue running, otherwise the
	 * response will contain the output of the override handler
	 * which should be echoed and the original handler should
	 * return and stop further execution.
	 */
	public function override ($handler) {
		static $overridden = array ();

		if (in_array ($handler, $overridden)) {
			// don't override the same handler
			// twice to prevent infinite loops
			return false;
		}
		$overridden[] = $handler;

		list ($app) = explode ('/', $handler);
		$custom = Appconf::get ($app, 'Custom Handlers', $handler);

		if (! $custom) {
			// disable this handler
			return $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
		}

		if ($custom !== $handler) {
			// override the handler
			$override = count ($this->params) ? $custom . '/' . join ('/', $this->params) : $custom;
			return $this->run ($override, $this->data, $this->internal);
		}

		// no override
		return false;
	}

	/**
	 * Is this URL clean of any directory manipulation attempts?
	 */
	public function clean ($url) {
		return ! strstr ($url, '..');
	}

	/**
	 * Adds to the start, since `route()` parse them off the end of the URI.
	 */
	public function add_param ($param) {
		array_unshift ($this->params, $param);
		return '.php';
	}

	/**
	 * Add a notification to the `elefant_notification` cookie, which is
	 * monitored by `admin/head` and will be displayed using jGrowl. Handy
	 * for setting confirmation messages and other notices for the current
	 * user to display on a subsequent screen.
	 */
	public function add_notification ($msg, $cookie_name = 'elefant_notification') {
		if (isset ($_COOKIE[$cookie_name])) {
			$msg = $_COOKIE[$cookie_name] . '|' . $msg;
		}
		return setcookie ($cookie_name, $msg, 0, '/');
	}

	/**
	 * Turn a relative URL into an absolute URL. If the `$base` is false,
	 * it will use the HTTP_HOST to construct a base for you.
	 */
	public function absolutize ($url, $base = false) {
		if (strpos ($url, '://') !== false) {
			// already contains scheme
			return $url;
		}

		if (strpos ($url, '//') === 0) {
			// scheme relative, add scheme
			return $this->is_https ()
				? 'https:' . $url
				: 'http:' . $url;
		}

		if ($base === false) {
			// construct the base from HTTP_HOST
			$base = $this->is_https ()
				? 'https://' . $_SERVER['HTTP_HOST']
				: 'http://' . $_SERVER['HTTP_HOST'];
		}

		if (strpos ($url, '/') === 0) {
			// absolute to the site root
			return $base . $url;
		}

		// relative, so assume the base contains enough of
		// a path prefix
		return (substr ($base, -1) === '/')
			? $base . $url
			: $base . '/' . $url;
	}

	/**
	 * Redirect the current request and exit.
	 */
	public function redirect ($url, $exit = true) {
		header ('Location: ' . $this->absolutize ($url));
		if ($exit) {
			$this->quit ();
		}
	}

	/**
	 * Permanently redirect to a new address and exit.
	 */
	public function permanent_redirect ($url, $exit = true) {
		header ('HTTP/1.1 301 Moved Permanently');
		$this->redirect ($url, $exit);
	}

	/**
	 * Wrapper around exit to work with subfolder installations.
	 */
	public function quit () {
		if (! defined ('SUB_FOLDER')) {
			exit;
		}
		throw new SubfolderException ();
	}

	/**
	 * Get the stdin stream for PUT requests.
	 */
	public function get_put_data () {
		if ($this->put_data === null) {
			$stdin = fopen ('php://input', 'r');
			$out = '';
			while ($data = fread ($stdin, 1024)) {
				$out .= $data;
			}
			fclose ($stdin);
			$this->put_data = $out;
		}
		return $this->put_data;
	}

	/**
	 * Get the raw POST data.
	 */
	public function get_raw_post_data () {
		return $GLOBALS['HTTP_RAW_POST_DATA'];
	}

	/**
	 * Get the request method. If X-HTTP-Method-Override header is set,
	 * it will return that instead of the actual request method.
	 */
	public function request_method () {
		return isset ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Get or set the HTTP response status code.
	 */
	public function status_code ($code = null, $text = '') {
		if ($code !== null && $code !== $this->status_code) {
			$this->status_code = $code;

			if (function_exists ('http_response_code')) {
				http_response_code ($code);
			} else {
				$proto = isset ($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

				switch ($code) {
					case 100: $text = 'Continue'; break;
					case 101: $text = 'Switching Protocols'; break;
					case 200: $text = 'OK'; break;
					case 201: $text = 'Created'; break;
					case 202: $text = 'Accepted'; break;
					case 203: $text = 'Non-Authoritative Information'; break;
					case 204: $text = 'No Content'; break;
					case 205: $text = 'Reset Content'; break;
					case 206: $text = 'Partial Content'; break;
					case 300: $text = 'Multiple Choices'; break;
					case 301: $text = 'Moved Permanently'; break;
					case 302: $text = 'Moved Temporarily'; break;
					case 303: $text = 'See Other'; break;
					case 304: $text = 'Not Modified'; break;
					case 305: $text = 'Use Proxy'; break;
					case 400: $text = 'Bad Request'; break;
					case 401: $text = 'Unauthorized'; break;
					case 402: $text = 'Payment Required'; break;
					case 403: $text = 'Forbidden'; break;
					case 404: $text = 'Not Found'; break;
					case 405: $text = 'Method Not Allowed'; break;
					case 406: $text = 'Not Acceptable'; break;
					case 407: $text = 'Proxy Authentication Required'; break;
					case 408: $text = 'Request Time-out'; break;
					case 409: $text = 'Conflict'; break;
					case 410: $text = 'Gone'; break;
					case 411: $text = 'Length Required'; break;
					case 412: $text = 'Precondition Failed'; break;
					case 413: $text = 'Request Entity Too Large'; break;
					case 414: $text = 'Request-URI Too Large'; break;
					case 415: $text = 'Unsupported Media Type'; break;
					case 500: $text = 'Internal Server Error'; break;
					case 501: $text = 'Not Implemented'; break;
					case 502: $text = 'Bad Gateway'; break;
					case 503: $text = 'Service Unavailable'; break;
					case 504: $text = 'Gateway Time-out'; break;
					case 505: $text = 'HTTP Version not supported'; break;
					default:  $text = (! empty ($text)) ? $text : 'Unknown'; break;
				}

				header ($protocol . ' ' . $code . ' ' . $text, true, $code);
			}
		}

		return $this->status_code;
	}

	/**
	 * Use an object's methods to handle RESTful requests for the current handler.
	 */
	public function restful ($obj) {
		// Disable page layout and set JSON header.
		$this->_page->layout = false;
		header ('Content-Type: application/json');

		// Try to determine a method to call from the HTTP request method
		// and the first extra parameter of the URL, or `_default`. For
		// example:
		//
		//     GET /myapp/api -> get__default()
		//     GET /myapp/api/foo -> get_foo()
		$request_method = strtolower ($this->request_method ());
		$action = isset ($this->params[0]) ? $this->params[0] : '_default';
		$method = $request_method . '_' . $action;

		// Verify the method exists.
		if (! method_exists ($obj, $method)) {
			if ($action !== '_default') {
				// The default hasn't been tried yet, so try that as a
				// fallback.
				$action = '_default';
				$method = $request_method . '_' . $action;

				// No fallback exists
				if (! method_exists ($obj, $method)) {
					return $obj->error ('Invalid action name');
				}
			} else {
				// Default was already tried, no go.
				return $obj->error ('Invalid action name');
			}
		}

		// Assign the controller and cache to the object.
		$obj->controller = $this;
		$obj->cache = $this->_cache;

		// Call the method with the extra URL parameters.
		$params = $this->params;
		if ($action !== '_default') {
			array_shift ($params);
		}
		try {
			$res = call_user_func_array (array ($obj, $method), $params);
		} catch (ErrorException $e) {
			$msg = $e->getMessage ();
			if (strpos ($msg, 'Missing argument') === 0) {
				return $obj->error ('Missing required argument');
			}
			return $obj->error ('Unexpected error occurred');
		}

		// If an error hasn't been output already, encode the response.
		if ($res !== null) {
			return $obj->wrap ($res);
		}
	}

	/**
	 * Returns a RESTful error response.
	 */
	public function restful_error ($message, $code = null) {
		// Disable page layout and set JSON header.
		$this->_page->layout = false;
		header ('Content-Type: application/json');

		$r = new Restful;
		$r->controller = $this;
		return $r->error ($message, $code);
	}

	/**
	 * Changes the response to use `Transfer-Encoding: chunked` and sends
	 * the current buffer to the client. Call this each time you want the
	 * script to send the next chunk of data to the client.
	 *
	 * Note that this will cause `render()` to call `flush(null)` at the end,
	 * which will not return your output to be included in a page layout.
	 * It will also flush and exit prior to setting a controller-level
	 * cache of your output.
	 */
	function flush ($out = false) {
		if (! $this->chunked) {
			header ('Transfer-Encoding: chunked');
			$this->chunked = true;
		}
		if ($out === null) {
			// Send an empty chunk and exit
			if (ob_get_level () > 0) {
				// Flush any existing data first
				$this->flush ();
			}
			echo "0\r\n\r\n";
			flush ();
			$this->quit ();
		} elseif ($out !== false) {
			// Send the data passed to flush()
			if (strlen ($out) > 0) {
				printf ("%X\r\n", strlen ($out));
				echo $out . "\r\n";
				flush ();
			}
		} else {
			// Send the current output buffer contents
			$out = '';
			while(ob_get_level() > 0) {
				$out .= ob_get_clean ();
			}
			if (strlen ($out) > 0) {
				printf ("%X\r\n", strlen ($out));
				echo $out . "\r\n";
				flush ();
			}
			ob_start ();
		}
	}

	/**
	 * Returns whether the current request is made over HTTPS or not.
	 */
	public function is_https () {
		if (! isset ($_SERVER['HTTPS']) || strtolower ($_SERVER['HTTPS']) !== 'on') {
			return false;
		}
		return true;
	}

	/**
	 * Forces the current request to be over HTTPS instead of HTTP
	 * via redirect if necessary.
	 */
	public function force_https () {
		if (! $this->is_https ()) {
			$this->redirect ('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
	}

	/**
	 * Forces the current request to be over HTTP instead of HTTPS
	 * via redirect if necessary.
	 */
	public function force_http () {
		if ($this->is_https ()) {
			$this->redirect ('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
	}

	/**
	 * Require the user to be logged in to proceed with the request.
	 * If not, it will redirect to the appropriate login handler.
	 */
	public function require_login ($redirect = '/user/login') {
		if (! User::require_login ()) {
			$this->redirect ($redirect . '?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
		}
	}

	/**
	 * Require the user to be an administrator to proceed with the request.
	 * If not, it will redirect to the appropriate admin login handler.
	 */
	public function require_admin ($redirect = '/admin') {
		if (! User::require_admin ()) {
			$this->redirect ($redirect . '?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
		}
	}

	/**
	 * Require the user to have access to one or more resources. Accepts
	 * any number of parameters, which should be resource names. If any
	 * resource fails, it will redirect to the `/admin` login screen.
	 *
	 * Usage:
	 *
	 *     $this->require_acl ('admin', 'admin/edit');
	 */
	public function require_acl ($resource) {
		$args = func_get_args ();
		foreach ($args as $resource) {
			if (! User::require_acl ($resource)) {
				$this->redirect ('/admin');
			}
		}
	}

	/**
	 * Require authentication via custom callbacks that are passed to `simple_auth()`.
	 * If the second callback is missing, the first will be assumed to be an array
	 * containing the two callbacks.
	 *
	 * See `apps/user/lib/Auth` for built-in auth handlers.
	 */
	public function require_auth ($verifier, $method = false) {
		if ($method === false) {
			list ($verifier, $method) = $verifier;
		}
		return simple_auth ($verifier, $method);
	}

	/**
	 * Check if an app and version have been installed. Returns true if
	 * installed, false if not, and current installed version if an upgrade
	 * should be performed.
	 */
	public function installed ($app, $version) {
		$v = DB::shift ('select version from #prefix#apps where name = ?', $app);
		if (! $v) {
			return false;
		}
		if (version_compare ($version, $v) === 0) {
			return true;
		}
		return $v;
	}

	/**
	 * Mark an app and version as installed.
	 */
	public function mark_installed ($app, $version) {
		$v = DB::shift ('select version from #prefix#apps where name = ?', $app);
		if ($v) {
			return DB::execute ('update #prefix#apps set version = ? where name = ?', $version, $app);
		}
		return DB::execute ('insert into #prefix#apps (name, version) values (?, ?)', $app, $version);
	}
}

?>
