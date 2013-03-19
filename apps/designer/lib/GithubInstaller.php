<?php

require_once ('apps/designer/lib/Functions.php');

/**
 * Installs an app or theme from a Github repository.
 */
class GithubInstaller extends Installer {
	/**
	 * Requires the Github reponsitory link (e.g., git://github.com/user/project.git).
	 */
	public static function install ($source) {
		list ($user, $project) = github_parse_url ($source);
		$github = new GithubFetcher ($user, $project);
		$tree = $github->tree ();
		
		if (! is_array ($tree)) {
			self::$error = __ ('Unable to fetch configuration file.');
			return false;
		}

		// Get config and verify it
		$found = false;
		$conf = false;
		foreach ($tree as $item) {
			if ($item->path === 'elefant.json') {
				$data = $github->get ($item);
				if (! $data || strlen ($data) !== $item->size) {
					self::$error = __ ('Unable to fetch configuration file.');
					return false;
				}
				$conf = json_decode ($data);
				if (! $conf) {
					self::$error = __ ('Verification failed: Invalid configuration file.');
					return false;
				}
				if (! self::verify ($conf)) {
					// self::$error already set by verify()
					return false;
				}
				$found = true;
				break;
			}
		}
		if (! $found) {
			self::$error = __ ('Configuration file not found.');
			return false;
		}

		// Create new install folder
		if ($conf->type === 'app') {
			$dest = 'apps/' . $conf->folder;
			if (! mkdir ($dest)) {
				self::$error = __ ('Unable to write to apps folder.');
				return false;
			}
		} else {
			$dest = 'layouts/' . $conf->folder;
			if (! mkdir ($dest)) {
				self::$error = __ ('Unable to write to layouts folder.');
				return false;
			}
		}

		// This may take some time for larger apps
		set_time_limit (120);

		// Build files from tree
		foreach ($tree as $n => $item) {
			if ($item->type === 'tree') {
				mkdir ($dest . '/' . $item->path);
			} else {
				$data = $github->get ($item);
				if ($data === false || strlen ($data) !== $item->size) {
					self::$error = __ ('Unable to fetch file') . ' ' . $item->path;
					rmdir_recursive ($dest);
					return false;
				}
				file_put_contents ($dest . '/' . $item->path, $data);

				// Create our own rate-limiting to be nice with Github
				$data = null;
				if ($n % 20 === 0) {
					sleep (1);
				}
			}
		}
		chmod_recursive ($dest, 0777);
		return $conf;
	}
}

?>