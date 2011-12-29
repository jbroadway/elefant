<?php

require_once ('lib/Cache.php');

class CacheTest extends PHPUnit_Framework_TestCase {
	function test_cache () {
		$c = new Cache ();
		$this->assertTrue ($c->set ('foo', 0));
		$this->assertEquals ($c->increment ('foo'), 1);
		$this->assertEquals ($c->increment ('foo'), 2);
		$this->assertEquals ($c->decrement ('foo'), 1);
		$this->assertEquals ($c->get ('foo'), 1);
		$this->assertTrue ($c->delete ('foo'));

		$c->set ('foo', array ('one' => 'two'));
		$c->set ('bar', array ('one', 'two'));
		$c->set ('asdf', (object) array ('one' => 'two'));
		$this->assertEquals (array ('one' => 'two'), $c->get ('foo'));
		$this->assertEquals (array ('one', 'two'), $c->get ('bar'));
		$this->assertEquals ((object) array ('one' => 'two'), $c->get ('asdf'));

		$this->assertTrue ($c->flush ());
	}
}

?>