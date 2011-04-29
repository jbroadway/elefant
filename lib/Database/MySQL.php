<?php

/**
 * SQLite driver for lib/Database.php
 */
function db_mysql_open ($host, $name, $user, $pass) {
	global $db, $db_err;
	$db = @mysql_connect ($host, $user, $pass);
	if ($db) {
		if (@mysql_select_db ($name, $db)) {
			return $db;
		}
	}
	$db_err = 'Database connection failed.';
	return false;
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
					$args[$k] = "'" . @mysql_real_escape_string ($arg) . "'";
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
	$res = @mysql_query ($sql, $db);
	$out = array ();
	while ($obj = @mysql_fetch_object ($res)) {
		$out[] = $obj;
	}
	return $out;
}

function db_execute ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	return @mysql_query ($sql, $db);
}

function db_single ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	$res = @mysql_query ($sql, $db);
	if ($res) {
		return @mysql_fetch_object ($res);
	}
	return false;
}

function db_shift ($sql) {
	global $db, $db_err;
	$args = func_get_args ();
	$sql = db_compile_query ($sql, $args);
	$res = @mysql_query ($sql, $db);
	if ($res) {
		$arr = @mysql_fetch_array ($res);
		if (is_array ($arr)) {
			return array_shift ($arr);
		}
	}
	return false;
}

function db_lastid () {
	global $db;
	return @mysql_insert_id ($db);
}

function db_last_error () {
	global $db;
	$code = @mysql_errno ($db);
	if ($code == 0) {
		return false;
	}
	return @mysql_error ($db);
}

function db_last_sql () {
	global $db_sql;
	return $db_sql;
}

?>