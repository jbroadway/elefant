<?php

require_once ('lib/Page.php');
require_once ('lib/Template.php');
require_once ('lib/Controller.php');
require_once ('lib/Database.php');
require_once ('lib/Model.php');

$conf = parse_ini_file ('conf/global.php');
date_default_timezone_set($conf['timezone']);
$page = new Page;
$controller = new Controller;
$tpl = new Template ($conf['charset']);

if (! db_open ('conf/site.db')) {
	die (db_error ());
}

$handler = $controller->route ($_SERVER['REQUEST_URI']);
$page->body = $controller->handle ($handler);

echo $page->render ();

?>