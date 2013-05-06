<?php

/**
 * This command imports a schema file into the database.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: elefant import-db <file>', 'info');
	die;
}

$file = $_SERVER['argv'][2];
if (! file_exists ($file)) {
	Cli::out ('** Error: File not found: ' . $file, 'error');
	die;
}

// import the database schema
$sqldata = sql_split (file_get_contents ($file));

DB::beginTransaction ();
foreach ($sqldata as $sql) {
	if (! DB::execute ($sql)) {
		Cli::out ('** Error: ' . DB::error (), 'error');
		DB::rollback ();
		return;
	}
}
DB::commit ();
Cli::out (count ($sqldata) . ' commands executed.', 'success');

?>