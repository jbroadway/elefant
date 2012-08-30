<?php

/**
 * This command exports a backup of the database into
 * the specified file using the database's associated
 * command line export utility. Note that the utility
 * must be in your path for this to work.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

$conf = parse_ini_file ('conf/config.php', true);
switch ($conf['Database']['master']['driver']) {
	case 'sqlite':
		if (isset ($_SERVER['argv'][2])) {
			exec ('sqlite3 ' . $conf['Database']['master']['file'] . ' .dump > ' . $_SERVER['argv'][2]);
		} else {
			passthru ('sqlite3 ' . $conf['Database']['master']['file'] . ' .dump');
		}
		break;

	case 'mysql':
		// get port number
		list ($host, $port) = (strpos ($conf['Database']['master']['host'], ':') !== false)
			? explode (':', $conf['Database']['master']['host'])
			: array ($conf['Database']['master']['host'], 3306);

		if (isset ($_SERVER['argv'][2])) {
			exec (sprintf (
				'mysqldump --password=%s -u %s -h %s -P %d %s > %s',
				 escapeshellcmd ($conf['Database']['master']['pass']),
				 $conf['Database']['master']['user'],
				 $host,
				 $port,
				 $conf['Database']['master']['name'],
				 $_SERVER['argv'][2]
			));
		} else {
			passthru (sprintf (
				'mysqldump --password=%s -u %s -h %s -P %d %s',
				 escapeshellcmd ($conf['Database']['master']['pass']),
				 $conf['Database']['master']['user'],
				 $host,
				 $port,
				 $conf['Database']['master']['name']
			));
		}
		break;

	case 'pgsql':
		// get port number
		list ($host, $port) = (strpos ($conf['Database']['master']['host'], ':') !== false)
			? explode (':', $conf['Database']['master']['host'])
			: array ($conf['Database']['master']['host'], 3306);

		file_put_contents ('conf/.pgpass', sprintf (
			'%s:%d:%s:%s:%s',
			$host,
			$port,
			$conf['Database']['master']['name'],
			$conf['Database']['master']['user'],
			$conf['Database']['master']['pass']
		));
		chmod ('conf/.pgpass', 0600);
		if (isset ($_SERVER['argv'][2])) {
			exec (sprintf (
				'export PGPASSFILE=conf/.pgpass; pg_dump -U %s -h %s -p %d %s > %s; export PGPASSFILE=~/.pgpass',
				 $conf['Database']['master']['user'],
				 $host,
				 $port,
				 $conf['Database']['master']['name'],
				 $_SERVER['argv'][2]
			));
		} else {
			passthru (sprintf (
				'export PGPASSFILE=conf/.pgpass; pg_dump -U %s -h %s -p %d %s; export PGPASSFILE=~/.pgpass',
				 $conf['Database']['master']['user'],
				 $host,
				 $port,
				 $conf['Database']['master']['name']
			));
		}
		unlink ('conf/.pgpass');
		break;

	default:
		echo "** Error: Unable to determine database driver from site config.\n";
		break;
}

?>