<?php

/**
 * Show the documentation for a helper, found in the first comment
 * block in the source file.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant helper-docs <helper>', 'error');
	die;
}

$helper = $_SERVER['argv'][2];

list ($app, $handler) = explode ('/', $helper, 2);
$route = 'apps/' . $app . '/handlers/' . $handler . '.php';

if (! file_exists ($route)) {
	Cli::out ('Helper not found in ' . $route, 'error');
	die;
}

echo "\n# Helper: " . $helper . "\n\n";

// Get the comment itself
$comments = array_filter (
	token_get_all (file_get_contents ($route)),
	function ($entry) {
		return $entry[0] == T_DOC_COMMENT;
	}
);

$comments = array_shift ($comments);
if (! isset ($comments[1])) {
	echo "No documentation found.\n\n";
	die;
}

$docs = $comments[1];

// remove comment block tags
$docs = preg_replace ('/^\/\*\*?/', '', $docs);
$docs = preg_replace ('/\*\/$/', '', $docs);
$docs = preg_replace ('/\n[ \t]+?\* ?/', "\n", $docs);

echo trim ($docs) . "\n\n";
