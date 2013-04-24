<?php

/**
 * Handles the default help output for the CLI tool.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if ($_SERVER['argv'][1] !== 'cli/index' && $_SERVER['argv'][1] !== 'cli/help') {
	list ($app, $handler) = explode ('/', $_SERVER['argv'][1]);
	printf ("** Error: Unknown option: %s\n", $handler);
	return;
}

$help = <<<HELP

== Elefant framework command line utility ==

Usage:

  $ cd /path/to/my/site
  $ elefant COMMAND

Commands:

  install                          Run the command line installer
  install <url-or-zip>             Install a new app or theme
  update                           Check for and apply Elefant updates
  backup <path>                    Save a backup of the site and db
  export-db <file>                 Export the db to a file or STDOUT
  import-db <file>                 Import a schema file into the db
  build-app <appname>              Build the scaffolding for an app
  crud-app <modelname> <fieldlist> Build the scaffolding for a CRUD app
  generate-password <length(8)>    Generate a random password
  encrypt-password <password>      Encrypt a password for the db
  bundle-translations <appname>    Bundle translations into an app
  version                          Output the Elefant version number
  help                             Print this help output


HELP;

// Extend command list with those from apps/*/conf/cli.php
$files = glob ('apps/*/conf/cli.php');
$commands = array ();
foreach ($files as $file) {
	$parsed = parse_ini_file ($file);
	if (! $parsed || ! isset ($parsed['commands'])) {
		continue;
	}
	$commands = array_merge ($commands, $parsed['commands']);
}

if (count ($commands) > 0) {
	$help .= "Extended commands:\n\n";
	foreach ($commands as $cmd => $desc) {
		$help .= sprintf ("  %-32s %s\n", $cmd, $desc);
	}
	$help .= "\n";
}

echo $help;

?>