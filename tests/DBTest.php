<?php

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase {
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
		$this->assertEquals ($cmp, DB::args (array ('one', 'two')));
		$this->assertEquals ($cmp, DB::args (array (array ('one', 'two'))));
		$this->assertEquals ($cmp, DB::args (array ($obj)));
	}

	function test_execute () {
		$this->assertTrue (DB::execute ('create table test ( foo int )'));
		$this->assertTrue (DB::execute ('insert into test (foo) values (?)', 'asdf'));
	}

	function test_last_sql () {
		$this->assertEquals ('insert into test (foo) values (?)', DB::last_sql ());
	}

	function test_last_args () {
		$this->assertEquals (array ('asdf'), DB::last_args ());
	}

	function test_fetch () {
		$res = DB::fetch ('select * from test');
		$this->assertEquals (1, count ($res));
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$this->assertEquals ($cmp, $res[0]);
	}

	function test_single () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$res = DB::single ('select * from test');
		$this->assertEquals ($cmp, $res);
	}

	function test_shift () {
		$res = DB::shift ('select foo from test');
		$this->assertEquals ('asdf', $res);
	}

	function test_shift_array () {		
		$res = DB::shift_array ('select * from test');
		$this->assertEquals (array ('asdf'), $res);
	}

	function test_pairs () {
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		DB::execute ('create table test2 (foo char(12), bar char(12))');
		DB::execute ('insert into test2 (foo, bar) values (?, ?)', 'one', 'joe');
		DB::execute ('insert into test2 (foo, bar) values (?, ?)', 'two', 'sam');
		$res = DB::pairs ('select * from test2 order by foo asc');
		$cmp = array ('one' => 'joe', 'two' => 'sam');
		$this->assertEquals ($cmp, $res);
	}

	function test_transactions () {
		$this->assertEquals (true, DB::execute ('create table transaction_test (id integer, name char(32))'));
		$this->assertEquals (0, DB::shift ('select count(*) from transaction_test'));

		$this->assertEquals (true, DB::beginTransaction ());
		$this->assertEquals (true, DB::execute ('insert into transaction_test (name) values ("Joe")'));
		$this->assertEquals (true, DB::execute ('insert into transaction_test (name) values ("Ron")'));
		$this->assertEquals (true, DB::rollback ());

		$this->assertEquals (0, DB::shift ('select count(*) from transaction_test'));

		$this->assertEquals (true, DB::beginTransaction ());
		$this->assertEquals (true, DB::execute ('insert into transaction_test (name) values ("Joe")'));
		$this->assertEquals (true, DB::execute ('insert into transaction_test (name) values ("Ron")'));
		$this->assertEquals (true, DB::execute ('insert into transaction_test (name) values ("Sue")'));
		$this->assertEquals (true, DB::commit ());
		
		$this->assertEquals (3, DB::shift ('select count(*) from transaction_test'));
	}
}
