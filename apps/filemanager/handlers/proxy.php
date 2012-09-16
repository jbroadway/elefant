<?php

/**
 * Acts as a proxy to intercept file requests and
 * enable them to be served by custom handlers,
 * to integrate with external storage solutions
 * such as Amazon S3 or Rackspace Cloud Files.
 * To use, edit the proxy_handler setting in
 * the file manager app and it will automatically
 * generate file links that are passed to the
 * proxy.
 */

if ($appconf['General']['proxy_handler']) {
	echo $this->run (
		$appconf['General']['proxy_handler'],
		array (
			'file' => join ('/', $this->params)
		)
	);
}

$this->permanent_redirect ('/files/' . join ('/', $this->params));

?>