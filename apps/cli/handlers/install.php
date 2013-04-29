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

if (isset ($_SERVER['argv'][2])) {
	// Install an app
	$url = $_SERVER['argv'][2];
	if (strpos ($url, '://') === false && file_exists ($url)) {
		// Install from local zip file
		ZipInstaller::clean ();

		// Import from Zip
		$res = ZipInstaller::install ($url);
		if (! $res) {
			ZipInstaller::clean ();
			echo 'Error: ' . ZipInstaller::$error . "\n";
			return;
		}
		ZipInstaller::clean ();
		echo "Install completed.\n";
		return;
	}

	require_once ('apps/designer/lib/Functions.php');

	if (github_is_zip ($url)) {
		ZipInstaller::clean ();
		
		// Retrieve zip file
		$info = ZipInstaller::fetch ($url);
		if (! $info) {
			ZipInstaller::clean ();
			echo 'Error: ' . ZipInstaller::$error . "\n";
			return;
		}
		
		$res = ZipInstaller::install ($info);
		if (! $res) {
			ZipInstaller::clean ();
			echo 'Error: ' . ZipInstaller::$error . "\n";
			return;
		}
		
		ZipInstaller::clean ();
		echo "Install completed.\n";
		return;
	} else {
		// Import from Github
		$res = GithubInstaller::install ($url);
		if (! $res) {
			echo 'Error: ' . GithubInstaller::$error . "\n";
			return;
		}
		
		echo "Install completed.\n";
		return;
	}
}

if (@file_exists ('conf/installed')) {
	echo "** Error: Installer has already been run.\n";
	return;
}

require_once ('apps/cli/lib/Functions.php');

$conf = parse_ini_file ('conf/config.php', true);

// set the necessary folder permissions
system ('chmod -R 777 cache conf css files lang layouts');
system ('chmod 777 apps');

// connect to the database
$connected = false;
DB::$prefix = isset ($conf['Database']['prefix']) ? $conf['Database']['prefix'] : '';
unset ($conf['Database']['prefix']);
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
	if (trim ($sql) === 'begin' || trim ($sql) === 'commit') {
		continue;
	}

	if (! DB::execute ($sql)) {
		echo '** Error: ' . DB::error () . "\n";
		DB::rollback ();
		return;
	}
}

// change the admin user's password
$pass = generate_password (8);
$date = gmdate ('Y-m-d H:i:s');
if (! DB::execute (
	"update `#prefix#user` set `email` = ?, `password` = ? where `id` = 1",
	$conf['General']['email_from'],
	User::encrypt_pass ($pass)
)) {
	echo 'Error: ' . DB::error () . "\n";
	DB::rollback ();
	return;
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