<?php

/**
 * Provides the JSON API for the file browser in the WYSIWYG editor.
 */

$this->require_admin ();

$page->layout = false;

if (isset ($_POST['action'])) {
	header ('Content-Type: text/plain; charset=UTF-8');
	$_GET['action'] = $_POST['action'];
} else {
	header ('Content-Type: application/json');
	$error = false;
}

switch ($_GET['action']) {
	case 'auth':
		if ($_GET['auth'] != 'jwysiwyg') {
			$error = 'Authorization failed.';
			break;
		}
		$out = array (
			'move' => array ('enabled' => false),
			'rename' => array ('enabled' => false),
			'remove' => array ('enabled' => false),
			'mkdir' => array ('enabled' => false),
			'upload' => array ('enabled' => true, 'handler' => '/filemanager/embed')
		);
		break;
	case 'list':
		$ok = 0;
		if (! isset ($_GET['dir']) || $_GET['dir'] === '/') {
			$_GET['dir'] = '/files';
		}
		if ($_GET['dir'] === '/files') {
			$ok = 3;
		} else {
			if (strpos ($_GET['dir'], '..') === false) {
				$ok++;
			}
			if (strpos ($_GET['dir'], '/files/') === 0) {
				$ok++;
			}
			if (@is_dir (getcwd () . $_GET['dir'])) {
				$ok++;
			}
		}
		if ($ok < 3) {
			$error = 'Invalid directory: ' . $_GET['dir'];
			break;
		}
		$out = array (
			'directories' => array (),
			'files' => array ()
		);
		$d = dir (getcwd () . $_GET['dir']);
		while (false !== ($entry = $d->read ())) {
			if (strpos ($entry, '.') === 0) {
				continue;
			} elseif (@is_dir (ltrim ($_GET['dir'], '/') . '/' . $entry)) {
				$out['directories'][$entry] = rtrim ($_GET['dir'], '/') . '/' . $entry;
			} else {
				$out['files'][$entry] = rtrim ($_GET['dir'], '/') . '/' . $entry;
			}
		}
		$d->close ();
		break;
	case 'upload':
		$ok = 0;
		
		// prevent encoded symbols bypassing checks
		$_POST['dir'] = urldecode ($_POST['dir']);
		$_POST['newName'] = urldecode ($_POST['newName']);

		if (! isset ($_POST['dir']) || $_POST['dir'] === '/') {
			$_POST['dir'] = '/files';
		}

		if ($_POST['dir'] === '/files') {
			$ok = 3;
		} else {
			if (strpos ($_POST['dir'], '..') === false) {
				$ok++;
			}

			if (strpos ($_POST['dir'], '/files/') === 0) {
				$ok++;
			}

			if (is_dir (getcwd () . $_POST['dir'])) {
				$ok++;
			}
		}
		
		if ($ok < 3) {
			echo __ ('Invalid directory');
			return;
		}
		
		if (! isset ($_POST['newName'])) {
			echo __ ('No name specified');
			break;
		}

		if (strpos ($_POST['newName'], '..') !== false || strpos ($_POST['newName'], '/') !== false) {
			echo __ ('Invalid name');
			return;
		}
		
		$_POST['newName'] = trim ($_POST['newName']);
		
		if (preg_match ('/\.(php|phtml|pht|php3|php4|php5|phar|js|rb|py|pl|sh|bash|exe|htaccess|htpasswd)$/i', $_POST['newName'])) {
			echo __ ('Invalid file type');
			return;
		}
		
		$dest = ltrim ($_POST['dir'], '/') . '/' . $_POST['newName'];

		if (file_exists ($dest)) {
			echo __ ('File already exists');
			return;
		}

		if (! is_uploaded_file ($_FILES['handle']['tmp_name'])) {
			echo __ ('File upload failed');
			return;
		}

		if (! move_uploaded_file ($_FILES['handle']['tmp_name'], $dest)) {
			echo __ ('File save failed');
			return;
		}

		echo 'File uploaded successfully';
		return;
}

if ($error) {
	echo json_encode (array (
		'success' => false,
		'error' => $error,
		'errno' => 1
	));
} else {
	echo json_encode (array (
		'success' => true,
		'data' => $out
	));
}
