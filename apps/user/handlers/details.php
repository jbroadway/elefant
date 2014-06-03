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

$links = $user->links ();
$notes = $user->notes ();
$user = $user->orig ();
$user->links = $links;
$user->notes = $notes;

$page->title = __ ('Member') . ': ' . $user->name;
$page->add_script ('/apps/user/js/links.js');
$page->add_script ('/apps/user/js/notes.js');
echo $tpl->render ('user/details', $user);

?>