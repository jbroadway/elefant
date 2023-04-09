<?php

/**
 * Creates a preview of a web page based on POST data sent to it.
 * POST data must match values available to the Page object.
 */

$this->require_admin ();

if (! isset ($_POST['id'])) {
	$page->title = __ ('Preview Expired');
	echo '<p><a href="#" onclick="window.close()">' . __ ('Close Window') . '</a></p>';
	return;
}

$page->id = $_POST['id'];
$page->title = $_POST['title'];
$page->menu_title = (! empty ($_POST['menu_title'])) ? $_POST['menu_title'] : $_POST['title'];
$page->window_title = (! empty ($_POST['window_title'])) ? $_POST['window_title'] : $_POST['title'];
$page->description = $_POST['description'];
$page->keywords = $_POST['keywords'];
$page->layout = $_POST['layout'];
$page->head = '';

$_SERVER['REQUEST_URI'] = '/' . $_POST['id'];

// show admin edit buttons
if (User::require_acl ('admin', 'admin/pages', 'admin/edit')) {
	$lock = new Lock ('Webpage', $_POST['id']);
	$page->locked = $lock->exists ();
	echo $this->run ('admin/editable', $page);
}

echo $tpl->run_includes ($_POST['body']);
