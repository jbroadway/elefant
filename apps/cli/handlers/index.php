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

== Elefant CMS command line utility ==

Usage:

  <info>$ cd /path/to/my/site
  $ ./elefant COMMAND
  $ ./elefant --env=staging COMMAND</info>

Commands:

  <info>install</info>                               Run the command line installer
  <info>install <url-or-zip></info>                  Install a new app or theme
  <info>update</info>                                Check for and apply Elefant updates
  <info>permissions</info>                           Set your filesystem permissions
  <info>conf <Section.setting> [value]</info>        Get or set a global setting
<info>  appconf <app.Section.setting> [value]</info> Get or set an app setting
  <info>backup <path></info>                         Save a backup of the site and db
  <info>export-db <file></info>                      Export the db to a file or STDOUT
  <info>import-db <file></info>                      Import a schema file into the db
  <info>build-app <appname></info>                   Build the scaffolding for an app
  <info>crud-app <modelname> <fieldlist></info>      Build the scaffolding for a CRUD app
  <info>crud-app list-types</info>                   List the available CRUD field types
  <info>clear-cache</info>                           Clear the cache and compiled templates
  <info>clear-cache <key></info>                     Clear a particular cached object
  <info>list-helpers</info>                          List available server-side helpers
  <info>generate-key</info>                          Generate a random 32 character key
  <info>generate-password <length(8)></info>         Generate a random password
  <info>encrypt-password <password></info>           Encrypt a password for the db
  <info>bundle-translations <appname></info>         Bundle translations into an app
  <info>version</info>                               Output the Elefant version number
  <info>help</info>                                  Print this help output


HELP;

// Extend command list with those from apps/*/conf/cli.php
$files = glob ('apps/*/conf/cli.php');
if ($files) {
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
			$help .= sprintf ("  <info>%-37s</info> %s\n", $cmd, $desc);
		}
		$help .= "\n";
	}
}

Cli::block ($help);
