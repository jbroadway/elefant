<?php

/**
 * For keeping a change history of any object.
 *
 * id
 * class
 * pkey
 * user
 * ts
 * serialized
 */
class Versions extends Model {
	/**
	 * Add a version to the store.
	 */
	function add ($obj) {
		global $user;
		$v = new Versions (array (
			'class' => get_class ($obj),
			'pkey' => $obj->{$obj->key},
			'user' => (! $user) ? 0 : $user->id,
			'ts' => gmdate ('Y-m-d H:i:s'),
			'serialized' => serialize ($obj)
		));
		$v->put ();
		return $v;
	}

	/**
	 * Get recent versions by a user or everyone.
	 */
	function recent ($user = false, $limit = 10, $offset = 0) {
		$v = Versions::query ();
		if ($user) {
			$v->where ('user', $user);
		}
		$v->order ('ts desc');
		$v->group ('class, pkey');
		return $v->fetch_orig ($limit, $offset);
	}

	/**
	 * Get recent versions of an object.
	 */
	function for_object ($obj, $limit = 10, $offset = 0) {
		return Versions::query ()
			->where ('class', get_class ($obj))
			->where ('pkey', $obj->{$obj->key})
			->order ('ts desc')
			->fetch_orig ($limit, $offset);
	}

	/**
	 * Compare two versions of an object. Returns an array of properties
	 * that have changed between the two versions, but does no comparison
	 * of the changes themselves.
	 */
	function diff ($obj1, $obj2) {
		$diff = new Diff;

		$vars = get_object_vars ($obj1);
		$changed = array ();
		foreach ($vars as $key => $value) {
			if ($value !== $obj2->{$key}) {
				$changed[] = $key;
			}
		}
		return $changed;
	}
}

?>