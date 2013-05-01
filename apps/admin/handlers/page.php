<?php

/**
 * Renders a web page from the webpage table.
 * This is the default handler in Elefant,
 * allowing it to work as a web page-serving
 * CMS by default.
 */

// determine page id
$id = count ($this->params)
    ? $this->params[
            (conf ('General', 'page_url_style') === 'flat')
                ? 0
                : count ($this->params) - 1
        ]
    : 'index';

// check if cached
$res = $cache->get ('_admin_page_' . $id);
if ($res) {
	$pg = (is_object ($res)) ? $res : unserialize ($res);
	foreach ($pg as $key => $value) {
		$page->{$key} = $value;
	}

	// show admin edit buttons
	if (User::require_acl ('admin', 'admin/edit')) {
		$lock = new Lock ('Webpage', $id);
		$page->locked = $lock->exists ();
		echo $tpl->render ('admin/editable', $page);
	}

	// output the page body
	echo $page->body;
	return;
}

// let apps handle sub-page requests
// e.g., /company/blog -> blog app
if (conf ('General', 'page_url_style') === 'nested' && is_dir ('apps/' . $id)) {
	echo $this->run ($id, $data, false);
	return;
}

// get it from the database
$wp = new Webpage ($id);

// page not found
if ($wp->error) {
	echo $this->error (404, __ ('Page not found'), '<p>' . __ ('Hmm, we can\'t seem to find the page you wanted at the moment.') . '</p>');
	return;
}

// access control
if ($wp->access !== 'public' && ! User::require_admin ()) {
	if (! User::require_login ()) {
		$page->title = __ ('Login required');
		echo $this->run ('user/login');
		return;
	}
	if (! User::access ($wp->access)) {
		echo $this->error (403, __ ('Access denied'), '<p>' . __ ('You do not have enough access privileges for this operation.') . '</p>');
		return;
	}
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
if (User::require_acl ('admin', 'admin/edit')) {
	$lock = new Lock ('Webpage', $id);
	$page->locked = $lock->exists ();
	echo $tpl->render ('admin/editable', $page);
}

// execute any embedded includes
$out = $tpl->run_includes ($wp->body);

if ($wp->access == 'public' && $out === $wp->body) {
	// public page, no includes, cacheable.
	$page->body = $out;
	$cache->set ('_admin_page_' . $id, serialize ($page));
}

// output the page body
echo $out;

?>
