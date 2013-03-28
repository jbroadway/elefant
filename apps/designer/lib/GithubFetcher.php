<?php

/**
 * A very basic Github public project fetching tool. Uses v3 of the Github
 * API through CURL. Useful for quickly fetching a project from Github,
 * and much smaller than the full Github PHP client library.
 *
 * Usage:
 *
 *     <?php
 *     
 *     require 'GithubFetcher.php';
 *     
 *     $github = new GithubFetcher ('git://github.com/codeguy/Slim.git');
 *     
 *     // get all files/folders from the repository
 *     $tree = $github->tree ();
 *     
 *     $first_file = false
 *     
 *     foreach ($tree as $item) {
 *         printf ("%s: %s\n", $item->type, $item->path);
 *         if (! $first_file && $item->type === 'blob') {
 *             $first_file = $item;
 *         }
 *     }
 *     
 *     // print the contents of the file
 *     echo $github->get ($first_file);
 *     
 *     ?>
 */
class GithubFetcher {
	/**
	 * Github user ID.
	 */
	public $user = false;

	/**
	 * Github project name.
	 */
	public $project = false;

	/**
	 * Branch of the repository to fetch from.
	 */
	public $branch = 'master';

	/**
	 * Stores the SHA1 of the latest commit from the active branch
	 * of the repository.
	 */
	private $_sha = false;

	/**
	 * Uses CURL to perform a GET request to the Github API v3.
	 */
	public static function _fetch ($url) {
		if (extension_loaded ('curl')) {
			$ch = curl_init ();
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_VERBOSE, 0);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_FAILONERROR, 0);
			curl_setopt ($ch, CURLOPT_URL, $url);
			$res = curl_exec ($ch);
			curl_close ($ch);
			return $res;
		}
		return file_get_contents ($url);
	}

	public static function parse_url ($url) {
		if (preg_match ('#^(git|https?)://github.com/([^/]+)/([^.]+)\.git$#', $url, $matches)) {
			return array ($matches[2], $matches[3]);
		}
		return array ($url, false);
	}

	/**
	 * Takes a Github user and project name, or the Github URL for a project.
	 */
	public function __construct ($user, $project = false) {
		if (! $project) {
			list ($this->user, $this->project) = self::parse_url ($user);
		} else {
			$this->user = $user;
			$this->project = $project;
		}
	}

	/**
	 * Fetches the SHA1 of the latest commit on the specified branch of a
	 * repository.
	 */
	public function sha ($branch = 'master') {
		$this->branch = $branch;
		$res = self::_fetch ('https://api.github.com/repos/'.$this->user.'/'.$this->project.'/branches');
		$res = json_decode ($res);

		if (! is_array ($res)) {
			return false;
		}
	
		foreach ($res as $row) {
			if ($row->name === $branch) {
				$this->_sha = $row->commit->sha;
				return $this->_sha;
			}
		}
		return false;
	}

	/**
	 * Fetch a list of all objects (blob and tree) in a branch of a repository.
	 * Uses the latest commit's SHA1, and will call `sha()` for you.
	 */
	public function tree ($branch = 'master') {
		if (! $this->_sha || $branch !== $this->branch) {
			$this->sha ($branch);
		}
		$res = self::_fetch ('https://api.github.com/repos/'.$this->user.'/'.$this->project.'/git/trees/'.$this->_sha.'?recursive=1');
		$res = json_decode ($res);

		if (! is_object ($res)) {
			return false;
		}

		return $res->tree;
	}

	/**
	 * Fetch the contents of a blob (aka file). `$blob` can be an object or
	 * the SHA1 value for that object. Contents with `base64` encoding will
	 * be decoded.
	 */
	public function get ($blob) {
		$sha = is_object ($blob) ? $blob->sha : $blob;
		$res = self::_fetch ('https://api.github.com/repos/'.$this->user.'/'.$this->project.'/git/blobs/'.$sha);
		$res = json_decode ($res);

		if (! is_object ($res)) {
			return false;
		}

		if ($res->encoding === 'base64') {
			return base64_decode ($res->content);
		}
		return $res->content;
	}
}

?>