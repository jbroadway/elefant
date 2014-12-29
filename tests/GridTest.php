<?php

use admin\Grid;
use I18n;

class GridTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array ('i18n');

	function setUp () {
		global $i18n;
		$i18n = new I18n ('lang', array ('negotiation_method' => 'http'));
		date_default_timezone_set ('GMT');
	}
		
	function test_new_grid () {
		$grid = new Grid (array ());
		$this->assertEquals (0, $grid->key ());
		$this->assertEquals (null, $grid->current ());
		
		$grid->add_row ('33,33,33', '', false, '', false, false, '', array ('A', 'B', 'C'));
		$this->assertEquals ('33,33,33', $grid->current ()->units);
		$this->assertEquals (3, count ($grid->current ()->cols));
		$this->assertEquals ('B', $grid->current ()->cols[1]);

		$this->assertEquals ('B', $grid->collapse (0, 1));
		$this->assertEquals ('50,50', $grid->current ()->units);
		$this->assertEquals ('C', $grid->current ()->cols[1]);
		
		$grid->add_row ('50,50', '', false, '', false, false, '', array ('A', 'B'));
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
		
		$this->assertEquals ('A', $grid->content (0, 0));
		$this->assertEquals ('C', $grid->content (0, 1));

		$grid->update (0, 1, 'BB');
		$this->assertEquals ('BB', $grid->content (0, 1));

		$this->assertEquals ('', $grid->property (1, 'css_class'));
		$this->assertEquals ('gray', $grid->property (1, 'css_class', 'gray'));
		$this->assertEquals ('gray', $grid->property (1, 'css_class'));
	}
}
