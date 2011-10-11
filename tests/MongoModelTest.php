<?php

require_once ('lib/Functions.php');
require_once ('lib/MongoManager.php');
require_once ('lib/MongoModel.php');
require_once ('lib/Form.php');

class Test extends MongoModel {
	var $name = 'foo';
}

class MongoModelTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('mongo_connection', 'conf');

	protected static $id = null;

	static function setUpBeforeClass () {
		$GLOBALS['conf']['Mongo'] = array (
			'host' => 'localhost:27017',
			'name' => 'test'
		);
	}

	static function tearDownAfterClass () {
		unset ($GLOBALS['mongo_connection']);
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

	function test_remove () {
		$t = new Test (self::$id);
		$this->assertEquals ('bar', $t->data['foo']);
		$this->assertTrue ($t->remove ());

		$t = Test::get (self::$id);
		$this->assertEquals ('No object by that ID.', $t->error);
	}
}

?>