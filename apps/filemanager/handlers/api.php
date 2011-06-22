<?php

$page->template = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$root = getcwd () . '/files/';
$webroot = '/files/';

$error = false;

$cmd = array_shift ($this->params);
$file = join ('/', $this->params);

switch ($cmd) {
	case 'mkdir':
		$newdir = array_pop (explode ('/', $file));
		$path = preg_replace ('/\/?' . preg_quote ($newdir) . '$/', '', $file);
		if (! FileManager::verify_folder ($path, $root)) {
			$error = 'Invalid location';
		} elseif (! FileManager::verify_folder_name ($newdir)) {
			$error = 'Invalid folder name';
		} elseif (@is_dir ($root . $file)) {
			$error = 'Folder already exists ' . $file;
		} elseif (! mkdir ($root . $file)) {
			$error = 'Unable to create folder ' . $file;
		} else {
			@chmod ($root . $file, 0777);
			$out = $file;
		}
		break;
	case 'rm':
		if (FileManager::verify_folder ($file, $root)) {
			$error = 'Unable to delete folders';
		} elseif (! FileManager::verify_file ($file, $root)) {
			$error = 'File not found';
		} elseif (! unlink ($root . $file)) {
			$error = 'Unable to delete ' . $file;
		} else {
			$out = $file;
		}
		break;
	case 'mv':
		if (FileManager::verify_folder ($file, $root)) {
			if (! FileManager::verify_folder_name ($_GET['rename'])) {
				$error = 'Invalid folder name';
			} else {
				$old = array_pop (explode ('/', $file));
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($root . $file, $root . $new)) {
					$error = 'Unable to rename ' . $file;
				} else {
					$out = $new;
				}
			}
		} elseif (FileManager::verify_file ($file, $root)) {
			if (! FileManager::verify_file_name ($_GET['rename'])) {
				$error = 'Invalid file name';
			} else {
				$old = array_pop (explode ('/', $file));
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($root . $file, $root . $new)) {
					$error = 'Unable to rename ' . $file;
				} else {
					$out = $new;
				}
			}
		} else {
			$error = 'File not found';
		}
		break;
	case 'ls':
		if (! FileManager::verify_folder ($file, $root)) {
			$error = 'Invalid folder name';
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
						'mtime' => date ('F j, Y - g:ia', filemtime ($root . $file . '/' . $entry))
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
	$res->data = $out;
}

echo json_encode ($res);

?>