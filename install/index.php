<?php

if (@file_exists ('installed')) {
	$_GET['step'] = 'finished';
}

function encrypt_pass ($plain) {
	$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$salt = '$1$';
	for ($i = 0; $i < 9; $i++) {
		$salt .= $base[rand (0, 61)];
	}
	return crypt ($plain, $salt . '$');
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
require_once ('../lib/Form.php');
require_once ('../lib/Database.php');
require_once ('../lib/Template.php');
require_once ('../lib/Model.php');
require_once ('../apps/admin/models/Webpage.php');
require_once ('../apps/blocks/models/Block.php');
require_once ('../apps/admin/models/Versions.php');

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

$data = array ();

// handle the request
switch ($_GET['step']) {
	case 'requirements':
		// check permissions
		$apache = ($_SERVER['SERVER_SOFTWARE'] == 'Apache') ? true : (strpos (php_sapi_name (), 'apache') === 0) ? true : false;
		$data = array (
			'req' => array (
				'PHP version must be 5.3+' => PHP_VERSION > '5.3',
				'.htaccess file is missing from the site root. Please save the following file to your server:</p><p><a href="https://raw.github.com/jbroadway/elefant/master/.htaccess" target="_blank">https://raw.github.com/jbroadway/elefant/master/.htaccess</a>' => (! $apache || ($apache && @file_exists ('../.htaccess'))),
				'cache folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../cache'),
				'conf folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../conf'),
				'css folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../css'),
				'files folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../files'),
				'install folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../install'),
				'layouts folder is not writeable. Please run:</p><p><tt>chmod -R 777 cache conf css files install layouts</tt>' => is_writeable ('../layouts'),
				'PHP PDO extension is missing.' => extension_loaded ('pdo'),
				'Apache mod_rewrite extension must be installed.' => (php_sapi_name () != 'apache2handler' || in_array ('mod_rewrite', apache_get_modules ()))
			),
			'passed' => 0
		);
		$data['passed'] = array_sum ($data['req']) == count ($data['req']);
		
		break;


	case 'database':
		$data['ready'] = false;
		$data['error'] = false;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// got the settings, test and create schema
			if (! db_open ($_POST)) {
				$data['error'] = db_error ();
			} else {
				$data['error'] = false;

				// create the database
				$sqldata = sql_split (file_get_contents ('../conf/install_' . $_POST['driver'] . '.sql'));
				foreach ($sqldata as $sql) {
					if (! db_execute ($sql)) {
						$data['error'] = db_error ();
						break;
					}
				}
				
				$wp = new Webpage ('index');
				Versions::add ($wp);
				$b = new Block ('members');
				Versions::add ($b);

				// write the settings
				if (! $data['error']) {
					$conf = file_get_contents ('../conf/config.php');
					// good to replace database settings
					$dbinfo = $tpl->render ('dbinfo', $_POST);
					$conf = preg_replace ('/\[Database\].*\[Hooks\]/s', $dbinfo, $conf);
					if (! file_put_contents ('../conf/config.php', $conf)) {
						$data['error'] = 'Failed to write to conf/config.php';
					} else {
						$data['ready'] = true;
					}
				}
			}
		} else {
			// set some default values
			$_POST['host'] = 'localhost';
			$_POST['port'] = 3306;
		}
		break;


	case 'settings':
		$data['ready'] = false;
		$data['error'] = false;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// got the settings, save them
			$conf = file_get_contents ('../conf/config.php');
			$conf = preg_replace ('/site_name = .*/', 'site_name = "' . $_POST['site_name'] . '"', $conf);
			$conf = preg_replace ('/email_from = .*/', 'email_from = "' . $_POST['email_from'] . '"', $conf);
			if (! file_put_contents ('../conf/config.php', $conf)) {
				$data['error'] = 'Failed to write to conf/config.php';
			} else {
				// create the admin user now
				$conf_ini = parse_ini_file ('../conf/config.php', true);
				if (isset ($conf_ini['Database']['file'])) {
					$conf_ini['Database']['file'] = '../' . $conf_ini['Database']['file'];
				}

				if (! db_open ($conf_ini['Database'])) {
					$data['error'] = db_error ();
				} else {
					$date = gmdate ('Y-m-d H:i:s');
					if (! db_execute (
						'insert into user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, ?, ?, null, ?, ?, "admin", ?, ?, ?)',
						$_POST['email_from'],
						encrypt_pass ($_POST['pass']),
						$date,
						$_POST['your_name'],
						$date,
						$date,
						json_encode (array ())
					)) {
						$data['error'] = db_error ();
					} else {
						$data['ready'] = true;
					}
				}
			}
		} else {
			// set some default values
			$_POST['site_name'] = 'Your Site Name';
			$_POST['email_from'] = 'you@example.com';
		}
		break;


	case 'finished':
		@umask (0000);
		@touch ('installed');
		break;
}

echo $tpl->render ($_GET['step'], $data);

/*
echo '<br clear="both" />';
info ($data);
//*/

?>