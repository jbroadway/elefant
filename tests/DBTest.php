<?php

require_once ('lib/Autoloader.php');

class DBTest extends PHPUnit_Framework_TestCase {
	function setUp () {
		$this->bad_conf = array (
			'master' => true,
			'driver' => 'fake_driver',
			'host' => '127.0.0.1',
			'name' => 'fake_db',
			'user' => 'fake',
			'pass' => 'fake'
		);
		$this->conf = array (
			'master' => true,
			'driver' => 'sqlite',
			'file' => ':memory:'
		);
		$this->conf2 = array (
			'driver' => 'sqlite',
			'file' => ':memory:'
		);
	}

	function test_open () {
		// test a bad connection
		$this->assertFalse (DB::open ($this->bad_conf));
		$this->assertEquals ('could not find driver', DB::error ());
		$this->assertEquals (0, DB::count ());

		// test a master connection
		$this->assertTrue (DB::open ($this->conf));
		$this->assertEquals (1, DB::count ());

		// test a second connection
		$this->assertTrue (DB::open ($this->conf2));
		$this->assertEquals (2, DB::count ());
		unset (DB::$connections['slave_1']);
	}

	function test_args () {
		$cmp = array ('one', 'two');
		$obj = (object) $cmp;
		$this->assertEquals (DB::args (array ('one', 'two')), $cmp);
		$this->assertEquals (DB::args (array (array ('one', 'two'))), $cmp);
		$this->assertEquals (DB::args (array ($obj)), $cmp);
	}

	function test_execute () {
		$this->assertTrue (DB::execute ('create table test ( foo int )'));
		$this->assertTrue (DB::execute ('insert into test (foo) values (?)', 'asdf'));
	}

	function test_last_sql () {
		$this->assertEquals (DB::last_sql (), 'insert into test (foo) values (?)');
	}

	function test_last_args () {
		$this->assertEquals (DB::last_args (), array ('asdf'));
	}

	function test_fetch () {
		$res = DB::fetch ('select * from test');
		$this->assertEquals (count ($res), 1);
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$this->assertEquals ($res[0], $cmp);
	}

	function test_single () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$res = DB::single ('select * from test');
		$this->assertEquals ($res, $cmp);
	}

	function test_shift () {
		$res = DB::shift ('select foo from test');
		$this->assertEquals ($res, 'asdf');
	}

	function test_shift_array () {		
		$res = DB::shift_array ('select * from test');
		$this->assertEquals ($res, array ('asdf'));
	}

	function test_pairs () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		DB::execute ('create table test2 (foo char(12), bar char(12))');
		DB::execute ('insert into test2 (foo, bar) values (?, ?)', 'one', 'joe');
		DB::execute ('insert into test2 (foo, bar) values (?, ?)', 'two', 'sam');
		$res = DB::pairs ('select * from test2 order by foo asc');
		$cmp = array ('one' => 'joe', 'two' => 'sam');
		$this->assertEquals ($res, $cmp);
	}
}

?>