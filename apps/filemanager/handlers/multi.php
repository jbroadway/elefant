<?php

/**
 * Multi-file uploader.
 */

// Fetch the session from Uploadify
if (isset ($_POST['uploadify_session_id'])) {
	@session_set_cookie_params (time () + 2592000);
	@session_start ();
	$_SESSION['session_id'] = $_POST['uploadify_session_id'];
	$_SERVER['HTTP_USER_AGENT'] = $_POST['uploadify_user_agent'];
}

// Authentication
if (! User::require_admin ()) {
	if (! empty ($_FILES)) {
		header ('HTTP/1.1 405 ' . i18n_get ('Admin access required.'));
		exit;
	}
	$this->redirect ('/admin');
}

$root = getcwd () . '/files/';
$_GET['path'] = isset ($_POST['path']) ? $_POST['path'] : $_GET['path'];

if (! empty ($_FILES)) {
	// Handle uploads
	$page->layout = false;

	if (! FileManager::verify_folder ($_GET['path'], $root)) {
		header ('HTTP/1.1 406 ' . i18n_get ('Invalid Path'));
		exit;
	} elseif ($_FILES['Filedata']['error'] > 0) {
		header ('HTTP/1.1 407 ' . i18n_get ('Unknown error'));
		exit;
	} elseif (file_exists ($root . $_GET['path'] . '/' . $_FILES['Filedata']['name'])) {
		header ('HTTP/1.1 408 ' . i18n_get ('A file by that name already exists.'));
		exit;
	} elseif (! move_uploaded_file ($_FILES['Filedata']['tmp_name'], $root . $_GET['path'] . '/' . $_FILES['Filedata']['name'])) {
		header ('HTTP/1.1 409 ' . i18n_get ('Unable to save the file.'));
		return;
	}
	// File saved

	chmod ($root . $_GET['path'] . '/' . $_FILES['Filedata']['name'], 0666);
	$this->hook ('filemanager/add', array (
		'file' => $_POST['path'] . '/' . $_FILES['Filedata']['name']
	));
	echo '1';
	return;
}

$page->layout = 'admin';
$page->title = i18n_get ('Multi-file uploader');

// Show uploader
$o = new StdClass;

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

$page->add_script ('/apps/filemanager/js/uploadify/swfobject.js');
$page->add_script ('/apps/filemanager/js/uploadify/jquery.uploadify.v2.1.4.min.js');

echo $tpl->render ('filemanager/multi', $o);

?>