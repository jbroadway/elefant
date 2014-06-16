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
 * A class you can extend to create model objects in your application. Assumes table and
 * class name are identical, but that the table is lowercase. Assumes primary key field
 * is named `'id'`. Both of these can be changed by specifying custom table and key properties.
 * Note that this class doesn't impose field names. It provides an easy way to get at the
 * usual query methods for a database table, but mainly to encapsulate your logic around
 * that data.
 *
 * Usage:
 *
 *     <?php
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
 *     
 *     ?>
 *
 * Also supports validation of values via:
 *
 *     <?php
 *     
 *     class MyTable extends Model {
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
 *     ?>
 *
 * Or specified as an INI file:
 *
 *     <?php
 *     
 *     class MyTable extends Model {
 *         var $verify = 'apps/myapp/forms/mytable.php';
 *     }
 *     
 *     ?>
 *
 * See `Form::verify_values` for more info on validation rules
 * and file formats.
 */
class Model {
	/**
	 * The database table to map to.
	 */
	public $table = '';

	/**
	 * The primary key field name.
	 */
	public $key = 'id';

	/**
	 * The primary key value of the current object.
	 */
	public $keyval = false;

	/**
	 * The properties of the current object are stored in this array.
	 */
	public $data = array ();

	/**
	 * Settings about fields such as relations to other tables.
	 */
	public $fields = array ();

	/**
	 * The error message if an error occurred, or false if there
	 * was no error.
	 */
	public $error = false;

	/**
	 * The error message from a batch() call if an error occurred,
	 * or false if there was no error.
	 */
	public static $batch_error = false;

	/**
	 * Keeps track of whether the current object is new and needs
	 * to be inserted or updated on save.
	 */
	public $is_new = false;

	/**
	 * Fields to return for the current query.
	 */
	public $query_fields = '*';
	
	/**
	 * The `order by` clause for the current query.
	 */
	public $query_order = '';

	/**
	 * The `group by` clause for the current query.
	 */
	public $query_group = '';

	/**
	 * A list of `where` clauses for the current query.
	 */
	public $query_filters = array ();

	/**
	 * A list of parameter values for the current query.
	 */
	public $query_params = array ();

	/**
	 * A list of `having` clauses for the current query.
	 */
	public $query_having = array ();

	/**
	 * An alternate table listing for the current query.
	 */
	public $query_from = false;

	/**
	 * A list of validation rules to apply to ensure data is valid on save.
	 */
	public $verify = array ();

	/**
	 * A list of fields that failed validation on the last `put()` call.
	 */
	public $failed = array ();

	/**
	 * If `$vals` is false, we're creating a new object from scratch.
	 * If it contains an array, it's a new object from an array.
	 * If `$is_new` is false, then the array is an existing field
	 * (mainly used internally by `fetch()`).
	 * If `$vals` contains a single value, the object is retrieved from the database.
	 */
	public function __construct ($vals = false, $is_new = true) {
		$this->table = ($this->table === '') ? strtolower (get_class ($this)) : $this->table;

		$vals = is_object ($vals) ? (array) $vals : $vals;
		if (is_array ($vals)) {
			$this->data = $vals;
			if (isset ($vals[$this->key])) {
				$this->keyval = $vals[$this->key];
			}
			if ($is_new) {
				$this->is_new = true;
			}
		} elseif ($vals !== false) {
			$res = DB::single ('select * from `' . $this->table . '` where `' . $this->key . '` = ?', $vals);
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
	 * Handles one-to-one and one-to-many relationships via
	 * dynamic methods based on the field names.
	 *
	 * Arguments are all optional, and are as follows:
	 *
	 * - `$reset_cache = false`
	 * - `$limit = false`
	 * - `$offset = 0`
	 *
	 * For example, to fetch the first 20 articles by author (these are
	 * made up models):
	 *
	 *     $articles = $author->articles (false, 20, 0);
	 */
	public function __call($name, $arguments) {
		// method not found in dynamic fields
		if (! isset ($this->fields[$name])) {
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

		// if true is passed, re-fetch from the database.
		// also check for limit and offset
		$reset_cache = (count ($arguments) > 0) ? $arguments[0] : false;
		$limit = (count ($arguments) > 1) ? $arguments[1] : false;
		$offset = (count ($arguments) > 2) ? $arguments[2] : 0;

		if (isset ($this->fields[$name]['ref'])) {
			// for backwards compatibility
			$this->fields[$name]['belongs_to'] = $this->fields[$name]['ref'];
			unset ($this->fields[$name]['ref']);
		}

		if (isset ($this->fields[$name]['belongs_to'])) {
			// handle belongs_to relationships (reverse of one to one or one to many)
			if (! $reset_cache && isset ($this->{'_ref_' . $name})) {
				return $this->{'_ref_' . $name};
			}
			$class = $this->fields[$name]['belongs_to'];
			$field_name = isset ($this->fields[$name]['field_name']) ? $this->fields[$name]['field_name'] : $name;
			$this->{'_ref_' . $name} = new $class ($this->data[$field_name]);
			return $this->{'_ref_' . $name};

		} elseif (isset ($this->fields[$name]['has_one'])) {
			// handle has_one relationships (one to one)
			if (! $reset_cache && isset ($this->{'_ref_' . $name})) {
				return $this->{'_ref_' . $name};
			}
			$class = $this->fields[$name]['has_one'];
			$field_name = isset ($this->fields[$name]['field_name']) ? $this->fields[$name]['field_name'] : $this->table;
			$this->{'_ref_' . $name} = $class::query ()
				->where ($field_name, $this->data[$this->key])
				->single ();
			return $this->{'_ref_' . $name};

		} elseif (isset ($this->fields[$name]['has_many'])) {
			// handle has_many relationships (one to many)
			if (! $reset_cache && isset ($this->{'_ref_' . $name})) {
				return $this->{'_ref_' . $name};
			}
			$class = $this->fields[$name]['has_many'];
			$field_name = isset ($this->fields[$name]['field_name']) ? $this->fields[$name]['field_name'] : $this->table;
			if (isset ($this->fields[$name]['order_by'])) {
				$this->{'_ref_' . $name} = $class::query ()
					->where ($field_name, $this->data[$this->key])
					->order ($this->fields[$name]['order_by'])
					->fetch ($limit, $offset);
			} else {
				$this->{'_ref_' . $name} = $class::query ()
					->where ($field_name, $this->data[$this->key])
					->fetch ($limit, $offset);
			}
			return $this->{'_ref_' . $name};

		} elseif (isset ($this->fields[$name]['many_many'])) {
			// handle many_many relationships (many to many)
			if (! $reset_cache && isset ($this->{'_ref_' . $name})) {
				return $this->{'_ref_' . $name};
			}
			$class = $this->fields[$name]['many_many'];
			$obj = new $class;
			$join_table = $this->fields[$name]['join_table'];
			$this_field = isset ($this->fields[$name]['this_field']) ? $this->fields[$name]['this_field'] : $this->table;
			$that_field = isset ($this->fields[$name]['that_field']) ? $this->fields[$name]['that_field'] : $obj->table;
			$order_by = isset ($this->fields[$name]['order_by']) ? $this->fields[$name]['order_by'] : false;

			// we need this for the table and primary key fields
			// of the other table
			$obj = new $class;

			if (is_array ($order_by)) {
				$order_by[0] = Model::backticks ($obj->table) . '.' . Model::backticks ($order_by[0]);
				$this->{'_ref_' . $name} = $class::query (Model::backticks ($obj->table) . '.*')
					->from (Model::backticks ($obj->table) . ', ' . Model::backticks ($join_table))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($that_field) . ' = ' . Model::backticks ($obj->table) . '.' . Model::backticks ($obj->key))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($this_field), $this->id)
					->order ($order_by[0], $order_by[1])
					->fetch ($limit, $offset);

			} elseif ($order_by !== false) {
				$order_by = Model::backticks ($obj->table) . '.' . Model::backticks ($order_by);
				$this->{'_ref_' . $name} = $class::query (Model::backticks ($obj->table) . '.*')
					->from (Model::backticks ($obj->table) . ', ' . Model::backticks ($join_table))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($that_field) . ' = ' . Model::backticks ($obj->table) . '.' . Model::backticks ($obj->key))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($this_field), $this->id)
					->order ($order_by)
					->fetch ($limit, $offset);

			} else {
				$this->{'_ref_' . $name} = $class::query (Model::backticks ($obj->table) . '.*')
					->from (Model::backticks ($obj->table) . ', ' . Model::backticks ($join_table))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($that_field) . ' = ' . Model::backticks ($obj->table) . '.' . Model::backticks ($obj->key))
					->where (Model::backticks ($join_table) . '.' . Model::backticks ($this_field), $this->id)
					->fetch ($limit, $offset);
			}

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
	public function __get ($key) {
		return (isset ($this->data[$key])) ? $this->data[$key] : null;
	}

	/**
	 * Custom setter that saves to `$this->data` array.
	 */
	public function __set ($key, $val) {
		$this->data[$key] = $val;
	}

	/**
	 * Save the object to the database. If $verify is set, it will
	 * validate the data against any rules in the array, or in the
	 * specified INI file if $verify is a string matching a file name.
	 */
	public function put () {
		$failed = (count ($this->verify) > 0)
			? Validator::validate_list ($this->data, $this->verify)
			: array ();

		if (! empty ($failed)) {
			$this->failed = $failed;
			$this->error = 'Validation failed for: ' . join (', ', $failed);
			return false;
		} else {
			$this->failed = array ();
		}

		if ($this->is_new) {
			// This is an insert
			$ins = array ();
			$len = count ($this->data);
			for ($i = 0; $i < $len; $i++) {
				$ins[] = '?';
			}
			if (! DB::execute ('insert into `' . $this->table . '` (' . join (', ', Model::backticks (array_keys ($this->data))) . ') values (' . join (', ', $ins) . ')', $this->data)) {
				$this->error = DB::error ();
				return false;
			}
			if (! isset ($this->data[$this->key])) {
				$this->data[$this->key] = (DB::get_connection(DB::$last_conn)->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') ? DB::last_id(str_replace ('#prefix#', DB::$prefix, $this->table) . '_' . $this->key . '_seq') : DB::last_id();
				$this->keyval = $this->data[$this->key];
			}
			$this->is_new = false;
			return true;
		}
		
		// This is an update
		$ins = '';
		$par = array ();
		$sep = '';
		foreach ($this->data as $key => $val) {
			$ins .= $sep . Model::backticks ($key) . ' = ?';
			$par[] = $val;
			$sep = ', ';
		}
		if ($this->keyval && $this->keyval !== $this->data[$this->key]) {
			$par[] = $this->keyval;
		} else {
			$par[] = $this->data[$this->key];
			$this->keyval = $this->data[$this->key];
		}
		if (! DB::execute ('update `' . $this->table . '` set ' . $ins . ' where `' . $this->key . '` = ?', $par)) {
			$this->error = DB::error ();
			return false;
		}
		$this->is_new = false;
		return true;
	}
	
	/**
	 * Delete the specified or the current element if no id is specified.
	 */
	public function remove ($id = false) {
		$id = ($id) ? $id : $this->data[$this->key];
		if (! $id) {
			$this->error = 'No id specified.';
			return false;
		}
		if (! DB::execute ('delete from `' . $this->table . '` where `' . $this->key . '` = ?', $id)) {
			$this->error = DB::error ();
			return false;
		}
		return true;
	}

	/**
	 * Get a single object and update the current instance with that data.
	 */
	public static function get ($id) {
		$class = get_called_class ();
		$q = new $class;
		$res = (array) DB::single ('select * from `' . $q->table . '` where `' . $q->key . '` = ?', $id);
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
	public static function query ($fields = false) {
		$class = get_called_class ();
		if ($fields) {
			$obj = new $class;
			$obj->query_fields = $fields;
			return $obj;
		}
		return new $class;
	}

	/**
	 * Specify an alternate `from` clause for an SQL query. Overrides
	 * using `$this->table` with a custom value.
	 */
	public function from ($from) {
		$this->query_from = $from;
		return $this;
	}

	/**
	 * Order the query by the specified clauses. Can be called multiple
	 * times to create complex sorting.
	 *
	 * Usage:
	 *
	 *   ->order ('field_name', 'asc') // preferred method
	 *   ->order ('another_field asc') // alternate usage
	 */
	public function order ($by, $order = false) {
		if (is_array ($by)) {
			list ($by, $order) = $by;
		}

		$sep = empty ($this->query_order) ? ' ' : ', ';
		if (! $order) {
			$this->query_order .= $sep . $by;
		} else {
			$this->query_order .= $sep . Model::backticks ($by) . ' ' . $order;
		}
		return $this;
	}

	/**
	 * Group the query by the specific clauses. Can be called multiple
	 * times to group by multiple fields.
	 *
	 * Usage:
	 *
	 *   ->group ('field_name')
	 *   ->group ('another_field')
	 */
	public function group ($group) {
		$sep = empty ($this->query_group) ? ' ' : ', ';
		$this->query_group .= $sep . Model::backticks ($group);
		return $this;
	}

	/**
	 * Add a where condition to the query. Can be either a field/value
	 * combo, or if no value is present the first parameter can be one
	 * of the following:
	 *
	 * - A custom where clause, e.g., `name like "%value%"`
	 * - An associative array of clauses grouped by parentheses
	 * - A closure function that creates one or more grouped clauses
	 */
	public function where ($key, $val = null) {
		if ($val === null) {
			if (is_array ($key)) {
				array_push ($this->query_filters, '(');
				foreach ($key as $k => $v) {
					$this->where ($k, $v);
				}
				array_push ($this->query_filters, ')');
			} elseif ($key instanceof Closure) {
				array_push ($this->query_filters, '(');
				$key ($this);
				array_push ($this->query_filters, ')');
			} else {
				array_push ($this->query_filters, $key);
			}
		} elseif (strpos ($key, '?') !== false) {
			array_push ($this->query_filters, $key);
			array_push ($this->query_params, $val);
		} else {
			array_push ($this->query_filters, Model::backticks ($key) . ' = ?');
			array_push ($this->query_params, $val);
		}
		return $this;
	}

	/**
	 * Creates an or clause with additional where conditions.
	 * Accepts the same parameters as `where()`.
	 */
	public function or_where ($key, $val = null) {
		array_push ($this->query_filters, ' or ');
		return $this->where ($key, $val);
	}

	/**
	 * Add a having condition to the query. Can be either a field/value
	 * combo, or if no value is present the first parameter can be one
	 * of the following:
	 *
	 * - A custom having clause, e.g., `name like "%value%"`
	 * - An associative array of clauses grouped by parentheses
	 * - A closure function that creates one or more grouped clauses
	 */
	public function having ($key, $val = null) {
		if (! empty ($this->query_group)) {
			if ($val === null) {
				if (is_array ($key)) {
					array_push ($this->query_having, '(');
					foreach ($key as $k => $v) {
						$this->having ($k, $v);
					}
					array_push ($this->query_having, ')');
				} elseif ($key instanceof Closure) {
					array_push ($this->query_having, '(');
					$key ($this);
					array_push ($this->query_having, ')');
				} else {
					array_push ($this->query_having, $key);
				}
			} elseif (strpos ($key, '?') !== false) {
				array_push ($this->query_having, $key);
				array_push ($this->query_params, $val);
			} else {
				array_push ($this->query_having, Model::backticks ($key) . ' = ?');
				array_push ($this->query_params, $val);
			}
		}
		return $this;
	}

	/**
	 * Verify that the limit is false or numeric, and that the offset
	 * is always numeric. Prevents SQL injection via these values.
	 */
	public function limit_offset_ok ($limit, $offset) {
		if ($limit !== false && ! is_numeric ($limit)) {
			$this->error = 'Invalid limit value';
			return false;
		}
		if (! is_numeric ($offset)) {
			$this->error = 'Invalid offset value';
			return false;
		}
		return true;
	}

	/**
	 * Generates the SQL query used for execution. If the limit or
	 * offset values are invalid, it will return false. Otherwise,
	 * it returns the SQL string with `?` for parameters to be
	 * filled with `$this->query_params`.
	 */
	public function sql ($limit = false, $offset = 0) {
		if (! $this->limit_offset_ok ($limit, $offset)) {
			return false;
		}

		if (is_array ($this->query_fields)) {
			$this->query_fields = join (', ', Model::backticks ($this->query_fields));
		}

		if ($this->query_from === false) {
			$this->query_from = Model::backticks ($this->table);
		}

		$sql = 'select ' . $this->query_fields . ' from ' . $this->query_from;

		if (count ($this->query_filters) > 0) {
			$sql .= ' where ';
			$and = '';
			foreach ($this->query_filters as $where) {
				if ($where === '(' || $where === ' or ') {
					$sql .= $where;
					$and = '';
				} elseif ($where === ')') {
					$sql .= $where;
					$and = ' and ';
				} else {
					$sql .= $and . $where;
					$and = ' and ';
				}
			}
		}
		if (! empty ($this->query_group)) {
			$sql .= ' group by' . $this->query_group;
		}
		if (count ($this->query_having) > 0) {
			$sql .= ' having ';
			$and = '';
			foreach ($this->query_having as $having) {
				if ($having === '(' || $having === ' or ') {
					$sql .= $having;
					$and = '';
				} elseif ($having === ')') {
					$sql .= $having;
					$and = ' and ';
				} else {
					$sql .= $and . $having;
					$and = ' and ';
				}
			}
		}
		if (! empty ($this->query_order)) {
			$sql .= ' order by' . $this->query_order;
		}
		if ($limit) {
			$sql .= ' limit ' . $limit . ' offset ' . $offset;
		}

		return $sql;
	}

	/**
	 * Fetch as an array of model objects.
	 */
	public function fetch ($limit = false, $offset = 0) {
		$sql = $this->sql ($limit, $offset);
		if ($sql === false) {
			return false;
		}

		$res = DB::fetch ($sql, $this->query_params);
		if (! $res) {
			$this->error = DB::error ();
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
	public function single () {
		$sql = $this->sql ();
		if ($sql === false) {
			return false;
		}

		$res = DB::single ($sql, $this->query_params);
		if (! $res) {
			$this->error = DB::error ();
			return $res;
		}
		$class = get_class ($this);
		$res = new $class ((array) $res, false);
		return $res;
	}

	/**
	 * Fetch the number of results for a query.
	 */
	public function count ($limit = false, $offset = 0) {
		$this->query_fields = 'count(*)';

		$sql = $this->sql ($limit, $offset);
		if ($sql === false) {
			return false;
		}

		$res = DB::shift ($sql, $this->query_params);
		if ($res === false) {
			$this->error = DB::error ();
		}
		return $res;
	}

	/**
	 * Fetch as an array of the original objects as returned from
	 * the database.
	 */
	public function fetch_orig ($limit = false, $offset = 0) {
		$sql = $this->sql ($limit, $offset);
		if ($sql === false) {
			return false;
		}

		$res = DB::fetch ($sql, $this->query_params);
		if (! $res) {
			$this->error = DB::error ();
		}
		return $res;
	}

	/**
	 * Fetch as an associative array of the specified key/value fields.
	 */
	public function fetch_assoc ($key, $value, $limit = false, $offset = 0) {
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
	public function fetch_field ($value, $limit = false, $offset = 0) {
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
	public function orig () {
		return (object) $this->data;
	}

	/**
	 * Return a single field's value for the object with the given ID.
	 * This is often useful for filters, for example:
	 *
	 *     {{ user_id|User::field (%s, 'name') }}
	 */
	public static function field ($id, $field) {
		error_log ($id . ': ' . $field);
		$class = get_called_class ();
		$obj = new $class;
		return DB::shift (
			'select ' . Model::backticks ($field) . ' from ' . Model::backticks ($obj->table) . ' where ' . Model::backticks ($obj->key) . ' = ?',
			$id
		);
	}

	/**
	 * Add backticks to a name or list of names to prevent clashing with
	 * reserved words in SQL.
	 */
	public static function backticks ($item) {
		if (is_array ($item)) {
			foreach ($item as $k => $v) {
				if (strpos ($v, '`') !== 0) {
					$item[$k] = '`' . str_replace ('.', '`.`', $v) . '`';
				} else {
					$item[$k] = $v; // Already has backticks
				}
			}
		} elseif (strpos ($item, '`') !== 0) {
			$item = '`' . str_replace ('.', '`.`', $item) . '`';
		}
		return $item;
	}

	/**
	 * Performs a batch of changes wrapped in a database transaction.
	 * The batch `$task` can be an array of items to insert at once,
	 * or a closure function that executes a series of tasks and
	 * performs whatever logic necessary. If any insert fails, or if
	 * the function returns false, the transaction will be rolled
	 * back, otherwise it will be committed. For databases that support
	 * it, records will be inserted using a single SQL insert statement
	 * for greater efficiency.
	 */
	public static function batch ($tasks) {
		DB::execute ('begin');
		if ($tasks instanceof Closure) {
			if ($tasks () === false) {
				self::$batch_error = DB::error ();
				DB::execute ('rollback');
				return false;
			}
		} elseif (is_array ($tasks)) {
			// Check the driver type, because SQLite doesn't support
			// multiple row inserts
			$db = DB::get_connection (1);
			if (! $db) {
				self::$batch_error = 'No database connection';
				return false;
			}

			if ($db->getAttribute (PDO::ATTR_DRIVER_NAME) === 'sqlite') {
				$class = get_called_class ();
				foreach ($tasks as $task) {
					$o = new $class ($task);
					if (! $o->put ()) {
						self::$batch_error = $o->error;
						DB::execute ('rollback');
						return false;
					}
				}
				return DB::execute ('commit');
			}

			// Build the multi-row insert statement
			$class = get_called_class ();
			$o = new $class;
			$sql = 'insert into `' . $o->table . '` (';
			$data = array ();

			// Figure out how many placeholders are needed per record
			$ins = array ();
			$len = count ($tasks[0]);
			for ($i = 0; $i < $len; $i++) {
				$ins[] = '?';
			}

			// Add fields to statement
			$sql .= join (', ', Model::backticks (array_keys ($tasks[0]))) . ') values ';
			$sep = '';

			// Add each record to the statement
			foreach ($tasks as $task) {
				$data = array_merge ($data, array_values ($task));
				$sql .= $sep . '(' . join (', ', $ins) . ')';
				$sep = ', ';
			}

			if (! DB::execute ($sql, $data)) {
				self::$batch_error = DB::error ();
				DB::execute ('rollback');
				return false;
			}
		}
		return DB::execute ('commit');
	}

	/**
	 * Fetch the next incremental value for the specified field.
	 * If no field name is specified, it will use the primary key
	 * field by default.
	 */
	public function next ($field = false) {
		if ($field === false) {
			$field = $this->key;
		}
		
		$res = DB::shift (
			'select (' . Model::backticks ($field) . ' + 1)' .
			' from ' . Model::backticks ($this->table) .
			' order by ' . Model::backticks ($field) . ' desc' .
			' limit 1'
		);
		if (! $res) {
			return 1;
		}
		return $res;
	}

	/**
	 * Get the table name for this model.
	 */
	public static function table () {
		$class = get_called_class ();
		$o = new $class;
		return $o->table;
	}

	/**
	 * Get the primary key field for this model.
	 */
	public static function key () {
		$class = get_called_class ();
		$o = new $class;
		return $o->key;
	}
}

?>
