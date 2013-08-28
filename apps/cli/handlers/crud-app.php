<?php

/**
 * This command builds the CRUD outline for a new
 * app in the apps folder. This includes the basic
 * directory structure as well as a model definition,
 * database schema files, and basic admin handlers
 * for Create, Read, Update, and Delete functions.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: elefant crud-app <modelname> <fieldlist>', 'info');
	die;
}

if (! isset ($_SERVER['argv'][3])) {
	Cli::out ('Usage: elefant crud-app <modelname> <fieldlist>', 'info');
	die;
}

if (file_exists ('apps/'.$_SERVER['argv'][2])) {
	Cli::out ('apps/'.$_SERVER['argv'][2].' already exists.  Please choose a different name for your new app.', 'info');
	die;
}

$name = strtolower ($_SERVER['argv'][2]);

// get plural name
$ar = new ActiveResource;
$plural = $ar->pluralize ($name);
unset ($ar);

// build list of fields
$fields = array ();
if ($_SERVER['argv'][3] !== 'id') {
	$fields[] = 'id';
}
for ($i = 3; $i < count ($_SERVER['argv']); $i++) {
	$fields[] = $_SERVER['argv'][$i];
}

$data = array (
	'appname' => $name,
	'plural' => $plural,
	'fields' => $fields,
	'open_tag' => '<?php',
	'close_tag' => '?>',
	'backslash' => '\\'
);

mkdir ('apps/' . $plural . '/conf', 0755, true);
mkdir ('apps/' . $plural . '/forms', 0755, true);
mkdir ('apps/' . $plural . '/handlers', 0755, true);
mkdir ('apps/' . $plural . '/lib', 0755, true);
mkdir ('apps/' . $plural . '/models', 0755, true);
mkdir ('apps/' . $plural . '/views', 0755, true);

require_once ('apps/cli/lib/CRUDHelpers.php');

file_put_contents (
	'apps/' . $plural . '/conf/config.php',
	$tpl->render ('cli/crud-app/config', $data)
);

file_put_contents (
	'apps/' . $plural . '/conf/install_mysql.sql',
	$tpl->render ('cli/crud-app/install_mysql', $data)
);

file_put_contents (
	'apps/' . $plural . '/conf/install_pgsql.sql',
	$tpl->render ('cli/crud-app/install_pgsql', $data)
);

file_put_contents (
	'apps/' . $plural . '/conf/install_sqlite.sql',
	$tpl->render ('cli/crud-app/install_sqlite', $data)
);

file_put_contents (
	'apps/' . $plural . '/forms/add.php',
	$tpl->render ('cli/crud-app/add_form', $data)
);

file_put_contents (
	'apps/' . $plural . '/forms/edit.php',
	$tpl->render ('cli/crud-app/edit_form', $data)
);

file_put_contents (
	'apps/' . $plural . '/models/' . ucfirst ($name) . '.php',
	$tpl->render ('cli/crud-app/model', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/admin.php',
	$tpl->render ('cli/crud-app/admin_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/index.php',
	$tpl->render ('cli/crud-app/index_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/add.php',
	$tpl->render ('cli/crud-app/add_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/edit.php',
	$tpl->render ('cli/crud-app/edit_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/delete.php',
	$tpl->render ('cli/crud-app/delete_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/views/index.html',
	$tpl->render ('cli/crud-app/index_view', $data)
);

file_put_contents (
	'apps/' . $plural . '/views/add.html',
	$tpl->render ('cli/crud-app/add_view', $data)
);

file_put_contents (
	'apps/' . $plural . '/views/edit.html',
	$tpl->render ('cli/crud-app/edit_view', $data)
);

file_put_contents (
	'apps/' . $plural . '/views/admin.html',
	$tpl->render ('cli/crud-app/admin_view', $data)
);

Cli::out ('App created in apps/' . $plural, 'success');

?>
