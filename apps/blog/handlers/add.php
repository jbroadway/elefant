<?php

/**
 * Blog post add form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blog', 'admin/add');

$p = new blog\Post (array (
	'title' => '',
	'ts' => gmdate ('Y-m-d H:i:s'),
	'author' => User::val ('name'),
	'body' => '',
	'published' => 'no'
));
$p->put ();
Versions::add ($p);
if (! $p->error) {
	$this->redirect ('/blog/edit?id=' . $p->id);
} else {
	$this->add_notification (__ ('An Error Occurred'));
	$this->redirect ('/blog/admin');
}
