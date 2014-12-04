<?php

/**
 * Details of a user profile.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user');

if (! isset ($_GET['id'])) {
	$this->redirect ('/user/admin');
}

$user = new User ($_GET['id']);
if ($user->error) {
	$page->title = __ ('Member not found');
	printf ('<p><a href="/user/admin">&laquo; %s</a></p>', __ ('Back'));
	return;
}

$page->title = __ ('Member') . ': ' . Template::sanitize ($user->name);
$page->add_script ('/apps/user/js/react/react.js');
$page->add_script ('/apps/user/js/build/links.js');
$page->add_script ('/apps/user/js/build/notes.js');
$page->add_style ('/apps/user/css/details.css');
echo $tpl->render ('user/details', $user->orig ());
