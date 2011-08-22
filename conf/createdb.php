<?php

function encrypt_pass ($plain) {
	$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$salt = '$1$';
	for ($i = 0; $i < 9; $i++) {
		$salt .= $base[rand (0, 61)];
	}
	return crypt ($plain, $salt . '$');
}

if (basename (getcwd ()) == 'conf') {
	chdir ('..');
}
require_once ('lib/Functions.php');
require_once ('lib/Database.php');
require_once ('lib/Model.php');
require_once ('apps/admin/models/Webpage.php');
require_once ('apps/admin/models/Versions.php');
require_once ('apps/user/models/User.php');
require_once ('apps/blocks/models/Block.php');

$conf = parse_ini_file ('conf/config.php', true);
date_default_timezone_set($conf['General']['timezone']);

if (! db_open ($conf['Database'])) {
	die (db_error ());
}

// import the database schema
$sqldata = sql_split (file_get_contents ('conf/install_' . $conf['Database']['driver'] . '.sql'));

foreach ($sqldata as $sql) {
	if (! db_execute ($sql)) {
		echo 'Error: ' . db_error () . "\n";
	}
}

// create first admin user
if (db_shift ('select count() from user') == 0) {
	$pass = substr (md5 (uniqid (mt_rand (), 1)), 0, 8);
	$date = gmdate ('Y-m-d H:i:s');
	if (! db_execute (
		'insert into user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, ?, ?, null, ?, "Admin User", "admin", ?, ?, ?)',
		$conf['General']['email_from'],
		encrypt_pass ($pass),
		$date,
		$date,
		$date,
		json_encode (array ())
	)) {
		echo 'Error: ' . db_error () . "\n";
	}
	
	$user = new User (1);

	// respond with the root password
	echo "Database created. Your initial admin account is:\n";
	echo 'Username: ' . $conf['General']['email_from'] . "\n";
	echo 'Password: ' . $pass . "\n";

	// create versions entries for initial content
	$wp = new Webpage ('index');
	Versions::add ($wp);
	$b = new Block ('members');
	Versions::add ($b);
} else {
	echo "Database created.\n";
}

// disable the installer
@umask (0000);
@touch ('install/installed');

?>