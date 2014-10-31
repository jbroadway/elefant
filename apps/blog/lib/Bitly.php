<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Gets a shortened version of the specified URL through the Bit.ly
 * (http://bit.ly/) URL shortener service.
 */
class Bitly {
	/**
	 * The Bit.ly login ID.
	 */
	public $login = 'elefantcms';

	/**
	 * The Bit.ly API key.
	 */
	public $apiKey = 'R_270d116ab462af1aaf7e5442e162ca62';

	/**
	 * Perform the shortening request and return the shortened URL.
	 */
	public function shorten ($url) {
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
