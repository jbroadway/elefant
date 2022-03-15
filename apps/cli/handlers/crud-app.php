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

$usage = <<<USAGE
Usage:

  <info>./elefant crud-app <modelname> <fieldlist></info>

Example:

  <info>./elefant crud-app book id title isbn released:date \
      description:textarea about:wysiwyg</info>

See also:

  <info>./elefant crud-app list-types</info>


USAGE;

if (! isset ($_SERVER['argv'][2])) {
	Cli::block ($usage);
	die;
}

$types = array (
	'checkbox',
	'date',
	'datetime',
	'email',
	'password',
	'pkey',
	'radio',
	'select',
	'text',
	'textarea',
	'time',
	'wysiwyg'
);

if ($_SERVER['argv'][2] === 'list-types') {
	Cli::out (' - ' . join ("\n - ", $types), 'info');
	die;
}

if (! isset ($_SERVER['argv'][3])) {
	Cli::block ($usage);
	die;
}

$name = strtolower ($_SERVER['argv'][2]);

// get plural name
$plural = Pluralizer::pluralize ($name);
unset ($ar);

if (file_exists ('apps/' . $plural)) {
	Cli::out ('apps/' . $plural . ' already exists.  Please choose a different name for your new app.', 'info');
	die;
}

// build list of fields
$fields = array ();
$pkey = false;
for ($i = 3; $i < count ($_SERVER['argv']); $i++) {

	// match name:type
	if (preg_match ('/^([a-zA-Z0-9_]+):(' . join ('|', $types) . ')$/', $_SERVER['argv'][$i], $regs)) {
		$fields[] = (object) array (
			'name' => $regs[1],
			'type' => $regs[2]
		);
		if ($regs[2] === 'pkey') {
			$pkey = $regs[1];
		}

	// match name alone
	} elseif (preg_match ('/^[a-zA-Z0-9_]+$/', $_SERVER['argv'][$i])) {

		// automatically promote 'id' field to primary key if unspecified
		if ($_SERVER['argv'][$i] === 'id') {
			$fields[] = (object) array (
				'name' => 'id',
				'type' => 'pkey'
			);
			$pkey = 'id';

		// default to type text
		} else {
			$fields[] = (object) array (
				'name' => $_SERVER['argv'][$i],
				'type' => 'text'
			);
		}

	// invalid type
	} else {
		Cli::out ('Invalid type for field ' . $_SERVER['argv'][$i] . '.  Please enter a valid field type.', 'info');
		die;
	}	
}

// make sure there is a primary key
if (! $pkey) {
	array_unshift ($fields, (object) array (
		'name' => 'id',
		'type' => 'pkey',
	));
	$pkey = 'id';
}

$total = count ($fields);
foreach ($fields as $k => $field) {
	if ($field->name === $pkey) {
		continue;
	}

	if ($k < $total - 1) {
		$fields[$k]->comma = ',';
	} else {
		$fields[$k]->comma = '';
	}
}

$data = array (
	'appname' => $name,
	'plural' => $plural,
	'fields' => $fields,
	'pkey' => $pkey,
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
	'apps/' . $plural . '/conf/acl.php',
	$tpl->render ('cli/crud-app/acl', $data)
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
	'apps/' . $plural . '/models/' . cli\Filter::camel ($name) . '.php',
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
	'apps/' . $plural . '/handlers/install.php',
	$tpl->render ('cli/crud-app/install_handler', $data)
);

file_put_contents (
	'apps/' . $plural . '/handlers/upgrade.php',
	$tpl->render ('cli/crud-app/upgrade_handler', $data)
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
