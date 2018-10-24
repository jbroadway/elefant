<?php

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase {
	function test_compare () {
		$diff = new Diff (DIFF_SPACE);

		$one = 'a b c d e f g';
		$two = 'a c d e t t f';

		$added = array ();
		$added[4] = 't';
		$added[5] = 't';

		$removed = array ();
		$removed[1] = 'b';
		$removed[6] = 'g';

		$intersect = array ();
		$intersect[0] = 'a';
		$intersect[2] = 'c';
		$intersect[3] = 'd';
		$intersect[4] = 'e';
		$intersect[5] = 'f';

		$res = $diff->compare ($one, $two);
		$this->assertEquals ($res[0], $added);
		$this->assertEquals ($res[1], $removed);
		$this->assertEquals ($res[2], $intersect);
	}

	function test_format () {
		$diff = new Diff (DIFF_SPACE);

		$one = 'a b c d e f g';
		$two = 'a c d e t t f';

		$res = $diff->compare ($one, $two);
		$out = $diff->format ($res);

		$expected = array (
			array ('', 'a'),
			array ('-', 'b'),
			array ('', 'c'),
			array ('', 'd'),
			array ('', 'e'),
			array ('+', 't'),
			array ('+', 't'),
			array ('', 'f'),
			array ('-', 'g')
		);

		$this->assertEquals ($out, $expected);
	}
}
