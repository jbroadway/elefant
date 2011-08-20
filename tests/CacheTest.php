<?php

require_once ('lib/Cache.php');

class CacheTest extends PHPUnit_Framework_TestCase {
	function test_cache () {
		$c = new Cache ();
		$this->assertEquals (count ($c->memory), 0);
		$this->assertTrue ($c->set ('foo', 0));
		$this->assertEquals ($c->increment ('foo'), 1);
		$this->assertEquals ($c->increment ('foo'), 2);
		$this->assertEquals ($c->decrement ('foo'), 1);
		$this->assertEquals ($c->get ('foo'), 1);
		$this->assertTrue ($c->delete ('foo'));
		$this->assertEquals (count ($c->memory), 0);
	}
}

?>