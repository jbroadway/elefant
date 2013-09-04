<?php

/**
 * List all handlers found in their app's handlers/util folder,
 * which are meant to be reused by other app developers.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

$files = glob ('apps/*/handlers/util/*.php');
$helpers = array ();

foreach ($files as $file) {
	preg_match ('/apps\/(.*)\/handlers\/util\/(.*)\.php$/', $file, $regs);
	$helpers[sprintf ('%s/util/%s', $regs[1], $regs[2])] = null;
}

$apps = glob ('apps/*/conf/helpers.php');

foreach ($apps as $file) {
	$list = parse_ini_file ($file);
	foreach ($list as $helper => $null) {
		$helpers[$helper] = null;
	}
}

ksort ($helpers);

echo join ("\n", array_keys ($helpers)) . "\n";

?>