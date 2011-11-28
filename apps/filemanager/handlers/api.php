<?php

/**
 * Provides the JSON API for the admin file manager/browser.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$root = getcwd () . '/files/';
$webroot = '/files/';

$error = false;
$msg = '';

$cmd = array_shift ($this->params);
$file = urldecode (join ('/', $this->params));

switch ($cmd) {
	case 'mkdir':
		$parts = explode ('/', $file);
		$newdir = array_pop ($parts);
		$path = preg_replace ('/\/?' . preg_quote ($newdir) . '$/', '', $file);
		if (! FileManager::verify_folder ($path, $root)) {
			$error = i18n_get ('Invalid location');
		} elseif (! FileManager::verify_folder_name ($newdir)) {
			$error = i18n_get ('Invalid folder name');
		} elseif (@is_dir ($root . $file)) {
			$error = i18n_get ('Folder already exists') . ' ' . $file;
		} elseif (! mkdir ($root . $file)) {
			$error = i18n_get ('Unable to create folder') . ' ' . $file;
		} else {
			@chmod ($root . $file, 0777);
			$out = $file;
			$msg = i18n_get ('Folder created.');
		}
		break;
	case 'rm':
		if (FileManager::verify_folder ($file, $root)) {
			$error = i18n_get ('Unable to delete folders');
		} elseif (! FileManager::verify_file ($file, $root)) {
			$error = i18n_get ('File not found');
		} elseif (! unlink ($root . $file)) {
			$error = i18n_get ('Unable to delete') . ' ' . $file;
		} else {
			$out = $file;
			$msg = i18n_get ('File deleted.');
		}
		break;
	case 'mv':
		if (FileManager::verify_folder ($file, $root)) {
			if (! FileManager::verify_folder_name ($_GET['rename'])) {
				$error = i18n_get ('Invalid folder name');
			} else {
				$parts = explode ('/', $file);
				$old = array_pop ($parts);
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($root . $file, $root . $new)) {
					$error = i18n_get ('Unable to rename') . ' ' . $file;
				} else {
					$out = $new;
					$msg = i18n_get ('Folder renamed.');
				}
			}
		} elseif (FileManager::verify_file ($file, $root)) {
			if (! FileManager::verify_file_name ($_GET['rename'])) {
				$error = i18n_get ('Invalid file name');
			} else {
				$parts = explode ('/', $file);
				$old = array_pop ($parts);
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($root . $file, $root . $new)) {
					$error = i18n_get ('Unable to rename') . ' ' . $file;
				} else {
					$out = $new;
					$msg = i18n_get ('File renamed.');
				}
			}
		} else {
			$error = i18n_get ('File not found');
		}
		break;
	case 'ls':
		if (! FileManager::verify_folder ($file, $root)) {
			$error = i18n_get ('Invalid folder name');
		} else {
			$d = dir ($root . $file);
			$out = array ('dirs' => array (), 'files' => array ());
			while (false != ($entry = $d->read ())) {
				if (preg_match ('/^\./', $entry)) {
					continue;
				} elseif (@is_dir ($root . $file . '/' . $entry)) {
					$out['dirs'][] = array (
						'name' => $entry,
						'path' => ltrim ($file . '/' . $entry, '/'),
						'mtime' => date ('F j, Y - g:ia', filemtime ($root . $file . '/' . $entry))
					);
				} else {
					$out['files'][] = array (
						'name' => $entry,
						'path' => ltrim ($file . '/' . $entry, '/'),
						'mtime' => date ('F j, Y - g:ia', filemtime ($root . $file . '/' . $entry)),
						'fsize' => format_filesize (filesize ($root . $file . '/' . $entry))
					);
				}
			}
			$d->close ();
			usort ($out['dirs'], array ('FileManager', 'fsort'));
			usort ($out['files'], array ('FileManager', 'fsort'));
		}
		break;
}

$res = new StdClass;
if ($error) {
	$res->success = false;
	$res->error = $error;
} else {
	$res->success = true;
	$res->msg = $msg;
	$res->data = $out;
}

echo json_encode ($res);

?>