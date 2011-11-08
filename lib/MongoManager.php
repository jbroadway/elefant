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
 * Class to manage the connection to a MongoDB database. To specify your
 * MongoDB settings, add the following section to `conf/config.php`:
 *
 *     [Mongo]
 *     host = localhost:27017
 *     name = database_name
 *     user = username ; optional
 *     pass = password ; optional
 *     set_name = my_replica_set ; optional
 *
 * Then you can either use this class directly like so:
 *
 *     <?php
 *     
 *     // get the connection
 *     $mongo = MongoManager::get_connection ();
 *     
 *     // get the MongoDbB object
 *     $db = MongoManager::get_database ();
 *     
 *     ?>
 *
 * Or use the MongoModel class to define a Mongo-based model for your data:
 *
 *     <?php
 *     
 *     class MyCollection extends MongoModel {}
 *
 *     $obj = new MyCollection (array ('foo' => 'bar'));
 *     $obj->put ();
 *     // etc.
 *     
 *     ?>
 */
class MongoManager {
	/**
	 * The Mongo connection object.
	 */
	public static $conn = false;

	/**
	 * Get the Mongo database connection object. Uses your
	 * settings from `conf/config.php` for the connection
	 * info (`host`, `name`, `user`, `pass`, and `set_name`).
	 * The host and database name are required, but authentication
	 * (`user` and `pass`) settings and replica set name
	 * (`set_name`) are optional.
	 */
	public static function get_connection () {
		$conf = conf ('Mongo');

		if (! self::$conn) {
			if (isset ($conf['user'])) {
				$connstr = 'mongodb://' . $conf['user'] . ':' . $conf['pass'] . '@' . $conf['host'];
			} else {
				$connstr = $conf['host'];
			}
			if (isset ($conf['set_name'])) {
				self::$conn = new Mongo ($connstr, array ('replicaSet' => $conf['set_name']));
			} else {
				self::$conn = new Mongo ($connstr);
			}
		}
		return self::$conn;
	}

	/**
	 * Get the MongoDB database object.
	 */
	public static function get_database () {
		return MongoManager::get_connection ()->{conf ('Mongo', 'name')};
	}
}

?>