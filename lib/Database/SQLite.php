<?php

/**
 * SQLite driver for lib/Database.php
 */
function db_sqlite_open ($file) {
	global $db, $db_err;
	if ($db = @sqlite_open ($file, 0666, $db_err)) {
		return $db;
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
		if (is_array ($args[0])) {
			$args = $args[0];
		}
		foreach ($args as $k => $arg) {
			if (! is_numeric ($arg)) {
				if (empty ($arg)) {
					$args[$k] = "''";
				} else {
					$args[$k] = "'" . @sqlite_escape_string ($arg) . "'";
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