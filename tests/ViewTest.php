<?php

require_once ('lib/Autoloader.php');

class ViewTest extends PHPUnit_Framework_TestCase {
	function test_init () {
		$tpl = new Template ('UTF-8');
		View::init ($tpl);
		$this->assertEquals ($tpl, View::$tpl);
	}
	
	function test_set () {
		$tpl = new Template ('UTF-8');
		View::init ($tpl);
		
		View::set ('name', 'Joe');
		$this->assertEquals (array ('name' => 'Joe'), View::$params);
		
		$params = array (
			'name' => 'Jill',
			'age' => 28
		);
		View::set ($params);
		$this->assertEquals ($params, View::$params);
	}
	
	function test_render () {
		$params = array (
			'name' => 'Jill',
			'age' => 28
		);
		
		$this->assertEquals (
			'Jill, 28',
			View::render (function ($params) {
				return join (', ', $params);
			})
		);
		
		$this->assertEquals (array (), View::$params);
	}
}

?>