<?php

if (extension_loaded ('mongo')) {
	class MTest extends MongoModel {
		var $name = 'foo';
	}
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

	protected function setUp () {
		if (! extension_loaded ('mongo')) {
			$this->markTestSkipped ('The Mongo extension is not available');
		}
	}

	static function tearDownAfterClass () {
		if (extension_loaded ('mongo')) {
			$t = new MTest ();
			foreach ($t->fetch () as $row) {
				$row->remove ();
			}
			unset ($GLOBALS['conf']);
		}
	}

	function test_construct () {
		$t = new MTest ();
		$this->assertFalse ($t->error);
		$this->assertInstanceOf ('MongoCollection', $t->collection);
		$this->assertTrue ($t->is_new);
		$this->assertEquals ('foo', $t->name);
	}

	/**
	 * @depends test_construct
	 */
	function test_put () {
		$t = new MTest (array ('foo' => 'bar'));
		$t->put ();
		$this->assertFalse ($t->error);
		$this->assertNotEmpty ($t->keyval);
		$this->assertEquals ($t->keyval, $t->data['_id']);
		$this->assertEquals ($t->data['foo'], 'bar');
		self::$id = $t->keyval;
	}

	/**
	 * @depends test_put
	 */
	function test_get () {
		$t = new MTest (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);

		$t = MTest::get (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);

		$t = MTest::get ($t->keyval ());
		$this->assertEquals ('bar', $t->data['foo']);
	}

	/**
	 * @depends test_get
	 */
	function test_keyval () {
		$t = new MTest (self::$id);
		$this->assertEquals (new MongoId ($t->keyval ()), $t->keyval);
	}

	/**
	 * @depends test_keyval
	 */
	function test_orig () {
		$t = new MTest (self::$id);
		$o = $t->orig ();
		$this->assertNotEmpty ($o->_id);
		$this->assertEquals ('bar', $o->foo);
	}

	/**
	 * @depends test_orig
	 */
	function test_remove () {
		$t = new MTest (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);
		$this->assertTrue ($t->remove ());

		$t = MTest::get (self::$id);
		$this->assertEquals ('No object by that ID.', $t->error);
	}

	/**
	 * @depends test_remove
	 */
	function test_fetch () {
		$t = new MTest (array ('foo' => 'bar'));
		$t->put ();

		$t = new MTest (array ('foo' => 'asdf'));
		$t->put ();

		$res = MTest::query ()
			->where ('foo', 'bar')
			->fetch ();

		$this->assertEquals (1, count ($res));
		$row = array_shift ($res);
		$this->assertEquals ('bar', $row->foo);
		$this->assertNotEmpty ($row->keyval ());
	}

	/**
	 * @depends test_fetch
	 */
	function test_fetch_orig () {
		$t = new MTest (array ('foo' => 'qwerty'));
		$t->put ();

		$t = new MTest (array ('foo' => '1234'));
		$t->put ();

		$res = MTest::query ()
			->where ('foo', 'qwerty')
			->fetch_orig ();

		$this->assertEquals (1, count ($res));
		$row = array_shift ($res);
		$this->assertEquals ('qwerty', $row->foo);
		$this->assertNotEmpty ($row->_id);
	}

	/**
	 * @depends test_fetch_orig
	 */
	function test_fetch_assoc () {
		$res = MTest::query ()
			->order ('foo asc')
			->fetch_assoc ('_id', 'foo');

		$this->assertEquals (4, count ($res));
		$this->assertEquals ('1234', array_shift ($res));
		$this->assertEquals ('asdf', array_shift ($res));
		$this->assertEquals ('bar', array_shift ($res));
		$this->assertEquals ('qwerty', array_shift ($res));
	}

	/**
	 * @depends test_fetch_assoc
	 */
	function test_fetch_field () {
		$res = MTest::query ()
			->order ('foo asc')
			->fetch_field ('foo');

		$this->assertEquals (4, count ($res));
		$this->assertEquals ('1234', array_shift ($res));
		$this->assertEquals ('asdf', array_shift ($res));
		$this->assertEquals ('bar', array_shift ($res));
		$this->assertEquals ('qwerty', array_shift ($res));
	}

	/**
	 * @depends test_fetch_field
	 */
	function test_count () {
		$res = MTest::query ()
			->count ();

		$this->assertEquals (4, $res);
	}

	/**
	 * @depends test_count
	 */
	function test_single () {
		$res = MTest::query ()
			->order ('foo desc')
			->single ();

		$this->assertEquals ('qwerty', $res->foo);
	}

	/**
	 * @depends test_single
	 */
	function test_group () {
		$res = MTest::query ()
			->fetch (2);
		foreach ($res as $row) {
			$row->category = 'one';
			$row->put ();
		}

		$res = MTest::query ()
			->fetch (2, 2);
		foreach ($res as $row) {
			$row->category = 'two';
			$row->put ();
		}

		$res = MTest::query ()
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