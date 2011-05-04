<?php

/**
 * Basic routing controller. Maps $_SERVER['REQUEST_URI'] to files in
 * a handlers/ folder, defaulting to handlers/index.php if no others
 * match.
 *
 * Matching is done by reducing the URL folder-by-folder until a file
 * matches. Here are some examples:
 *
 *   / -> $conf[default_handler]
 *
 *   /foo -> apps/foo/handlers/index.php,
 *           $conf[default_handler]
 *
 *   /user/login -> apps/user/handlers/login.php,
 *                  apps/user/handlers/index.php,
 *                  $conf[default_handler]
 *
 *   /user/info/123 -> apps/user/handlers/info/123.php,
 *                     apps/user/handlers/info.php,
 *                     apps/user/handlers/index.php,
 *                     $conf[default_handler]
 *
 * The controller simply returns the matching URL so you can include
 * it via the following code:
 *
 * $handler = $controller->route ($_SERVER['REQUEST_URI']);
 * ob_start ();
 * require_once ($handler);
 * $page->body = ob_get_contents ();
 * ob_end_clean ();
 *
 * Or more simply (but in practice the same):
 *
 * $handler = $controller->route ($_SERVER['REQUEST_URI']);
 * $page->body = $controller->handle ($handler);
 *
 * In this way, there is less scaffolding code for individual controllers,
 * they can simply begin executing in the global namespace just like an
 * ordinary PHP script, and the output is simply echoed like an ordinary
 * PHP script too.
 *
 * The remaining elements of the URL are accessible in the array
 * $this->params, so for /user/123 handled by handlers/user.php,
 * you could get the value '123' via $this->params[0].
 *
 * You can also call one handler from within another and get its results
 * like this:
 *
 * $res = $this->run ('/user/123');
 */
class Controller {
	var $params = array ();

	function run ($uri) {
		$c = new Controller;
		$handler = $c->route ($uri);
		return $c->handle ($handler);
	}

	function handle ($handler) {
		global $controller, $db, $conf, $page, $tpl;
		ob_start ();
		require ($handler);
		$out = ob_get_contents ();
		ob_end_clean ();
		return $out;
	}

	function route ($uri) {
		global $conf;
		$this->app = array_shift (explode ('/', $conf['General']['default_handler']));
		$this->params = array ();

		// remove queries and hash from uri
		$uri = preg_replace ('/(\?|#).*$/', '', $uri);

		if (! $this->clean ($uri) || $uri == '/') {
			$uri = $conf['General']['default_handler'];
		}

		// remove leading /
		$uri = ltrim ($uri, '/');

		// if no / and doesn't match an app's name with an index.php
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
				return $conf['General']['default_handler'];
			}
		}
		$this->app = $app;
		return $route;
	}

	function clean ($url) {
		if (strstr ($url, '..')) {
			return false;
		}
		return true;
	}

	/**
	 * Adds to the start, since route() parse them off the end of the URI.
	 */
	function add_param ($param) {
		array_unshift ($this->params, $param);
		return '.php';
	}
}

?>