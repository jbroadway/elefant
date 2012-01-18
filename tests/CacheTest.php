<?php

require_once ('lib/Cache.php');

class CacheTest extends PHPUnit_Framework_TestCase {
	function test_cache () {
		$c = new Cache ();

		// test set, increment, decrement, get, and delete
		$this->assertTrue ($c->set ('foo', 0));
		$this->assertEquals ($c->increment ('foo'), 1);
		$this->assertEquals ($c->increment ('foo'), 2);
		$this->assertEquals ($c->decrement ('foo'), 1);
		$this->assertEquals ($c->get ('foo'), 1);
		$this->assertTrue ($c->delete ('foo'));

		// test set/get on structures
		$c->set ('foo', array ('one' => 'two'));
		$c->set ('bar', array ('one', 'two'));
		$c->set ('asdf', (object) array ('one' => 'two'));
		$this->assertEquals (array ('one' => 'two'), $c->get ('foo'));
		$this->assertEquals (array ('one', 'two'), $c->get ('bar'));
		$this->assertEquals ((object) array ('one' => 'two'), $c->get ('asdf'));

		// test cache expiry
		$c->set ('bar', 'asdf', 0, 1);
		$this->assertEquals ('asdf', $c->get ('bar'));
		sleep (2);
		$this->assertFalse ($c->get ('bar'));

		// test flush
		$this->assertTrue ($c->flush ());
	}
}

?>