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
 * This is the front controller of Elefant. All dynamic requests
 * are sent here to be handled and served.
 */

/**
 * Set the default error reporting level to All except Notices,
 * and turn off displaying errors. Error handling/debugging can
 * be done by setting conf[General][debug] to true, causing full
 * debug traces to be displayed with highlighted code in the
 * browser (*for development purposes only*), or by checking
 * the error log for errors.
 */
error_reporting (E_ALL & ~E_NOTICE);
ini_set ('display_errors', 'Off');

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
 * configuration to load, and load it. Also set the default
 * timezone to avoid warnings in date functions.
 */
define ('ELEFANT_ENV', getenv ('ELEFANT_ENV') ? getenv ('ELEFANT_ENV') : 'config');
$conf = parse_ini_file ('conf/' . ELEFANT_ENV . '.php', true);
date_default_timezone_set($conf['General']['timezone']);

/**
 * Enable the debugger if conf[General][debug] is true.
 */
if ($conf['General']['debug']) {
	require_once ('lib/Debugger.php');
	Debugger::start ();
}

/**
 * Include the core libraries used by the front controller
 * to dispatch and respond to requests.
 */
require_once ('conf/version.php');
require_once ('lib/Autoloader.php');
require_once ('lib/Functions.php');
require_once ('lib/Database.php');
require_once ('lib/Page.php');
require_once ('lib/I18n.php');
require_once ('lib/Controller.php');
require_once ('lib/Template.php');

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
$i18n = new I18n ('lang', $conf['I18n']);
$page = new Page;
$controller = new Controller ($conf['Hooks']);
$tpl = new Template ($conf['General']['charset'], $controller);

/**
 * Initialize the built-in Memcache support, or provide a
 * transparent fallback so we can include caching in our
 * handlers and in the front controller, whether Memcache
 * is available or not.
 */
if (isset ($conf['Memcache']['server']) && extension_loaded ('memcache')) {
	$memcache = new MemcacheExt;
	foreach ($conf['Memcache']['server'] as $s) {
		list ($server, $port) = explode (':', $s);
		$memcache->addServer ($server, $port);
	}
} else {
	$memcache = new Cache ();
}

/**
 * Connect to the databases. Will die if the master connect
 * fails, or if all connections fail, but will continue
 * as long as the master connection succeeds since that is
 * require to issue write commands.
 */
foreach (array_keys ($conf['Database']) as $key) {
	if ($key == 'master') {
		$conf['Database'][$key]['master'] = true;
	}
	if (! db_open ($conf['Database'][$key])) {
		if ($conf['Database'][$key]['master'] === true) {
			// Die immediately if connection to master fails,
			// since we can't issue any write commands.
			die (db_error ());
		}
	}
}
// Die if no connections succeeded.
if (db_conn_count () === 0) {
	die (db_error ());
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
 * Render and send the output to the client, using gzip
 * compression if conf[General][compress_output] is true.
 */
$out = $page->render ($tpl);
if ($conf['General']['compress_output'] && extension_loaded ('zlib')) {
	ob_start ('ob_gzhandler');
}
echo $out;

?>