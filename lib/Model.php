<?php

/**
 * A class you can extend to create model objects in your application. Assumes table and
 * class name are identical, but that the table is lowercase. Assumes primary key field
 * is named 'id'. Both of these can be changed by specifying custom table and key properties.
 * Note that this class doesn't impose field names. It provides an easy way to get at the
 * usual query methods for a database table, but mainly to encapsulate your logic around
 * that data.
 *
 * Usage:
 *
 *   class MyTable extends Model {
 *     function get_all_by_x () {
 *       return new MyTable ().query ()
 *         .order ('x desc')
 *         .fetch ();
 *     }
 *   }
 *
 *   $one = new MyTable (array (
 *     'id' => 123,
 *     'fieldname' => 'Some value'
 *   ));
 *   $one->put ();
 *
 *   $two = new MyTable ().get (123);
 *
 *   $two->fieldname = 'Some other value';
 *   $two->put ();
 *
 *   $res = new MyTable ().query ()
 *     .where ('fieldname', 'Some other value')
 *     .where ('id = 123')
 *     .order ('fieldname asc')
 *     .fetch (10, 5); // limit, offset
 *
 *   $res = MyTable.get_all_by_x ();
 *
 *   foreach ($res as $row) {
 *     $row->remove ();
 *   }
 */
class Model {
	var $table = '';
	var $key = 'id';
	var $data = array ();
	var $fields = array ();
	var $error = false;
	var $is_new = false;
	var $query_order = '';
	var $query_filters = array ();
	var $query_params = array ();

	/**
	 * If $vals is false, we're creating a new object from scratch.
	 * If it contains an array, it's a new object from an array.
	 * If $is_new is false, then the array is an existing field
	 * (mainly used internally by fetch()).
	 * If $vals contains a single value, the object is retrieved from the database.
	 */
	function __construct ($vals = false, $is_new = true) {
		$this->table = ($this->table == '') ? strtolower (get_class ($this)) : $this->table;

		if (is_array ($vals)) {
			$this->data = $vals;
			if ($is_new) {
				$this->is_new = true;
			}
		} elseif ($vals != false) {
			$res = db_single ('select * from ' . $this->table . ' where ' . $this->key . ' = %s', $vals);
			if (! $res) {
				$this->error = db_error ();
			} else {
				$this->data = (array) $res;
			}
		} else {
			$this->is_new = true;
		}
	}

	function __get ($key) {
		return $this->data[$key];
	}

	function __set ($key, $val) {
		$this->data[$key] = $val;
	}

	function put() {
		if ($this->is_new) {
			// insert
			$ins = array ();
			for ($i = 0; $i < count ($this->data); $i++) {
				$ins[] = '%';
			}
			if (! db_execute ('insert into ' . $this->table . ' (' . join (', ', array_keys ($this->data)) . ') values (' . join (', ', $ins) . ')', $this->data)) {
				$this->error = db_error ();
				return false;
			}
			if (! isset ($this->data[$this->key])) {
				$this->data[$this->key] = db_lastid ();
			}
			$this->is_new = false;
			return true;
		}
		
		// update
		$ins = '';
		$par = array ();
		$sep = '';
		foreach ($this->data as $key => $val) {
			if ($key == $this->key) {
				continue;
			}
			$ins .= $sep . $key . ' = %s';
			$sep = ', ';
		}
		$par[$this->key] = $this->data[$this->key];
		if (! db_execute ('update ' . $this->table . ' set ' . $ins . ' where ' . $this->key . ' = %s', $par)) {
			$this->error = db_error ();
			return false;
		}
		return true;
	}
	
	function remove ($id = false) {
		$id = ($id) ? $id : $this->data[$this->key];
		if (! $id) {
			$this->error = 'No id specified.';
			return false;
		}
		if (! db_execute ('delete from ' . $this->table . ' where ' . $this->key . ' = %s', $id)) {
			$this->error = db_error ();
			return false;
		}
		return true;
	}

	function get ($id) {
		$this->data = (array) db_single ('select * from ' . $this->table . ' where ' . $this->key . ' = %s', $id);
		$this->is_new = false;
		return $this;
	}

	function query () {
		$this->query_order = '';
		$this->query_filters = array ();
		$this->query_params = array ();
		return $this;
	}

	function order ($order) {
		$this->query_order = $order;
		return $this;
	}

	function where ($key, $val = false) {
		if (! $val) {
			array_push ($this->query_filters, $key);
		} else {
			array_push ($this->query_filters, $key . ' = %s');
			array_push ($this->query_params, $val);
		}
		return $this;
	}

	function fetch ($limit = false, $offset = 0) {
		$sql = 'select * from ' . $this->table;
		if (count ($this->query_filters) > 0) {
			$sql .= ' where ' . join (' and ', $this->query_filters);
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by ' . $this->query_order;
		}
		if ($limit) {
			$sql .= ' limit ' . $limit . ' offset ' . $offset;
		}
		$res = db_fetch_array ($sql, $this->query_params);
		$class = get_class ($this);
		foreach ($res as $key => $row) {
			$res[$key] = new $class ((array) $row, false);
		}
		return $res;
	}
}

?>