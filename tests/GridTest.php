<?php

use admin\Grid;

class GridTest extends PHPUnit_Framework_TestCase {
	function test_new_grid () {
		$grid = new Grid (array ());
		$this->assertEquals (0, $grid->key ());
		$this->assertEquals (null, $grid->current ());
		
		$grid->add_row ('33,33,33', '', false, '', array ('A', 'B', 'C'));
		$this->assertEquals ('33,33,33', $grid->current ()->units);
		$this->assertEquals (3, count ($grid->current ()->cols));
		$this->assertEquals ('B', $grid->current ()->cols[1]);

		$this->assertEquals ('B', $grid->collapse (0, 1));
		$this->assertEquals ('50,50', $grid->current ()->units);
		$this->assertEquals ('C', $grid->current ()->cols[1]);
		
		$grid->add_row ('50,50', '', false, '', array ('A', 'B'));
		foreach ($grid as $row) {
			$this->assertEquals ('50,50', $row->units);
			$this->assertEquals (2, count ($row->cols));
			$this->assertEquals ('A', $row->cols[0]);
		}
		
		$all = $grid->all ();
		foreach ($all as $row) {
			$this->assertEquals ('50,50', $row->units);
			$this->assertEquals (2, count ($row->cols));
			$this->assertEquals ('A', $row->cols[0]);
		}
	}
}
