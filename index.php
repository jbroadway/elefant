<?php

$conf = parse_ini_file ('conf/global.php', true);
if ($conf['General']['mode'] == 'development') {
	error_reporting (E_ALL);
} else {
	error_reporting (0);
}
date_default_timezone_set($conf['General']['timezone']);

require_once ('lib/Functions.php');
require_once ('lib/Page.php');
require_once ('lib/Template.php');
require_once ('lib/Controller.php');
require_once ('lib/Database.php');
require_once ('lib/Model.php');

$page = new Page;
$controller = new Controller;
$tpl = new Template ($conf['General']['charset']);

if (! db_open ($conf['Database'])) {
	die (db_error ());
}

$handler = $controller->route ($_SERVER['REQUEST_URI']);
$page->body = $controller->handle ($handler);

echo $page->render ();

?>