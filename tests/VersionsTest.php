<?php

require_once ('lib/Autoloader.php');

class Foobar extends Model {}

class VersionsTest extends PHPUnit_Framework_TestCase {
	protected static $foo;
	protected static $foo2;
	protected static $v;

	static function setUpBeforeClass () {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		DB::execute ('create table foobar (id int not null, name char(32) not null)');
		if (! DB::execute ('create table versions (
			id integer primary key,
			class char(72) not null,
			pkey char(72) not null,
			user int not null,
			ts datetime not null,
			serialized text not null
		)')) {
			die ('Failed to create versions table');
		}
		if (! DB::execute ('create index versions_class on versions (class, pkey, ts)')) {
			die ('Failed to create versions_class index');
		}
		if (! DB::execute ('create index versions_user on versions (user, ts)')) {
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
		$this->assertEquals (DB::shift ('select count(*) from versions'), 1);
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
		$this->assertEquals (DB::shift ('select count(*) from versions'), 2);

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

		// get a count with class name (groups by pkey, so one result)
		$history = Versions::history ('Foobar', true);
		$this->assertEquals ($history, 1);

		// get a count with object (all for the item, so two results)
		$history = Versions::history (self::$foo, true);
		$this->assertEquals ($history, 2);

		// get history from class name (groups by pkey, so one result)
		$history = Versions::history ('Foobar');
		$this->assertEquals (count ($history), 1);
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

	/**
	 * @depends test_recent
	 */
	function test_get_classes () {
		$res = self::$v->get_classes ();
		$this->assertEquals (array ('Foobar'), $res);
	}

	/**
	 * @depends test_get_classes
	 */
	function test_restore_deleted () {
		// test restore on deleted item
		$foo = new Foobar (array ('id' => 5, 'name' => 'Deleted'));
		$v = Versions::add ($foo);
		$foo->remove ();
		$foo2 = $v->restore ();
		$this->assertEquals ($foo, $foo2);
	}

	/**
	 * @depends test_get_classes
	 */
	function test_recent_user () {
		// recent with user
		DB::execute ('update versions set user = 1');
		$recent = Versions::recent (1);
		$this->assertEquals (count ($recent), 2);
	}
}

?>