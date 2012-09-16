<?php

/**
 * This is the command line install routine.
 * Its job is to create the database schema
 * based on the settings in conf/config.php,
 * and to create an initial admin user account.
 * It will output the password generated for
 * that account at the end, and mark itself
 * as done so as to prevent it or the web
 * installer from being run a second time.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (@file_exists ('conf/installed')) {
	echo "** Error: Installer has already been run.\n";
	return;
}

$conf = parse_ini_file ('conf/config.php', true);

// set the necessary folder permissions
system ('chmod -R 777 cache conf css files lang layouts');
system ('chmod 777 apps');

// connect to the database
$connected = false;
foreach (array_keys ($conf['Database']) as $key) {
	if ($key == 'master') {
		$conf['Database'][$key]['master'] = true;
		if (! DB::open ($conf['Database'][$key])) {
			echo "** Error: Could not connect to the database. Please check the\n";
			echo "          settings in conf/config.php and try again.\n";
			echo "\n";
			echo "          " . DB::error () . "\n";
			return;
		}
		$connected = true;
		break;
	}
}
if (! $connected) {
	echo "** Error: Could not find a master database. Please check the\n";
	echo "          settings in conf/config.php and try again.\n";
	return;
}

// import the database schema
$sqldata = sql_split (file_get_contents ('conf/install_' . $conf['Database']['master']['driver'] . '.sql'));

DB::beginTransaction ();

foreach ($sqldata as $sql) {
	if (! DB::execute ($sql)) {
		echo '** Error: ' . DB::error () . "\n";
		DB::rollback ();
	}
}

// change the admin user's password
$pass = generate_password (8);
$date = gmdate ('Y-m-d H:i:s');
if (! DB::execute (
	"update `elefant_user` set `email` = ?, `password` = ? where `id` = 1",
	$conf['General']['email_from'],
	encrypt_password ($pass)
)) {
	echo 'Error: ' . DB::error () . "\n";
	DB::rollback ();
}

DB::commit ();

// respond with the root password
echo "Database created. Your initial admin account is:\n";
echo 'Username: ' . $conf['General']['email_from'] . "\n";
echo 'Password: ' . $pass . "\n";

// create versions entries for initial content
$wp = new Webpage ('index');
Versions::add ($wp);
$b = new Block ('members');
Versions::add ($b);

// disable the installer
@umask (0000);
@touch ('conf/installed');
echo "Done.\n";

?>