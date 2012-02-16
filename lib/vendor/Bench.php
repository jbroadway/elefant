<?php

/**
 * Bench - A single-method PHP 5.3+ benchmarking utility
 *
 * http://github.com/jbroadway/bench
 *
 * Keep track of milliseconds passed and memory usage throughout
 * a script's execution. Simply call `Bench::mark()` at the end
 * of each step (with an optional note), then call it again at
 * the end passing a true boolean to have it print the output.
 * If you also set `$raw` to true, it will output the memory
 * without rounding to KB.
 *
 * Usage:
 *
 *   <?php
 *   
 *   // start benchmarking:
 *   require 'Bench.php';
 *   Bench::mark ('start');
 *   
 *   // perform some logic here
 *   
 *   Bench::mark ('notes...');
 *   
 *   // perform some more logic
 *   
 *   Bench::mark ('next marker');
 *   
 *   // output the results:
 *   Bench::mark (true);
 *   
 *   ?>
 */
class Bench {
	public static function mark ($out = false, $raw = false) {
		// Record time and memory
		static $bench = array ();
		$bench[] = array (microtime (true), memory_get_usage (), $out);

		if ($out === true) {
			// Output the results
			$cur_time = 0;
			$mem_diff = 0;

			// Memory formatter
			$printm = function ($mem) use ($raw) {
				if ($raw) {
					return $mem;
				}
				return round ($mem / 1024, 1) . 'KB';
			};

			// Style and table header
			echo '<style>
					.bench td,.bench th { text-align: right; padding: 3px; }
					.bench .left { text-align: left; }
				</style>
				<table class="bench">
					<tr>
						<th>Time</th>
						<th>Memory</th>
						<th>Diff</th>
						<th style="text-align:left">Notes</th>
					</tr>';

			// Loop through collected data
			foreach ($bench as $mark) {
				list ($mtime, $mem, $note) = $mark;
				printf (
					'<tr><td>%f</td><td>%s</td><td>%s</td><td class="left">%s</td></tr>',
					($cur_time === 0) ? $cur_time : $mtime - $cur_time,
					$printm ($mem),
					$printm ($mem - $mem_diff),
					is_bool ($note) ? '' : $note
				);
				$cur_time = $mtime;
				$mem_diff = $mem;
			}

			// Output totals at the end
			printf (
				'<tr><th>%f</th><th>%s</th><th>&nbsp</th><th class="left">Totals</th></tr>',
				$bench[count ($bench) - 1][0] - $bench[0][0],
				$printm (memory_get_peak_usage ())
			);
			echo '</table>';
		}
	}
}

?>