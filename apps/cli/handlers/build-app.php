<?php

/**
 * This command builds the scaffolding for a new
 * app in the apps folder. This includes the basic
 * directory structure as well as some sample
 * files (config, handlers, views).
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: elefant build-app <appname>', 'info');
	die;
}

$appname = $_SERVER['argv'][2];
$data = array (
	'appname' => $appname,
	'open_tag' => '<?php',
	'close_tag' => '?>'
);

mkdir ('apps/' . $appname . '/conf', 0755, true);
mkdir ('apps/' . $appname . '/forms', 0755, true);
mkdir ('apps/' . $appname . '/handlers', 0755, true);
mkdir ('apps/' . $appname . '/lib', 0755, true);
mkdir ('apps/' . $appname . '/models', 0755, true);
mkdir ('apps/' . $appname . '/views', 0755, true);

file_put_contents (
	'apps/' . $appname . '/handlers/index.php',
	$tpl->render ('cli/build-app/index_handler', $data)
);

file_put_contents (
	'apps/' . $appname . '/handlers/admin.php',
	$tpl->render ('cli/build-app/admin_handler', $data)
);

file_put_contents (
	'apps/' . $appname . '/views/index.html',
	$tpl->render ('cli/build-app/index_view', $data)
);

file_put_contents (
	'apps/' . $appname . '/conf/config.php',
	$tpl->render ('cli/build-app/config', $data)
);

Cli::out ('App created in apps/' . $appname, 'success');

?>