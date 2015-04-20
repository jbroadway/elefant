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
 * This is the Elefant front controller class. All dynamic requests
 * are sent here to be handled and served. This was originally all
 * contained in the main `index.php` file, but moving it to a class
 * meant additional optimizations when running under HHVM.
 *
 * Usage:
 *
 *     require 'lib/FrontController.php';
 *     FrontController::run ();
 */
class FrontController {
	/**
	 * The front controller only has one static method, `run()`, which
	 * 
	 */
	public static function run ($argv, $argc) {
		/**
		 * For compatibility with PHP 5.4's built-in web server, we bypass
		 * the front controller for requests with file extensions and
		 * return false.
		 */
		if (php_sapi_name () === 'cli-server' && isset ($_SERVER['REQUEST_URI']) && preg_match ('/\.[a-zA-Z0-9]+$/', parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
			return false;
		}

		/**
		 * Normalize slashes for servers that are still poorly
		 * configured...
		 */
		if (get_magic_quotes_gpc ()) {
			function stripslashes_gpc (&$value) {
				$value = stripslashes ($value);
			}
			array_walk_recursive ($_GET, 'stripslashes_gpc');
			array_walk_recursive ($_POST, 'stripslashes_gpc');
			array_walk_recursive ($_COOKIE, 'stripslashes_gpc');
			array_walk_recursive ($_REQUEST, 'stripslashes_gpc');
		}

		/**
		 * Check ELEFANT_ENV environment variable to determine which
		 * configuration to load. Also include the Elefant version,
		 * autoloader, and core functions, and set the default
		 * timezone to avoid warnings in date functions.
		 */
		define ('ELEFANT_ENV', getenv ('ELEFANT_ENV') ? getenv ('ELEFANT_ENV') : 'config');
		require ('conf/version.php');
		require ('lib/Autoloader.php');
		require ('lib/Functions.php');
		date_default_timezone_set(conf ('General', 'timezone'));
		ini_set ('session.cookie_httponly', 1);
		ini_set ('session.use_only_cookies', 1);

		/**
		 * Set the default error reporting level to All except Notices,
		 * and turn off displaying errors. Error handling/debugging can
		 * be done by setting conf[General][debug] to true, causing full
		 * debug traces to be displayed with highlighted code in the
		 * browser (*for development purposes only*), or by checking
		 * the error log for errors.
		 */
		error_reporting (E_ALL & ~E_NOTICE);
		if (conf ('General', 'display_errors')) {
			ini_set ('display_errors', 'On');
		} else {
			ini_set ('display_errors', 'Off');
		}

		/**
		 * Enable the debugger if conf[General][debug] is true.
		 */
		require ('lib/Debugger.php');
		Debugger::start (conf ('General', 'debug'));

		/**
		 * Include the core libraries used by the front controller
		 * to dispatch and respond to requests.
		 */
		require ('lib/DB.php');
		require ('lib/Page.php');
		require ('lib/I18n.php');
		require ('lib/Controller.php');
		require ('lib/Template.php');
		require ('lib/View.php');

		/**
		 * If we're on the command line, set the request to use
		 * the first argument passed to the script.
		 */
		if (defined ('STDIN')) {
			$_SERVER['REQUEST_URI'] = '/' . $argv[1];
		}

		/**
		 * Initialize some core objects. These function as singletons
		 * because only one instance of them per request is desired
		 * (no duplicate execution for things like loading translation
		 * files).
		 */
		$i18n = new I18n ('lang', conf ('I18n'));
		$page = new Page;
		$controller = new Controller (conf ('Hooks'));
		$tpl = new Template (conf ('General', 'charset'), $controller);
		$controller->page ($page);
		$controller->i18n ($i18n);
		$controller->template ($tpl);
		View::init ($tpl);

		/**
		 * Check for a bootstrap.php file in the root of the site
		 * and if found, use it for additional app-level configurations
		 * (Dependency Injection, custom logging settings, etc.).
		 */
		if (file_exists ('bootstrap.php')) {
			require ('bootstrap.php');
		}

		/**
		 * Initialize the built-in cache support. Provides a
		 * consistent cache API (based on Memcache) so we can always
		 * include caching in our handlers and in the front controller.
		 */
		if (! isset ($cache) || ! is_object ($cache)) {
			$cache = Cache::init (conf ('Cache'));
		}
		$controller->cache ($cache);

		/**
		 * Provide global access to core objects, although the preferred
		 * way of accessing these is via the Controller object (`$this`
		 * in handlers).
		 */
		$GLOBALS['i18n'] = $i18n;
		$GLOBALS['page'] = $page;
		$GLOBALS['controller'] = $controller;
		$GLOBALS['tpl'] = $tpl;
		$GLOBALS['cache'] = $cache;

		/**
		 * Run any config level route overrides.
		 */
		if (file_exists ('conf/routes.php')) {
			$_routes = parse_ini_file ('conf/routes.php',true);
			if (isset($_routes['Disable'])){
			foreach ($_routes['Disable'] as $_route => $_strict) {
				if (
					(!$_strict && strpos($_SERVER['REQUEST_URI'],$_route) === 0 && $_SERVER['REQUEST_URI'] !== $_route) //match from left, exclude exact
					|| 
					($_strict && $_SERVER['REQUEST_URI'] == $_route) // match exact
				) {
					$page->body = $controller->run (conf ('General', 'error_handler'), array (
						'code' => 404,
						'title' => 'Page not found.',
						'message' => ''
					));
					echo $page->render ($tpl, $controller); // render 404 page and exit
					return true;
				}
			}}
			if (isset($_routes['Redirect'])){
			foreach ($_routes['Redirect'] as $_old => $_new) {
				if ($_old !== $_new && $_SERVER['REQUEST_URI'] == $_old) $controller->redirect($_new);
			}}
			if (isset($_routes['Alias'])){
			foreach ($_routes['Alias'] as $_old => $_new) {
				if (strpos($_SERVER['REQUEST_URI'],$_old) === 0) {
					$_SERVER['REQUEST_URI'] = str_replace($_old,$_new,$_SERVER['REQUEST_URI']);
					break;
				}
			}}
			unset($_routes);
		}

		/**
		 * Route the request to the appropriate handler and get
		 * the handler's response.
		 */
		if ($i18n->url_includes_lang) {
			$handler = $controller->route ($i18n->new_request_uri);
		} else {
			$handler = $controller->route ($_SERVER['REQUEST_URI']);
		}
		$page->body = $controller->handle ($handler, false);

		/**
		 * Control caching of the response
		 */
		if (conf ('Cache', 'control') && !conf ('General', 'debug')) {
			/* Cache control is ON */
			if (session_id () === '' && $page->cache_control)
			{
				if (isset ($_SERVER["SERVER_SOFTWARE"]) && strpos ($_SERVER["SERVER_SOFTWARE"],"nginx") !== false) {
					/* Allow NGINX to cache this request  - see http://wiki.nginx.org/X-accel */
					$controller->header ('X-Accel-Buffering: yes');
					$controller->header ('X-Accel-Expires: ' . conf ('Cache', 'expires'));
				}
				/* Standard http headers */
				$controller->header ('Cache-Control: public, no-cache="set-cookie", must-revalidate, proxy-revalidate, max-age=0');
				$controller->header ('Pragma: public');
				$controller->header ('Expires: ' . gmdate ('D, d M Y H:i:s', time () + conf ('Cache', 'expires')) . ' GMT');
			} else {
				if (isset ($_SERVER["SERVER_SOFTWARE"]) && strpos ($_SERVER["SERVER_SOFTWARE"],"nginx") !== false) {
					/* Do NOT allow NGINX to cache this request - see http://wiki.nginx.org/X-accel */
					$controller->header ('X-Accel-Buffering: no');
					$controller->header ('X-Accel-Expires: 0');
				}

				/* Standard http headers */
				$controller->header ('Pragma: no-cache');
				$controller->header ('Cache-Control: no-cache, must-revalidate');
				$controller->header ('Expires: 0');
			}
		} else {
			if (isset ($_SERVER["SERVER_SOFTWARE"]) && strpos ($_SERVER["SERVER_SOFTWARE"],"nginx") !== false) {
				/* Do NOT allow NGINX to cache this request by default  - see http://wiki.nginx.org/X-accel */
				$controller->header ('X-Accel-Buffering: no');
				$controller->header ('X-Accel-Expires: 0');
			}
		}

		/**
		 * Render and send the output to the client, using gzip
		 * compression if conf[General][compress_output] is true.
		 */
		$out = $page->render ($tpl, $controller);
		if (extension_loaded ('zlib') && conf ('General', 'compress_output')) {
			ini_set ('zlib.output_compression', 4096);
		}
		@session_write_close ();
		echo $out;
		return true;
	}
}
