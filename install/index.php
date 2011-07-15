<?php

if (@file_exists ('installed')) {
	die ('Installer has already been run. Please delete the install folder.');
}

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
date_default_timezone_set('GMT');

require_once ('../lib/Functions.php');
require_once ('../lib/Database.php');
require_once ('../lib/Template.php');

// create core objects
$tpl = new Template ('UTF-8');

$steps = array (
	'introduction',
	'license',
	'requirements',
	'database',
	'settings',
	'finished'
);
$_GET['step'] = in_array ($_GET['step'], $steps) ? $_GET['step'] : 'introduction';

// handle the request
switch ($_GET['step']) {
	case 'requirements':
		// check permissions
		break;
	case 'database':
		// get database settings
		break;
}

echo $tpl->render ($_GET['step']);

?>