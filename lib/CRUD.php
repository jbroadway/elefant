<?php

/**
 * Generates a RESTful CRUD (Create, Read, Update, Delete) interface
 * for a given model, with control for enforcing limits, visibility
 * of fields, and which fields can be updated through the REST API.
 *
 * Usage:
 *
 * 1. Define your API library:
 *
 *     <?php // apps/blog/lib/API.php
 *     
 *     namespace blog;
 *     
 *     use CRUD;
 *     
 *     class API extends CRUD {
 *         public $model = 'blog\Post';
 *     
 *         public $visible = array (
 *             'id', 'title', 'ts', 'body'
 *         );
 *     
 *         public $editable = array (
 *             'title', 'body'
 *         );
 *     }
 *     
 *     ?>
 * 
 * 2. Connect it in a handler:
 * 
 *     <?php // apps/blog/handlers/api/post.php
 *     
 *     // Add your authentication scheme
 *     $this->require_auth (user\Auth\HMAC::init ($this, $cache, 3600));
 *     
 *     // Hand the request off to your new API
 *     $this->restful (new blog\API);
 *     
 *     ?>
 *
 * Your API should now be accessible at the following endpoint:
 *
 *     http://www.example.com/blog/api/post
 *
 * To version your API, simply save it into a subfolder with the
 * version name, for example:
 *
 *     apps/blog/handlers/api/v1/post.php
 *
 * Will map to:
 *
 *     http://www.example.com/blog/api/v1/post
 *
 * For easy JavaScript access, see `apps/admin/js/api.js` which provides
 * a client-side wrapper around the built-in CRUD methods. Simply give
 * it a name and an endpoint URL path.
 *
 * The various built-in methods include:
 *
 *     GET    /resource             # get first 30 objects
 *     GET    /resource?offset=30   # get next 30 objects
 *     GET    /resource/ID          # get object by ID value
 *     POST   /resource             # create a new object
 *     POST   /resource/ID          # update an existing object
 *     POST   /resource/delete/ID   # delete an object
 *     DELETE /resource/ID        # delete via HTTP DELETE method
 *     GET    /resource/limit       # get the limit /resource is set for
 *     GET    /resource/permissions # get the crud permissions
 * 
 * Also note that `CRUD` inherits from `Restful`, so you can add as many
 * new methods to your API as you need.
 */
class CRUD extends Restful {
	/**
	 * The Model class to act upon.
	 */
	public $model = null;

	/**
	 * A list of fields that can be updated via REST.
	 */
	public $editable = array ();

	/**
	 * A list of fields that are accessible in REST results.
	 */
	public $visible = array ();

	/**
	 * The item limit to be returned in one list call.
	 */
	public $limit = 30;
	
	/**
	 * The permissions to enable/disable for this model.
	 */
	public $permissions = array (
		'create', 'read', 'update', 'delete'
	);

	/**
	 * Strip the non-visible properties from an object.
	 * Returns it as an associative array.
	 */
	private function strip_object ($obj) {
		$obj = (array) $obj->orig ();
		
		foreach ($obj as $k => $v) {
			if (! in_array ($k, $this->visible)) {
				unset ($obj[$k]);
			}
		}
		
		return $obj;
	}

	/**
	 * Fetch a single object by its ID.
	 */
	private function fetch ($id) {
		$class = $this->model;
		return new $class ($id);
	}

	/**
	 * Create a new query object.
	 */
	private function query () {
		$class = $this->model;
		return $class::query ();
	}

	/**
	 * Returns the limit number.
	 *
	 * Usage:
	 *
	 *     GET /resource/limit
	 */
	public function get_limit () {
		return $this->limit;
	}

	/**
	 * Returns the permissions for this model.
	 *
	 * Usage:
	 *
	 *     GET /resource/permissions
	 */
	public function get_permissions () {
		return $this->permissions;
	}

	/**
	 * Handles getting a list of items via `GET /resource`
	 * or a single item via `GET /resource/ID`.
	 *
	 * Usage:
	 *
	 *     GET /resource/ID
	 *     GET /resource
	 *     GET /resource?offset=30
	 */
	public function get__default ($id = false) {
		if (! in_array ('read', $this->permissions)) {
			return $this->error ('Permission denied');
		}

		if ($id !== false) {
			// fetch single
			$obj = $this->fetch ($id);
			if ($obj->error) {
				return $this->error ('Not found');
			}

			return $this->strip_object ($obj);
		}

		// fetch list
		if (isset ($_POST['offset'])) {
			if (! is_numeric ($_POST['offset'])) {
				return $this->error ('Invalid offset value');
			}
		} else {
			$_POST['offset'] = 0;
		}

		if (count ($this->visible) === 0) {
			$visible = array ('');
		} else {
			$visible = $this->visible;
		}

		$res = $this->query ($visible)
			->fetch_orig ($this->limit, (int) $_POST['offset']);
		$total = $this->query ($visible)
			->count ();
		return array ('results' => $res, 'total' => $total);
	}

	/**
	 * Handles creating a new item via `POST /resource`.
	 * Returns the newly created resource, which should
	 * include its new ID value. Also handles updating
	 * existing items via `POST /resource/ID`.
	 *
	 * Usage:
	 *
	 *     POST /resource
	 *     POST /resource/ID
	 */
	public function post__default ($id = false) {
		if ($id !== false) {
			// update
			if (! in_array ('update', $this->permissions)) {
				return $this->error ('Permission denied');
			}

			$obj = $this->fetch ($id);
			if ($obj->error) {
				return $this->error ('Not found');
			}

			foreach ($_POST as $k => $v) {
				if (! in_array ($k, $this->editable)) {
					return $this->error ('Invalid parameter: ' . $k);
				}
				$obj->{$k} = $v;
			}

			if (! $obj->put ()) {
				return $this->error ('Error updating object');
			}

			return $this->strip_object ($obj);
		}

		// create
		if (! in_array ('create', $this->permissions)) {
			return $this->error ('Permission denied');
		}

		$class = $this->model;
		$obj = new $class ();

		foreach ($_POST as $k => $v) {
			if (! in_array ($k, $this->editable)) {
				return $this->error ('Invalid parameter: ' . $k);
			}
			$obj->{$k} = $v;
		}

		if (! $obj->put ()) {
			return $this->error ('Error creating object');
		}

		return $this->strip_object ($obj);
	}

	/**
	 * Delete an object via `DELETE /resource/ID`.
	 * Returns the deleted object data on success.
	 *
	 * Usage:
	 *
	 *     DELETE /resource/ID
	 */
	public function delete__default ($id) {
		if (! in_array ('delete', $this->permissions)) {
			return $this->error ('Permission denied');
		}

		$obj = $this->fetch ($id);
		if ($obj->error) {
			return $this->error ('Not found');
		}

		if (! $obj->remove ()) {
			return $this->error ('Error deleting object');
		}

		return $this->strip_object ($obj);
	}

	/**
	 * Delete an object via `POST /resource/delete/ID`.
	 * Alias of `DELETE /resource/ID` for those without
	 * `DELETE` capability.
	 *
	 * Usage:
	 *
	 *     POST /resource/delete/ID
	 */
	public function post_delete ($id) {
		return $this->delete__default ($id);
	}
}

?>