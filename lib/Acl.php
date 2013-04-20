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
 * A simple access control class for implementing custom permissions in your
 * applications.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // Default usage
 *     $acl = new Acl ();
 *     
 *     if (! $acl->allowed ('resource')) {
 *         // Keep the current user out
 *     }
 *     
 *     // Use an alternate user
 *     $user = new User ($user_id);
 *     
 *     if (! $acl->allowed ('resource', $user)) {
 *         // Keep the user out
 *     }
 *     
 *     ?>
 *
 * The format of the INI file is as follows:
 *
 *     [admin]
 *     
 *     default = On
 *     
 *     [editor]
 *     
 *     default = On
 *     user/admin = Off
 *     myapp = Off
 *     
 *     [member]
 *     
 *     default = Off
 *     user = On
 *
 * The default lines change whether you should allow or deny by default for a
 * given role. The naming convention `user/admin` signifies a feature within an
 * app as opposed to the app itself.
 *
 * Note that Elefant doesn't use Acl in its own features, just `require_admin()`
 * and `require_login()`. This class is intended for custom applications built on
 * top of the framework.
 */
class Acl {
	/**
	 * The INI file that the rules were read from.
	 */
	public $file = 'conf/acl.php';

	/**
	 * The access control rules as an array of roles and their permissions.
	 */
	public $rules = array ();
	
	/**
	 * A list of resources defined by the installed apps.
	 */
	public $resources = null;

	/**
	 * Constructor will call `init()` if a file is provided, or simply
	 * set the `$rules` if an array is passed to it. With no parameters,
	 * it will try to read `conf/acl.php` for the access list.
	 */
	public function __construct ($file = 'conf/acl.php') {
		if (is_string ($file)) {
			$this->file = $file;
			$this->init ();
		} elseif (is_array ($file)) {
			$this->rules = $file;
		}
	}

	/**
	 * Parses the INI file and generates the rule list, adding default=false
	 * if no default is specified for that role (deny by default).
	 */
	public function init () {
		if (file_exists ($this->file)) {
			$list = parse_ini_file ($this->file, true);
		} else {
			$list = parse_ini_string ($this->file, true);
		}

		foreach ($list as $role => $rules) {
			$rules['default'] = isset ($rules['default']) ? $rules['default'] : false;
			$this->rules[$role] = $rules;
		}
	}

	/**
	 * Test whether they can access a resource. If no user object is provided,
	 * will use `User::$user->type` to determine the role.
	 */
	public function allowed ($resource, $user = false) {
		$type = $user ? $user->type : User::$user->type;
		error_log ("Is $type allowed to access $resource?");

		return isset ($this->rules[$type][$resource])
			? $this->rules[$type][$resource]
			: $this->rules[$type]['default'];
	}

	/**
	 * Add a role to the list, optionally assigning whether it should
	 * allow or deny by default.
	 */
	public function add_role ($role, $default = false) {
		$this->rules[$role] = array (
			'default' => $default
		);
	}

	/**
	 * Deny a role access to the specified resource.
	 */
	public function deny ($role, $resource) {
		if (! is_array ($this->rules[$role])) {
			$this->add_role ($role);
		}
		$this->rules[$role][$resource] = false;
	}

	/**
	 * Allow a role access to the specified resource.
	 */
	public function allow ($role, $resource) {
		if (! is_array ($this->rules[$role])) {
			$this->add_role ($role);
		}
		$this->rules[$role][$resource] = true;
	}

	/**
	 * Find all resources defined by the installed apps.
	 */
	public function resources () {
		if ($this->resources === null) {
			$files = glob ('apps/*/conf/acl.php');
			$files = is_array ($files) ? $files : array ();
			$this->resources = array ();
			foreach ($files as $file) {
				$resources = parse_ini_file ($file);
				if (! is_array ($resources)) {
					continue;
				}
				$this->resources = array_merge ($this->resources, $resources);
			}
			asort ($this->resources);
		}
		return $this->resources;
	}
}

?>