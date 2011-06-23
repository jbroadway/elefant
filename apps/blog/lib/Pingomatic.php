<?php

class Pingomatic {
	var $host = 'rpc.pingomatic.com';
	var $body = '<?xml version="1.0"?>
<methodCall>
	<methodName>weblogUpdates.ping</methodName>
		<params>
			<param><value>%s</value></param>
			<param><value>%s</value></param>
		</params>
</methodCall>';

	function post ($name, $url) {
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

?>