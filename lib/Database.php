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
 * This package consists of two things:
 *
 * 1. Database - A connection management class with a few additional convenience
 *    methods.
 *
 * 2. db_*() - A set of convenience functions that operate transparently on the
 *    connection class.
 *
 * The connection manager lazy loads the connections on the first call to
 * Database::get_connection(), so requests that don't need a database connection
 * don't suffer the extra overhead. It is also master/slave aware, with write
 * requests going to the master and reads being directed to a random connection.
 *
 * The db_*() functions are a simple procedural wrapper around a basic PDO
 * connection manager. While you're always free to use the PDO objects directly
 * via `Database::get_connection()` (or better, build off the Model class to
 * organize your logic into models), these are simply meant to provide a handful
 * of convenience functions to reduce direct-to-database logic down to just one
 * or two lines of code.
 *
 * Note: This is not a complete database abstraction layer, simply a connection
 * manager and a set of convenience functions. For creating models, and for any
 * real degree of ORM, see `lib/Model.php` which provides a more complete
 * abstraction, and a way of organizing your application logic.
 *
 * Usage:
 *
 *     if (! Database::open (array (
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
class Database {
	/**
	 * List of PDO connection objects.
	 */
	public static $connections = array ();

	/**
	 * Error message or false if no error.
	 */
	public static $error = false;

	/**
	 * The array key of the last connection.
	 */
	public static $last_conn = false;

	/**
	 * The last SQL statement.
	 */
	public static $last_sql = false;

	/**
	 * The arguments for the last SQL statement.
	 */
	public static $last_args = array ();

	/**
	 * Open a database connection and add it to the pool. Accepts
	 * an array of connection info taken from the global conf().
	 */
	public function open ($conf) {
		if (! self::$connections) {
			self::$connections = array ();
		}
		$id = (isset ($conf['master']) && $conf['master']) ? 'master' : 'slave_' . count (self::$connections);
		try {
			switch ($conf['driver']) {
				case 'sqlite':
					self::$connections[$id] = new PDO ('sqlite:' . $conf['file']);
					break;
				case 'pgsql':
					if (strstr ($conf['host'], ':')) {
						$conf['host'] = str_replace (':', ';port=', $conf['host']);
					}
					self::$connections[$id] = new PDO ('pgsql:host=' . $conf['host'] . ';dbname=' . $conf['name'] . ';user=' . $conf['user'] . ';password=' . $conf['pass']);
					break;
				default:
					if (strstr ($conf['host'], ':')) {
						$conf['host'] = str_replace (':', ';port=', $conf['host']);
					}
					self::$connections[$id] = new PDO ($conf['driver'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'], $conf['user'], $conf['pass']);
			}
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
		self::$connections[$id]->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$connections[$id]->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		return true;
	}

	/**
	 * Connect to the databases. Will die if the master connect
	 * fails, or if all connections fail, but will continue
	 * as long as the master connection succeeds since that is
	 * require to issue write commands.
	 */
	public function load_connections () {
		$list = conf ('Database');
		foreach ($list as $key => $conf) {
			if ($key == 'master') {
				$conf['master'] = true;
			}
			if (! self::open ($conf)) {
				if ($conf['master'] === true) {
					// Die immediately if connection to master fails,
					// since we can't issue any write commands.
					die (self::$error);
				}
			}
		}
		// Die if no connections succeeded.
		if (self::count () === 0) {
			die (self::$error);
		}
	}

	/**
	 * Get a database connection. If `$master` is `1`, it will return the
	 * master connection, `-1` and it will return a random connection from
	 * only the slaves, `0` and it will return a random connection which
	 * could be any of the slaves or the master.
	 */
	public function get_connection ($master = 0) {
		if (count (self::$connections) === 0) {
			self::load_connections ();
		}
	
		if ($master === 1 && isset (self::$connections['master'])) {
			self::$last_conn = 'master';
			return self::$connections['master'];
		} elseif ($master === -1) {
			$keys = array_keys (self::$connections);
			if (isset (self::$connections['master'])) {
				foreach ($keys as $k => $v) {
					if ($v === 'master') {
						unset ($keys[$k]);
						break;
					}
				}
			}
			self::$last_conn = $keys[array_rand ($keys)];
			return self::$connections[self::$last_conn];
		}
		$keys = array_keys (self::$connections);
		self::$last_conn = $keys[array_rand ($keys)];
		return self::$connections[self::$last_conn];
	}

	/**
	 * Returns a count of active database connections.
	 */
	public function count () {
		return count (self::$connections);
	}

	/**
	 * Normalizes arguments passed as an array, object, or as
	 * multiple extra parameters.
	 */
	public function args ($args) {
		if (count ($args) === 0) {
			return null;
		} elseif (count ($args) === 1 && is_object ($args[0])) {
			return array_values ((array) $args[0]);
		} elseif (count ($args) === 1 && is_array ($args[0])) {
			return array_values ($args[0]);
		}
		return $args;
	}
	
	/**
	 * Normalize use of escape characters (`) to the database
	 * that's currently in use.
	 */
	public function normalize_sql ($db, $sql) {
		$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
		switch ($dbtype) {
			case 'pgsql':
				$sql = str_replace ('`', '"', $sql);
				break;
			default:
				break;
		}
		return $sql;
	}

	public function prepare ($args, $master = 0) {
		$db = self::get_connection ($master);
		$sql = array_shift ($args);
		$args = self::args ($args);
		$sql = self::normalize_sql ($db, $sql);
		self::$last_sql = $sql;
		self::$last_args = $args;

		$stmt = $db->prepare ($sql);
		return array ($stmt, $args);
	}
}

/**
 * Get the last error message.
 */
function db_error () {
	return Database::$error;
}

/**
 * Fetch an array of all result objects.
 */
function db_fetch_array () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args ());
		$stmt->execute ($args);
		return $stmt->fetchAll ();
	} catch (Exception $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Execute a statement and return true/false.
 */
function db_execute () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args (), 1);
		return $stmt->execute ($args);
	} catch (PDOException $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch a single object.
 */
function db_single () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args ());
		$stmt->execute ($args);
		return $stmt->fetchObject ();
	} catch (PDOException $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch the a single value from the first result returned.
 * Useful for `count()` and other such calculations, and when
 * you only need a single piece of information.
 */
function db_shift () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args ());
		$stmt->execute ($args);
		$res = (array) $stmt->fetchObject ();
		return array_shift ($res);
	} catch (PDOException $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an array of a single field.
 */
function db_shift_array () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args ());
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Fetch an associative array of two fields, the first
 * being the keys and the second being the values.
 */
function db_pairs () {
	try {
		list ($stmt, $args) = Database::prepare (func_get_args ());
		$stmt->execute ($args);
		$res = $stmt->fetchAll ();
		$out = array ();
		foreach ($res as $row) {
			$row = (array) $row;
			$out[array_shift ($row)] = array_shift ($row);
		}
		return $out;
	} catch (PDOException $e) {
		Database::$error = $e->getMessage ();
		return false;
	}
}

/**
 * Get the last inserted id value.
 */
function db_lastid () {
	return Database::$connections[Database::$last_conn]->lastInsertId ();
}

/**
 * Get the last error message directly from PDO.
 * Useful for queries that were done with the global $db
 * object directly.
 */
function db_last_error () {
	$err = Database::$connections[Database::$last_conn]->errorInfo ();
	return $err[2];
}

/**
 * Fetch the last SQL statement.
 */
function db_last_sql () {
	return Database::$last_sql;
}

/**
 * Fetch the arguments for the last SQL statement.
 */
function db_last_args () {
	return Database::$last_args;
}

?>