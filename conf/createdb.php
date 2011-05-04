<?php

if (basename (getcwd ()) == 'conf') {
	chdir ('..');
}
require_once ('lib/Functions.php');
require_once ('lib/Database.php');

$db = db_open (array ('driver' => 'sqlite', 'file' => 'conf/site.db'));
foreach (sql_split (file_get_contents ('conf/install_sqlite.sql')) as $sql) {
	if (! db_execute ($sql)) {
		echo 'Error: ' . db_error () . "\n";
	}
}

?>