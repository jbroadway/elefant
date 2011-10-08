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

// check the error log for errors
error_reporting (E_ALL & ~E_NOTICE);
ini_set ('display_errors', 'Off');

// apparently we still have to deal with this... *sigh*
if (get_magic_quotes_gpc ()) {
	function stripslashes_gpc (&$value) {
		$value = stripslashes ($value);
	}
	array_walk_recursive ($_GET, 'stripslashes_gpc');
	array_walk_recursive ($_POST, 'stripslashes_gpc');
	array_walk_recursive ($_COOKIE, 'stripslashes_gpc');
	array_walk_recursive ($_REQUEST, 'stripslashes_gpc');
}

// get the global configuration
$conf = parse_ini_file ('conf/config.php', true);
date_default_timezone_set($conf['General']['timezone']);
if ($conf['General']['debug']) {
	require_once ('lib/Debugger.php');
	Debugger::start ();
}

require_once ('conf/version.php');
require_once ('lib/Autoloader.php');
require_once ('lib/Functions.php');
require_once ('lib/Database.php');
require_once ('lib/Page.php');
require_once ('lib/I18n.php');
require_once ('lib/Controller.php');
require_once ('lib/Template.php');

// cli support
if (defined ('STDIN')) {
	$_SERVER['REQUEST_URI'] = '/' . $argv[1];
}

// create core objects
$i18n = new I18n ('lang', $conf['I18n']);
$page = new Page;
$controller = new Controller ($conf['Hooks']);
$tpl = new Template ($conf['General']['charset'], $controller);

// initialize cache
if (isset ($conf['Memcache']['server']) && extension_loaded ('memcache')) {
	$memcache = new MemcacheExt;
	foreach ($conf['Memcache']['server'] as $s) {
		list ($server, $port) = explode (':', $s);
		$memcache->addServer ($server, $port);
	}
} else {
	$memcache = new Cache ();
}

// connect to the database
foreach (array_keys ($conf['Database']) as $key) {
	if ($key == 'master') {
		$conf['Database'][$key]['master'] = true;
	}
	if (! db_open ($conf['Database'][$key])) {
		die (db_error ());
	}
}

// handle the request
if ($i18n->url_includes_lang) {
	$handler = $controller->route ($i18n->new_request_uri);
} else {
	$handler = $controller->route ($_SERVER['REQUEST_URI']);
}
$page->body = $controller->handle ($handler, false);

// render and send the output
$out = $page->render ($tpl);
if ($conf['General']['compress_output'] && extension_loaded ('zlib')) {
	ob_start ('ob_gzhandler');
}
echo $out;

?>