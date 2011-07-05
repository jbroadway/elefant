<?php

/**
 * A simple locking mechanism for ensuring users don't edit the same
 * object at the same time.
 *
 * Usage:
 *
 *     $lock = new Lock ('type', 'id');
 *     if ($lock->exists ()) {
 *         echo $tpl->render ('admin/locked', $lock->info ());
 *         return;
 *     } else {
 *         $lock->add ();
 *     }
 *
 *     // on save:
 *     $lock->remove ();
 *
 *     // when logging an admin out:
 *     Lock::clear ();
 *
 * Fields:
 *
 * id
 * user
 * resource
 * resource_id
 * expires
 * created
 * modified
 */
class Lock {
	var $timeout = 2400; // 40 minutes
	var $error = false;
	var $resource = false;
	var $key = false;

	/**
	 * Constructor.
	 */
	function __construct ($resource = false, $key = false) {
		$this->resource = $resource;
		$this->key = $key;
	}

	/**
	 * Create a lock on the specified object.
	 */
	function add ($resource = false, $key = false) {
		global $user;
		
		if (! $resource) {
			$resource = $this->resource;
			$key = $this->key;
		}
		
		if (db_execute (
			'insert into lock
				(user, resource, resource_id, expires, created, modified)
			values
				(?, ?, ?, ?, ?, ?)',
			$user->id,
			$resource,
			$key,
			gmdate ('Y-m-d H:i:s', time () + $this->timeout),
			gmdate ('Y-m-d H:i:s'),
			gmdate ('Y-m-d H:i:s')
		)) {
			return db_lastid ();
		}
		$this->error = db_error ();
		return false;
	}

	/**
	 * Check whether a lock is held on an object by someone other than the current user.
	 */
	function exists ($resource = false, $key = false) {
		global $user;

		if (! $resource) {
			$resource = $this->resource;
			$key = $this->key;
		}

		return db_shift (
			'select id from lock where user != ? and resource = ? and resource_id = ? and expires > ?',
			$user->id,
			$resource,
			$key,
			gmdate ('Y-m-d H:i:s')
		);
	}

	/**
	 * Get the info about a lock.
	 */
	function info ($resource = false, $key = false) {

		if (! $resource) {
			$resource = $this->resource;
			$key = $this->key;
		}

		return db_single ('select * from lock where resource = ? and resource_id = ?', $resource, $key);
	}

	/**
	 * Update the expiry and modification time of an existing lock.
	 */
	function update ($resource = false, $key = false) {
		global $user;

		if (! $resource) {
			$resource = $this->resource;
			$key = $this->key;
		}

		if (db_execute (
			'update lock set modified = ?, expires = ? where resource = ? and resource_id = ?',
			gmdate ('Y-m-d H:i:s'),
			gmdate ('Y-m-d H:i:s', time () + $this->timeout),
			$resource,
			$key
		)) {
			return true;
		}
		$this->error = db_error ();
		return false;
	}

	/**
	 * Remove a specific lock.
	 */
	function remove ($resource = false, $key = false) {

		if (! $resource) {
			$resource = $this->resource;
			$key = $this->key;
		}

		return db_execute ('delete from lock where resource = ? and resource_id = ?', $resource, $key);
	}

	/**
	 * Clear all locks held by the current user.
	 */
	function clear () {
		global $user;
		return db_execute ('delete from lock where user = ?', $user->id);
	}

	/**
	 * Clear all locks.
	 */
	function clear_all () {
		return db_execute ('delete from lock');
	}
}

?>