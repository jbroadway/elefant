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
/*$res = $cache->get ('_admin_page_' . $id);
if ($res) {
	$pg = (is_object ($res)) ? $res : unserialize ($res);
	foreach ($pg as $key => $value) {
		$page->{$key} = $value;
	}

	// verify permissions before serving
	if (isset ($page->access) && $page->access !== 'public' && ! User::require_login ()) {
		if (! User::require_login ()) {
			$page->title = __ ('Login required');
			return $this->run ('user/login');
		}
		if (! User::access ($page->access)) {
			return $this->error (403, __ ('Access denied'), '<p>' . __ ('You do not have enough access privileges for this operation.') . '</p>');
		}
	}

	// show admin edit buttons
	if (User::require_acl ('admin', 'admin/pages', 'admin/edit')) {
		$lock = new Lock ('Webpage', $id);
		$page->locked = $lock->exists ();
		echo $this->run ('admin/editable', $page);
	}

	// output the page body
	echo $this->run ('admin/grid', array ('grid' => $pg->body ())); // error: doesn't work on Page
	return;
}*/

// let apps handle sub-page requests
// e.g., /company/blog -> blog app
if (conf ('General', 'page_url_style') === 'nested' && is_dir ('apps/' . $id)) {
	return $this->run ($id, $data, false);
}

// get it from the database
$wp = new Webpage ($id);

// page not found
if ($wp->error) {
	return $this->error (404, __ ('Page not found'), '<p>' . __ ('Hmm, we can\'t seem to find the page you wanted at the moment.') . '</p>');
}

// access control
if ($wp->access !== 'public' && ! User::require_admin ()) {
	if (! User::require_login ()) {
		$page->title = __ ('Login required');
		return $this->run ('user/login');
	}
	if (! User::access ($wp->access)) {
		return $this->error (403, __ ('Access denied'), '<p>' . __ ('You do not have enough access privileges for this operation.') . '</p>');
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
$page->access = $wp->access;
$page->extra = (object) $wp->extra;

// show admin edit buttons
if (User::require_acl ('admin', 'admin/pages', 'admin/edit')) {
	$lock = new Lock ('Webpage', $id);
	$page->locked = $lock->exists ();
	echo $this->run ('admin/editable', $page);
}

// build and render the page body grid
$out = $this->run ('admin/grid', array ('grid' => $wp->body ()));

/*if ($out === $wp->body) {
	// no includes, cacheable.
	$page->body = $out;
	$cache->set ('_admin_page_' . $id, serialize ($page));
}*/

// output the page body
echo $out;
