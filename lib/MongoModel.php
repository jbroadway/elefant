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
 * A class you can extend to create model objects in your application that write
 * to a MongoDB collection in the back-end. Assumes collection and class name are
 * identical, but that the collection is lowercase. Assumes primary key field is named
 * `'_id'`. The collection can be changed by specifying a custom `name` property.
 * Note that this class doesn't impose field names. It provides an easy way to get at
 * MongoDB collections using the same pattern as regular SQL-based Model objects,
 * and can be extended to encapsulate your logic around that data.
 *
 * Usage:
 *
 *     class MyTable extends MongoModel {
 *         function get_all_by_x () {
 *             return MyTable::query ()
 *                 ->order ('x desc')
 *                 ->fetch ();
 *         }
 *     }
 *
 *     $one = new MyTable (array (
 *         'fieldname' => 'Some value'
 *     ));
 *     $one->put ();
 *     echo $one->keyval ();
 *
 *     $two = MyTable::get ($one->keyval ());
 *
 *     $two->fieldname = 'Some other value';
 *     $two->put ();
 *
 *     $res = MyTable::query ()
 *         ->where ('fieldname', 'Some other value')
 *         ->where ('age', array ('$gt' => 123))
 *         ->order ('fieldname asc')
 *         ->fetch (10, 5); // limit, offset
 *
 *     $res = MyTable::get_all_by_x ();
 *
 *     foreach ($res as $row) {
 *         $row->remove ();
 *     }
 *
 * Also supports validation of values via:
 *
 *     class MyTable extends MongoModel {
 *         var $verify = array (
 *             'email' => array (
 *                 'email' => 1,
 *                 'contains' => '@ourdomain.com'
 *             ),
 *             'name' => array (
 *                 'not empty' => 1
 *             )
 *         );
 *     }
 *
 * Or specified as an INI file:
 *
 *     class MyTable extends MongoModel {
 *         var $verify = 'apps/myapp/forms/myrules.php';
 *     }
 *
 * See Form::verify_values for more info on validation rules
 * and file formats.
 *
 * Differences from Model objects:
 *
 * - `keyval()` method for retrieving the unique ID value and ensuring
 *   it's not a `MongoId` object.
 */
class MongoModel {
	/**
	 * The Mongo database (a MongoDB object). Set in the constructor
	 * via `MongoManager::get_database ();`
	 */
	var $db;

	/**
	 * The name of the collection to operate on.
	 */
	var $name = '';

	/**
	 * The Mongo collection (a MongoCollection object).
	 */
	var $collection;

	/**
	 * The primary key field name.
	 */
	var $key = '_id';

	/**
	 * The primary key value of the current object.
	 */
	var $keyval = false;

	/**
	 * The properties of the current object are stored in this array.
	 */
	var $data = array ();

	/**
	 * Settings about fields such as relations to other tables.
	 */
	var $fields = array ();

	/**
	 * The error message if an error occurred, or false if there
	 * was no error.
	 */
	var $error = false;

	/**
	 * Keeps track of whether the current object is new and needs
	 * to be inserted or updated on save.
	 */
	var $is_new = false;

	/**
	 * Fields to return for the current query.
	 */
	var $query_fields = '*';
	
	/**
	 * The `order by` clause for the current query.
	 */
	var $query_order = array ();

	/**
	 * The `group by` clause for the current query.
	 */
	var $query_group = array ();

	/**
	 * A list of `where` clauses for the current query.
	 */
	var $query_filters = array ();

	/**
	 * A list of parameter values for the current query.
	 */
	var $query_params = array ();

	/**
	 * A list of validation rules to apply to ensure data is valid on save.
	 */
	var $verify = array ();

	/**
	 * If `$vals` is false, we're creating a new object from scratch.
	 * If it contains an array, it's a new object from an array.
	 * If `$is_new` is false, then the array is an existing field
	 * (mainly used internally by `fetch()`).
	 * If `$vals` contains a single value, the object is retrieved from the database.
	 */
	function __construct ($vals = false, $is_new = true) {
		$this->name = ($this->name == '') ? strtolower (get_class ($this)) : $this->name;

		$this->db = MongoManager::get_database ();
		$this->collection = $this->db->{$this->name};

		if (is_object ($vals)) {
			if (get_class ($vals) != 'MongoId') {
				$vals = (array) $vals;
			}
		}

		if (is_array ($vals)) {
			$this->data = $vals;
			if (isset ($vals[$this->key])) {
				$this->keyval = $vals[$this->key];
			}
			if ($is_new) {
				$this->is_new = true;
			}
		} elseif ($vals != false) {
			$res = $this->collection->findOne (array ('_id' => $this->_id ($vals)));
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

	/**
	 * Custom caller to handle references to related models.
	 */
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

	/**
	 * Custom getter that uses `$this->data` array.
	 */
	function __get ($key) {
		return (isset ($this->data[$key])) ? $this->data[$key] : null;
	}

	/**
	 * Custom setter that saves to `$this->data` array.
	 */
	function __set ($key, $val) {
		$this->data[$key] = $val;
	}

	/**
	 * Returns a MongoId object from a regular ID value or if it's
	 * already a MongoId value, the original is returned.
	 */
	function _id ($id = false) {
		if (! $id) {
			$id = $this->keyval;
		}

		if (! is_object ($id)) {
			return new MongoId ($id);
		}
		return $id;
	}

	/**
	 * Returns the ID value from a MongoId object, or the original
	 * value if it's not a MongoId object.
	 */
	function keyval ($id = false) {
		if (! $id) {
			$id = $this->keyval;
		}

		if (! is_object ($id)) {
			return $id;
		}
		return $id->{'$id'};
	}

	/**
	 * Save the object to the database. If $verify is set, it will
	 * validate the data against any rules in the array, or in the
	 * specified INI file if $verify is a string matching a file name.
	 */
	function put() {
		$f = new Form;
		$failed = $f->verify_values ($this->data, $this->verify);
		if (! empty ($failed)) {
			$this->error = 'Validation failed for: ' . join (', ', $failed);
			return false;
		}

		if ($this->is_new) {
			// Insert
			$ins = array ();
			$len = count ($this->data);
			for ($i = 0; $i < $len; $i++) {
				$ins[] = '?';
			}
			if (! $this->collection->insert ($this->data)) {
				$err = $this->db->lastError ();
				$this->error = $err['err'];
				return false;
			}
			$this->keyval = $this->data[$this->key];
			$this->is_new = false;
			return true;
		}
		
		// Update
		if (! $this->keyval) {
			$this->keyval = $this->data[$this->key];
		}
		if (! $this->collection->update (array ('_id' => $this->_id ($this->keyval)), $this->data)) {
			$err = $this->db->lastError ();
			$this->error = $err['err'];
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
		if (! $this->collection->remove (array ('_id' => $this->_id ($id)))) {
			$err = $this->db->lastError ();
			$this->error = $err['err'];
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
		$res = (array) $q->collection->findOne (array ('_id' => $q->_id ($id)));
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
	 * Order the query by the specified clauses. Specify each as:
	 *
	 *     field1 asc, field2 desc
	 */
	function order ($order) {
		$list = preg_split ('/, ?/', $order);
		foreach ($list as $ord) {
			if (preg_match ('/([a-z0-9_]+ desc$/i', $ord, $regs)) {
				$this->query_order[$regs[1]] = -1;
			} elseif (preg_match ('/([a-z0-9_]+ asc$/i', $ord, $regs)) {
				$this->query_order[$regs[1]] = 1;
			} else {
				$this->query_order[$ord] = 1;
			}
		}
		return $this;
	}

	/**
	 * Group the query by the specific clauses.
	 * NOTE: NOT YET IMPLEMENTED.
	 */
	function group ($group) {
		$this->query_group = $group;
		return $this;
	}

	/**
	 * Add a where condition to the query. This is a field/value
	 * combo, and special values are allowed, such as:
	 *
	 *     ->where ('age' => array ('$gt', 18))
	 */
	function where ($key, $val) {
		$this->query_filters[$key] = $val;
		return $this;
	}

	/**
	 * Fetch as an array of model objects.
	 */
	function fetch ($limit = false, $offset = 0) {
		if (is_array ($this->query_fields)) {
			$cur = $this->collection->find ($this->query_filters, $this->query_fields);
		} else {
			$cur = $this->collection->find ($this->query_filters);
		}

		if (count ($this->query_order) > 0) {
			$cur = $cur->sort ($this->query_order);
		}

		if ($limit) {
			$cur = $cur->limit ($limit);
		}

		if ($offset > 0) {
			$cur = $cur->skip ($offset);
		}

		if (! $cur) {
			$err = $this->db->lastError ();
			$this->error = $err['err'];
			return $cur;
		}
		$class = get_class ($this);
		$res = array ();
		foreach ($cur as $key => $row) {
			$res[$key] = new $class ((array) $row, false);
		}
		return $res;
	}

	/**
	 * Fetch a single result as a model object.
	 * NOTE: NOT YET IMPLEMENTED.
	 */
	function single () {
		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', Model::backticks ($this->query_fields));
		}
		$sql = 'select ' . $this->query_fields . ' from ' . Model::backticks ($this->table);
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
	 * NOTE: NOT YET IMPLEMENTED.
	 */
	function count ($limit = false, $offset = 0) {
		$sql = 'select count(*) from ' . Model::backticks ($this->table);
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
	 * NOTE: NOT YET IMPLEMENTED.
	 */
	function fetch_orig ($limit = false, $offset = 0) {
		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', Model::backticks ($this->query_fields));
		}
		$sql = 'select ' . $this->query_fields . ' from ' . Model::backticks ($this->table);
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