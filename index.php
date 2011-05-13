<?php

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

require_once ('lib/Functions.php');
require_once ('lib/Database.php');

$i18n = new I18n ('lang', $conf['I18n']);
$page = new Page;
$controller = new Controller;
$tpl = new Template ($conf['General']['charset']);

// connect to the database
if (! db_open ($conf['Database'])) {
	die (db_error ());
}

// handle the request
if ($i18n->url_includes_lang) {
	$handler = $controller->route ($i18n->new_request_uri);
} else {
	$handler = $controller->route ($_SERVER['REQUEST_URI']);
}
$page->body = $controller->handle ($handler);

// render and send the output
$out = $page->render ();
if ($conf['General']['compress_output'] && extension_loaded ('zlib')) {
	ob_start ('ob_gzhandler');
}
echo $out;

?>