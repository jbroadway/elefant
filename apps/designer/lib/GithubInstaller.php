<?php

require_once ('apps/designer/lib/Functions.php');

class GithubInstaller extends Installer {
	public static function install ($source) {
		list ($user, $project) = github_parse_url ($source);
		$github = new GithubFetcher ($user, $project);
		$tree = $github->tree ();

		// Get config and verify it

		// Build files from tree
	}
}

?>