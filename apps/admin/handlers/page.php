<?php

/**
 * Renders a web page from the webpage table.
 * This is the default handler in Elefant,
 * allowing it to work as a web page-serving
 * CMS by default.
 */

global $user;

// determine page id
$id = count ($this->params) ? $this->params[0] : 'index';

// check if cached
$res = $memcache->get ('_admin_page_' . $id);
if ($res) {
	$page = (is_object ($res)) ? $res : unserialize ($res);

	// show admin edit buttons
	if (User::is_valid () && $user->type == 'admin') {
		$lock = new Lock ('Webpage', $id);
		$page->locked = $lock->exists ();
		echo $tpl->render ('admin/editable', $page);
	}

	// output the page body
	echo $page->body;
	return;
}

// get it from the database
$wp = new Webpage ($id);

// page not found
if ($wp->error) {
	echo $this->error (404, i18n_get ('Page not found'), '<p>' . i18n_get ('Hmm, we can\'t seem to find the page you wanted at the moment.') . '</p>');
	return;
}

// access control
if ($wp->access == 'member' && ! User::require_login ()) {
	$page->title = i18n_get ('Login required');
	echo $this->run ('user/login');
	return;
} elseif ($wp->access == 'private' && ! User::require_admin ()) {
	$page->title = i18n_get ('Login required');
	echo $this->run ('user/login');
	return;
}

// set the page properties
$page->id = $id;
$page->title = $wp->title;
$page->_menu_title = $wp->menu_title;
$page->_window_title = $wp->window_title;
$page->description = $wp->description;
$page->keywords = $wp->keywords;
$page->layout = $wp->layout;
$page->head = $wp->head;

// show admin edit buttons
	if (User::is_valid () && $user->type == 'admin') {
	$lock = new Lock ('Webpage', $id);
	$page->locked = $lock->exists ();
	echo $tpl->render ('admin/editable', $page);
}

// execute any embedded includes
$out = $tpl->run_includes ($wp->body);

if ($wp->access == 'public' && $out === $wp->body) {
	// public page, no includes, cacheable.
	$page->body = $out;
	$memcache->set ('_admin_page_' . $id, serialize ($page));
}

// output the page body
echo $out;

?>