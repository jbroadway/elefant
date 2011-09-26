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
 * This is the command line installer for Elefant. Adjust your folder
 * permissions then run this to install the database tables and create
 * your initial admin user account.
 *
 * It will output your default admin username/password when the script
 * completes.
 *
 * Usage:
 *
 *     Usage: php conf/createdb.php
 */

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
require_once ('lib/Form.php');
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