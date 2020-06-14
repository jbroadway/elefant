<?php

use PHPUnit\Framework\TestCase;

class MyModel extends ExtendedModel {
	public $_extended_field = 'extra';
	public $verify = array (
		'name' => array (
			'not empty' => 1
		),
		'foo' => array (
			'not empty' => 1,
			'extended' => 1
		)
	);
}

class MyModelWithDefault extends ExtendedModel {
	public $_extended_field = 'extra';
	public $verify = array (
		'name' => array (
			'not empty' => 1
		),
		'foo' => array (
			'not empty' => 1,
			'extended' => 1,
			'default' => 'default value'
		)
	);
}

class MyModelJsonPretty extends ExtendedModel {
	public $_extended_field = 'extra';
	public $_json_flags = JSON_PRETTY_PRINT;
	public $verify = array (
		'name' => array (
			'not empty' => 1
		),
		'foo' => array (
			'not empty' => 1,
			'extended' => 1
		)
	);
}

class ExtendedModelTest extends TestCase {
	protected static $o;

	static function setUpBeforeClass (): void {
		DB::open (array ('master' => true, 'driver' => 'sqlite', 'file' => ':memory:'));
		DB::execute ('create table mymodel ( id integer primary key, name char(32), extra text )');
		DB::execute ('create table mymodelwithdefault ( id integer primary key, name char(32), extra text )');
	}

	function test_create () {
		self::$o = new MyModel (array ('name' => 'Test'));

		// Verify there's a validation error on the extended property
		$this->assertFalse (self::$o->put ());
		$this->assertEquals (
			'Validation failed for extended fields: foo',
			self::$o->error
		);

		// Fix that and verify it works now
		self::$o->foo = 'foo';
		$this->assertTrue (self::$o->put ());
		$this->assertEquals (1, self::$o->id);
		
		// Test model with default foo value
		$o2 = new MyModelWithDefault (array ('name' => 'Test'));
		$this->assertEquals ('default value', $o2->ext ('foo'));
		$this->assertTrue ($o2->put ());
	}

	/**
	 * @depends test_create
	 */
	function test_set_and_get () {
		$extra = self::$o->extra;
		$this->assertEquals (array ('foo' => 'foo'), $extra);
		$extra['foo'] = 'bar';
		self::$o->extra = $extra;

		$extra = self::$o->extra;
		$this->assertEquals (array ('foo' => 'bar'), $extra);
	}

	/**
	 * @depends test_set_and_get
	 */
	function test_save () {
		// Save the object
		self::$o->put ();

		// Fetch it again by ID
		self::$o = new MyModel (1);

		// Verify its extra property was saved/fetched correctly
		$extra = self::$o->extra;
		$this->assertEquals (array ('foo' => 'bar'), $extra);
		$this->assertEquals (
			json_encode (array ('foo' => 'bar')),
			self::$o->data['extra']
		);
	}

	/**
	 * @depends test_save
	 */
	function test_extended () {
		self::$o = new MyModel (1);

		// Assert ext() returns full structure
		$this->assertEquals (array ('foo' => 'bar'), self::$o->ext ());

		// Get an individual extra property
		$foo = self::$o->ext ('foo');
		$this->assertEquals ('bar', $foo);

		// Set an individual extra property
		self::$o->ext ('foo', 'asdf');
		$foo = self::$o->ext ('foo');
		$this->assertEquals ('asdf', $foo);

		// Verify it updated behind the scenes
		$extra = array ('foo' => 'asdf');
		$this->assertEquals ($extra, self::$o->ext ());
		$this->assertEquals (json_encode ($extra), self::$o->data['extra']);
	}

	/**
	 * @depends test_extended
	 */
	function test_direct () {
		self::$o = new MyModel (1);

		// Set individual extra property directly
		self::$o->foo = 'qwerty';

		// Get individual extra property directly
		$this->assertEquals ('qwerty', self::$o->foo);

		// Verify it updated behind the scenes
		$extra = array ('foo' => 'qwerty');
		$this->assertEquals ($extra, self::$o->ext ());
		$this->assertEquals (json_encode ($extra), self::$o->data['extra']);
	}

	function test_json_flags () {
		$m = new MyModelJsonPretty ();
		
		$extra = [
			'foo' => 'qwerty',
			'bar' => 'asdf'
		];
		
		foreach ($extra as $k => $v) {
			$m->ext ($k, $v);
		}

		$this->assertEquals ($extra, $m->ext ());
		$this->assertEquals (json_encode ($extra, JSON_PRETTY_PRINT), $m->data['extra']);
	}
}
