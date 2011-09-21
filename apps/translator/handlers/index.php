<?php

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$page->title = 'Translator';
$page->layout = 'admin';

?>