/**
 * # $.crud_api ()
 *
 * Client-side REST API consumer with built-in CRUD methods. Works with
 * Elefant's CRUD class to provide client-side access to generated CRUD
 * APIs.
 *
 * ## Usage
 *
 * Assumes the following CRUD endpoint built using the Elefant CMS's
 * CRUD helper class:
 *
 *     <?php
 *     
 *     namespace contacts\API;
 *     
 *     class Contact extends \CRUD {
 *         public $model = 'contacts\Contact';
 *         
 *         public $visible = array (
 *             'id', 'name', 'phone', 'email'
 *         );
 *         
 *         public $editable = array (
 *             'name', 'phone', 'email'
 *         );
 *         
 *         // A custom method, assumes that contacts\Contact also
 *         // has a favourite_food() method.
 *         public function get_favourite_food ($id) {
 *             $obj = $this->fetch ($id);
 *             if ($obj->error) {
 *                 return $this->error ('Not found');
 *             }
 *             return $obj->favourite_food ();
 *         }
 *     }
 *     
 *     ?>
 *
 * In a new handler script, include `api.js` as well as a new file we'll
 * call `apps/contacts/jscontact.js` via:
 *
 *     <?php
 *     
 *     $page->add_script ('/apps/admin/js/jquery.crud_api.js');
 *     $page->add_script ('/apps/contacts/js/contact.js');
 *     
 *     ?>
 *
 * Now create a file in your app called `apps/contacts/js/contact.js` with
 * the following:
 *
 *     var contact = $.crud_api ({
 *         name: 'contact',
 *         endpoint: '/contacts/api/contact',
 *         custom: {
 *             // define your custom methods here
 *             favourite_food: function (id, callback) {
 *                 this.current = 'favourite_food';
 *                 $.get (this.endpoint + '/favourite_food/' + id, callback);
 *                 return this;
 *             }
 *         },
 *         error: function (event, jqXHR, ajaxSettings, thrownError) {
 *             // handle errors here. you can see which method
 *             // was being called via this.current.
 *         }
 *     });
 *     
 *     // alternate way of adding or overriding custom methods
 *     contact.define ('favourite_food', function (id, callback) {
 *         this.current = 'favourite_food';
 *         $.get (this.endpoint + '/favourite_food/' + id, callback);
 *         return this;
 *     });
 *
 * This defines your API as a JavaScript object that wraps your REST calls
 * in simple, lightweight method calls.
 *
 * To use the API now you can do this in your view template:
 *
 *     <script>
 *     $(function () {
 *         post.get (1, function (res) {
 *             if (! res.success) throw new Error (res.error);
 *             console.log (res.data);
 *         });
 *         
 *         post.favourite_food (1, function (res) {
 *             if (! res.success) throw new Error (res.error);
 *             constole.log ('Favourite food is: ' + res.data);
 *         });
 *     });
 *     </script>
 *
 * ## Methods
 *
 *     obj.get (id, callback)          // fetch a single object by its ID
 *     obj.list (data, callback)       // fetch a list of objects, parameters: offset
 *     obj.add (data, callback)        // add a new object to the collection
 *     obj.update (id, data, callback) // update an object
 *     obj.delete (id, callback)       // delete an object
 *     obj.permissions (callback)      // fetch the permissions on this collection
 *     obj.limit (callback)            // fetch the list limit value for list() calls
 *     obj.build_query_string (data)   // build an object into a ?foo=bar string
 *     obj.define (name, function)     // define a new REST method call
 *
 * ## Callbacks
 *
 * The callback function will receive a result object containing
 * two parameters. The first is the `success` parameter which is
 * a boolean that says whether the request succeeded or not. This
 * is separate from connection or server errors which would be
 * handled by the `error` option in the API definition.
 *
 * If `success=true` then the second parameter will be called `data`
 * and will contain the response data. If `success=false` then the
 * second parameter will be called `error` and will contain an
 * error message.
 */
(function ($) {
	var self = {};

	/**
	 * The current method being called, for use in error handling.
	 * Contains the name of the method, e.g., 'list' or 'update';
	 */
	self.current = null;
	
	/**
	 * Helper function to verify parameters.
	 */
	var _has = function (obj, prop) {
		return obj.hasOwnProperty (prop);
	};
	
	/**
	 * console.log wrapper for debugging. Returns the object
	 * passed to it, so it can be used as a pass-through.
	 */
	var _log = function (obj) {
		if (this.debug && window.console) {
			console.log (obj);
		}
		return obj;
	};

	/**
	 * Build a query string from an object.
	 */
	var _build_query_string = function (data) {
		var q = '', sep = '?';
		for (var i in data) {
			q += sep + i + '=' + encodeURIComponent (data[i]);
			sep = '&';
		}
		return q;
	};

	/**
	 * Fetch a single object.
	 */
	var _get = function (id, callback) {
		this.current = 'get';
		$.get (this.endpoint + '/' + id, callback);
		return this;
	};

	/**
	 * Fetch a list of objects.
	 */
	var _list = function (data, callback) {
		this.current = 'list';
		$.get (this.endpoint + _build_query_string (data), callback);
		return this;
	};
	
	/**
	 * Update an object.
	 */
	var _update = function (id, data, callback) {
		this.current = 'update';
		$.post (this.endpoint + '/' + id, data, callback);
		return this;
	};

	/**
	 * Delete an object.
	 */
	var _delete = function (id, callback) {
		this.current = 'delete';
		$.post (this.endpoint + '/delete/' + id, {}, callback);
		return this;
	};

	/**
	 * Find the limit to objects returned by `.list()` in one call.
	 */
	var _limit = function (callback) {
		this.current = 'limit';
		$.get (this.endpoint + '/limit', callback);
		return this;
	};
	
	/**
	 * Fetch the CRUD permissions.
	 */
	var _permissions = function (callback) {
		this.current = 'permissions';
		$.get (this.endpoint + '/permissions', callback);
		return this;
	};

	/**
	 * Add a custom method to your API.
	 */
	var _define = function (name, func) {
		this.custom[name] = func;
		this[name] = $.proxy (func, this);
		return this;
	};

	/**
	 * Add a new view template to the list.
	 */
	$.crud_api = function (api) {
		if (! _has (api, 'name')) throw new Error ('Required: name');
		if (! _has (api, 'endpoint')) api.endpoint = '/' + api.name;
		api.endpoint = api.endpoint.replace (/\/$/, '');
		
		var defaults = {
			debug: false, // Enable/disable debugging output to the console.
			error: null,  // The error handler callback
			custom: {}    // A list of custom methods to define
		};
		
		self[api.name] = $.extend (defaults, api);

		if (self[api.name].error === null) {
			self[api.name].error = $.proxy (_log, self[api.name]);
		}

		self[api.name].build_query_string = $.proxy (_build_query_string, self[api.name]);
		self[api.name].get = $.proxy (_get, self[api.name]);
		self[api.name].list = $.proxy (_list, self[api.name]);
		self[api.name].update = $.proxy (_update, self[api.name]);
		self[api.name].delete = $.proxy (_delete, self[api.name]);
		self[api.name].limit = $.proxy (_limit, self[api.name]);
		self[api.name].permissions = $.proxy (_permissions, self[api.name]);
		self[api.name].define = $.proxy (_define, self[api.name]);

		for (var i in self[api.name].custom) {
			self[api.name].define (i, self[api.name].custom[i]);
		}

		$(document).ajaxError ($.proxy (self[api.name].error, self[api.name]));

		return self[api.name];
	};
})(jQuery);