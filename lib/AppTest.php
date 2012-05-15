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
 * Extends PHPUnit to provide testing capabilities for handlers. Creates
 * the necessary environment that a handler expects. Note that you will
 * have to set up any `$_GET` or `$_POST` values that your handlers expect.
 *
 * Also includes the following convenience functions:
 *
 * - `get()` Aliases `Controller::run()` but catches `header()` exceptions
 *   so you can test for them.
 * - `userAdmin()` Makes you an admin user for subsequent requests.
 * - `userAnon()` Makes you an anonymous user for subsequent requests.
 * - `userMember()` Makes you a member user for subsequent requests.
 *
 * Usage:
 *
 *     <?php
 *     
 *     require_once ('lib/Autoloader.php');
 *     
 *     class MyappAppTest extends AppTest {
 *         public function test_myhandler () {
 *             // Perform a handler request and test its output
 *             $res = $this->get ('myapp/myhandler', array ('one' => 'two');
 *             $this->assertContains ('Expected output', $res);
 *         }
 *     
 *         public function test_admin () {
 *             // This should redirect to /admin
 *             $res = $this->get ('myapp/admin');
 *             $this->assertContains ('headers already sent', $res);
 *       
 *             // Become the admin and try again
 *             $this->userAdmin ();
 *             $res = $this->get ('myapp/admin');
 *             $this->assertContains ('My admin output', $res);
 *       
 *             // Become anonymous user again
 *             $this->userAnon ();
 *       
 *             // Continue...
 *         }
 *     }
 *     
 *     ?>
 */
class AppTest extends PHPUnit_Framework_TestCase {
	/**
	 * Prevent these from being reset between tests.
	 */
	protected $backupGlobalsBlacklist = array ('i18n', 'memcache', 'page', 'tpl');

	/**
	 * The Controller object.
	 */
	protected static $c;

	/**
	 * Make the current user an admin.
	 */
	public function userAdmin () {
		User::$user = new User (1);
	}

	/**
	 * Make the current user a member.
	 */
	public function userMember () {
		User::$user = new User (2);
	}

	/**
	 * Make the current user anonymous.
	 */
	public function userAnon () {
		User::$user = false;
	}

	/**
	 * Alias for [[Controller]]'s `run()` method.
	 */
	public function get ($uri, $data = array ()) {
		try {
			return self::$c->run ($uri, $data);
		} catch (Exception $e) {
			return $e->getMessage ();
		}
	}

	/**
	 * Initializes the `$i18n`, `$memcache`, `$page`, and `$tpl` objects
	 * for use with the controller in testing handlers.
	 */
	public static function setUpBeforeClass () {
		require_once ('lib/Functions.php');
		require_once ('lib/DB.php');
		error_reporting (E_ALL & ~E_NOTICE);
		define ('ELEFANT_ENV', 'config');
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
		$_SERVER['REQUEST_URI'] = '/';

		global $conf, $i18n, $memcache, $page, $tpl;

		// Set up the database connection to be in memory
		$conf = parse_ini_file ('conf/config.php', true);
		$conf['Database'] = array (
			'master' => array (
				'driver' => 'sqlite',
				'file' => ':memory:'
			)
		);

		// Initializes PDO connection automatically
		foreach (sql_split (file_get_contents ('conf/install_sqlite.sql')) as $sql) {
			if (! DB::execute ($sql)) {
				die ('SQL failed: ' . $sql);
			}
		}

		// Create default admin and member users
		$date = gmdate ('Y-m-d H:i:s');
		DB::execute (
			"insert into `user` (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, ?, ?, null, ?, 'Admin User', 'admin', ?, ?, ?)",
			'admin@test.com',
			User::encrypt_pass ('testing'),
			$date, $date, $date,
			json_encode (array ())
		);
		DB::execute (
			"insert into `user` (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (2, ?, ?, null, ?, 'Joe Member', 'member', ?, ?, ?)",
			'member@test.com',
			User::encrypt_pass ('testing'),
			$date, $date, $date,
			json_encode (array ())
		);

		$i18n = new I18n ('lang', array ('negotiation_method' => 'http'));
		$page = new Page;
		self::$c = new Controller ();
		$tpl = new Template ('utf-8', self::$c);
		$memcache = Cache::init (array ());
	}

	/**
	 * Unset the `$i18n`, `$memcache`, `$page`, and `$tpl` objects upon
	 * completion.
	 */
	public static function tearDownAfterClass () {
		error_reporting (E_ALL);
		global $i18n, $memcache, $page, $tpl;
		unset ($i18n);
		unset ($memcache);
		unset ($page);
		unset ($tpl);

		if (isset (DB::$connections) && isset (DB::$connections['master'])) {
			unset (DB::$connections['master']);
		}

		if (isset (User::$user)) {
			User::$user = false;
		}
	}
}

?>