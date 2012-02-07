<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// split by newlines
define ('DIFF_LINE', "/(\r\n|\n)/s");

// split by empty space
define ('DIFF_SPACE', "/([\r\n\t ]+)/s");

/**
 * Diff provides a lightweight, simple and relatively fast means of
 * comparing the difference between two strings.  Diff bears no code-level
 * similarity to most of the popular diff algorithm implementations, because
 * it relies on three simple, built-in PHP functions to accomplish the tough
 * stuff, being preg_split(), array_diff(), and array_intersect().
 * 
 * Usage:
 * 
 *   $diff = new Diff (DIFF_SPACE);
 * 
 *   $original = 'a b c e h j l m n p';
 *   $new = 'b c d e f j k l m r s t';
 * 
 *   echo '<pre>';
 *   foreach ($diff->format ($diff->compare ($original, $new)) as $line) {
 *     if ($line[0]) {
 *       echo $line[0] . ' ' . htmlentities_compat ($line[1]) . "\n";
 *     } else {
 *       echo '  ' . htmlentities_compat ($line[1]) . "\n";
 *     }
 *   }
 *   echo '</pre>';
 */
class Diff {
	/**
	 * Contains the regular expression to use to split the
	 * original strings into arrays for comparison via the array_diff()
	 * and array_intersect() functions.  The two preset modes are
	 * defined in the constants DIFF_LINE and DIFF_SPACE, which split
	 * by newline character and by blank space, respectively.
	 */
	public $splitMode;

	/**
	 * Constructor method.
	 */
	public function __construct ($splitMode = DIFF_LINE) {
		$this->splitMode = $splitMode;
	}

	/**
	 * Compares two strings and returns a 2-D array of
	 * the strings added, removed, and that are shared between
	 * the two original strings.
	 */
	public function compare ($str1, $str2) {
		// returns 3 arrays: added, removed, and the intersect of str1 and str2
		$a = preg_split ($this->splitMode, $str1, -1);
		$b = preg_split ($this->splitMode, $str2, -1);
		$removed = array_diff ($a, $b);
		$added = array_diff ($b, $a);
		$intersect = array_intersect ($a, $b);
		return array ($added, $removed, $intersect);
	}

	/**
	 * Accepts the input from compare() either directly or
	 * indirectly, and returns another 2-D array where each element
	 * in the top level array is an array with the first value being
	 * either false, "+" (plus), or "-" (minus), to represent whether
	 * that line should be added or removed from the first original
	 * string to produce the second, and the second value being the
	 * string to add, remove, or keep as-is.  The parameters $a,
	 * $r, and $i stand for "add", "remove", and "intersect".
	 */
	public function format ($a, $r = false, $i = false) {
		if ($r === false) {
			$r = $a[1];
			$i = $a[2];
			$a = $a[0];
		}
		$out = array ();

		$incr = 0;

		$top = array ();
		$top[] = array_shift (array_reverse (array_keys ($i)));
		$top[] = array_shift (array_reverse (array_keys ($r)));
		$top[] = array_shift (array_reverse (array_keys ($a)));
		rsort ($top);
		$top = array_shift ($top);

		for ($x = 0; $x <= $top; $x++) {
			if (isset ($a[$x + $incr])) {
				while (isset ($a[$x + $incr])) {
					$out[] = array ('+', $a[$x + $incr]);
					unset ($a[$x + $incr]);
					$incr++;
				}
			}
			if (isset ($r[$x])) {
				$count = 0;
				while (isset ($r[$x + $count])) {
					$out[] = array ('-', $r[$x + $count]);
					unset ($r[$x + $count]);
					$count++;
					$incr--;
				}
			}
			if (isset ($i[$x])) {
				$out[] = array (false, $i[$x]);
			}
		}
		return $out;
	}
}

?>