<?php

require_once ('lib/Database.php');
require_once ('lib/Model.php');

class Qwerty extends Model {
	var $key = 'foo';
}

class Foo extends Model {}
class Bar extends Model {
	var $fields = array (
		'foo' => array ('ref' => 'Foo')
	);
}

class ModelTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('db', 'db_err', 'db_sql', 'db_args');
	protected static $q;

	static function setUpBeforeClass () {
		db_open (array ('driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table qwerty ( foo char(12), bar char(12) )');

		self::$q = new Qwerty ();
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['db']);
	}

	function test_construct () {

		self::$q->foo = 'asdf';
		self::$q->bar = 'qwerty';
		$this->assertTrue (self::$q->is_new);
		$this->assertEquals (self::$q->foo, 'asdf');
		$this->assertTrue (self::$q->put ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 1);
		$this->assertFalse (self::$q->is_new);
	}

	function test_orig () {
		// orig()
		$orig = new StdClass;
		$orig->foo = 'asdf';
		$orig->bar = 'qwerty';
		$this->assertEquals (self::$q->orig (), $orig);
	}

	function test_fetch_orig () {
		// fetch_orig()
		$orig = new StdClass;
		$orig->foo = 'asdf';
		$orig->bar = 'qwerty';
		$res = array_shift (self::$q->query ()->fetch_orig ());
		$this->assertEquals ($res, $orig);
	}

	function test_count () {
		// count()
		$this->assertEquals (self::$q->query ()->count (), 1);
	}

	function test_single () {
		// single()
		$single = Qwerty::query ()->single ();
		$this->assertEquals ($single->foo, 'asdf');

		// test requesting certain fields
		$single = Qwerty::query ('bar')->single ();
		$this->assertEquals ($single->foo, null);
		$this->assertEquals ($single->bar, 'qwerty');
	}

	function test_put () {
		// put()
		self::$q->bar = 'foobar';
		$this->assertTrue (self::$q->put ());
		$this->assertEquals (db_shift ('select bar from qwerty where foo = ?', 'asdf'), 'foobar');
	}

	function test_get () {
		// get()
		$n = self::$q->get ('asdf');
		$this->assertEquals ($n, self::$q);
		$this->assertEquals ($n->bar, 'foobar');
	}

	function test_fetch_assoc () {
		// fetch_assoc()
		$res = self::$q->query ()->fetch_assoc ('foo', 'bar');
		$this->assertEquals ($res, array ('asdf' => 'foobar'));
	}

	function test_fetch_field () {
		// fetch_field()
		$res = self::$q->query ()->fetch_field ('bar');
		$this->assertEquals ($res, array ('foobar'));
	}

	function test_remove () {
		// should be the same since they're both
		// Qwerty objects with the same database row
		$res = array_shift (self::$q->query ()->where ('foo', 'asdf')->order ('foo asc')->fetch ());
		$this->assertEquals ($res, self::$q);

		// remove()
		$this->assertTrue ($res->remove ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 0);
	}

	function test_references () {
		// references
		db_execute ('create table foo(id int, name char(12))');
		db_execute ('create table bar(id int, name char(12), foo int)');
		
		$f = new Foo (array ('id' => 1, 'name' => 'Joe'));
		$f->put ();
		$b = new Bar (array ('id' => 1, 'name' => 'Jim', 'foo' => 1));
		$b->put ();

		$this->assertEquals ($b->name, 'Jim');
		$this->assertEquals ($b->foo, 1);
		$this->assertEquals ($b->foo ()->name, 'Joe');
		$this->assertEquals ($b->foo ()->name, 'Joe');
		
		// fake reference should fail
		try {
			$this->assertTrue ($b->fake ());
		} catch (Exception $e) {
			$this->assertRegExp (
				'/Call to undefined method Bar::fake in .+tests\/ModelTest\.php on line [0-9]+/',
				$e->getMessage ()
			);
		}
	}
}

?>