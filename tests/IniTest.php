<?php

use PHPUnit\Framework\TestCase;

class IniTest extends TestCase {
	function test_parse () {
		$str = "one = two\nthree = four";

		$this->assertEquals (
			array ('one' => 'two', 'three' => 'four'),
			Ini::parse ($str)
		);

		$str = "[Section]\none = two\nthree = four";

		$this->assertEquals (
			array ('Section' => array ('one' => 'two', 'three' => 'four')),
			Ini::parse ($str, true)
		);
	}

	function test_write () {
		// write simple structure, with 24 char padding
		$data = array ('one' => 'two', 'three' => true);
		$ini = "; <?php /*\n\none                     = two\nthree                   = On\n\n; */ ?>";
		$this->assertEquals ($ini, Ini::write ($data));

		// write with sections
		$data = array ('Section' => array ('one' => 'http://www.foo.com/', 'two' => false));
		$ini = "; <?php /*\n\n[Section]\n\none                     = \"http://www.foo.com/\"\ntwo                     = Off\n\n; */ ?>";
		$this->assertEquals ($ini, Ini::write ($data));
	}

	function test_write_with_header () {
		$data = array ('one' => 'two', 'three' => 'four');
		$ini = "; <?php /*\n;\n; Header test.\n;\n\none                     = two\nthree                   = four\n\n; */ ?>";
		$this->assertEquals ($ini, Ini::write ($data, false, 'Header test.'));
	}

	function test_write_with_arrays () {
		$data = array ('Section' => array ('one' => array ('one', 'two')));
		$ini = "; <?php /*\n\n[Section]\n\none[]                   = one\none[]                   = two\n\n; */ ?>";
		$this->assertEquals ($ini, Ini::write ($data));
	}

	function test_write_with_assoc () {
		$data = array ('Section' => array ('one' => array ('a' => 'one', 'b' => 'two')));
		$ini = "; <?php /*\n\n[Section]\n\none[a]                  = one\none[b]                  = two\n\n; */ ?>";
		$this->assertEquals ($ini, Ini::write ($data));
	}
}
