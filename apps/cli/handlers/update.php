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

// get the major/minor version
$major_minor = preg_replace ('/\.[0-9]+$/', '', ELEFANT_VERSION);

// fetch the latest version from the server
$res = json_decode (
	file_get_contents (
		'http://www.elefantcms.com/updates/check.php?v=' . $major_minor
	)
);

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
$res = json_decode (file_get_contents ('http://www.elefantcms.com/updates/patches.php'));

foreach ($res->patches as $patch_file) {
	$base = basename ($patch_file);
	if (! file_exists ('conf/updates/' . $base)) {
		echo "Fetching new patch: {$base}\n";
		file_put_contents (
			'conf/updates/' . $base,
			file_get_contents ($patch_file)
		);
		// TODO: MD5 checks from 3rd party repo
	}
}

foreach ($res->scripts as $script_file) {
	$base = basename ($script_file);
	if (! file_exists ('conf/updates/' . $base)) {
		echo "Fetching new db update: {$base}\n";
		file_put_contents (
			'conf/updates/' . $base,
			file_get_contents ($script_file)
		);
		// TODO: MD5 checks from 3rd party repo
	}
}

// TODO:
// 1. determine which patches/db updates need to be run
// 2. test and apply the patches and db updates in sequence

?>