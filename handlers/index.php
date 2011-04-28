<?php

// Implement a basic page->db mapper here,
// with 404 handling.

require_once ('models/Webpage.php');

$id = count ($this->params) ? $this->params[0] : 'index';
$wp = new Webpage ($id);
if ($wp->error) {
	$page->title = 'Page Not Found';
	$page->template = 'error';
	echo 'Hmm, we can\'t seem to find the page you wanted at the moment.';
	return;
}

$page->title = $wp->title;
$page->template = $wp->template;
$page->head = $wp->head;

echo $wp->body;

?>