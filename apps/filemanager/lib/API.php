<?php

namespace filemanager;

use DB, FileManager, I18n, Restful, Zipper;

/**
 * Provides the JSON API for the admin file manager/browser, as well as functions
 * to verify files and folders.
 */
class API extends Restful {
	/**
	 * Handle list directory requests (/filemanager/api/ls).
	 */
	public function get_ls () {
		$file = trim (urldecode (join ('/', func_get_args ())));

		$res = FileManager::dir ($file);
		if (! $res) {
			return $this->error (FileManager::error ());
		}

		foreach ($res['dirs'] as $k => $dir) {
			$res['dirs'][$k]['mtime'] = I18n::short_date_year_time ($dir['mtime']);
		}

		foreach ($res['files'] as $k => $file) {
			$res['files'][$k]['mtime'] = I18n::short_date_year_time ($file['mtime']);
			$res['files'][$k]['fsize'] = format_filesize ($file['fsize']);
		}

		return $res;
	}

	/**
	 * Handle a directories request (/filemanager/api/dirs).
	 */
	public function get_dirs () {
		return FileManager::list_folders ();
	}
	
	/**
	 * Handle Bitly link requests (/filemanager/api/bitly).
	 */
	public function get_bitly () {
		$file = trim (urldecode (join ('/', func_get_args ())));
		$link = $this->controller->absolutize ('/files/' . $file);
		return BitlyLink::lookup ($link);
	}

	/**
	 * Handle remove file requests (/filemanager/api/rm).
	 */
	public function post_rm () {
		$file = trim (urldecode (join ('/', func_get_args ())));

		$res = FileManager::unlink ($file);
		if (! $res) {
			return $this->error (FileManager::error ());
		}

		$this->controller->hook ('filemanager/delete', array (
			'file' => $file
		));

		return array ('msg' => __ ('File deleted.'), 'data' => $file);
	}
	
	/**
	 * Handle remove folder requests (/filemanager/api/rmdir).
	 * Note: Erases the contents of the folder as well.
	 */
	public function post_rmdir () {
		$file = trim (urldecode (join ('/', func_get_args ())));

		$res = FileManager::rmdir ($file, true);
		if (! $res) {
			return $this->error (FileManager::error ());
		}

		$this->controller->hook ('filemanager/delete', array (
			'file' => $file
		));

		return array ('msg' => __ ('Folder deleted.'), 'data' => $file);
	}

	/**
	 * Handle rename requests (/filemanager/api/mv).
	 */
	public function post_mv () {
		$file = trim (urldecode (join ('/', func_get_args ())));

		$is_folder = FileManager::verify_folder ($file) ? true : false;
		
		if (! FileManager::rename ($file, $_POST['rename'])) {
			return $this->error (FileManager::error ());
		}
		$parts = explode ('/', $file);
		$old = array_pop ($parts);
		$new = preg_replace ('/' . preg_quote ($old) . '$/', $_POST['rename'], $file);
		if ($is_folder) {
			return array ('msg' => __ ('Folder renamed.'), 'data' => $new);
		}
		$this->controller->hook ('filemanager/rename', array (
			'file' => $file,
			'renamed' => $new
		));
		return array ('msg' => __ ('File renamed.'), 'data' => $new);
	}

	/**
	 * Handle drop requests (/filemanager/api/drop), which move files between
	 * folders.
	 */
	public function post_drop () {
		$file = trim (urldecode (join ('/', func_get_args ())));
		
		if (! FileManager::move ($file, $_POST['folder'])) {
			return $this->error (FileManager::error ());
		}

		$new = $_POST['folder'] . '/' . basename ($file);
		$this->controller->hook ('filemanager/drop', array (
			'file' => $file,
			'folder' => $_POST['folder'],
			'new' => $new
		));
		return array ('msg' => __ ('File moved.'), 'data' => $new);
	}

	/**
	 * Handle make directory requests (/filemanager/api/mkdir).
	 */
	public function post_mkdir () {
		$file = trim (urldecode (join ('/', func_get_args ())));
		
		if (! FileManager::mkdir ($file)) {
			return $this->error (FileManager::error ());
		}

		return array ('msg' => __ ('Folder created.'), 'data' => $file);
	}

	/**
	 * Handle property update requests (/filemanager/api/prop).
	 *
	 * If passed a `props` array, the key/value pairs will be
	 * saved as properties and returned as a `props` field in
	 * the response.
	 *
	 * Otherwise, individual `prop` and `value` parameters can
	 * be used to set an individual property's value.
	 */
	public function post_prop () {
		$file = trim (urldecode (join ('/', func_get_args ())));
		if (! FileManager::verify_file ($file)) {
			return $this->error (__ ('Invalid file name'));
		}
		
		// handle multiple properties at once
		if (isset ($_POST['props'])) {
			if (! is_array ($_POST['props'])) {
				return $this->error (__ ('Invalid properties'));
			}
			
			foreach ($_POST['props'] as $k => $v) {
				if (FileManager::prop ($file, $k, $v) === false) {
					return $this->error (__ ('Error saving properties.'));
				}
			}
			
			return array (
				'file' => $file,
				'props' => $_POST['props'],
				'msg' => __ ('Properties saved.')
			);
		}
		
		// handle a single property
		if (! isset ($_POST['prop'])) {
			return $this->error (__ ('Missing property name'));
		}
		if (isset ($_POST['value'])) {
			// update and fetch
			$res = FileManager::prop ($file, $_POST['prop'], $_POST['value']);
		} else {
			// fetch
			$res = FileManager::prop ($file, $_POST['prop']);
		}
		return array (
			'file' => $file,
			'prop' => $_POST['prop'],
			'value' => $res,
			'msg' => __ ('Properties saved.')
		);
	}
	
	/**
	 * Handle unzip requests via (/filemanager/api/unzip).
	 */
	public function post_unzip () {
		$file = trim (urldecode (join ('/', func_get_args ())));
		if (! FileManager::verify_file ($file)) {
			return $this->error (__ ('Invalid file name'));
		}
		
		// make sure it's a zip file
		if (! preg_match ('/\.zip$/i', $file)) {
			return $this->error (__ ('Invalid file type'));
		}
		
		// make sure the folder doesn't already exist
		$folder = preg_replace ('/\.zip$/i', '', $file);
		if (FileManager::verify_folder ($folder)) {
			return $this->error (__ ('Folder already exists'));
		}
		
		// unzip the file
		try {
			Zipper::unzip (FileManager::root () . $file);
		} catch (\Exception $e) {
			return $this->error ($e->getMessage ());
		}

		// move the unzipped folder
		$created = Zipper::find_folder (FileManager::root () . $file);
		error_log ($created);
		if (! $created) {
			if (! rename (Zipper::$folder, FileManager::root () . $folder)) {
				return $this->error (__ ('Unable to save unzipped folder.'));
			}
		} elseif (! rename ($created, FileManager::root () . $folder)) {
			return $this->error (__ ('Unable to save unzipped folder.'));
		}

		// return the newly-created folder
		return array (
			'file' => $folder,
			'msg' => __ ('File unzipped.')
		);
	}
}
