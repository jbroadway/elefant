<?php

function filemanager_list_folders ($path = '') {
	$folders = array ();

	if (! empty ($path)) {
		$rpath = 'files/' . $path;
		$epath = $path . '/';
	} else {
		$rpath = 'files';
		$epath = '';
	}
	$d = dir ($rpath);
	if (! $d) {
		return array ();
	}
	while (false !== ($file = $d->read ())) {
		$files[] = $file;
	}
	$d->close ();

	foreach ($files as $file) {
		if (strpos ($file, '.') === 0 || ! @is_dir ($rpath . '/' . $file)) {
			continue;
		}
		$folders[] = $epath . $file;
		$subs = filemanager_list_folders ($epath . $file);
		foreach ($subs as $sub) {
			$folders[] = $sub;
		}
	}
	return $folders;
}

?>