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
			Cli::out ('Error: ' . ZipInstaller::$error, 'error');
			return;
		}
		ZipInstaller::clean ();
		Cli::out ('Install completed.', 'success');
		return;
	}

	require_once ('apps/designer/lib/Functions.php');

	if (github_is_zip ($url)) {
		ZipInstaller::clean ();
		
		// Retrieve zip file
		$info = ZipInstaller::fetch ($url);
		if (! $info) {
			ZipInstaller::clean ();
			Cli::out ('Error: ' . ZipInstaller::$error, 'error');
			return;
		}
		
		$res = ZipInstaller::install ($info);
		if (! $res) {
			ZipInstaller::clean ();
			Cli::out ('Error: ' . ZipInstaller::$error, 'error');
			return;
		}
		
		ZipInstaller::clean ();
		Cli::out ('Install completed.', 'success');
		return;
	} else {
		// Import from Github
		$res = GithubInstaller::install ($url);
		if (! $res) {
			Cli::out ('Error: ' . GithubInstaller::$error, 'error');
			return;
		}
		
		Cli::out ('Install completed.', 'success');
		return;
	}
}

if (@file_exists ('conf/installed')) {
	Cli::out ('** Error: Installer has already been run.', 'error');
	return;
}

require_once ('apps/cli/lib/Functions.php');

// set the necessary folder permissions
system ('chmod -R 777 cache conf css files lang layouts');
system ('chmod 777 apps');

// update config file 
$config_plain = file_get_contents ('conf/config.php');
$config_plain = preg_replace ('/site_key = .*/', 'site_key = "' . md5 (uniqid (rand (), true)) . '"', $config_plain, 1);
if (! file_put_contents ('conf/config.php', $config_plain)) {
	// currently it is not error, just warning
	Cli::out ('** Warning: Failed to write to conf/config.php.');
}

$conf = parse_ini_file ('conf/config.php', true);

// connect to the database
$connected = false;
DB::$prefix = isset ($conf['Database']['prefix']) ? $conf['Database']['prefix'] : '';
unset ($conf['Database']['prefix']);
foreach (array_keys ($conf['Database']) as $key) {
	if ($key == 'master') {
		$conf['Database'][$key]['master'] = true;
		if (! DB::open ($conf['Database'][$key])) {
			Cli::out ('** Error: Could not connect to the database. Please check the', 'error');
			Cli::out ('          settings in conf/config.php and try again.', 'error');
			echo "\n";
			Cli::out ('          ' . DB::error (), 'error');
			return;
		}
		$connected = true;
		break;
	}
}
if (! $connected) {
	Cli::out ('** Error: Could not find a master database. Please check the', 'error');
	Cli::out ('          settings in conf/config.php and try again.', 'error');
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
		Cli::out ('** Error: ' . DB::error (), 'error');
		DB::rollback ();
		return;
	}
}

// change the admin user's password
$pass = getenv ('ELEFANT_DEFAULT_PASS');
if ($pass === false) {
	$pass = generate_password (8);
}
$date = gmdate ('Y-m-d H:i:s');
if (! DB::execute (
	"update `#prefix#user` set `email` = ?, `password` = ? where `id` = 1",
	$conf['General']['email_from'],
	User::encrypt_pass ($pass)
)) {
	Cli::out ('Error: ' . DB::error (), 'error');
	DB::rollback ();
	return;
}

DB::commit ();

// respond with the root password
echo "Database created. Your initial admin account is:\n";
Cli::block ('Username: <info>' . $conf['General']['email_from'] . "</info>\n");
Cli::block ('Password: <info>' . $pass . "</info>\n");

// create versions entries for initial content
$wp = new Webpage ('index');
Versions::add ($wp);
$b = new Block ('members');
Versions::add ($b);

// disable the installer
@umask (0000);
@touch ('conf/installed');
echo "Done.\n";
