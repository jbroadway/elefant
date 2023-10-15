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
	$page->title = __ ('Account not found');
	printf ('<p><a href="/user/admin">&laquo; %s</a></p>', __ ('Back'));
	return;
}

$user = $user->orig ();

if ($user->photo != '' && strpos ($user->photo, '/') !== 0 && strpos ($user->photo, '://') === false) {
	$user->photo = '/' . $user->photo;
}

if (! isset ($user->tabs) || ! is_array ($user->tabs)) {
	$user->tabs = array ();
}

$tabs = Appconf::options ('user');
foreach ($tabs as $handler => $name) {
	$user->tabs[$name] = $this->run ($handler, array ('user' => $user->id));
}

$page->title = Template::sanitize ($user->name);
$page->add_style ('/apps/user/css/details.css');
$page->add_script ('/js/jquery-migrate-1.2.1.js');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/apps/user/js/jquery.tools.min.js');
$page->add_script ('/apps/user/js/react/react.js');
$page->add_script ('/apps/user/js/build/links.js');
$page->add_script ('/apps/user/js/build/notes.js');
echo $tpl->render ('user/details', $user);
