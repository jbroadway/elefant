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