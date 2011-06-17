<?php

/**
 * A class you can extend to create model objects in your application. Assumes table and
 * class name are identical, but that the table is lowercase. Assumes primary key field
 * is named `'id'`. Both of these can be changed by specifying custom table and key properties.
 * Note that this class doesn't impose field names. It provides an easy way to get at the
 * usual query methods for a database table, but mainly to encapsulate your logic around
 * that data.
 *
 * Usage:
 *
 *     class MyTable extends Model {
 *         function get_all_by_x () {
 *             return MyTable::query ()
 *                 ->order ('x desc')
 *                 ->fetch ();
 *         }
 *     }
 *
 *     $one = new MyTable (array (
 *         'id' => 123,
 *         'fieldname' => 'Some value'
 *     ));
 *     $one->put ();
 *
 *     $two = MyTable::get (123);
 *
 *     $two->fieldname = 'Some other value';
 *     $two->put ();
 *
 *     $res = MyTable::query ()
 *         ->where ('fieldname', 'Some other value')
 *         ->where ('id = 123')
 *         ->order ('fieldname asc')
 *         ->fetch (10, 5); // limit, offset
 *
 *     $res = MyTable::get_all_by_x ();
 *
 *     foreach ($res as $row) {
 *         $row->remove ();
 *     }
 */
class Model {
	var $table = '';
	var $key = 'id';
	var $keyval = false;
	var $data = array ();
	var $fields = array ();
	var $error = false;
	var $is_new = false;
	var $query_fields = '*';
	var $query_order = '';
	var $query_group = '';
	var $query_filters = array ();
	var $query_params = array ();

	/**
	 * If `$vals` is false, we're creating a new object from scratch.
	 * If it contains an array, it's a new object from an array.
	 * If `$is_new` is false, then the array is an existing field
	 * (mainly used internally by `fetch()`).
	 * If `$vals` contains a single value, the object is retrieved from the database.
	 */
	function __construct ($vals = false, $is_new = true) {
		$this->table = ($this->table == '') ? strtolower (get_class ($this)) : $this->table;

		$vals = is_object ($vals) ? (array) $vals : $vals;
		if (is_array ($vals)) {
			$this->data = $vals;
			if (isset ($vals[$this->key])) {
				$this->keyval = $vals[$this->key];
			}
			if ($is_new) {
				$this->is_new = true;
			}
		} elseif ($vals != false) {
			$res = db_single ('select * from ' . $this->table . ' where ' . $this->key . ' = ?', $vals);
			if (! $res) {
				$this->error = 'No object by that ID.';
			} else {
				$this->data = (array) $res;
				$this->keyval = $this->data[$this->key];
			}
		} else {
			$this->is_new = true;
		}
	}

	function __call($name, $arguments) {
		if (isset ($this->data[$name]) && isset ($this->fields[$name]) && isset ($this->fields[$name]['ref'])) {
			if (isset ($this->{'_ref_' . $name})) {
				return $this->{'_ref_' . $name};
			}
			$class = $this->fields[$name]['ref'];
			$this->{'_ref_' . $name} = new $class ($this->data[$name]);
			return $this->{'_ref_' . $name};
		}
		$trace = debug_backtrace ();
		trigger_error (
			sprintf ('Call to undefined method %s::%s in %s on line %d',
				get_class ($this),
				$name,
				$trace[0]['file'],
				$trace[0]['line']
			),
			E_USER_ERROR
		);
	}

	function __get ($key) {
		return (isset ($this->data[$key])) ? $this->data[$key] : null;
	}

	function __set ($key, $val) {
		$this->data[$key] = $val;
	}

	/**
	 * Save the object to the database.
	 */
	function put() {
		if ($this->is_new) {
			// Insert
			$ins = array ();
			$len = count ($this->data);
			for ($i = 0; $i < $len; $i++) {
				$ins[] = '?';
			}
			if (! db_execute ('insert into ' . $this->table . ' (' . join (', ', array_keys ($this->data)) . ') values (' . join (', ', $ins) . ')', $this->data)) {
				$this->error = db_error ();
				return false;
			}
			if (! isset ($this->data[$this->key])) {
				$this->data[$this->key] = db_lastid ();
				$this->keyval = $this->data[$this->key];
			}
			$this->is_new = false;
			return true;
		}
		
		// Update
		$ins = '';
		$par = array ();
		$sep = '';
		foreach ($this->data as $key => $val) {
			$ins .= $sep . $key . ' = ?';
			$par[] = $val;
			$sep = ', ';
		}
		if ($this->keyval && $this->keyval != $this->data[$this->key]) {
			$par[] = $this->keyval;
		} else {
			$par[] = $this->data[$this->key];
			$this->keyval = $this->data[$this->key];
		}
		if (! db_execute ('update ' . $this->table . ' set ' . $ins . ' where ' . $this->key . ' = ?', $par)) {
			$this->error = db_error ();
			return false;
		}
		$this->is_new = false;
		return true;
	}
	
	/**
	 * Delete the specified or the current element if no id is specified.
	 */
	function remove ($id = false) {
		$id = ($id) ? $id : $this->data[$this->key];
		if (! $id) {
			$this->error = 'No id specified.';
			return false;
		}
		if (! db_execute ('delete from ' . $this->table . ' where ' . $this->key . ' = ?', $id)) {
			$this->error = db_error ();
			return false;
		}
		return true;
	}

	/**
	 * Get a single object and update the current instance with that data.
	 */
	static function get ($id) {
		$class = get_called_class ();
		$q = new $class;
		$res = (array) db_single ('select * from ' . $q->table . ' where ' . $q->key . ' = ?', $id);
		if (! $res) {
			$q->error = 'No object by that ID.';
			$q->data = array ();
		} else {
			$q->data = (array) $res;
			$q->keyval = $id;
		}
		$q->is_new = false;
		return $q;
	}

	/**
	 * Begin a new query. Resets the internal state for a new query.
	 * Optionally you can pass the fields you want to return in
	 * the query, so you can optimize and not return them all.
	 */
	static function query ($fields = false) {
		$class = get_called_class ();
		if ($fields) {
			$obj = new $class;
			$obj->query_fields = $fields;
			return $obj;
		}
		return new $class;
	}

	/**
	 * Order the query by the specified clauses.
	 */
	function order ($order) {
		$this->query_order = $order;
		return $this;
	}

	/**
	 * Group the query by the specific clauses.
	 */
	function group ($group) {
		$this->query_group = $group;
		return $this;
	}

	/**
	 * Add a where condition to the query. Can be either a field/value
	 * combo, or if no value is present it assumes a custom condition
	 * in the first parameter.
	 */
	function where ($key, $val = false) {
		if (! $val) {
			array_push ($this->query_filters, $key);
		} else {
			array_push ($this->query_filters, $key . ' = ?');
			array_push ($this->query_params, $val);
		}
		return $this;
	}

	/**
	 * Fetch as an array of model objects.
	 */
	function fetch ($limit = false, $offset = 0) {
		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', $this->query_fields);
		}
		$sql = 'select ' . $this->query_fields . ' from ' . $this->table;
		if (count ($this->query_filters) > 0) {
			$sql .= ' where ' . join (' and ', $this->query_filters);
		}
		if (! empty ($this->query_group)) {
			$sql .= ' group by ' . $this->query_group;
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by ' . $this->query_order;
		}
		if ($limit) {
			$sql .= ' limit ' . $limit . ' offset ' . $offset;
		}
		$res = db_fetch_array ($sql, $this->query_params);
		if (! $res) {
			$this->error = db_error ();
			return $res;
		}
		$class = get_class ($this);
		foreach ($res as $key => $row) {
			$res[$key] = new $class ((array) $row, false);
		}
		return $res;
	}

	/**
	 * Fetch a single result as a model object.
	 */
	function single () {
		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', $this->query_fields);
		}
		$sql = 'select ' . $this->query_fields . ' from ' . $this->table;
		if (count ($this->query_filters) > 0) {
			$sql .= ' where ' . join (' and ', $this->query_filters);
		}
		if (! empty ($this->query_group)) {
			$sql .= ' group by ' . $this->query_group;
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by ' . $this->query_order;
		}
		$res = db_single ($sql, $this->query_params);
		if (! $res) {
			$this->error = db_error ();
			return $res;
		}
		$class = get_class ($this);
		$res = new $class ((array) $res, false);
		return $res;
	}

	/**
	 * Fetch the number of results for a query.
	 */
	function count ($limit = false, $offset = 0) {
		$sql = 'select count() from ' . $this->table;
		if (count ($this->query_filters) > 0) {
			$sql .= ' where ' . join (' and ', $this->query_filters);
		}
		if (! empty ($this->query_group)) {
			$sql .= ' group by ' . $this->query_group;
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by ' . $this->query_order;
		}
		$res = db_shift ($sql, $this->query_params);
		if ($res === false) {
			$this->error = db_error ();
		}
		return $res;
	}

	/**
	 * Fetch as an array of the original objects as returned from
	 * the database.
	 */
	function fetch_orig ($limit = false, $offset = 0) {
		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', $this->query_fields);
		}
		$sql = 'select ' . $this->query_fields . ' from ' . $this->table;
		if (count ($this->query_filters) > 0) {
			$sql .= ' where ' . join (' and ', $this->query_filters);
		}
		if (! empty ($this->query_group)) {
			$sql .= ' group by ' . $this->query_group;
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by ' . $this->query_order;
		}
		if ($limit) {
			$sql .= ' limit ' . $limit . ' offset ' . $offset;
		}
		$res = db_fetch_array ($sql, $this->query_params);
		if (! $res) {
			$this->error = db_error ();
		}
		return $res;
	}

	/**
	 * Fetch as an associative array of the specified key/value fields.
	 */
	function fetch_assoc ($key, $value, $limit = false, $offset = 0) {
		$tmp = $this->fetch ($limit, $offset);
		if (! $tmp) {
			return $tmp;
		}
		$res = array ();
		foreach ($tmp as $obj) {
			$res[$obj->{$key}] = $obj->{$value};
		}
		return $res;
	}

	/**
	 * Fetch as an array of the specified field name.
	 */
	function fetch_field ($value, $limit = false, $offset = 0) {
		$tmp = $this->fetch ($limit, $offset);
		if (! $tmp) {
			return $tmp;
		}
		$res = array ();
		foreach ($tmp as $obj) {
			$res[] = $obj->{$value};
		}
		return $res;
	}

	/**
	 * Return the original data as an object.
	 */
	function orig () {
		return (object) $this->data;
	}
}

?>