<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$o = new StdClass;

if (isset ($_GET['path'])) {
	$o->path = trim ($_GET['path'], '/');
	$o->slashpath = '/' . $o->path;
	$o->fullpath = getcwd () . '/files/' . $o->path;
	$tmp = explode ('/', $o->path);
	$o->parts = array ();
	foreach ($tmp as $part) {
		$joined = join ('/', $o->parts);
		$o->parts[$part] = $joined . '/' . $part;
	}
	if (strpos ($o->path, '..') !== false || ! @is_dir ($o->fullpath)) {
		$page->title = 'Invalid Path';
		echo '<p><a href="/filemanager">Back</a></p>';
		return;
	}
	$page->window_title = 'Files/' . $o->path;
} else {
	$o->path = '';
	$o->slashpath = '/';
	$o->fullpath = getcwd () . '/files';
	$o->parts = array ();
	$page->window_title = 'Files';
}

$d = dir ($o->fullpath);
$o->files = array ();
$o->dirs = array ();
while (false != ($entry = $d->read ())) {
	if (preg_match ('/^\./', $entry)) {
		continue;
	} elseif (@is_dir ($o->fullpath . '/' . $entry)) {
		$o->dirs[$entry] = filemtime ($o->fullpath . '/' . $entry);
	} else {
		$o->files[$entry] = filemtime ($o->fullpath . '/' . $entry);
	}
}
$d->close ();

asort ($o->dirs);
asort ($o->files);

echo $tpl->render ('filemanager/index', $o);

?>