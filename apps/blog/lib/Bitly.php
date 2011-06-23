<?php

class Bitly {
	var $login = 'elefantcms';
	var $apiKey = 'R_270d116ab462af1aaf7e5442e162ca62';
	function shorten ($url) {
		$post = sprintf (
			'http://api.bit.ly/v3/shorten?longUrl=%s&login=%s&apiKey=%s&format=json',
			urlencode ($url),
			$this->login,
			$this->apiKey
		);
		$curl = curl_init ();
		curl_setopt ($curl, CURLOPT_URL, $post);
		curl_setopt ($curl, CURLOPT_HEADER, false);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec ($curl);
		curl_close ($curl);
		$obj = json_decode ($res, true);
		return $obj['data']['url'];
	}
}

?>