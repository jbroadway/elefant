<?php

require_once ('lib/Functions.php');

class FunctionsTest extends PHPUnit_Framework_TestCase {
	function test_simple_auth () {
		$verifier = function ($user, $pass) {
			return true;
		};
		$method = function ($callback) {
			return $callback ('', '');
		};
		$this->assertTrue (simple_auth ($verifier, $method));
	}

	function test_sql_split () {
		$sql = "select * from foo;\nselect * from bar";
		$split = sql_split ($sql);
		$this->assertEquals (2, count ($split));
		$this->assertEquals ("select * from foo\n", $split[0]);
		$this->assertEquals ("select * from bar\n", $split[1]);
	}
}

?>