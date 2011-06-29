<?php

$page->template = false;
header ('Content-Type: application/json');

$error = false;

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
			'upload' => array ('enabled' => false)
		);
		break;
	case 'list':
		$ok = 0;
		if (! isset ($_GET['dir']) || $_GET['dir'] == '/') {
			$_GET['dir'] = '/files';
		}
		if ($_GET['dir'] == '/files') {
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

?>