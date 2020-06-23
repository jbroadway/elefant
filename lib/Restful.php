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
 * Restful is a base class for implementing REST APIs. Restful is
 * meant to be extended and passed to `Controller::restful()`
 * to create RESTful request handlers. Works with GET, POST, PUT, and
 * DELETE request methods, as well as the `X-HTTP-Method-Override`
 * header if your server can't handle PUT and DELETE requests.
 *
 * Usage:
 *
 * 1\. Create a Restful-based class and save it to
 * the file `apps/myapp/lib/API.php`:
 *
 *     <?php
 *     
 *     namespace myapp;
 *     
 *     class API extends \Restful {
 *         // Accessible via `GET /myapp/api/article/123`
 *         public function get_article ($id) {
 *             // Return some data
 *             return (object) array (
 *                 'id' => $id,
 *                 'title' => 'My title'
 *             );
 *         }
 *     
 *         // Accessible via `POST /myapp/api/article`
 *         public function post_article () {
 *             // Return an error
 *             return $this->error ('Error message');
 *         }
 *     }
 *     
 *     ?>
 *
 * 2\. Create a handler and assign your class as its
 * restful handler in the file `apps/myapp/handlers/api.php`:
 *
 *     <?php
 *     
 *     $this->restful (new myapp\API);
 *     
 *     ?>
 *
 * Responses come in the following form, depending on success
 * or failure:
 *
 *     {"success": true, "data": {"id": 123, "title": "My title"}}
 *
 *     {"success": false, "error": "Error message"}
 *
 * Additional notes:
 *
 * - Parameters passed to methods requests are expected to be from
 *   the extra URL parameters. `$_GET` and `$_POST` are available
 *   separately.
 *
 * - PUT data can be accessed via `$this->get_put_data()` or via
 *   `$this->get_put_data (true)` to automatically JSON decode it.
 */
class Restful {
	/**
	 * The controller object.
	 */
	public $controller = null;

	/**
	 * The cache object.
	 */
	public $cache = null;

	/**
	 * Whether `wrap()` should alter the output data to add
	 * a `{"success":true,"data":"..."}` structure around it.
	 */
	public $wrap = true;

	/**
	 * List any custom routes in this array. Useful for complex
	 * routes such as with nested resources. Format is as
	 * follows:
	 *
	 *     public $custom_routes = array (
	 *         'GET article/%d/comment/%d' => 'article_comment'
	 *     );
	 *
	 * The key is made up of the request method and the route
	 * pattern to match. The value is the name of the method that
	 * handles the request.
	 *
	 * The request method can be specified as follows:
	 *
	 * - One of `GET`, `POST`, etc.
	 * - Multiple methods combined via `GET|POST`
	 * - All methods via `ALL`
	 *
	 * The route pattern matches the tail of the request URI
	 * after the current handler's name. For example, if your
	 * REST handler is found at `/myapp/api` and you want to
	 * match `GET /myapp/api/custom/method` then you would specify:
	 *
	 *     'GET custom/method' => 'custom_method'
	 *
	 * Pattern matching in the route works with `%s` for matching
	 * strings and %d for matching numbers, which is converted into
	 * a regular expression for evaluation. For example:
	 *
	 *     'GET article/%d/comment/%d' => 'article_comment'
	 *
	 * Where `article_comment()` should accept two parameters:
	 *
	 *     public function article_comment ($article_id, $comment_id) {
	 *         // etc.
	 *     }
	 */
	public $custom_routes = array ();

	/**
	 * Get and optionally JSON decode the PUT requests data.
	 */
	public function get_put_data ($decode = false) {
		$data = $this->controller->get_put_data ();
		if ($decode) {
			return json_decode ($data);
		}
		return $data;
	}

	/**
	 * Get and optionally JSON decode the raw POST data.
	 */
	public function get_raw_post_data ($decode = false) {
		$data = $this->controller->get_raw_post_data ();
		if ($decode) {
			return json_decode ($data);
		}
		return $data;
	}

	/**
	 * Wrap the specified data in a `{success:true,data:data}` structure
	 * and echo it.
	 */
	public function wrap ($data) {
		if ($this->wrap) {
			$res = new StdClass;
			$res->success = true;
			$res->data = $data;
			echo json_encode ($res);
		} else {
			echo json_encode ($data);
		}
		return true;
	}

	/**
	 * Echo a JSON-encoded error response object and return null.
	 */
	public function error (string $message, $code = null) {
		if ($code !== null) {
			$this->controller->status_code ($code);
		}
		$res = new StdClass;
		$res->success = false;
		$res->error = $message;
		error_log ($message);
		echo json_encode ($res);
		return null;
	}

	/**
	 * Verify that the user is authorized to access one or more resources.
	 * If the user is not logged in, it will also return false.
	 */
	public function require_acl ($resource) {
		$args = func_get_args ();
		foreach ($args as $resource) {
			if (! User::require_acl ($resource)) {
				return false;
			}
		}
		return true;
	}
}
