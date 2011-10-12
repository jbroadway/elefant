<?php

require_once ('lib/Functions.php');
require_once ('lib/MongoManager.php');
require_once ('lib/MongoModel.php');
require_once ('lib/Form.php');

class Test extends MongoModel {
	var $name = 'foo';
}

class MongoModelTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('conf');

	protected static $id = null;

	static function setUpBeforeClass () {
		$GLOBALS['conf']['Mongo'] = array (
			'host' => 'localhost:27017',
			'name' => 'test'
		);
	}

	static function tearDownAfterClass () {
		$t = new Test ();
		foreach ($t->fetch () as $row) {
			$row->remove ();
		}
		unset ($GLOBALS['conf']);
	}

	function test_construct () {
		$t = new Test ();
		$this->assertInstanceOf ('MongoCollection', $t->collection);
		$this->assertTrue ($t->is_new);
		$this->assertEquals ('foo', $t->name);
	}

	function test_put () {
		$t = new Test (array ('foo' => 'bar'));
		$t->put ();
		$this->assertFalse ($t->error);
		$this->assertNotEmpty ($t->keyval);
		$this->assertEquals ($t->keyval, $t->data['_id']);
		$this->assertEquals ($t->data['foo'], 'bar');
		self::$id = $t->keyval;
	}

	function test_get () {
		$t = new Test (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);

		$t = Test::get (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);

		$t = Test::get ($t->keyval ());
		$this->assertEquals ('bar', $t->data['foo']);
	}

	function test_keyval () {
		$t = new Test (self::$id);
		$this->assertEquals (new MongoId ($t->keyval ()), $t->keyval);
	}

	function test_orig () {
		$t = new Test (self::$id);
		$o = $t->orig ();
		$this->assertNotEmpty ($o->_id);
		$this->assertEquals ('bar', $o->foo);
	}

	function test_remove () {
		$t = new Test (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);
		$this->assertTrue ($t->remove ());

		$t = Test::get (self::$id);
		$this->assertEquals ('No object by that ID.', $t->error);
	}

	function test_fetch () {
		$t = new Test (array ('foo' => 'bar'));
		$t->put ();
		$t = new Test (array ('foo' => 'asdf'));
		$t->put ();

		$res = Test::query ()
			->where ('foo', 'bar')
			->fetch ();

		$this->assertEquals (1, count ($res));
		$row = array_shift ($res);
		$this->assertEquals ('bar', $row->foo);
		$this->assertNotEmpty ($row->keyval ());
	}

	function test_fetch_orig () {
		$t = new Test (array ('foo' => 'qwerty'));
		$t->put ();
		$t = new Test (array ('foo' => '1234'));
		$t->put ();

		$res = Test::query ()
			->where ('foo', 'qwerty')
			->fetch_orig ();

		$this->assertEquals (1, count ($res));
		$row = array_shift ($res);
		$this->assertEquals ('qwerty', $row->foo);
		$this->assertNotEmpty ($row->_id);
	}

	function test_fetch_assoc () {
		$res = Test::query ()
			->order ('foo asc')
			->fetch_assoc ('_id', 'foo');

		$this->assertEquals (4, count ($res));
		$this->assertEquals ('1234', array_shift ($res));
		$this->assertEquals ('asdf', array_shift ($res));
		$this->assertEquals ('bar', array_shift ($res));
		$this->assertEquals ('qwerty', array_shift ($res));
	}

	function test_fetch_field () {
		$res = Test::query ()
			->order ('foo asc')
			->fetch_field ('foo');

		$this->assertEquals (4, count ($res));
		$this->assertEquals ('1234', array_shift ($res));
		$this->assertEquals ('asdf', array_shift ($res));
		$this->assertEquals ('bar', array_shift ($res));
		$this->assertEquals ('qwerty', array_shift ($res));
	}

	function test_count () {
		$res = Test::query ()
			->count ();

		$this->assertEquals (4, $res);
	}

	function test_single () {
		$res = Test::query ()
			->order ('foo desc')
			->single ();

		$this->assertEquals ('qwerty', $res->foo);
	}

	function test_group () {
		$res = Test::query ()
			->fetch (2);
		foreach ($res as $row) {
			$row->category = 'one';
			$row->put ();
		}

		$res = Test::query ()
			->fetch (2, 2);
		foreach ($res as $row) {
			$row->category = 'two';
			$row->put ();
		}

		$res = Test::query ()
			->group (
				array ('category' => 1),
				array ('items' => array ()),
				'function (obj, prev) { prev.items.push (obj.foo); }'
			);

		$this->assertEquals (4, $res['count']);
		$this->assertEquals (2, $res['keys']);
		$this->assertEquals (1, $res['ok']);

		$one = $res['retval'][0];
		$this->assertEquals (2, count ($one['items']));

		$two = $res['retval'][1];
		$this->assertEquals (2, count ($two['items']));
	}
}

?>