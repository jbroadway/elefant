<?php

$this->require_auth (user\Auth\OAuth::init ());

$page->layout = false;

class OAuthRestTest extends \Restful {
	public function get_authenticated () {
		return true;
	}
}

$this->restful (new OAuthRestTest);
