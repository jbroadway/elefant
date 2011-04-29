<?php

/**
 * The database package is just a list of functions that act on
 * a global $db resource. These provide direct access to the database
 * throuhg a set of convenience functions, and can help reduce
 * direct-to-database logic down to just one or two lines of code.
 *
 * For object modelling, see lib/Model.php.
 *
 * Usage:
 *
 *   if (! db_open (array ('driver' => 'sqlite', 'file' => 'conf/site.db'))) {
 *     die (db_error ());
 *   }
 *
 *   db_execute ('insert into sometable values (%s, %s)', 'one', 'two');
 *
 *   $id = db_lastid ();
 *
 *   $res = db_fetch_array ('select * from sometable');
 *   foreach ($res as $row) {
 *     echo $row->fieldname;
 *   }
 *
 *   $row = db_single ('select * from sometable where id = %s', $id);
 *
 *   $fieldname = db_shift ('select fieldname from sometable where id = %s', $id);
 *
 * Note that values inserted in the above way are automatically
 * escaped upon insertion.
 */
function db_open ($conf) {
	switch ($conf['driver']) {
		case 'sqlite':
			require_once ('lib/Database/SQLite.php');
			return db_sqlite_open ($conf['file']);
		case 'mysql':
			require_once ('lib/Database/MySQL.php');
			return db_mysql_open ($conf['host'], $conf['name'], $conf['user'], $conf['pass']);
	}
	return false;
}

function db_error () {
	global $db_err;
	if ($db_err) {
		return $db_err;
	}
	return false;
}

?>