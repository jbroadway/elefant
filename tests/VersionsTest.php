<?php

require_once ('lib/Autoloader.php');

class Foobar extends Model {}

class VersionsTest extends PHPUnit_Framework_TestCase {
	protected static $foo;
	protected static $foo2;
	protected static $v;

	static function setUpBeforeClass () {
		Database::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table foobar (id int not null, name char(32) not null)');
		if (! db_execute ('create table versions (
			id integer primary key,
			class char(72) not null,
			pkey char(72) not null,
			user int not null,
			ts datetime not null,
			serialized text not null
		)')) {
			die ('Failed to create versions table');
		}
		if (! db_execute ('create index versions_class on versions (class, pkey, ts)')) {
			die ('Failed to create versions_class index');
		}
		if (! db_execute ('create index versions_user on versions (user, ts)')) {
			die ('Failed to create versions_user index');
		}
		User::$user = false;
	}

	static function tearDownAfterClass () {
		User::$user = false;
	}

	function test_add () {
		self::$foo = new Foobar (array ('id' => 1, 'name' => 'Test'));
		self::$foo->put ();

		self::$v = Versions::add (self::$foo);
		$this->assertEquals (db_shift ('select count(*) from versions'), 1);
		$this->assertEquals (self::$v->class, 'Foobar');
		$this->assertEquals (self::$v->pkey, 1);
		$this->assertEquals (self::$v->user, 0);
	}

	/**
	 * @depends test_add
	 */
	function test_restore () {
		
		self::$foo2 = self::$v->restore ();
		$this->assertEquals (self::$foo, self::$foo2);
	}

	/**
	 * @depends test_restore
	 */
	function test_diff () {
		// test diff
		self::$foo->name = 'Test2';
		self::$foo->put ();

		$v = Versions::add (self::$foo);
		$this->assertEquals (db_shift ('select count(*) from versions'), 2);

		$modified = Versions::diff (self::$foo2, self::$foo);
		$this->assertEquals ($modified[0], 'name');
	}

	/**
	 * @depends test_diff
	 */
	function test_history () {
		// test history
		$history = Versions::history (self::$foo);
		$this->assertEquals (count ($history), 2);

		$modified = Versions::diff ($history[0], $history[1]);
		$this->assertEquals ($modified[0], 'name');
	}

	/**
	 * @depends test_history
	 */
	function test_recent () {
		// test recent
		$recent = Versions::recent ();
		$this->assertEquals (count ($recent), 1);

		$restored = self::$v->restore ($recent[0]);
		$this->assertEquals ($restored->name, 'Test2');
	}
}

?>