<?php

/**
 * Basic routing controller. Maps $_SERVER['REQUEST_URI'] to files in
 * a handlers/ folder, defaulting to handlers/index.php if no others
 * match.
 *
 * Matching is done by reducing the URL folder-by-folder until a file
 * matches. Here are some examples:
 *
 *   / -> handlers/index.php
 *   /foo -> handlers/foo.php
 *   /user/login -> handlers/user/login.php, handlers/user.php
 *   /user/123 -> handlers/user/123.php, handlers/user.php
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
		$uri = preg_replace ('/(\?|#).*$/', '', $uri);
		if (! $this->clean ($uri) || $uri == '/') {
			return 'handlers/index.php';
		}
		
		$route = 'handlers' . $uri . '.php';
		while (! file_exists ($route)) {
			$route = preg_replace ('/\/([^\/]*)\.php$/e', '$this->add_param (\'\\1\')', $route);
			if ($route == 'handlers.php') {
				return 'handlers/index.php';
			}
		}
		return $route;
	}

	function clean ($url) {
		if (strstr ($url, '..')) {
			return false;
		}
		return true;
	}

	function add_param ($param) {
		array_push ($this->params, $param);
		return '.php';
	}
}

?>