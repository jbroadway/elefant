<?php

$id = count ($this->params) ? $this->params[0] : 'index';

$wp = new Webpage ($id);

if ($wp->error) {
	header ('HTTP/1.1 404 Not Found');
	$page->title = 'Page Not Found';
	$page->template = 'admin/base';
	$page->layout = 'error';
	echo '<p>Hmm, we can\'t seem to find the page you wanted at the moment.</p>';
	return;
}

$page->title = $wp->title;
$page->template = 'admin/base';
$page->layout = $wp->layout;
$page->head = $wp->head;

echo $wp->body;

?>