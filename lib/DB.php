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
 * DB is a database abstraction layer and connection manager. It provides two things:
 *
 * 1. A flexible connection manager with lazy loading and master/slave awareness.
 * 2. A set of convenience methods that operate transparently on the PDO connection(s).
 *
 * The connection manager lazy loads the connections on the first call to
 * `DB::get_connection()`, so requests that don't need a database connection
 * don't suffer the extra overhead. It is also master/slave aware, with write
 * requests going to the master and reads being directed to a random connection.
 *
 * Note: This is a simple database abstraction layer. For more advanced modelling
 * see [[Model]] which provides a more complete abstraction and a way of
 * organizing your application logic.
 *
 * Usage:
 *
 *     <?php
 *     
 *     if (! DB::open (array (
 *         'driver' => 'sqlite', 'file' => 'conf/site.db'
 *     ))) {
 *         die (DB::error ());
 *     }
 *     
 *     DB::execute (
 *         'insert into sometable values (?, ?)', 'one', 'two'
 *     );
 *
 *     $id = DB::last_id ();
 *     
 *     $res = DB::fetch ('select * from sometable');
 *     foreach ($res as $row) {
 *         echo $row->fieldname;
 *     }
 *     
 *     $row = DB::single ('select * from sometable where id = ?', $id);
 *     
 *     $fieldname = DB::shift (
 *         'select fieldname from sometable where id = ?', $id
 *     );
 *     
 *     ?>
 *
 * Values inserted use proper prepared statements and bound parameters to prevent
 * SQL injection.
 */
class DB {
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
	 * Table name prefix to replace `#prefix#` occurrences with.
	 */
	public static $prefix = '';

	/**
	 * Open a database connection and add it to the pool. Accepts
	 * an array of connection info taken from the global `conf()`.
	 */
	public static function open ($conf) {
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
					if (!isset($conf['charset'])) {
						$conf['charset']='utf8';
					}
					self::$connections[$id] = new PDO ($conf['driver'] . ':host=' . $conf['host'] . ';dbname=' . $conf['name'] . ';charset=' . $conf['charset'], $conf['user'], $conf['pass']);
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
	public static function load_connections () {
		$list = conf ('Database');
		self::$prefix = isset ($list['prefix']) ? $list['prefix'] : '';
		unset ($list['prefix']);
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
	public static function get_connection ($master = 0) {
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
	public static function count () {
		return count (self::$connections);
	}

	/**
	 * Normalizes arguments passed as an array, object, or as
	 * multiple extra parameters.
	 */
	public static function args ($args) {
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
	 * Normalize use of escape characters (<code>`</code>) to the database
	 * that's currently in use.
	 */
	public static function normalize_sql ($db, $sql) {
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

	/**
	 * Prepares a statement from a list of arguments,
	 * the first being the SQL query and the rest being
	 * the parameters, and a `$master` flag to determine
	 * which connection to use. Also replaces `#query#`
	 * with a database table name prefix set in the global
	 * configuration.
	 */
	public static function prepare ($args, $master = 0) {
		$db = self::get_connection ($master);
		$sql = array_shift ($args);
		$args = self::args ($args);
		$sql = self::normalize_sql ($db, $sql);
		$sql = str_replace ('#prefix#', self::$prefix, $sql);
		self::$last_sql = $sql;
		self::$last_args = $args;

		$stmt = $db->prepare ($sql);
		return array ($stmt, $args);
	}

	/**
	 * Get the last error message.
	 */
	public static function error () {
		return self::$error;
	}
	
	/**
	 * Fetch an array of all result objects.
	 */
	public static function fetch () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			return $stmt->fetchAll ();
		} catch (Exception $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Execute a statement and return true/false.
	 */
	public static function execute () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args (), 1);
			return $stmt->execute ($args);
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}

	/**
	 * Execute a query and return the PDO statement object
	 * so you can minimize memory usage.
	 */
	public static function query () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			return $stmt;
		} catch (Exception $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Fetch a single object.
	 */
	public static function single () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			return $stmt->fetchObject ();
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Fetch the a single value from the first result returned.
	 * Useful for `count()` and other such calculations, and when
	 * you only need a single piece of information.
	 */
	public static function shift () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			$res = $stmt->fetch (PDO::FETCH_NUM);
			return $res[0];
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Fetch an array of a single field.
	 */
	public static function shift_array () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			return $stmt->fetchAll (PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Fetch an associative array of two fields, the first
	 * being the keys and the second being the values.
	 */
	public static function pairs () {
		try {
			list ($stmt, $args) = self::prepare (func_get_args ());
			$stmt->execute ($args);
			$res = $stmt->fetchAll (PDO::FETCH_NUM);
			$out = array ();
			foreach ($res as $row) {
				$out[$row[0]] = $row[1];
			}
			return $out;
		} catch (PDOException $e) {
			self::$error = $e->getMessage ();
			return false;
		}
	}
	
	/**
	 * Get the last inserted id value.
	 */
	public static function last_id () {
		return self::$connections[self::$last_conn]->lastInsertId ();
	}
	
	/**
	 * Get the last error message directly from PDO.
	 * Useful for queries that were done with the global $db
	 * object directly.
	 */
	public static function last_error () {
		$err = self::$connections[self::$last_conn]->errorInfo ();
		return $err[2];
	}
	
	/**
	 * Fetch the last SQL statement.
	 */
	public static function last_sql () {
		return self::$last_sql;
	}
	
	/**
	 * Fetch the arguments for the last SQL statement.
	 */
	public static function last_args () {
		return self::$last_args;
	}

	/**
	 * Begin a database transaction.
	 */
	public static function beginTransaction () {
		$db = self::get_connection (1);
		return $db->beginTransaction ();
	}

	/**
	 * Commit a database transaction.
	 */
	public static function commit () {
		$db = self::get_connection (1);
		return $db->commit ();
	}

	/**
	 * Rollback a database transaction.
	 */
	public static function rollback () {
		$db = self::get_connection (1);
		return $db->rollback ();
	}
}

/**
 * Deprecated. Alias of `DB::error()`
 */
function db_error () {
	return DB::error ();
}

/**
 * Deprecated. Alias of `DB::fetch()`
 */
function db_fetch_array () {
	return call_user_func_array (array ('DB', 'fetch'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::execute()`
 */
function db_execute () {
	return call_user_func_array (array ('DB', 'execute'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::single()`
 */
function db_single () {
	return call_user_func_array (array ('DB', 'single'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::shift()`
 */
function db_shift () {
	return call_user_func_array (array ('DB', 'shift'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::shift_array()`
 */
function db_shift_array () {
	return call_user_func_array (array ('DB', 'shift_array'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::pairs()`
 */
function db_pairs () {
	return call_user_func_array (array ('DB', 'pairs'), func_get_args ());
}

/**
 * Deprecated. Alias of `DB::last_id()`
 */
function db_lastid () {
	return DB::last_id ();
}

/**
 * Deprecated. Alias of `DB::last_error()`
 */
function db_last_error () {
	return DB::last_error ();
}

/**
 * Deprecated. Alias of `DB::last_sql()`
 */
function db_last_sql () {
	return DB::last_sql ();
}

/**
 * Deprecated. Alias of `DB::last_args()`
 */
function db_last_args () {
	return DB::last_args ();
}

?>