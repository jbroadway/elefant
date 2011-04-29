<?php

if (basename (getcwd ()) == 'conf') {
	chdir ('..');
}
require_once ('lib/Database.php');

$db = db_open (array ('driver' => 'sqlite', 'file' => 'conf/site.db'));
db_execute (file_get_contents ('conf/install.sql'));

?>