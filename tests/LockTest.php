<?php

require_once ('lib/Database.php');
require_once ('apps/admin/lib/Lock.php');

class LockTest extends PHPUnit_Framework_TestCase {
	function test_lock () {
		db_open (array ('driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table lock (
			id integer primary key,
			user int not null,
			resource varchar(72) not null,
			resource_id varchar(72) not null,
			expires datetime not null,
			created datetime not null,
			modified datetime not null
		)');

		$GLOBALS['user'] = (object) array ('id' => 1);

		// Add the lock and return id=1
		$lock = new Lock ('test', 'one');
		$this->assertEquals ($lock->add (), 1);

		// Check the lock info
		$info = db_single ('select * from lock');
		$this->assertEquals ($lock->info (), $info);

		// Shouldn't find our lock
		$this->assertEquals ($lock->exists (), false);

		// Change users, should find the lock now
		$GLOBALS['user']->id = 2;
		$this->assertEquals ($lock->exists (), 1);
		
		// Back to original user id
		$GLOBALS['user']->id = 1;

		// Update the lock after one second delay
		sleep (1);
		$this->assertEquals ($lock->update (), true);
		$this->assertNotEquals ($lock->info (), $info);

		// Remove the lock
		$this->assertEquals ($lock->remove (), true);
		$this->assertFalse ($lock->info ());
	}
}

?>