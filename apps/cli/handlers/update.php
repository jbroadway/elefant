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

// Build a list of apps to upgrade after the core has been upgraded first.
$app = 'none';
$update_apps = array ();
if (isset ($_SERVER['argv'][2])) {
	$app = $_SERVER['argv'][2];
	
	if ($app === 'all') {
		$files = glob ('apps/*/elefant.json');
		
		foreach ($files as $file) {
			$info = explode ('/', $file);
			$update_apps[] = $info[1];
		}
	} elseif (strpos ($app, '..') !== false || ! file_exists ('apps/' . $app . '/elefant.json')) {
		// App not found or invalid
		Cli::out ('Invalid app name: ' . $app, 'error');
		return;
	} else {
		$update_apps[] = $app;
	}

	Cli::out ('Error: Not implemented yet.', 'error');
	return;
}

if ($app === 'none' || $app === 'all') {
	require_once ('apps/cli/lib/Functions.php');

	// Get the major/minor version
	$major_minor = preg_replace ('/\.[0-9]+$/', '', ELEFANT_VERSION);

	// Fetch the latest version from the server
	$data = Updater::fetch ('releases/' . $major_minor . '.json');
	if (! $data) {
		Cli::out ('Error: ' . Updater::error () . ' releases/' . $major_minor . '.json', 'error');
		return;
	}
	$res = json_decode ($data);

	if (! is_object ($res)) {
		Cli::out ('Error: Unable to fetch latest version from the server.', 'error');
		return;
	}

	// Are we already up to date?
	if ($res->latest <= ELEFANT_VERSION) {
		echo ELEFANT_VERSION . " is already up-to-date.\n";
		return;
	}

	// New version ready
	$latest = $res->latest;
	echo "New version: {$latest}\n";

	// Make sure conf/updates exists
	if (! file_exists ('conf/updates')) {
		mkdir ('conf/updates');
	}

	// Check for and download new patch files
	$data = Updater::fetch ('patches.json');
	if (! $data) {
		Cli::out ('Error: ' . Updater::error () . ' patches.json', 'error');
		return;
	}
	$res = json_decode ($data);

	if (! is_object ($res)) {
		Cli::out ('Error: Unable to fetch patch list from the server.', 'error');
		return;
	}

	foreach ($res->patches as $patch_file) {
		$base = basename ($patch_file);
		if (! file_exists ('conf/updates/' . $base)) {
			echo "Fetching new patch: {$base}\n";
			$contents = Updater::fetch ($patch_file);
			if (! $contents) {
				Cli::out ('Error: ' . Updater::error () . ' ' . $patch_file, 'error');
				return;
			}
			file_put_contents ('conf/updates/' . $base, $contents);
		}
	}

	foreach ($res->scripts as $script_file) {
		$base = basename ($script_file);
		if (! file_exists ('conf/updates/' . $base)) {
			echo "Fetching new db update: {$base}\n";
			$contents = Updater::fetch ($script_file);
			if (! $contents) {
				Cli::out ('Error: ' . Updater::error () . ' ' . $script_file, 'error');
				return;
			}
			file_put_contents ('conf/updates/' . $base, $contents);
		}
	}

	$versions = cli_get_versions (ELEFANT_VERSION, $latest);

	// Test and apply the patches and db updates in sequence
	foreach ($versions as $version) {
		printf ("Testing patch: %s\n", basename ($version['patch']));
		exec ('patch --dry-run -p1 -f -i ' . $version['patch'], $output);
		$output = join ("\n", $output);
		if (strpos ($output, 'FAILED')) {
			Cli::out ('Error applying patch ' . $version['patch'], 'error');
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
					Cli::out ('Error applying db update: ' . $version['script'], 'error');
					echo "See conf/updates/error.log for details.\n";
					file_put_contents ('conf/updates/error.log', $error);
					return;
				}
			}
			DB::commit ();
		}
	}

	Cli::out (sprintf ("Applied %d updates.", count ($versions)), 'success');
}

// Upgrade the apps that were specified as well
foreach ($update_apps as $app) {
	// TODO: Implement me
}

?>
