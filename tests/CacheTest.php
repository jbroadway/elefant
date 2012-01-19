<?php

require_once ('lib/Cache.php');

class CacheTest extends PHPUnit_Framework_TestCase {
	function test_set_get () {
		$c = new Cache ();

		// test set, increment, decrement, get, and delete
		$this->assertTrue ($c->set ('foo', 'Some value'));
		$this->assertEquals ($c->get ('foo'), 'Some value');
	}

	function test_delete () {
		$c = new Cache ();

		$this->assertEquals ($c->get ('foo'), 'Some value');
		$this->assertTrue ($c->delete ('foo'));
		$this->assertFalse ($c->get ('foo'));
	}

	function test_incr_decr () {
		$c = new Cache ();

		$this->assertTrue ($c->set ('foo', 0));
		$this->assertEquals ($c->increment ('foo'), 1);
		$this->assertEquals ($c->increment ('foo'), 2);
		$this->assertEquals ($c->increment ('foo', 2), 4);
		$this->assertEquals ($c->decrement ('foo'), 3);
		$this->assertEquals ($c->get ('foo'), 3);
	}

	function test_structures () {
		$c = new Cache ();

		// test set/get on structures
		$c->set ('foo', array ('one' => 'two'));
		$c->set ('bar', array ('one', 'two'));
		$c->set ('asdf', (object) array ('one' => 'two'));
		$this->assertEquals (array ('one' => 'two'), $c->get ('foo'));
		$this->assertEquals (array ('one', 'two'), $c->get ('bar'));
		$this->assertEquals ((object) array ('one' => 'two'), $c->get ('asdf'));
	}

	function test_timeout () {
		$c = new Cache ();

		// test cache expiry
		$c->set ('bar', 'asdf', 0, 1);
		$this->assertEquals ('asdf', $c->get ('bar'));
		sleep (2);
		$this->assertFalse ($c->get ('bar'));
	}

	function test_flush () {
		$c = new Cache ();

		$c->set ('foo', 'test', 0, 10);
		$c->set ('bar', 'test2');

		$this->assertTrue ($c->flush ());

		// check that it also flushed dot-files for timeouts too
		$files = glob ($c->dir . '/{,.}*', GLOB_BRACE);
		$this->assertEquals (count ($files), 2);
	}
}

?>