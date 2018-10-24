<?php

use PHPUnit\Framework\TestCase;

class LockTest extends TestCase {
	protected static $lock;

	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		DB::execute ('drop table #prefix#lock');
		DB::execute ('create table `#prefix#lock` (
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
		DB::execute ('drop table #prefix#lock');
	}

	function test_construct () {
		$lock = new Lock ('foo', 'bar');
		$this->assertEquals ('foo', $lock->resource);
		$this->assertEquals ('bar', $lock->key);
	}

	function test_add () {
		// Add the lock and return id=1
		$this->assertEquals (self::$lock->add (), 1);
	}

	/**
	 * @depends test_add
	 */
	function test_info () {
		// Check the lock info
		$info = DB::single ('select * from #prefix#lock');
		$this->assertEquals (self::$lock->info (), $info);
		$this->assertEquals ($info->user, 1);
	}

	/**
	 * @depends test_info
	 */
	function test_exists () {
		// Shouldn't find our lock
		$this->assertEquals (self::$lock->exists (), false);

		// Change users, should find the lock now
		User::val ('id', 2);
		$this->assertEquals (self::$lock->exists (), 1);
	}

	/**
	 * @depends test_exists
	 */
	function test_update () {
		// Get the lock info
		$info = DB::single ('select * from #prefix#lock');

		// Back to original user id
		User::$user = (object) array ('id' => 1);

		// Update the lock after one second delay
		sleep (1);
		$this->assertEquals (self::$lock->update (), true);
		$this->assertNotEquals (self::$lock->info (), $info);
	}

	/**
	 * @depends test_update
	 */
	function test_remove () {
		// Remove the lock
		$this->assertEquals (self::$lock->remove (), true);
		$this->assertFalse (self::$lock->info ());
	}

	/**
	 * @depends test_remove
	 */
	function test_clear () {
		// Add a lock
		$this->assertEquals (self::$lock->add (), 1);
		$this->assertTrue (self::$lock->clear ());
		$this->assertEquals (0, DB::shift ('select count(*) from #prefix#lock'));
	}

	/**
	 * @depends test_clear
	 */
	function test_clear_all () {
		// Add a lock
		$this->assertEquals (self::$lock->add (), 1);
		$this->assertTrue (self::$lock->clear_all ());
		$this->assertEquals (0, DB::shift ('select count(*) from #prefix#lock'));
	}

	/**
	 * @depends test_clear_all
	 */
	function test_errors () {
		unset (DB::$connections['master']);
		$this->assertFalse (self::$lock->add ());
		$this->assertNotEquals (false, self::$lock->error);

		$this->assertFalse (self::$lock->update ());
		$this->assertNotEquals (false, self::$lock->error);
	}
}
