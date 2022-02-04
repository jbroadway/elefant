<?php

/**
 * Start background workers from the command line.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

$workers = parse_ini_file ('conf/workers.php');

foreach ($workers as $worker => $name) {
	echo "Starting worker: {$name} ({$worker})...";
	$pid = trim (shell_exec ('./elefant ' . $worker . ' > /dev/null 2>&1 & echo $!'));
	echo " Worker started with PID {$pid}.\n";
}
