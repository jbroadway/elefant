<?php

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$wp = new Webpage ($_POST);

$page->id = $_POST['id'];
$page->title = $_POST['title'];
$page->menu_title = (! empty ($_POST['menu_title'])) ? $_POST['menu_title'] : $_POST['title'];
$page->window_title = (! empty ($_POST['window_title'])) ? $_POST['window_title'] : $_POST['title'];
$page->description = $_POST['description'];
$page->keywords = $_POST['keywords'];
$page->layout = $_POST['layout'];
$page->head = '';

echo $tpl->run_includes ($_POST['body']);

?>