<?php

require_once ('lib/Database.php');
require_once ('apps/admin/lib/Lock.php');

$GLOBALS['db_list'] = array ();

class LockTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('db_list', 'db_err', 'db_sql', 'db_args', 'user');
	protected static $lock;

	static function setUpBeforeClass () {
		db_open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table `lock` (
			id integer primary key,
			user int not null,
			resource varchar(72) not null,
			resource_id varchar(72) not null,
			expires datetime not null,
			created datetime not null,
			modified datetime not null
		)');

		$GLOBALS['user'] = (object) array ('id' => 1);

		self::$lock = new Lock ('test', 'one');
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['db_list']);
		unset ($GLOBALS['user']);
	}

	function test_add () {
		// Add the lock and return id=1
		$this->assertEquals (self::$lock->add (), 1);
	}

	function test_info () {
		// Check the lock info
		$info = db_single ('select * from lock');
		$this->assertEquals (self::$lock->info (), $info);
		$this->assertEquals ($info->user, 1);
	}

	function test_exists () {
		// Shouldn't find our lock
		$this->assertEquals (self::$lock->exists (), false);

		// Change users, should find the lock now
		$GLOBALS['user']->id = 2;
		$this->assertEquals (self::$lock->exists (), 1);
	}

	function test_update () {
		// Get the lock info
		$info = db_single ('select * from lock');

		// Back to original user id
		$GLOBALS['user']->id = 1;

		// Update the lock after one second delay
		sleep (1);
		$this->assertEquals (self::$lock->update (), true);
		$this->assertNotEquals (self::$lock->info (), $info);
	}

	function test_remove () {
		// Remove the lock
		$this->assertEquals (self::$lock->remove (), true);
		$this->assertFalse (self::$lock->info ());
	}
}

?>