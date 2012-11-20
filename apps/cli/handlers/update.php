<?php

/**
 * This command checks for and applies updates
 * to the Elefant CMS software. It should be
 * used only after `./conf/elefant backup` has
 * been run, as it will apply the updates to
 * the current site files via the Unix patch
 * command and `./conf/elefant import-db`.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

echo "Not implemented yet.\n";
return;

require_once ('apps/cli/lib/Functions.php');

// get the major/minor version
$major_minor = preg_replace ('/\.[0-9]+$/', '', ELEFANT_VERSION);

// fetch the latest version from the server
$res = json_decode (
	fetch_url (
		'http://www.elefantcms.com/updates/check.php?v=' . $major_minor
	)
);

if (! is_object ($res)) {
	echo "Error: Unable to fetch latest version from the server.\n";
	return;
}

// are we already up to date?
if ($res->latest <= ELEFANT_VERSION) {
	echo "Already up-to-date.\n";
	return;
}

// new version ready
$latest = $res->latest;
echo "New version: {$latest}\n";

// make sure conf/updates exists
if (! file_exists ('conf/updates')) {
	mkdir ('conf/updates');
}

// check for and download new patch files
$res = json_decode (fetch_url ('http://www.elefantcms.com/updates/patches.php'));

if (! is_object ($res)) {
	echo "Error: Unable to fetch patch list from the server.\n";
	return;
}

foreach ($res->patches as $patch_file) {
	$base = basename ($patch_file);
	if (! file_exists ('conf/updates/' . $base)) {
		echo "Fetching new patch: {$base}\n";
		$contents = fetch_url ($patch_file);
		if (! $contents) {
			printf ("Error: Unable to retrieve file %s\n", $patch_file);
			return;
		}
		file_put_contents ('conf/updates/' . $base, $contents);

		// TODO: MD5 checks from 3rd party repo
	}
}

foreach ($res->scripts as $script_file) {
	$base = basename ($script_file);
	if (! file_exists ('conf/updates/' . $base)) {
		echo "Fetching new db update: {$base}\n";
		$contents = fetch_url ($script_file);
		if (! $contents) {
			printf ("Error: Unable to retrieve file %s\n", $script_file);
			return;
		}
		file_put_contents ('conf/updates/' . $base, $contents);

		// TODO: MD5 checks from 3rd party repo
	}
}

$versions = cli_get_versions (ELEFANT_VERSION, $latest);

// Test and apply the patches and db updates in sequence
foreach ($versions as $version) {
	printf ("Testing patch: %s\n", basename ($version['patch']));
	exec ('patch --dry-run -p1 -f -i ' . $version['patch'], $output);
	$output = join ("\n", $output);
	if (strpos ($output, 'FAILED')) {
		printf ("Error applying patch %s\n", $version['patch']);
		echo "See conf/updates/error.log for details.\n";
		file_put_contents ('conf/updates/error.log', $output);
		return;
	}

	// Patch is okay to apply
	echo "Patch ok, applying...\n";
	exec ('patch -p1 -f -i ' . $version['patch']);

	// Apply associated database updates
	if ($version['script']) {
		printf ("Applying db update: %s\n", basename ($version['script']));
		$sqldata = sql_split (file_get_contents ($version['script']));
		DB::beginTransaction ();
		foreach ($sqldata as $sql) {
			if (! DB::execute ($sql)) {
				$error = DB::error ();
				DB::rollback ();
				printf ("Error applying db update: %s\n", $version['script']);
				echo "See conf/updates/error.log for details.\n";
				file_put_contents ('conf/updates/error.log', $error);
				return;
			}
		}
		DB::commit ();
	}
}

printf ("Applied %d updates.\n", count ($versions));

?>