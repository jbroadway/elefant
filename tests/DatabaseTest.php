<?php

require_once ('lib/Database.php');

class DatabaseTest extends PHPUnit_Framework_TestCase {
	function test_1_open () {
		$conf = array (
			'driver' => 'fake_driver',
			'host' => '127.0.0.1',
			'name' => 'fake_db',
			'user' => 'fake',
			'pass' => 'fake'
		);
		$this->assertFalse (db_open ($conf));
		$this->assertEquals ('could not find driver', db_error ());
		$conf = array (
			'driver' => 'sqlite',
			'file' => ':memory:'
		);
		$this->assertTrue (db_open ($conf));

		$cmp = array ('one', 'two');
		$obj = (object) $cmp;
		$this->assertEquals (db_args (array ('one', 'two')), $cmp);
		$this->assertEquals (db_args (array (array ('one', 'two'))), $cmp);
		$this->assertEquals (db_args (array ($obj)), $cmp);

		$this->assertTrue (db_execute ('create table test ( foo int )'));
		$this->assertTrue (db_execute ('insert into test (foo) values (?)', 'asdf'));
		$this->assertEquals (db_last_sql (), 'insert into test (foo) values (?)');
		$this->assertEquals (db_last_args (), array ('asdf'));

		$res = db_fetch_array ('select * from test');
		$this->assertEquals (count ($res), 1);
		$cmp = new StdClass;
		$cmp->foo = 'asdf';
		$this->assertEquals ($res[0], $cmp);
		
		$res = db_single ('select * from test');
		$this->assertEquals ($res, $cmp);
		
		$res = db_shift ('select foo from test');
		$this->assertEquals ($res, 'asdf');
		
		$res = db_shift_array ('select * from test');
		$this->assertEquals ($res, array ('asdf'));

		db_execute ('create table test2 (foo char(12), bar char(12))');
		db_execute ('insert into test2 (foo, bar) values (?, ?)', 'one', 'joe');
		db_execute ('insert into test2 (foo, bar) values (?, ?)', 'two', 'sam');
		$res = db_pairs ('select * from test2 order by foo asc');
		$cmp = array ('one' => 'joe', 'two' => 'sam');
		$this->assertEquals ($res, $cmp);
	}
}

?>