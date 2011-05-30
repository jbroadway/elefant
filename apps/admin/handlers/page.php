<?php

$id = count ($this->params) ? $this->params[0] : 'index';

$wp = new Webpage ($id);

if ($wp->error) {
	echo $this->error (404, 'Page not found', '<p>Hmm, we can\'t seem to find the page you wanted at the moment.</p>');
	return;
}

$page->title = $wp->title;
$page->template = 'admin/base';
$page->layout = $wp->layout;
$page->head = $wp->head;

echo $wp->body;

?>