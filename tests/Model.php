<?php

require_once ('lib/Database.php');
require_once ('lib/Model.php');

class Qwerty extends Model {
	var $key = 'foo';
}

class ModelTest extends PHPUnit_Framework_TestCase {
	function test_model () {
		db_open (array ('driver' => 'sqlite', 'file' => ':memory:'));
		db_execute ('create table qwerty ( foo char(12), bar char(12) )');

		$q = new Qwerty ();

		$q->foo = 'asdf';
		$q->bar = 'qwerty';
		$this->assertTrue ($q->is_new);
		$this->assertEquals ($q->foo, 'asdf');
		$this->assertTrue ($q->put ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 1);
		$this->assertFalse ($q->is_new);

		$q->bar = 'foobar';
		$this->assertTrue ($q->put ());
		$this->assertEquals (db_shift ('select bar from qwerty where foo = ?', 'asdf'), 'foobar');

		$n = $q->get ('asdf');
		$this->assertEquals ($n, $q);
		$this->assertEquals ($n->bar, 'foobar');

		$res = $q->query ()->fetch_assoc ('foo', 'bar');
		$this->assertEquals ($res, array ('asdf' => 'foobar'));

		$res = $q->query ()->fetch_field ('bar');
		$this->assertEquals ($res, array ('foobar'));

		// should be the same after a query() reset since they're both
		// Qwerty objects with the same database row
		$res = array_shift ($q->query ()->where ('foo', 'asdf')->order ('foo asc')->fetch ());
		$this->assertEquals ($res, $q->query ());

		$this->assertTrue ($res->remove ());
		$this->assertEquals (db_shift ('select count() from qwerty'), 0);
	}
}

?>