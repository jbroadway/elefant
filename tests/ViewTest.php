<?php

use PHPUnit\Framework\TestCase;

class TemplateMock {
	public function render ($template, $data = array ()) {
		$data = is_object ($data) ? $data : (object) $data;
		return sprintf ('<p>Hello %s</p>', $data->name);
	}
}

class ViewTest extends TestCase {
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

		$tpl = new TemplateMock;
		View::init ($tpl);

		$this->assertEquals (
			'<p>Hello Jill</p>',
			View::render ('myapp/hello', $params)
		);
	}
}
