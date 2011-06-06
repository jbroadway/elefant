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

$conf = parse_ini_file ('conf/config.php', true);
date_default_timezone_set($conf['General']['timezone']);

if (! db_open ($conf['Database'])) {
	die (db_error ());
}

$sqldata = sql_split (file_get_contents ('conf/install_' . $conf['Database']['driver'] . '.sql'));

foreach ($sqldata as $sql) {
	if (! db_execute ($sql)) {
		echo 'Error: ' . db_error () . "\n";
	}
}

$date = gmdate ('Y-m-d H:i:s');

if (! db_execute (
	'insert into user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, ?, ?, null, ?, "Admin User", "admin", ?, ?, ?)',
	$conf['General']['master_username'],
	encrypt_pass ($conf['General']['master_password']),
	$date,
	$date,
	$date,
	json_encode (array ())
)) {
	echo 'Error: ' . db_error () . "\n";
}

?>