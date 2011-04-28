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
 *   if (! db_open ('conf/site.db')) {
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
function db_open ($file) {
	global $db, $db_err;
	if ($db = @sqlite_open ($file, 0666, $db_err)) {
		return $db;
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

if (! function_exists ('sqlite_fetch_object')) {
	function sqlite_fetch_object (&$res) {
		$arr = @sqlite_fetch_array ($res, SQLITE_ASSOC);
		if ($arr) {
			return (object) $arr;
		}
		return $arr;
	}
}

function db_compile_query ($sql, $args) {
	if (count ($args) > 1 || $args[0] != $sql) {
		if ($args[0] == $sql) {
			array_shift ($args);
		}
		foreach ($args as $k => $arg) {
			if (! is_numeric ($arg)) {
				if (empty ($arg)) {
					$args[$k] = "''";
				} else {
					$args[$k] = "'" . str_replace ("'", "''", stripslashes ($arg)) . "'";
				}
			}
		}
		$sql = vsprintf ($sql, $args);
	}
	global $db_sql;
	$db_sql = $sql;
	return $sql;
}

function db_fetch_array ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	$res = @sqlite_query ($db, $sql, SQLITE_ASSOC, $db_err);
	$out = array ();
	while ($obj = @sqlite_fetch_object ($res)) {
		$out[] = $obj;
	}
	return $out;
}

function db_execute ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	return @sqlite_query ($db, $sql, SQLITE_ASSOC, $db_err);
}

function db_single ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	$res = @sqlite_query ($db, $sql, SQLITE_ASSOC, $db_err);
	if ($res) {
		return @sqlite_fetch_object ($res);
	}
	return false;
}

function db_shift ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	$res = @sqlite_query ($db, $sql, SQLITE_ASSOC, $db_err);
	if ($res) {
		$arr = @sqlite_fetch_array ($res);
		if (is_array ($arr)) {
			return array_shift ($arr);
		}
	}
	return false;
}

function db_lastid () {
	global $db;
	return @sqlite_last_insert_rowid ($db);
}

function db_last_error () {
	global $db;
	$code = @sqlite_last_error ($db);
	if ($code == 0) {
		return false;
	}
	return @sqlite_error_string ($code);
}

function db_last_sql () {
	global $db_sql;
	return $db_sql;
}

?>