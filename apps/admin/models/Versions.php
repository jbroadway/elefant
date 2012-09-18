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
 * Keeps a change history of any [[Model]] object.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // saving a new version
 *     Versions::add ($obj);
 *     
 *     // getting the history of an object
 *     $history = Versions::history ($obj);
 *     
 *     // get recent changes by current user
 *     $recent = Versions::recent (User::$user);
 *     
 *     // compare current version of a web page to previous:
 *     
 *     // 1. fetch the page
 *     $w = new Webpage ('index');
 *     
 *     // 2. get the previous version (limit=1, offset=1)
 *     $history = Versions::history ($w, 1, 1);
 *     
 *     // 3. restore the first result's object
 *     $v = new Versions;
 *     $w2 = $v->restore ($history[0]);
 *     
 *     // 4. compare the two
 *     $modified_fields = Versions::diff ($w, $w2);
 *     
 *     ?>
 *
 * Fields:
 *
 * - `id` - versions id
 * - `class` - class name of the object
 * - `pkey` - object's 'key' field value
 * - `user` - user id or 0 if no user saved
 * - `ts` - date/time of the change
 * - `serialized` - serialized version of the object
 */
class Versions extends Model {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#versions';

	/**
	 * Add a version to the store.
	 */
	public static function add ($obj) {
		$v = new Versions (array (
			'class' => get_class ($obj),
			'pkey' => $obj->{$obj->key},
			'user' => (! User::$user) ? 0 : User::val ('id'),
			'ts' => gmdate ('Y-m-d H:i:s'),
			'serialized' => json_encode ($obj->data)
		));
		$v->put ();
		return $v;
	}

	/**
	 * Recreate an object from the stored version. Takes any
	 * result from `recent()` or `history()`.
	 */
	public function restore ($vobj = false) {
		if (! $vobj) {
			$vobj = $this;
		}
		$class = $vobj->class;
		$data = json_decode ($vobj->serialized);

		// determine if it's a deleted item
		$tmp = new $class;
		$obj = new $class ($data->{$tmp->key});
		if ($obj->error) {
			$obj = new $class ($data);
		} else {
			$obj = new $class ($data, false);
		}
		return $obj;
	}

	/**
	 * Get recent versions by a user or everyone.
	 */
	public static function recent ($user = false, $limit = 10, $offset = 0) {
		$v = Versions::query ();
		if ($user !== false) {
			$v->where ('user', $user);
		}
		return $v->order ('ts desc')
			->group ('class')
			->group ('pkey')
			->fetch_orig ($limit, $offset);
	}

	/**
	 * Get recent versions of an object, or of objects of a specific
	 * class. If `$limit` is set to `true` (boolean), it will return
	 * a count of the history.
	 */
	public static function history ($obj, $limit = 10, $offset = 0) {
		if ($limit === true) {
			if (is_string ($obj)) {
				return count (Versions::query ()
					->where ('class', $obj)
					->group ('pkey')
					->fetch_field ('pkey'));
			}
			return count (Versions::query ()
				->where ('class', get_class ($obj))
				->where ('pkey', $obj->{$obj->key})
				->fetch_field ('pkey'));
		}
		if (is_string ($obj)) {
			return Versions::query ()
				->where ('class', $obj)
				->order ('ts desc')
				->group ('pkey')
				->fetch_orig ($limit, $offset);
		}
		return Versions::query ()
			->where ('class', get_class ($obj))
			->where ('pkey', $obj->{$obj->key})
			->order ('ts desc')
			->fetch_orig ($limit, $offset);
	}

	/**
	 * Compare two versions of a Model object. Returns an array of properties
	 * that have changed between the two versions, but does no comparison
	 * of the changes themselves. Note that this looks at the data array
	 * of the Model objects, not object properties, so it will not work
	 * on ordinary objects, only Model-based objects and objects returned
	 * by the `recent()` and `history()` methods.
	 */
	public static function diff ($obj1, $obj2) {
		if (get_class ($obj1) === 'stdClass') {
			$v = new Versions;
			$obj1 = $v->restore ($obj1);
		}
		if (get_class ($obj2) === 'stdClass') {
			$v = new Versions;
			$obj2 = $v->restore ($obj2);
		}

		$changed = array ();
		foreach ($obj1->data as $key => $value) {
			if ($value !== $obj2->data[$key]) {
				$changed[] = $key;
			}
		}
		return $changed;
	}

	/**
	 * Get a list of classes that have objects stored.
	 */
	public static function get_classes () {
		return DB::shift_array (
			'select distinct class from #prefix#versions order by class asc'
		);
	}
}

?>