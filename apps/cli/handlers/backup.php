<?php

/**
 * Create a backup of the site and database as a tarball
 * and move it to the specified folder.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	echo "Usage: elefant backup <path>\n";
	die;
}

$path = $_SERVER['argv'][2];

if (! is_dir ($path)) {
	echo "** Error: Specified path is not a folder.\n";
	die;
}
if (! is_writeable ($path)) {
	echo "** Error: Specified folder is not writeable.\n";
	die;
}

// add trailing slash
$path = (preg_match ('/\/$/', $path)) ? $path : $path . '/';

date_default_timezone_set ('GMT');
$ts = gmdate ('Y-m-d-H-i-s');

if (! @is_dir ('.backups')) {
	mkdir ('.backups');
	file_put_contents ('.backups/.htaccess', "Order allow,deny\nDeny from all\n");
}
mkdir ('.backups/backup-' . $ts);
exec ('./elefant export-db .backups/backup-' . $ts . '/dump.sql');
copy ('.htaccess', '.backups/backup-' . $ts . '/.htaccess');
exec ('cp -R * .backups/backup-' . $ts . '/');
chdir ('.backups');
exec ('tar -cf backup-' . $ts . '.tar backup-' . $ts);
exec ('gzip backup-' . $ts . '.tar');
chdir ('..');
exec ('mv .backups/backup-' . $ts . '.tar.gz ' . $path);
exec ('rm -Rf .backups/backup-' . $ts);


?>