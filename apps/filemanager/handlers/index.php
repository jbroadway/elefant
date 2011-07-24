<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$root = getcwd () . '/files/';

$o = new StdClass;

if (isset ($_GET['path'])) {
	if (! FileManager::verify_folder ($_GET['path'], $root)) {
		$page->title = 'Invalid Path';
		echo '<p><a href="/filemanager">Back</a></p>';
		return;
	}
	$o->path = trim ($_GET['path'], '/');
	$o->fullpath = $root . $o->path;
	$tmp = explode ('/', $o->path);
	$joined = '';
	$sep = '';
	$o->parts = array ();
	$o->lastpath = '';
	foreach ($tmp as $part) {
		$joined .= $sep . $part;
		$sep = '/';
		$o->parts[$part] = $joined;
		$o->lastpath = $part;
	}
	$page->window_title = 'Files/' . $o->path;
} else {
	$o->path = '';
	$o->fullpath = $root;
	$o->parts = array ();
	$o->lastpath = '';
	$page->window_title = 'Files';
}

echo $tpl->render ('filemanager/index', $o);

?>