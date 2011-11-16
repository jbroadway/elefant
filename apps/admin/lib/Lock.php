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
	/**
	 * The lock timeout. Defaults to 40 minutes.
	 */
	public $timeout = 2400;

	/**
	 * The error message if an error occurs, or false if no errors.
	 */
	public $error = false;

	/**
	 * The type of resource being locked.
	 */
	public $resource = false;

	/**
	 * The unique ID of the resource being locked.
	 */
	public $key = false;

	/**
	 * Constructor.
	 */
	public function __construct ($resource = false, $key = false) {
		$this->resource = $resource;
		$this->key = $key;
	}

	/**
	 * Create a lock on the specified object.
	 */
	public function add ($resource = false, $key = false) {
		global $user;
		
		$resource = ($resource) ? $resource : $this->resource;
		$key = ($key) ? $key : $this->key;
		
		if (db_execute (
			'insert into `lock`
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
	public function exists ($resource = false, $key = false) {
		global $user;

		$resource = ($resource) ? $resource : $this->resource;
		$key = ($key) ? $key : $this->key;

		return db_shift (
			'select id from `lock` where user != ? and resource = ? and resource_id = ? and expires > ?',
			$user->id,
			$resource,
			$key,
			gmdate ('Y-m-d H:i:s')
		);
	}

	/**
	 * Get the info about a lock.
	 */
	public function info ($resource = false, $key = false) {
		$resource = ($resource) ? $resource : $this->resource;
		$key = ($key) ? $key : $this->key;

		return db_single ('select * from `lock` where resource = ? and resource_id = ?', $resource, $key);
	}

	/**
	 * Update the expiry and modification time of an existing lock.
	 */
	public function update ($resource = false, $key = false) {
		global $user;

		$resource = ($resource) ? $resource : $this->resource;
		$key = ($key) ? $key : $this->key;

		if (db_execute (
			'update `lock` set modified = ?, expires = ? where resource = ? and resource_id = ?',
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
	public function remove ($resource = false, $key = false) {
		$resource = ($resource) ? $resource : $this->resource;
		$key = ($key) ? $key : $this->key;

		return db_execute ('delete from `lock` where resource = ? and resource_id = ?', $resource, $key);
	}

	/**
	 * Clear all locks held by the current user.
	 */
	public static function clear () {
		global $user;
		return db_execute ('delete from `lock` where user = ?', $user->id);
	}

	/**
	 * Clear all locks.
	 */
	public static function clear_all () {
		return db_execute ('delete from `lock`');
	}
}

?>