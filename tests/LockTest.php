<?php

require_once ('lib/Autoloader.php');

class LockTest extends PHPUnit_Framework_TestCase {
	protected static $lock;

	static function setUpBeforeClass () {
		Database::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table `lock` (
			id integer primary key,
			user int not null,
			resource varchar(72) not null,
			resource_id varchar(72) not null,
			expires datetime not null,
			created datetime not null,
			modified datetime not null
		)');

		User::$user = (object) array ('id' => 1);

		self::$lock = new Lock ('test', 'one');
	}

	static function tearDownAfterClass () {
		User::$user = false;
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
		User::val ('id', 2);
		$this->assertEquals (self::$lock->exists (), 1);
	}

	function test_update () {
		// Get the lock info
		$info = db_single ('select * from lock');

		// Back to original user id
		User::$user = (object) array ('id' => 1);

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