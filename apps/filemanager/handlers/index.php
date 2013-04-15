<?php

/**
 * The admin file manager/browser handler.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$root = getcwd () . '/' . conf('Paths','filemanager_path') .'/';

$o = new StdClass;

if (isset ($_GET['path'])) {
	if (! FileManager::verify_folder ($_GET['path'], $root)) {
		$page->title = __ ('Invalid Path');
		echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
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
	$page->window_title = __ ('Files') . '/' . $o->path;
} else {
	$o->path = '';
	$o->fullpath = $root;
	$o->parts = array ();
	$o->lastpath = '';
	$page->window_title = __ ('Files');
}

if ($appconf['General']['aviary_key']) {
	$page->add_script ('http://feather.aviary.com/js/feather.js');
	$o->aviary_key = $appconf['General']['aviary_key'];
} else {
	$o->aviary_key = false;
}

$page->add_style ('/apps/filemanager/css/filemanager.css');
$page->add_script (
    sprintf (
        '<script>var conf_root = "%s";</script>',
        conf('Paths','filemanager_path')
    )
);
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/js/urlify.js');
$page->add_script ('/apps/filemanager/js/jquery.filedrop.js');
$page->add_script ('/apps/filemanager/js/jquery.tmpl.beta1.min.js');
$page->add_script ('/apps/filemanager/js/jquery.filemanager.js');
$page->add_script (I18n::export (
	'New folder name:',
	'Rename:',
	'Are you sure you want to delete this file?',
	'Your browser does not support drag and drop file uploads.',
	'Please upload fewer files at a time.',
	'The following file is too large to upload'
));

echo $tpl->render ('filemanager/index', $o);

?>
