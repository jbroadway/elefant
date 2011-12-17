<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The database package is a simple procedural wrapper around one or more
 * PDO objects. While you're always free to use the PDO objects directly
 * (or better, build off the Model class to organize your logic into models),
 * these are simply meant to provide a handful of convenience functions to
 * reduce direct-to-database logic down to just one or two lines of code.
 *
 * Note: This is not a complete database abstraction layer, simply a set
 * of convenience functions. For creating models, and for any real degree
 * of ORM, see `lib/Model.php` which provides a more complete abstraction,
 * and a way of organizing your app logic.
 *
 * Usage:
 *
 *     if (! db_open (array (
 *         'driver' => 'sqlite', 'file' => 'conf/site.db'
 *     ))) {
 *         die (db_error ());
 *     }
 *     
 *     db_execute (
 *         'insert into sometable values (%s, %s)', 'one', 'two'
 *     );
 *
 *     $id = db_lastid ();
 *     
 *     $res = db_fetch_array ('select * from sometable');
 *     foreach ($res as $row) {
 *         echo $row->fieldname;
 *     }
 *     
 *     $row = db_single ('select * from sometable where id = %s', $id);
 *     
 *     $fieldname = db_shift (
 *         'select fieldname from sometable where id = %s', $id
 *     );
 *
 * Note that values inserted in the above way are automatically
 * escaped upon insertion.
 */
function db_open ($conf) {
	global $db_list, $db_err;
	if (! $db_list) {
		$db_list = array ();
	}
	$id = ($conf['master']) ? 'master' : 'slave_' . count ($db_list);
	try {
		switch ($conf['driver']) {
			case 'sqlite':
				$db_list[$id] = new PDO ('sqlite:' . $conf['file']);
				break;
			default:
				if (strstr ($conf['host'], ':')) {
					$conf['host'] = str_replace (':', ';port=', $conf['host']);
				}
				$db_list[$id] = new PDO ($conf['driver'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'], $conf['user'], $conf['pass']);
		}
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
	$db_list[$id]->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db_list[$id]->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	return true;
}

/**
 * Get a database connection. If `$master` is `1`, it will return the
 * master connection, `-1` and it will return a random connection from
 * only the slaves, `0` and it will return a random connection which
 * could be any of the slaves or the master.
 */
function db_get_conn ($master = 0) {
	global $db_list, $db_last_conn;
	if ($master === 1 && isset ($db_list['master'])) {
		$db_last_conn = 'master';
		return $db_list['master'];
	} elseif ($master === -1) {
		$keys = array_keys ($db_list);
		if (isset ($db_list['master'])) {
			foreach ($keys as $k => $v) {
				if ($v === 'master') {
					unset ($keys[$k]);
					break;
				}
			}
		}
		$db_last_conn = $keys[array_rand ($keys)];
		return $db_list[$db_last_conn];
	}
	$keys = array_keys ($db_list);
	$db_last_conn = $keys[array_rand ($keys)];
	return $db_list[$db_last_conn];
}

/**
 * Returns a count of active database connections.
 */
function db_conn_count () {
	return count ($GLOBALS['db_list']);
}

/**
 * Get the last error message.
 */
function db_error () {
	global $db_err;
	if ($db_err) {
		return $db_err;
	}
	return false;
}

/**
 * Normalizes arguments passed as an array, object, or as
 * multiple extra parameters.
 */
function db_args ($args) {
	if (count ($args) === 0) {
		return null;
	} elseif (count ($args) === 1 && is_object ($args[0])) {
		$res = array ();
		foreach ((array) $args[0] as $arg) {
			$res[] = $arg;
		}
		return $res;
	} elseif (count ($args) === 1 && is_array ($args[0])) {
		$res = array ();
		foreach ($args[0] as $arg) {
			$res[] = $arg;
		}
		return $res;
	}
	return $args;
}

/**
 * Fetch an array of all result objects.
 */
function db_fetch_array ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn ();
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		return $stmt->fetchAll ();
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Execute a statement and return true/false.
 */
function db_execute ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn (1);
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		return $stmt->execute ($args);
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch a single object.
 */
function db_single ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn ();
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		return $stmt->fetchObject ();
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch the a single value from the first result returned.
 * Useful for `count()` and other such calculations, and when
 * you only need a single piece of information.
 */
function db_shift ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn ();
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = (array) $stmt->fetchObject ();
		return array_shift ($res);
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an array of a single field.
 */
function db_shift_array ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn ();
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an associative array of two fields, the first
 * being the keys and the second being the values.
 */
function db_pairs ($sql) {
	global $db_err, $db_sql, $db_args;
	$db = db_get_conn ();
	$args = func_get_args ();
	array_shift ($args);
	$args = db_args ($args);
	$db_sql = $sql;
	$db_args = $args;

	try {
		$stmt = $db->prepare ($sql);
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[array_shift ($row)] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		$db_err = $e->getMessage ();
		return false;
	}
}

/**
 * Get the last inserted id value.
 */
function db_lastid () {
	global $db_list, $db_last_conn;
	return $db_list[$db_last_conn]->lastInsertId ();
}

/**
 * Get the last error message directly from PDO.
 * Useful for queries that were done with the global $db
 * object directly.
 */
function db_last_error () {
	global $db_list, $db_last_conn;
	$err = $db_list[$db_last_conn]->errorInfo ();
	return $err[2];
}

/**
 * Fetch the last SQL statement.
 */
function db_last_sql () {
	return $GLOBALS['db_sql'];
}

/**
 * Fetch the arguments for the last SQL statement.
 */
function db_last_args () {
	return $GLOBALS['db_args'];
}

?>