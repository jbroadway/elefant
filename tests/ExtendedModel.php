<?php

require_once ('lib/Autoloader.php');

class MyModel extends ExtendedModel {
	public $_extended_field = 'extra';
}

class ExtendedModelTest extends PHPUnit_Framework_TestCase {
	protected static $o;

	static function setUpBeforeClass () {
		Database::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table mymodel ( id integer primary key, name char(32), extra text )');
	}

	function test_create () {
		self::$o = new MyModel (array ('name' => 'Test'));
		self::$o->put ();
		$this->assertEquals (1, self::$o->id);
	}

	function test_set_and_get () {
		$extra = self::$o->extra;
		$this->assertEquals (array (), $extra);
		$extra['foo'] = 'bar';
		self::$o->extra = $extra;

		$extra = self::$o->extra;
		$this->assertEquals (array ('foo' => 'bar'), $extra);
	}

	function test_save () {
		self::$o->put ();

		self::$o = new MyModel (1);

		$extra = self::$o->extra;
		$this->assertEquals (array ('foo' => 'bar'), $extra);
	}
}

?>