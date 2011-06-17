<?php

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
 * they can simply begin executing in the global namespace just like an
 * ordinary PHP script, and the output is simply echoed like an ordinary
 * PHP script too.
 *
 * The remaining elements of the URL are accessible in the array
 * `$this->params`, so for `/user/123` handled by `handlers/user.php`,
 * you could get the value `'123'` via `$this->params[0]`.
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
	var $params = array ();
	
	/**
	 * Whether the request originated internally or externally.
	 */
	var $internal = true;
	
	/**
	 * Data sent from another handler to the current one.
	 */
	var $data = array ();

	/**
	 * Set to true if the request came from the command line.
	 */
	var $cli = false;

	/**
	 * A list of handlers defined to be called for each type of hook.
	 * Similar to the idea of webhooks, this provides a means of triggering
	 * handlers from each other without hard-coding the specific handlers in
	 * the triggering code. See `conf/config.php`'s `[Hooks]` section for examples.
	 */
	var $hooks = array ();

	/**
	 * Keeps track of the number of times each handler has been called in
	 * this request. You can check `$controller->called['handler']` for
	 * the number, but make sure you use $controller and not $this in
	 * handlers to make sure it refers to the global controller.
	 */
	var $called = array ();

	/**
	 * When a handler is loaded, if there is a `conf/config.php` for that
	 * app, its contents will be loaded into `$appconf['appname']` once
	 * the first time it is called, and accessible thereafter by any
	 * handler in that app directly via $appconf.
	 */
	var $appconf = array ();

	function __construct ($hooks = array ()) {
		if (defined ('STDIN')) {
			$this->cli = true;
		}
		$this->hooks = $hooks;
	}

	/**
	 * Run an internal request from one handler to another.
	 */
	function run ($uri, $data = array ()) {
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
	function error ($code = 404, $title = 'Page not found', $message = '') {
		global $conf;

		// Erase any existing output up to this point
		ob_clean ();

		// Call the error handler
		return $this->run ($conf['General']['error_handler'], array (
			'code' => $code,
			'title' => $title,
			'message' => $message
		));
	}

	/**
	 * Run any handlers for the specified hook type. Note that the
	 * output for hooks is ignored.
	 */
	function hook ($type, $data = array ()) {
		if (! isset ($this->hooks[$type])) {
			return false;
		}
		foreach ($this->hooks[$type] as $handler) {
			$this->run ($handler, $data);
		}
	}

	/**
	 * Execute the request handler. $internal determines whether the
	 * request originated internally from another handler or template,
	 * or externally from a browser request.
	 */
	function handle ($handler, $internal = true, $data = array ()) {
		global $controller, $db, $conf, $i18n, $page, $tpl, $memcache;
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
		return ob_get_clean ();
	}

	/**
	 * Route a request URI to a file.
	 */
	function route ($uri) {
		global $conf;
		$this->app = array_shift (explode ('/', $conf['General']['default_handler']));
		$this->params = array ();

		// Remove queries and hash from uri
		$uri = preg_replace ('/(\?|#).*$/', '', $uri);

		if (! $this->clean ($uri) || $uri == '/') {
			$uri = $conf['General']['default_handler'];
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
				$uri = $conf['General']['default_handler'];
			}
		}

		list ($app, $handler) = preg_split ('/\//', $uri, 2);
		$route = 'apps/' . $app . '/handlers/' . $handler . '.php';
		while (! @file_exists ($route)) {
			$route = preg_replace ('/\/([^\/]*)\.php$/e', '$this->add_param (\'\\1\')', $route);
			if ($route == 'apps/' . $app . '/handlers.php') {
				if (@file_exists ('apps/' . $app . '/handlers/index.php')) {
					$this->app = $app;
					return 'apps/' . $app . '/handlers/index.php';
				}
				return vsprintf (
					'apps/%s/handlers/%s.php',
					explode ('/', $conf['General']['default_handler'])
				);
			}
		}
		$this->app = $app;
		return $route;
	}

	/**
	 * Is this URL clean of any directory manipulation attempts?
	 */
	function clean ($url) {
		if (strstr ($url, '..')) {
			return false;
		}
		return true;
	}

	/**
	 * Adds to the start, since `route()` parse them off the end of the URI.
	 */
	function add_param ($param) {
		array_unshift ($this->params, $param);
		return '.php';
	}
}

?>