<?php

namespace filemanager;

//added PropManager
use DB, FileManager, PropManager, I18n, Restful;

/**
 * Provides the JSON API for the admin file manager/browser, as well as functions
 * to verify files and folders.
 */
class API extends Restful {
	/**
	 * Handle list directory requests (/filemanager/api/ls).
	 */
	public function get_ls () {
		$file = urldecode (join ('/', func_get_args ()));

		$res = FileManager::dir ($file);
		if (! $res) {
			return $this->error (FileManager::error ());
		}

		foreach ($res['dirs'] as $k => $dir) {
			$res['dirs'][$k]['mtime'] = I18n::date_time ($dir['mtime']);
		}

		foreach ($res['files'] as $k => $file) {
			$res['files'][$k]['mtime'] = I18n::date_time ($file['mtime']);
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
	 * Handle remove file requests (/filemanager/api/rm).
	 */
	public function get_rm () {
		$file = urldecode (join ('/', func_get_args ()));

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
	 * Handle rename requests (/filemanager/api/mv).
	 */
	public function get_mv () {
		$file = urldecode (join ('/', func_get_args ()));

		$is_folder = FileManager::verify_folder ($file) ? true : false;
		
		if (! FileManager::rename ($file, $_GET['rename'])) {
			return $this->error (FileManager::error ());
		}
		$parts = explode ('/', $file);
		$old = array_pop ($parts);
		$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
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
	public function get_drop () {
		$file = urldecode (join ('/', func_get_args ()));
		
		if (! FileManager::move ($file, $_GET['folder'])) {
			return $this->error (FileManager::error ());
		}

		$new = $_GET['folder'] . '/' . basename ($file);
		$this->controller->hook ('filemanager/drop', array (
			'file' => $file,
			'folder' => $_GET['folder'],
			'new' => $new
		));
		return array ('msg' => __ ('File moved.'), 'data' => $new);
	}

	/**
	 * Handle make directory requests (/filemanager/api/mkdir).
	 */
	public function get_mkdir () {
		$file = urldecode (join ('/', func_get_args ()));
		
		if (! FileManager::mkdir ($file)) {
			return $this->error (FileManager::error ());
		}

		return array ('msg' => __ ('Folder created.'), 'data' => $file);
	}

	/**
	 * Handle property update requests (/filemanager/api/prop).
	 */
	public function get_prop () {
		$file = urldecode (join ('/', func_get_args ()));
		if (! FileManager::verify_file ($file, $this->root)) {
			return $this->error (__ ('Invalid file name'));
		}
		if (! isset ($_GET['prop'])) {
			return $this->error (__ ('Missing property name'));
		}
		if ((!FileManager::prop($file, $_GET['prop']) && $_GET['value'] != '') || $_GET['value'] != Filemanager::prop ($file, $_GET['prop'])) {
			// update and fetch
			$res = FileManager::prop ($file, $_GET['prop'], $_GET['value']);
		} else {
			// fetch
			$res = FileManager::prop ($file, $_GET['prop']);
		}
		return array (
			'file' => $file,
			'prop' => $_GET['prop'],
			'value' => $res,
			'msg' => __ ('Properties saved.')
		);
	}
  
  /**
	* Handle property manager update requests (/filemanager/api/propman).
	*/
  public function get_propman () {
		$action = urldecode (join ('/', func_get_args ()));
		if (! isset ($_GET['id'])) {
			return $this->error (__ ('Missing property name.'));
    }
    if (! preg_match ('/^[a-zA-Z0-9_]+$/', $_GET['id'])) {
			return $this->error (__ ('Invalid property name.'));
    }
    if (! $action == 'delete' && ! preg_match ('/^[a-zA-Z0-9_]+$/', $_GET['label'])) {
      return $this->error (__ ('Invalid label.'));
    }
    if ($action == 'add' || $action == 'edit') {
		  // update and fetch
			$res = PropManager::set ($_GET['id'], $_GET['type'], $_GET['label']);
    } elseif ($action == 'delete') {
      $res = PropManager::delete ($_GET['id']);
    }
		return $res;
	}
}

?>