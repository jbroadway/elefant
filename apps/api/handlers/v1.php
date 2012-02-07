<?php

/**
 * Provides RESTful CRUD API access to the objects specified in
 * `apps/api/conf/config.php`. Requests all require providing
 * an API token and secret key that matches those stored in
 * the `api` database table. Uses HTTP Basic authentication
 * to request the credentials. See `apps/api/models/Api.php`
 * for info on generating access tokens.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! Api::require_auth ()) {
	$res = new StdClass;
	$res->success = false;
	$res->error = 'Authorization required.';
	header ('WWW-Authenticate: Basic realm="API"');
	header ('HTTP/1.0 401 Unauthorized');
	echo json_encode ($res);
	return;
}

$error = false;

if (! isset ($appconf['Objects'][$this->params[0]])) {
	$error = 'Invalid request object: ' . $this->params[0];
} else {
	$class = $appconf['Objects'][$this->params[0]];
	switch ($this->params[1]) {
		case 'add':
			/**
			 * Add an item. Parameters are the values of each required field
			 * for that item type, including empty values for non-specified
			 * fields which can't be null.
			 *
			 * Request Method: POST
			 *
			 * Usage:
			 *
			 *   /api/v1/class/add
			 *
			 * Request data:
			 *
			 *   field1=value&field2=value
			 *
			 * Response:
			 *
			 *   {"success":true,"data":{"keyfieldname": "value"}}
			 */
			if (strtolower ($_SERVER['REQUEST_METHOD']) != 'post') {
				$error = 'Request method must be POST';
				break;
			}
			$obj = new $class ($_POST);
			if (! $obj->put ()) {
				$error = $obj->error;
				break;
			} else {
				$out = array ($obj->key => $obj->{$obj->key});
			}
			break;
		case 'edit':
			/**
			 * Edit an item. Parameters are the values of each required field
			 * for that item type, including empty values for non-specified
			 * fields which can't be null.
			 *
			 * Request Method: POST
			 *
			 * Usage:
			 *
			 *   /api/v1/class/edit
			 *
			 * Request data:
			 *
			 *   field1=value&field2=value
			 *
			 * Response:
			 *
			 *   {"success":true,"data":{"keyfieldname": "value"}}
			 */
			if (strtolower ($_SERVER['REQUEST_METHOD']) != 'post') {
				$error = 'Request method must be POST';
				break;
			}
			$obj = new $class;
			if (! isset ($_POST[$obj->key])) {
				$error = 'No item specified.';
				break;
			}
			$obj = $obj->get ($_POST[$obj->key]);
			foreach ($_POST as $key => $value) {
				$obj->{$key} = $value;
			}
			if (! $obj->put ()) {
				$error = $obj->error;
				break;
			} else {
				$out = array ($obj->key => $obj->{$obj->key});
			}
			break;
		case 'delete':
			/**
			 * Delete an object.
			 *
			 * Usage:
			 *
			 *   /api/v1/class/delete/id
			 *
			 *   /api/v1/page/delete/index
			 *
			 * Response:
			 *
			 *   {"success":true,"data":{"keyfieldname":"value"}}
			 */
			$obj = new $class ($this->params[2]);
			if ($obj->error) {
				$error = $obj->error;
			} elseif (! $obj->remove ()) {
				$error = $obj->error;
			} else {
				$out = array ($obj->key => $this->params[2]);
			}
			break;
		case 'find':
			/**
			 * Fetch all objects matching a search. Parameters include:
			 *
			 *   fieldname=value -> WHERE fieldname = "value"
			 *
			 *   fieldname=~value -> WHERE fieldname LIKE "%value%"
			 *
			 *   order=field:asc -> ORDER BY field asc
			 *   order=field:desc -> ORDER BY field desc
			 *
			 *   group=field -> GROUP BY field
			 *
			 *   limit=10 -> LIMIT 10
			 *
			 *   offset=10 -> OFFSET 10
			 *
			 * Limit defaults to 20 and offset to 0. You can specify an arbitrary number
			 * of fields to search, but only equality can be compared. Only one order by
			 * or group by allowed.
			 *
			 * Usage:
			 *
			 *   /api/v1/class/find?field=value&limit=20&offset=0&order=field:asc
			 */
			$obj = $class::query ();
			$limit = 20;
			$offset = 0;
			foreach ($_GET as $key => $value) {
				if ($key == 'limit') {
					$limit = $value;
				} elseif ($key == 'offset') {
					$offset = $value;
				} elseif ($key == 'order' && preg_match ('/^([a-zA-Z0-9_]+):(asc|desc)$/i', $value, $regs)) {
					$obj->order ($regs[1] . ' ' . $regs[2]);
				} elseif ($key == 'group' && preg_match ('/^([a-zA-Z0-9_]+)$/i', $value, $regs)) {
					$obj->group ($regs[1]);
				} else {
					$obj->where ($key, $value);
				}
			}
			$out = $obj->fetch_orig ($limit, $offset);
			if ($obj->error) {
				$error = $obj->error;
			}
			break;
		case null:
			$error = 'No request method specified.';
			break;
		default:
			/**
			 * Fetch a single object by ID.
			 *
			 * Usage:
			 *
			 *   /api/v1/class/id
			 *
			 *   /api/v1/page/index
			 */
			$obj = new $class ($this->params[1]);
			if ($obj->error) {
				$error = $obj->error;
			} else {
				$out = $obj->orig ();
			}
			break;
	}
}

// output
$res = new StdClass;
if ($error) {
	$res->success = false;
	$res->error = $error;
} else {
	$res->success = true;
	$res->data = $out;
}

echo json_encode ($res);

?>