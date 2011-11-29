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
 * Basic routing controller. Maps `$_SERVER['REQUEST_URI']` to files in
 * a `apps/{appname}/handlers/` folder, defaulting to
 * `$conf['General']['default_handler']` if no others match.
 *
 * Matching is done by reducing the URL folder-by-folder until a file
 * matches. Here are some examples:
 *
 *     / -> $conf[default_handler]
 *
 *     /foo -> apps/foo/handlers/index.php,
 *             $conf[default_handler]
 *
 *     /user/login -> apps/user/handlers/login.php,
 *                    apps/user/handlers/index.php,
 *                    $conf[default_handler]
 *
 *     /user/info/123 -> apps/user/handlers/info/123.php,
 *                       apps/user/handlers/info.php,
 *                       apps/user/handlers/index.php,
 *                       $conf[default_handler]
 *
 * The controller simply returns the matching URL so you can include
 * it via the following code:
 *
 *     $handler = $controller->route ($_SERVER['REQUEST_URI']);
 *     ob_start ();
 *     require_once ($handler);
 *     $page->body = ob_get_clean ();
 *
 * Or more simply (but in practice the same):
 *
 *     $handler = $controller->route ($_SERVER['REQUEST_URI']);
 *     $page->body = $controller->handle ($handler);
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
 *     list ($id, $title) = $this->params;
 *
 * You can also call one handler from within another and get its results
 * like this:
 *
 *     $res = $this->run ('/user/123');
 *
 * Sometimes you might want to pass values to another handler for internal
 * processing, which you can do like this:
 *
 *     $res = $this->run ('/user/123', array ('foo' => 'bar'));
 *
 * You can then access the array via:
 *
 *     echo $this->data['foo'];
 *
 * In addition to running one handler from another, you can configure
 * hooks with one or more handlers to be run for you when you trigger
 * the hook. This is a 3-step process:
 *
 * 1. Add your hook and its handler to `conf/config.php`:
 *
 *     myapp/somehandler[] = otherapp/handler
 *
 * 2. In `myapp/somehandler`, add the hook call and pass it some data:
 *
 *     $this->hook ('myapp/somehandler', array('id' => 123));
 *
 * 3. In `otherapp/handler`, verify the request and do something
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
	public $hooks = array ();

	/**
	 * Keeps track of the number of times each handler has been called in
	 * this request. You can check `$controller->called['handler']` for
	 * the number, but make sure you use $controller and not $this in
	 * handlers to make sure it refers to the global controller.
	 */
	public $called = array ();

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
	 * When a handler is loaded, if there is a `conf/config.php` for that
	 * app, its contents will be loaded into `$appconf['appname']` once
	 * the first time it is called, and accessible thereafter by any
	 * handler in that app directly via $appconf.
	 */
	public $appconf = array ();

	/**
	 * Whether the current handler's output should be cached automatically
	 * when it returns. Set to `true` to cache indefinitely, and a number
	 * to set a timeout in seconds. The cache key will be the app and handler
	 * name, with slashes converted to underscores, e.g., `myapp_handler`.
	 *
	 * Usage:
	 *
	 *     // cache indefinitely
	 *     $this->cache = true;
	 *
	 *     // cache for 5 minutes
	 *     $this->cache = 300;
	 *
	 * To clear a cached handler before its time, which you would have to
	 * do from a separate handler since the original won't be called while
	 * cached, you can use:
	 *
	 *     $memcache->delete ('myapp_handler');
	 */
	public $cache = false;

	public function __construct ($hooks = array ()) {
		if (defined ('STDIN')) {
			$this->cli = true;
		}
		$this->hooks = $hooks;
	}

	/**
	 * Run an internal request from one handler to another.
	 */
	public function run ($uri, $data = array ()) {
		global $controller;
		$c = new Controller;
		$handler = $c->route ($uri);

		if (! isset ($controller->called[$uri])) {
			$controller->called[$uri] = 1;
		} else {
			$controller->called[$uri]++;
		}

		return $c->handle ($handler, true, $data);
	}

	/**
	 * Trigger the default error handler. Note that you must echo the
	 * output from your handler before returning, for example:
	 *
	 *     echo $this->error ();
	 *     return;
	 *
	 * Not like this:
	 *
	 *     return $this->error ();
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
		if (! isset ($this->hooks[$type])) {
			return false;
		}
		foreach ($this->hooks[$type] as $handler) {
			$this->run ($handler, $data);
		}
	}

	/**
	 * Takes a list of names as arguments and returns an associative
	 * array of the handler parameters using the names as keys. Note
	 * that it will return false if the number of names is different
	 * than the number of parameters. Handy for using named parameters
	 * via:
	 *
	 *     extract ($this->params ('id', 'title'));
	 *
	 * Note that you can also achieve the same thing via:
	 *
	 *     list ($id, $title) = $this->params;
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
		global $controller, $page, $tpl, $memcache;
		
		$out = $memcache->get (str_replace ('/', '_', $this->uri));
		if ($out) {
			return $out;
		}

		$this->internal = $internal;
		$data = (array) $data;
		$this->data = $data;
		if (! isset ($controller->appconf[$this->app])) {
			if (@file_exists ('apps/' . $this->app . '/conf/config.php')) {
				$controller->appconf[$this->app] = parse_ini_file ('apps/' . $this->app . '/conf/config.php', true);
			} else {
				$controller->appconf[$this->app] = array ();
			}
		}
		$appconf = $controller->appconf[$this->app];
		ob_start ();
		require ($handler);
		$out = ob_get_clean ();
		if ($this->cache !== false) {
			$timeout = is_numeric ($this->cache) ? $this->cache : 0;
			$res = $memcache->replace (str_replace ('/', '_', $this->uri), $out, 0, $timeout);
			if ($res === false) {
				$memcache->set (str_replace ('/', '_', $this->uri), $out, 0, $timeout);
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
			if (@file_exists ('apps/' . $uri . '/handlers/index.php')) {
				$uri .= '/index';
			} else {
				$this->add_param ($uri);
				$uri = conf ('General', 'default_handler');
			}
		}

		list ($app, $handler) = preg_split ('/\//', $uri, 2);
		$route = 'apps/' . $app . '/handlers/' . $handler . '.php';
		while (! @file_exists ($route)) {
			$route = preg_replace ('/\/([^\/]*)\.php$/e', '$this->add_param (\'\\1\')', $route);
			if ($route === 'apps/' . $app . '/handlers.php') {
				if (@file_exists ('apps/' . $app . '/handlers/index.php')) {
					$this->app = $app;
					$this->uri = $app . '/index';
					return 'apps/' . $app . '/handlers/index.php';
				}
				$this->app = $app;
				$this->uri = conf ('General', 'default_handler');
				return vsprintf (
					'apps/%s/handlers/%s.php',
					explode ('/', conf ('General', 'default_handler'))
				);
			}
		}
		$this->app = $app;
		$this->uri = $uri;
		return $route;
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
	public function add_notification ($msg) {
		if (isset ($_COOKIE['elefant_notification'])) {
			$msg = $_COOKIE['elefant_notification'] . '|' . $msg;
		}
		return setcookie ('elefant_notification', $msg, 0, '/');
	}

	/**
	 * Redirect the current request and exit.
	 */
	public function redirect ($url, $exit = true) {
		header ('Location: ' . $url);
		if ($exit) {
			exit;
		}
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
	 * Get the request method. If X-HTTP-Method-Override header is set,
	 * it will return that instead of the actual request method.
	 */
	public function request_method () {
		return isset ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : $_SERVER['REQUEST_METHOD'];
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
	 */
	public function require_login ($redirect = '/user/login') {
		if (! User::require_login ()) {
			$this->redirect ($redirect . '?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
		}
	}

	/**
	 * Require the user to be an administrator to proceed with the request.
	 */
	public function require_admin ($redirect = '/admin') {
		if (! User::require_admin ()) {
			$this->redirect ($redirect . '?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
		}
	}

	/**
	 * Check if an app and version have been installed. Returns true if
	 * installed, false if not, and current installed version if an upgrade
	 * should be performed.
	 */
	public function installed ($app, $version) {
		$v = db_shift ('select version from apps where name = ?', $app);
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
		$v = db_shift ('select version from apps where name = ?', $app);
		if ($v) {
			return db_execute ('update apps set version = ? where name = ?', $version, $app);
		}
		return db_execute ('insert into apps (name, version) values (?, ?)', $app, $version);
	}
}

?>