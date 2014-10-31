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
 * Makes an XML-RPC request to Ping-o-matic (http://pingomatic.com/),
 * which notifies a bunch of blog search engines that you have a new
 * post for them to index.
 */
class Pingomatic {
	/**
	 * Ping-o-matic host name.
	 */
	public $host = 'rpc.pingomatic.com';

	/**
	 * Request template.
	 */
	var $body = '<?xml version="1.0"?>
<methodCall>
	<methodName>weblogUpdates.ping</methodName>
		<params>
			<param><value>%s</value></param>
			<param><value>%s</value></param>
		</params>
</methodCall>';

	/**
	 * Performs the post request with the specified blog name
	 * and URL.
	 */
	public function post ($name, $url) {
		require_once ('conf/version.php');
		$body = sprintf ($this->body, $name, $url);
		$len = strlen ($body);
		$req = "POST / HTTP/1.0\r\n";
		$req .= sprintf ("User-Agent: elefantcms %s/blog\r\n", VERSION);
		$req .= sprintf ("Host: %s\r\n", $this->host);
		$req .= "Content-Type: text/xml\r\n";
		$req .= sprintf ("Content-Length: %d\r\n\r\n", $len);
		$req .= $body . "\r\n";

		$res = '';
		if ($ph = @fsockopen ($this->host, 80)) {
			@fputs ($ph, $req);
			while (! @feof ($ph)) {
				$res .= @fgets ($ph, 128);
			}
			@fclose ($ph);
		}
		return $res;
	}
}
