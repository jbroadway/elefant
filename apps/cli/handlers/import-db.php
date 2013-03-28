<?php

/**
 * This command imports a schema file into the database.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	echo "Usage: elefant import-db <file>\n";
	die;
}

$file = $_SERVER['argv'][2];
if (! file_exists ($file)) {
	echo "** Error: File not found: $file\n";
	die;
}

$conf = parse_ini_file ('conf/config.php', true);
date_default_timezone_set ($conf['General']['timezone']);// connect to the database

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
$sqldata = sql_split (file_get_contents ($file));

DB::beginTransaction ();
foreach ($sqldata as $sql) {
	if (! DB::execute ($sql)) {
		echo '** Error: ' . DB::error () . "\n";
		DB::rollback ();
		return;
	}
}
DB::commit ();
echo count ($sqldata) . " commands executed.\n";

?>