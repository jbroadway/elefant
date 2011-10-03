<?php

$id = count ($this->params) ? $this->params[0] : 'index';

$wp = new Webpage ($id);

if ($wp->error) {
	echo $this->error (404, i18n_get ('Page not found'), '<p>' . i18n_get ('Hmm, we can\'t seem to find the page you wanted at the moment.') . '</p>');
	return;
}

if ($wp->access == 'member' && ! User::require_login ()) {
	$page->title = i18n_get ('Login required');
	echo $this->run ('user/login');
	return;
} elseif ($wp->access == 'private' && ! User::require_admin ()) {
	$page->title = i18n_get ('Login required');
	echo $this->run ('user/login');
	return;
}

$page->id = $id;
$page->title = $wp->title;
$page->menu_title = $wp->menu_title;
$page->window_title = $wp->window_title;
$page->description = $wp->description;
$page->keywords = $wp->keywords;
$page->layout = $wp->layout;
$page->head = $wp->head;

global $user;

if (User::is_valid () && $user->type == 'admin') {
	$lock = new Lock ('Webpage', $id);
	$page->locked = $lock->exists ();
	echo $tpl->render ('admin/editable', $page);
}

echo $tpl->run_includes ($wp->body);

?>