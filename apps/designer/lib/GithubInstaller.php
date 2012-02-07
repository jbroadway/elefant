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

		// Get config and verify it
		$found = false;
		$conf = false;
		foreach ($tree as $item) {
			if ($item->path === 'elefant.json') {
				$data = $github->get ($item);
				if (! $data) {
					self::$error = i18n_get ('Unable to fetch configuration file.');
					return false;
				}
				$conf = json_decode ($data);
				if (! $conf) {
					self::$error = i18n_get ('Verification failed: Invalid configuration file.');
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
			self::$error = i18n_get ('Configuration file not found.');
			return false;
		}

		// Build files from tree
		if ($conf->type === 'app') {
			$dest = 'apps/' . $conf->folder;
			if (! mkdir ($dest)) {
				self::$error = i18n_get ('Unable to write to apps folder.');
				return false;
			}
		} else {
			$dest = 'layouts/' . $conf->folder;
			if (! mkdir ($dest)) {
				self::$error = i18n_get ('Unable to write to layouts folder.');
				return false;
			}
		}

		foreach ($tree as $item) {
			if ($item->type === 'tree') {
				mkdir ($dest . '/' . $item->path);
			} else {
				$data = $github->get ($item);
				if (! $data) {
					self::$error = i18n_get ('Unable to fetch data from Github.');
					return false;
				}
				file_put_contents ($dest . '/' . $item->path, $data);
			}
		}
		chmod_recursive ($dest, 0777);
		return $conf;
	}
}

?>