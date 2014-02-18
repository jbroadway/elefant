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
	Cli::out ('Usage: ./elefant backup <path>', 'info');
	die;
}

$path = $_SERVER['argv'][2];

if (! is_dir ($path)) {
	Cli::out ('** Error: Specified path is not a folder.', 'error');
	die;
}
if (! is_writeable ($path)) {
	Cli::out ('** Error: Specified folder is not writeable.', 'error');
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